<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAccepted;
use App\Events\CallDeclined;
use App\Events\CallEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Peterujah\Agora\Agora;
use Peterujah\Agora\User as AgoraUser;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;

class CallController extends Controller
{
    /**
     * Resolve authenticated user from Session, Sanctum, Bearer token, or request ID
     */
    private function resolveCaller(Request )
    {
         = ->user() ?: auth('sanctum')->user() ?: Auth::user();
        if (!) {
             = ->bearerToken();
            if () {
                 = \Laravel\Sanctum\PersonalAccessToken::findToken();
                if ( && ->tokenable) {
                     = ->tokenable;
                }
            }
        }
        if (! && ->filled('caller_id')) {
             = User::find(->caller_id);
        }
        if (! && ->filled('user_id')) {
             = User::find(->user_id);
        }
        if () {
            auth()->setUser();
            auth('sanctum')->setUser();
        }
        return ;
    }

    /**
     * Initiate a call request.
     */
    public function initiateCall(Request )
    {
         = (int) (->receiver_id ?? ->receiverId ?? ->input('receiver_id') ?? ->input('receiverId'));

        if (! || !User::where('id', )->exists()) {
            return response()->json(['status' => 'error', 'message' => 'receiver_id is invalid or missing'], 422);
        }

         = ->resolveCaller();
        if (!) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

         = (int)->id;

        if ( === ) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك الاتصال بنفسك.'], 400);
        }

         = User::find();

        if (! || !->is_active) {
            return response()->json(['status' => 'error', 'message' => 'المستخدم الآخر غير نشط حالياً.'], 404);
        }

         = env('AGORA_APP_ID');
         = env('AGORA_APP_CERTIFICATE');

        if (! || !) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

        // Generate a unique channel name using current timestamp to avoid collisions
         = 'call_' . min(, ) . '_' . max(, ) . '_' . time();

