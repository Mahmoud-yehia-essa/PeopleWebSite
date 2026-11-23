<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountDeletionController extends Controller
{
    /**
     * Public account & data deletion page (Google Play Data Safety compliant).
     */
    public function show(): View
    {
        return view('frontend.wiselook.pages.delete_account');
    }

    /**
     * Handle public web account deletion request (for users without the app installed).
     */
    public function submitRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier'       => ['required', 'string', 'max:190'],
            'reason'           => ['nullable', 'string', 'max:1000'],
            'confirm_deletion' => ['accepted'],
        ], [
            'identifier.required'       => 'يرجى إدخال البريد الإلكتروني أو رقم الهاتف / Please enter your email address or phone number.',
            'confirm_deletion.accepted' => 'يرجى تأكيد الموافقة على حذف الحساب / Please confirm that you understand account deletion is permanent.',
        ]);

        $identifier = trim($validated['identifier']);
        $reason = isset($validated['reason']) ? trim((string)$validated['reason']) : null;
        $referenceCode = 'DEL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        $matchedUser = Auth::user();
        if (!$matchedUser) {
            $matchedUser = User::where('email', $identifier)
                ->orWhere('phone_number', $identifier)
                ->first();
        }

        // Persist inside support_tickets when a matching user exists (without disclosing existence to caller)
        if ($matchedUser && Schema::hasTable('support_tickets') && Schema::hasTable('support_ticket_messages')) {
            try {
                $ticket = SupportTicket::create([
                    'user_id'  => $matchedUser->id,
                    'subject'  => 'طلب حذف حساب / Account Deletion (' . $referenceCode . ')',
                    'priority' => 'high',
                    'status'   => 'open',
                ]);

                $messageLines = [
                    'تم استلام طلب حذف الحساب من صفحة الويب العامة.',
                    'Reference: ' . $referenceCode,
                    'Identifier: ' . $identifier,
                ];
                if (!empty($reason)) {
                    $messageLines[] = 'Reason: ' . $reason;
                }

                SupportTicketMessage::create([
                    'ticket_id'   => $ticket->id,
                    'sender_id'   => $matchedUser->id,
                    'sender_type' => 'user',
                    'message'     => implode("\n", $messageLines),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Could not store support ticket for deletion request: ' . $e->getMessage());
            }
        }

        $logEntry = [
            'reference'  => $referenceCode,
            'identifier' => $identifier,
            'user_id'    => $matchedUser?->id,
            'reason'     => $reason,
            'ip'         => $request->ip(),
            'created_at' => now()->toIso8601String(),
        ];

        Log::info('WiseLook public account deletion request received', $logEntry);
        try {
            file_put_contents(
                storage_path('logs/account_deletion_requests.log'),
                json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } catch (\Throwable $e) {
            Log::warning('Failed writing account_deletion_requests.log: ' . $e->getMessage());
        }

        return redirect()
            ->route('account.deletion')
            ->with('deletion_request_submitted', true)
            ->with('reference_code', $referenceCode);
    }

    /**
     * Allow an already authenticated web user to permanently delete their own account immediately.
     */
    public function executeAuthenticated(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_instant' => ['accepted'],
        ], [
            'confirm_instant.accepted' => 'يرجى تأكيد الحذف النهائي للحساب / Please confirm permanent deletion.',
        ]);

        $user = $request->user();
        if (!$user) {
            return redirect()->route('account.deletion');
        }

        Log::info('WiseLook authenticated web user deleted own account', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('account.deletion')
            ->with('deleted_immediately', true);
    }
}
