@extends('layouts.dashboard')

@section('title', 'การแจ้งเตือน')

@section('content')
<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-bell me-2"></i>การแจ้งเตือน
        </h1>
        <div class="d-flex gap-2">
            @if($notifications->total() > 0)
                <button onclick="markAllNotificationsRead()" class="btn btn-primary">
                    <i class="bi bi-check2-all me-1"></i>ทำเครื่องหมายอ่านทั้งหมด
                </button>
            @endif
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">รายการแจ้งเตือน</h6>
                    <span class="badge bg-info">{{ $notifications->total() }} รายการ</span>
                </div>
                <div class="card-body p-0">
                    @forelse($notifications as $notification)
                        <div class="border-bottom p-3 {{ $notification->read_at ? '' : 'bg-light' }}">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    @php
                                        $type = $notification->data['type'] ?? 'default';
                                    @endphp
                                    <div class="icon-circle {{ $type == 'approval_override' ? 'bg-warning' : ($type == 'approval_escalation' ? 'bg-danger' : 'bg-info') }}">
                                        @if($type == 'approval_override')
                                            <i class="bi bi-arrow-repeat text-white"></i>
                                        @elseif($type == 'approval_escalation')
                                            <i class="bi bi-exclamation-triangle text-white"></i>
                                        @else
                                            <i class="bi bi-person-plus text-white"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">{{ $notification->data['message'] ?? 'การแจ้งเตือน' }}</h6>
                                        @if(!$notification->read_at)
                                            <span class="badge bg-primary">ใหม่</span>
                                        @endif
                                    </div>
                                    <p class="text-muted small mb-1">{{ $notification->created_at->format('d/m/Y H:i:s') }}</p>
                                    <p class="small text-gray-600">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">ไม่มีการแจ้งเตือน</h5>
                            <p class="text-muted">คุณจะได้รับการแจ้งเตือนเมื่อมีกิจกรรมสำคัญ</p>
                        </div>
                    @endforelse
                </div>
                
                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    
</div>

<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
function markAllNotificationsRead() {
    fetch('{{ route("notifications.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

@endsection
