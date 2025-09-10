<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'read_at',
        'replied_at',
        'priority',
        'message_type',
        'parent_id',
        'attachments'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'attachments' => 'array'
    ];

    /**
     * ผู้ส่งข้อความ
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * ผู้รับข้อความ
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * ข้อความต้นฉบับ (สำหรับการตอบกลับ)
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * ข้อความตอบกลับ
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    /**
     * Scope สำหรับข้อความที่ยังไม่ได้อ่าน
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope สำหรับข้อความที่อ่านแล้ว
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope สำหรับข้อความที่ส่งมาหาผู้ใช้
     */
    public function scopeReceived($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    /**
     * Scope สำหรับข้อความที่ผู้ใช้ส่งออกไป
     */
    public function scopeSent($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * ตรวจสอบว่าข้อความถูกอ่านแล้วหรือยัง
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * ตรวจสอบว่าเป็นข้อความสำคัญหรือไม่
     */
    public function isHighPriority()
    {
        return $this->priority === 'high';
    }

    /**
     * ตรวจสอบว่าเป็นข้อความด่วนหรือไม่
     */
    public function isUrgent()
    {
        return $this->priority === 'urgent';
    }

    /**
     * มาร์คข้อความเป็นอ่านแล้ว
     */
    public function markAsRead()
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * รับข้อความใหม่สำหรับผู้ใช้
     */
    public static function getUnreadCountForUser($userId)
    {
        return static::received($userId)->unread()->count();
    }

    /**
     * รับข้อความล่าสุดสำหรับผู้ใช้
     */
    public static function getRecentMessagesForUser($userId, $limit = 5)
    {
        return static::received($userId)
            ->with(['sender'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
