@extends('layouts.dashboard')

@section('title', 'ดูข้อความ')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-envelope-open text-primary me-2"></i>
            ดูข้อความ
        </h1>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> กลับ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <!-- ข้อความหลัก -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-2">
                                {{ $message->subject }}
                                @if($message->message_type === 'system')
                                    <span class="badge bg-info ms-2">ข้อความระบบ</span>
                                @endif
                            </h5>
                            <div class="d-flex align-items-center text-muted">
                                <img class="rounded-circle me-2" 
                                     src="https://ui-avatars.com/api/?name={{ urlencode($message->sender->first_name . ' ' . $message->sender->last_name) }}&color=7F9CF5&background=EBF4FF" 
                                     alt="Avatar" style="width: 32px; height: 32px;">
                                <span class="me-3">
                                    <strong>จาก:</strong> {{ $message->sender->first_name }} {{ $message->sender->last_name }}
                                    <small class="text-muted">({{ ucfirst($message->sender->role) }})</small>
                                </span>
                                <span class="me-3">
                                    <strong>ถึง:</strong> {{ $message->recipient->first_name }} {{ $message->recipient->last_name }}
                                </span>
                                <span class="me-3">
                                    <strong>วันที่:</strong> {{ $message->created_at->format('d/m/Y H:i') }}
                                </span>
                                @php
                                    $priorityClass = [
                                        'low' => 'text-muted',
                                        'normal' => 'text-primary',
                                        'high' => 'text-warning',
                                        'urgent' => 'text-danger'
                                    ][$message->priority] ?? 'text-primary';
                                    
                                    $priorityText = [
                                        'low' => 'ต่ำ',
                                        'normal' => 'ปกติ',
                                        'high' => 'สูง',
                                        'urgent' => 'ด่วน'
                                    ][$message->priority] ?? 'ปกติ';
                                @endphp
                                <span class="{{ $priorityClass }}">
                                    <i class="bi bi-circle-fill"></i> {{ $priorityText }}
                                </span>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#replyModal">
                                <i class="bi bi-reply me-1"></i>ตอบกลับ
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteMessage({{ $message->id }})">
                                <i class="bi bi-trash me-1"></i>ลบ
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="message-content">
                        {!! nl2br(e($message->body)) !!}
                    </div>
                </div>
                @if($message->attachments && count($message->attachments) > 0)
                    <div class="card-footer">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-paperclip me-1"></i>ไฟล์แนบ:
                        </h6>
                        @foreach($message->attachments as $attachment)
                            <div class="d-inline-block me-3">
                                <a href="#" class="text-decoration-none">
                                    <i class="bi bi-file-earmark me-1"></i>{{ $attachment['name'] }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- ข้อความตอบกลับ -->
            @if($message->replies->count() > 0)
                <div class="mt-4">
                    <h5 class="mb-3">
                        <i class="bi bi-chat-dots me-2"></i>
                        การตอบกลับ ({{ $message->replies->count() }})
                    </h5>
                    @foreach($message->replies as $reply)
                        <div class="card shadow-sm mb-3">
                            <div class="card-header py-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($reply->sender->first_name . ' ' . $reply->sender->last_name) }}&color=7F9CF5&background=EBF4FF" 
                                         alt="Avatar" style="width: 24px; height: 24px;">
                                    <span class="fw-semibold">{{ $reply->sender->first_name }} {{ $reply->sender->last_name }}</span>
                                    <small class="text-muted ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                {!! nl2br(e($reply->body)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal สำหรับตอบกลับข้อความ -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">
                    <i class="bi bi-reply me-2"></i>ตอบกลับข้อความ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('messages.reply', $message) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ตอบกลับถึง:</label>
                        <div class="d-flex align-items-center">
                            <img class="rounded-circle me-2" 
                                 src="https://ui-avatars.com/api/?name={{ urlencode($message->sender->first_name . ' ' . $message->sender->last_name) }}&color=7F9CF5&background=EBF4FF" 
                                 alt="Avatar" style="width: 32px; height: 32px;">
                            <span>{{ $message->sender->first_name }} {{ $message->sender->last_name }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">หัวข้อ:</label>
                        <input type="text" class="form-control" value="Re: {{ $message->subject }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="reply_body" class="form-label fw-semibold">
                            ข้อความตอบกลับ <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reply_body" name="body" rows="6" 
                                  placeholder="พิมพ์ข้อความตอบกลับของคุณที่นี่..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>ส่งการตอบกลับ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.message-content {
    font-size: 14px;
    line-height: 1.6;
    color: #333;
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.message-content p {
    margin-bottom: 1rem;
}

.message-content:last-child {
    margin-bottom: 0;
}

.card-header .text-muted small {
    font-size: 0.85rem;
}

.reply-content {
    background-color: #f1f3f4;
    border-left: 3px solid #007bff;
    padding: 15px;
    border-radius: 0 8px 8px 0;
}

.hover-card {
    transition: transform 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-2px);
}
</style>
@endpush

@push('scripts')
<script>
function deleteMessage(messageId) {
    if (confirm('คุณต้องการลบข้อความนี้ใช่หรือไม่?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/messages/${messageId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// มาร์คข้อความเป็นอ่านแล้วถ้ายังไม่ได้อ่าน
@if($message->recipient_id === auth()->id() && !$message->isRead())
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("messages.mark-read", $message) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).catch(error => console.error('Error marking message as read:', error));
});
@endif

// ตัวนับตัวอักษรในการตอบกลับ
document.getElementById('reply_body').addEventListener('input', function() {
    const maxLength = 5000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let counter = document.getElementById('reply-char-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'reply-char-counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.innerHTML = `${currentLength}/${maxLength} ตัวอักษร`;
    
    if (remaining < 100) {
        counter.className = 'form-text text-end text-warning';
    } else if (remaining < 0) {
        counter.className = 'form-text text-end text-danger';
    } else {
        counter.className = 'form-text text-end text-muted';
    }
});
</script>
@endpush
@endsection
