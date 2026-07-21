<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SupportTicketController extends Controller
{
    /**
     * عرض قائمة جميع تذاكر الدعم الفني
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'messages'])->latest();

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // تصفية حسب الأولوية
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->get();

        // إحصائيات سريعة
        $openCount = SupportTicket::where('status', 'open')->count();
        $pendingCount = SupportTicket::where('status', 'pending')->count();
        $closedCount = SupportTicket::where('status', 'closed')->count();
        $totalCount = SupportTicket::count();

        return view('admin.support_tickets.index', compact('tickets', 'openCount', 'pendingCount', 'closedCount', 'totalCount'));
    }

    /**
     * عرض تفاصيل التذكرة والمحادثة
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.sender'])->findOrFail($id);
        return view('admin.support_tickets.show', compact('ticket'));
    }

    /**
     * إرسال رد من قبل الإدارة وتعديل الحالة/الأولوية
     */
    public function storeReply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,zip,txt|max:5120', // كحد أقصى 5 ميجابايت
            'status' => 'required|in:open,pending,closed',
            'priority' => 'required|in:low,medium,high',
        ]);

        // معالجة الملف المرفق
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // التأكد من وجود المجلد
            $destinationPath = public_path('upload/support_attachments');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }
            
            $file->move($destinationPath, $attachmentName);
        }

        // إضافة رسالة الرد
        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $request->message,
            'attachment_path' => $attachmentName ? 'upload/support_attachments/' . $attachmentName : null,
        ]);

        // تحديث التذكرة (الحالة والأولوية)
        $ticket->update([
            'status' => $request->status,
            'priority' => $request->priority,
        ]);

        $notification = [
            'message' => 'تم إرسال الرد وتحديث التذكرة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * تحديث حالة التذكرة مباشرة
     */
    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:open,pending,closed',
        ]);

        $ticket->update([
            'status' => $request->status,
        ]);

        $notification = [
            'message' => 'تم تحديث حالة التذكرة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * تحديث أولوية التذكرة مباشرة
     */
    public function updatePriority(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket->update([
            'priority' => $request->priority,
        ]);

        $notification = [
            'message' => 'تم تحديث درجة الأهمية بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * حذف التذكرة مع كافة رسائلها وملفاتها
     */
    public function destroy($id)
    {
        $ticket = SupportTicket::with('messages')->findOrFail($id);

        // حذف الملفات المرفقة من السيرفر
        foreach ($ticket->messages as $msg) {
            if ($msg->attachment_path && File::exists(public_path($msg->attachment_path))) {
                File::delete(public_path($msg->attachment_path));
            }
        }

        $ticket->delete();

        $notification = [
            'message' => 'تم حذف التذكرة وجميع رسائلها بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.support_tickets.index')->with($notification);
    }

    /**
     * شاشة فتح تذكرة جديدة لعضو محدد
     */
    public function create()
    {
        // استدعاء جميع الأعضاء العاديين
        $users = \App\Models\User::where('role', 'user')->latest()->get();
        return view('admin.support_tickets.create', compact('users'));
    }

    /**
     * حفظ التذكرة الجديدة التي أنشأتها الإدارة وإرسال أول رسالة
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,zip,txt|max:5120',
        ]);

        // إنشاء التذكرة
        $ticket = SupportTicket::create([
            'user_id' => $request->user_id,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 'pending', // نضعها كمعلقة لأن الإدارة هي من فتحتها وبانتظار رد المستخدم
        ]);

        // معالجة الملف المرفق
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('upload/support_attachments');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }
            
            $file->move($destinationPath, $attachmentName);
        }

        // إنشاء أول رسالة
        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $request->message,
            'attachment_path' => $attachmentName ? 'upload/support_attachments/' . $attachmentName : null,
        ]);

        $notification = [
            'message' => 'تم إنشاء التذكرة وإرسال الرسالة للمستخدم بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.support_tickets.show', $ticket->id)->with($notification);
    }
}
