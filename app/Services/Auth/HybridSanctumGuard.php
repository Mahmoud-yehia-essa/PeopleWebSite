<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Events\TokenAuthenticated;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class HybridSanctumGuard
{
    /**
     * Create a new guard instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @param  int|null  $expiration
     * @param  string|null  $provider
     * @param  bool  $trackLastUsedAt
     */
    public function __construct(
        protected AuthFactory $auth,
        protected $expiration = null,
        protected $provider = null,
        protected $trackLastUsedAt = true
    ) {}

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        // 1. Check Session/Web Guard
        foreach (Arr::wrap(config('sanctum.guard', 'web')) as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                return $this->supportsTokens($user)
                    ? $user->withAccessToken(new SafeTransientToken)
                    : $user;
            }
        }

        // 2. Check Sanctum Bearer Token (For new users and authenticated clients)
        if ($token = $this->getTokenFromRequest($request)) {
            $model = Sanctum::$personalAccessTokenModel;
            $accessToken = $model::findToken($token);

            if ($accessToken && $this->isValidAccessToken($accessToken) && $this->supportsTokens($accessToken->tokenable)) {
                $tokenable = $accessToken->tokenable->withAccessToken($accessToken);
                event(new TokenAuthenticated($accessToken));

                if ($this->trackLastUsedAt) {
                    $this->updateLastUsedAt($accessToken);
                }

                return $tokenable;
            }

            // If token is a pure numeric ID (legacy client format: Bearer 127)
            if (is_numeric($token) && (int)$token > 0) {
                $legacyUser = User::where('id', (int)$token)->where('is_active', 1)->first();
                if ($legacyUser) {
                    return $legacyUser->withAccessToken(new SafeTransientToken);
                }
            }

            // Check users.token column for legacy session/device tokens
            if (is_string($token) && strlen($token) > 10) {
                $legacyUser = User::where('token', $token)->where('is_active', 1)->latest('last_login')->first();
                if ($legacyUser) {
                    return $legacyUser->withAccessToken(new SafeTransientToken);
                }
            }
        }

        // 3. Fallback for Legacy / Old App Users (Requests without Bearer Token)
        return $this->resolveLegacyUser($request);
    }

    /**
     * Resolve legacy user from request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\User|null
     */
    public function resolveLegacyUser(Request $request): ?User
    {
        $userId = $request->header('X-User-Id')
            ?: $request->header('X-Auth-Id')
            ?: $request->header('userID')
            ?: $request->header('userId')
            ?: $request->header('user_id')
            ?: $request->input('user_id')
            ?: $request->input('userId')
            ?: $request->input('userID')
            ?: $request->input('sender_id')
            ?: $request->input('person_id')
            ?: $request->input('personID')
            ?: $request->input('author_id');

        // Check 'id' for self-profile routes or profile viewing
        if (!$userId) {
            $path = $request->path();
            if (
                str_contains($path, 'profile/') ||
                str_contains($path, 'logout') ||
                str_contains($path, 'delete_account') ||
                str_contains($path, 'change_password') ||
                str_contains($path, 'change_profile') ||
                (str_contains($path, 'users.php') && ($request->has('profile_id') || $request->has('id') || $request->has('user_id') || $request->has('userID')))
            ) {
                $userId = $request->input('id');
            }
        }

        if ($userId && is_numeric($userId) && (int)$userId > 0) {
            $user = User::where('id', (int)$userId)->where('is_active', 1)->first();
            if ($user) {
                return $user->withAccessToken(new SafeTransientToken);
            }
        }

        // Check if token was provided in request body
        $rawToken = $request->input('token') ?: $request->input('access_token');
        if ($rawToken && is_string($rawToken) && strlen($rawToken) > 10) {
            $model = Sanctum::$personalAccessTokenModel;
            $accessToken = $model::findToken($rawToken);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable->withAccessToken($accessToken);
            }

            $userByToken = User::where('token', $rawToken)->where('is_active', 1)->latest('last_login')->first();
            if ($userByToken) {
                return $userByToken->withAccessToken(new SafeTransientToken);
            }
        }

        return null;
    }

    /**
     * Determine if the tokenable model supports API tokens.
     *
     * @param  mixed  $tokenable
     * @return bool
     */
    protected function supportsTokens($tokenable = null): bool
    {
        return $tokenable && in_array(
            \Laravel\Sanctum\HasApiTokens::class,
            class_uses_recursive(get_class($tokenable))
        );
    }

    /**
     * Get the token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        if (is_callable(Sanctum::$accessTokenRetrievalCallback)) {
            return (string) (Sanctum::$accessTokenRetrievalCallback)($request);
        }

        $token = $request->bearerToken();
        return $this->isValidBearerToken($token) ? $token : null;
    }

    /**
     * Determine if the bearer token is in the correct format.
     *
     * @param  string|null  $token
     * @return bool
     */
    protected function isValidBearerToken(?string $token = null): bool
    {
        if (!is_null($token) && str_contains($token, '|')) {
            $model = new Sanctum::$personalAccessTokenModel;
            if ($model->getKeyType() === 'int') {
                [$id, $token] = explode('|', $token, 2);
                return ctype_digit($id) && !empty($token);
            }
        }

        return !empty($token);
    }

    /**
     * Determine if the provided access token is valid.
     *
     * @param  mixed  $accessToken
     * @return bool
     */
    protected function isValidAccessToken($accessToken): bool
    {
        if (!$accessToken) {
            return false;
        }

        $isValid = (!$this->expiration || $accessToken->created_at->gt(now()->subMinutes($this->expiration)))
            && (!$accessToken->expires_at || !$accessToken->expires_at->isPast());

        if (is_callable(Sanctum::$accessTokenAuthenticationCallback)) {
            $isValid = (bool) (Sanctum::$accessTokenAuthenticationCallback)($accessToken, $isValid);
        }

        return $isValid;
    }

    /**
     * Store the time the token was last used.
     *
     * @param  \Laravel\Sanctum\PersonalAccessToken  $accessToken
     * @return void
     */
    protected function updateLastUsedAt($accessToken): void
    {
        try {
            if (
                method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
                method_exists($accessToken->getConnection(), 'setRecordModificationState')
            ) {
                $hasModifiedRecords = $accessToken->getConnection()->hasModifiedRecords();
                $accessToken->forceFill(['last_used_at' => now()])->save();
                $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
            } else {
                $accessToken->forceFill(['last_used_at' => now()])->save();
            }
        } catch (\Throwable $e) {
            // Ignore if token table update fails
        }
    }
}
