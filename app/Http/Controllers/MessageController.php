<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * แสดงหน้าข้อความหลัก
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'inbox');
        
        $query = Message::with(['sender', 'recipient']);
        
        switch ($tab) {
            case 'inbox':
                $query->received($user->id);
                break;
            case 'sent':
                $query->sent($user->id);
                break;
            case 'unread':
                $query->received($user->id)->unread();
                break;
            default:
                $query->received($user->id);
        }
        
        $messages = $query->latest()->paginate(20);
        $unreadCount = Message::getUnreadCountForUser($user->id);
        
        return view('messages.index', compact('messages', 'unreadCount', 'tab'));
    }

    /**
     * แสดงฟอร์มเขียนข้อความใหม่
     */
    public function create()
    {
        // รับรายชื่อผู้ใช้ทั้งหมดยกเว้นตัวเอง
        $users = User::where('id', '!=', Auth::id())
                    ->where('status', 'active')
                    ->orderBy('first_name')
                    ->get(['id', 'first_name', 'last_name', 'role']);
        
        return view('messages.create', compact('users'));
    }

    /**
     * บันทึกข้อความใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'priority' => $request->priority,
            'message_type' => 'user'
        ]);

        return redirect()->route('messages.index')
                         ->with('success', 'ส่งข้อความเรียบร้อยแล้ว');
    }

    /**
     * แสดงข้อความ
     */
    public function show(Message $message)
    {
        $user = Auth::user();
        
        // ตรวจสอบสิทธิ์การเข้าถึงข้อความ
        if ($message->recipient_id !== $user->id && $message->sender_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงข้อความนี้');
        }
        
        // ถ้าเป็นผู้รับและยังไม่ได้อ่าน ให้มาร์คเป็นอ่านแล้ว
        if ($message->recipient_id === $user->id && !$message->isRead()) {
            $message->markAsRead();
        }
        
        // โหลดข้อความที่เกี่ยวข้อง (การตอบกลับ)
        $message->load(['sender', 'recipient', 'replies.sender']);
        
        return view('messages.show', compact('message'));
    }

    /**
     * ตอบกลับข้อความ
     */
    public function reply(Request $request, Message $message)
    {
        $user = Auth::user();
        
        // ตรวจสอบสิทธิ์
        if ($message->recipient_id !== $user->id && $message->sender_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ตอบกลับข้อความนี้');
        }
        
        $request->validate([
            'body' => 'required|string'
        ]);

        // สร้างข้อความตอบกลับ
        $reply = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $message->sender_id === $user->id ? $message->recipient_id : $message->sender_id,
            'subject' => 'Re: ' . $message->subject,
            'body' => $request->body,
            'parent_id' => $message->id,
            'priority' => $message->priority,
            'message_type' => 'user'
        ]);

        // อัปเดตเวลาตอบกลับของข้อความต้นฉบับ
        $message->update(['replied_at' => now()]);

        return redirect()->route('messages.show', $message)
                         ->with('success', 'ตอบกลับข้อความเรียบร้อยแล้ว');
    }

    /**
     * ลบข้อความ
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();
        
        // ตรวจสอบสิทธิ์
        if ($message->recipient_id !== $user->id && $message->sender_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ลบข้อความนี้');
        }
        
        $message->delete();
        
        return redirect()->route('messages.index')
                         ->with('success', 'ลบข้อความเรียบร้อยแล้ว');
    }

    /**
     * มาร์คข้อความเป็นอ่านแล้ว
     */
    public function markAsRead(Message $message)
    {
        if ($message->recipient_id === Auth::id()) {
            $message->markAsRead();
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * มาร์คข้อความทั้งหมดเป็นอ่านแล้ว
     */
    public function markAllAsRead()
    {
        Message::received(Auth::id())
               ->unread()
               ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * API สำหรับ dropdown - รับข้อความล่าสุด
     */
    public function getRecentMessages()
    {
        $user = Auth::user();
        $messages = Message::getRecentMessagesForUser($user->id, 5);
        $unreadCount = Message::getUnreadCountForUser($user->id);
        
        return response()->json([
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'sender' => $message->sender->first_name . ' ' . $message->sender->last_name,
                    'sender_role' => ucfirst($message->sender->role),
                    'created_at' => $message->created_at->diffForHumans(),
                    'is_read' => $message->isRead(),
                    'priority' => $message->priority,
                    'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->first_name . ' ' . $message->sender->last_name) . '&color=7F9CF5&background=EBF4FF'
                ];
            }),
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * ส่งข้อความระบบ (สำหรับ Admin)
     */
    public function sendSystemMessage(Request $request)
    {
        $user = Auth::user();
        
        // ตรวจสอบสิทธิ์ Admin
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'คุณไม่มีสิทธิ์ส่งข้อความระบบ');
        }
        
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent'
        ]);

        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $request->recipient_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'priority' => $request->priority,
            'message_type' => 'system'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ส่งข้อความระบบเรียบร้อยแล้ว'
        ]);
    }
}
