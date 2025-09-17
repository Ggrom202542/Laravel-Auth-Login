<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of user notifications.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => "ทำเครื่องหมายอ่านการแจ้งเตือน {$unreadCount} รายการแล้ว"
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบการแจ้งเตือน'
            ], 404);
        }

        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'ทำเครื่องหมายอ่านแล้ว'
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json([
            'unread_count' => $count
        ]);
    }
    
    /**
     * Test notification system (development only).
     */
    public function testNotification(): JsonResponse
    {
        if (!app()->environment('local')) {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Create test notification manually
        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => [
                'title' => 'ทดสอบระบบการแจ้งเตือน',
                'message' => 'การแจ้งเตือนนี้ถูกส่งเพื่อทดสอบระบบ',
                'type' => 'test'
            ],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'ส่งการแจ้งเตือนทดสอบแล้ว',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Get unread security notifications for the authenticated user.
     */
    public function getUnreadSecurityNotifications(): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            
            // Get unread security-related notifications
            $securityNotifications = $user->unreadNotifications()
                ->where('type', 'LIKE', '%Security%')
                ->orWhere('data->type', 'security')
                ->get();
            
            return response()->json([
                'success' => true,
                'notifications' => $securityNotifications,
                'count' => $securityNotifications->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถดึงข้อมูลการแจ้งเตือนความปลอดภัยได้',
                'notifications' => [],
                'count' => 0
            ]);
        }
    }
}