        try {
             = time() + 3600; // 1 hour expiration

             = new Agora(, );
            ->setExpiration();

            // Generate token for Caller (User A) using caller ID as the Agora UID
             = (new AgoraUser())
                ->setChannel()
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire();
             = RtcToken::buildTokenWithUid(, );

            // Generate token for Receiver (User B) using receiver ID as the Agora UID
             = (new AgoraUser())
                ->setChannel()
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire();
             = RtcToken::buildTokenWithUid(, );

             = trim((->first_name ?? '') . ' ' . (->last_name ?? ''));
            if (empty())  = ->name ?? 'مستخدم';
             = ->avatar_url ?? ->profile_picture ?? null;

            // Send FCM Call Notification to Receiver
            try {
                app(\App\Services\FcmNotificationService::class)->sendChatNotification(
                    ,
                    ,
                    '📞 مكالمة صوتية واردة...',
                    (int),
                    ,
                    ->token ?? null
                );
            } catch (\Throwable ) {}

            // Broadcast the call initiation event to the receiver
            broadcast(new CallInitiated(
                ,
                ,
                ,
                ,
                ,
                
            ));

            return response()->json([
                'status' => 'success',
                'channel_name' => ,
                'token' => ,
                'caller_id' => ,
                'receiver_id' => ,
                'receiver_name' => trim((->first_name ?? '') . ' ' . (->last_name ?? '')),
                'receiver_avatar' => ->avatar_url ?? ->profile_picture ?? null,
                'agora_app_id' => 
            ]);
        } catch (\Exception ) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز الاتصال: ' . ->getMessage()], 500);
        }
    }

    /**
     * Accept an incoming call.
     */
    public function acceptCall(Request )
    {
         = ->resolveCaller();
         = (int) (->caller_id ?? ->callerId ?? ->input('caller_id') ?? ->input('callerId'));
         = ->channel_name ?? ->channelName ?? ->input('channel_name');
         =  ? (int)->id : (auth('sanctum')->id() ?? Auth::id());

        if ( && ) {
            broadcast(new CallAccepted(, , ));
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Decline an incoming call.
     */
    public function declineCall(Request )
    {
         = ->resolveCaller();
         = (int) (->caller_id ?? ->callerId ?? ->input('caller_id') ?? ->input('callerId'));
         =  ? (int)->id : (auth('sanctum')->id() ?? Auth::id());

        if () {
            broadcast(new CallDeclined(, ));
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * End or cancel a call.
     */
    public function endCall(Request )
    {
         = ->resolveCaller();
         = (int) (->target_user_id ?? ->targetUserId ?? ->input('target_user_id') ?? ->input('targetUserId'));
         = ->channel_name ?? ->channelName ?? ->input('channel_name');
         =  ? (int)->id : (auth('sanctum')->id() ?? Auth::id());

        if (! &&  && str_starts_with(, 'call_')) {
             = explode('_', );
            if (count() >= 3) {
                 = (int) ;
                 = (int) ;
                 = ( === (int) ) ?  : ;
            }
        }

        if ( && ) {
            broadcast(new CallEnded(, ));
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Initiate a group call request.
     */
    public function initiateGroupCall(Request )
    {
        ->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

         = ->resolveCaller();
        if (!) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }
         = (int)->id;
         = (int) ->group_id;

         = \App\Models\Group::with(['members' => function() {
            ->where('is_active', 1);
        }])->find();

        if (!) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

         = ->members->contains('user_id', );
        if (!) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بالاتصال في هذه المجموعة.'], 403);
        }

         = env('AGORA_APP_ID');
         = env('AGORA_APP_CERTIFICATE');

        if (! || !) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

         = 'group_call_' .  . '_' . time();

        try {
             = time() + 3600;

             = new Agora(, );
            ->setExpiration();

             = (new AgoraUser())
                ->setChannel()
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire();
             = RtcToken::buildTokenWithUid(, );

             = trim((->first_name ?? '') . ' ' . (->last_name ?? ''));
            if (empty())  = ->name ?? 'مستخدم';
             = ->avatar_url ?? ->profile_picture ?? null;

            foreach (->members as ) {
                if ((int)->user_id !== (int)) {
                    broadcast(new \App\Events\GroupCallInitiated(
                        ,
                        ,
                        ,
                        ,
                        ->name,
                        ,
                        (int)->user_id
                    ));
                }
            }

            return response()->json([
                'status' => 'success',
                'channel_name' => ,
                'token' => ,
                'caller_id' => ,
                'group_id' => ,
                'group_name' => ->name,
                'agora_app_id' => 
            ]);
        } catch (\Exception ) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز الاتصال الجماعي: ' . ->getMessage()], 500);
        }
    }

    /**
     * Join an active group call (generate a token for the channel).
     */
    public function joinGroupCall(Request )
    {
        ->validate([
            'group_id' => 'required|exists:groups,id',
            'channel_name' => 'required|string',
        ]);

         = ->resolveCaller();
        if (!) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }
         = (int)->id;
         = (int) ->group_id;
         = ->channel_name;

         = \App\Models\Group::with(['members' => function() {
            ->where('is_active', 1);
        }])->find();

        if (! || !->members->contains('user_id', )) {
            return response()->json(['status' => 'error', 'message' => 'غير مصرح لك بالانضمام لهذه المكالمة.'], 403);
        }

         = env('AGORA_APP_ID');
         = env('AGORA_APP_CERTIFICATE');

        try {
             = time() + 3600;
             = new Agora(, );
            ->setExpiration();

             = (new AgoraUser())
                ->setChannel()
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire();
             = RtcToken::buildTokenWithUid(, );

            return response()->json([
                'status' => 'success',
                'channel_name' => ,
                'token' => ,
                'caller_id' => ,
                'group_id' => ,
                'group_name' => ->name,
                'agora_app_id' => 
            ]);
        } catch (\Exception ) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز الانضمام للمكالمة: ' . ->getMessage()], 500);
        }
    }

    /**
     * Generate Agora Token dynamically for any channel.
     */
    public function generateToken(Request )
    {
         = ->resolveCaller();
         =  ? (int)->id : (int) (->user_id ?? ->userId ?? 0);
         = ->channel_name ?? ->channelName ?? ->input('channel_name') ?? 'wiselook_call';

         = env('AGORA_APP_ID');
         = env('AGORA_APP_CERTIFICATE');

        if (! || !) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

        try {
             = time() + 3600;
             = new Agora(, );
            ->setExpiration();

             = (new AgoraUser())
                ->setChannel()
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire();
             = RtcToken::buildTokenWithUid(, );

            return response()->json([
                'status' => 'success',
                'channel_name' => ,
                'token' => ,
                'user_id' => ,
                'agora_app_id' => 
            ]);
        } catch (\Exception ) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز Agora: ' . ->getMessage()], 500);
        }
    }
}
