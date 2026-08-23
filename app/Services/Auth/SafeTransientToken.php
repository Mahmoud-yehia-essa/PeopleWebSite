<?php

namespace App\Services\Auth;

use Laravel\Sanctum\TransientToken;

class SafeTransientToken extends TransientToken
{
    /**
     * Delete the token (dummy method to safely support ->delete() on transient tokens).
     *
     * @return bool
     */
    public function delete(): bool
    {
        return true;
    }
}
