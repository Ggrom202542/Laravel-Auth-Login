@extends('layouts.dashboard')

@section('title', 'ข้อความ')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-chat-dots text-primary me-2"></i>
            ข้อความ
        </h1>
        <a href="{{ route('messages.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> เขียนข้อความใหม่
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
            <div class="card shadow">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'inbox' ? 'active' : '' }}" 
                                       href="{{ route('messages.index', ['tab' => 'inbox']) }}">
                                        <i class="bi bi-inbox me-1"></i>
                                        กล่องข้อความ
                                        @if($unreadCount > 0)
                                            <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'sent' ? 'active' : '' }}" 
                                       href="{{ route('messages.index', ['tab' => 'sent']) }}">
                                        <i class="bi bi-send me-1"></i>
                                        ส่งแล้ว
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'unread' ? 'active' : '' }}" 
                                       href="{{ route('messages.index', ['tab' => 'unread']) }}">
                                        <i class="bi bi-envelope me-1"></i>
                                        ยังไม่ได้อ่าน
                                        @if($unreadCount > 0)
                                            <span class="badge bg-warning text-dark ms-1">{{ $unreadCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @if($tab === 'inbox' && $unreadCount > 0)
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                                <i class="bi bi-check2-all me-1"></i>
                                ทำเครื่องหมายอ่านทั้งหมด
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">สถานะ</th>
                                        <th>{{ $tab === 'sent' ? 'ผู้รับ' : 'ผู้ส่ง' }}</th>
                                        <th>หัวข้อ</th>
                                        <th width="150">ความสำคัญ</th>
                                        <th width="150">วันที่</th>
                                        <th width="100">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr class="{{ !$message->isRead() && $tab !== 'sent' ? 'table-light fw-bold' : '' }}">
                                            <td>
                                                @if($tab !== 'sent')
                                                    @if($message->isRead())
                                                        <i class="bi bi-envelope-open text-muted"></i>
                                                    @else
                                                        <i class="bi bi-envelope-fill text-primary"></i>
                                                    @endif
                                                @else
                                                    @if($message->replied_at)
                                                        <i class="bi bi-reply-fill text-success" title="มีการตอบกลับ"></i>
                                                    @else
                                                        <i class="bi bi-send text-muted"></i>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img class="rounded-circle me-2" 
                                                         src="https://ui-avatars.com/api/?name={{ urlencode(($tab === 'sent' ? $message->recipient : $message->sender)->first_name . ' ' . ($tab === 'sent' ? $message->recipient : $message->sender)->last_name) }}&color=7F9CF5&background=EBF4FF" 
                                                         alt="Avatar" style="width: 30px; height: 30px;">
                                                    <div>
                                                        <div class="fw-semibold">
                                                            {{ ($tab === 'sent' ? $message->recipient : $message->sender)->first_name }} 
                                                            {{ ($tab === 'sent' ? $message->recipient : $message->sender)->last_name }}
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ ucfirst(($tab === 'sent' ? $message->recipient : $message->sender)->role) }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('messages.show', $message) }}" class="text-decoration-none">
                                                    {{ $message->subject }}
                                                    @if($message->message_type === 'system')
                                                        <span class="badge bg-info ms-1">ระบบ</span>
                                                    @endif
                                                </a>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td style="text-align: center;">
                                                <small class="text-muted">
                                                    {{ $message->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('messages.show', $message) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="deleteMessage({{ $message->id }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $messages->appends(['tab' => $tab])->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">
                                @if($tab === 'inbox')
                                    ไม่มีข้อความในกล่องข้อความ
                                @elseif($tab === 'sent')
                                    ยังไม่มีข้อความที่ส่งออกไป
                                @else
                                    ไม่มีข้อความที่ยังไม่ได้อ่าน
                                @endif
                            </h5>
                            @if($tab === 'inbox')
                                <a href="{{ route('messages.create') }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-1"></i> เขียนข้อความใหม่
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAllAsRead() {
    if (confirm('ทำเครื่องหมายข้อความทั้งหมดเป็นอ่านแล้วใช่หรือไม่?')) {
        fetch('{{ route("messages.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการทำเครื่องหมายข้อความ');
        });
    }
}

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
</script>
@endpush
@endsection
