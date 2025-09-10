@extends('layouts.dashboard')

@section('title', 'รายละเอียดกิจกรรม')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('activities.index') }}">ประวัติกิจกรรม</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                รายละเอียดกิจกรรม #{{ $activity->id }}
            </li>
        </ol>
    </nav>
    <a href="{{ route('activities.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>กลับ
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- ข้อมูลหลัก -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="{{ $activity->activity_icon }} me-2"></i>
                    รายละเอียดกิจกรรม
                </h6>
                @if($activity->is_suspicious)
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle me-1"></i>น่าสงสัย
                    </span>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>รหัสกิจกรรม:</strong></div>
                    <div class="col-sm-9">#{{ $activity->id }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>ประเภทกิจกรรม:</strong></div>
                    <div class="col-sm-9">
                        <span class="d-flex align-items-center">
                            <i class="{{ $activity->activity_icon }} me-2"></i>
                            {{ $activity->friendly_description }}
                        </span>
                        <small class="text-muted">{{ $activity->activity_type }}</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>คำอธิบาย:</strong></div>
                    <div class="col-sm-9">{{ $activity->description }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>วันที่และเวลา:</strong></div>
                    <div class="col-sm-9">
                        <div>{{ $activity->created_at->format('d/m/Y H:i:s') }}</div>
                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                
                @if($activity->user)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>ผู้ใช้:</strong></div>
                    <div class="col-sm-9">
                        <div class="d-flex align-items-center">
                            <img src="{{ $activity->user->profile_image ? asset('storage/avatars/'.$activity->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($activity->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                 alt="{{ $activity->user->name }}" 
                                 class="rounded-circle me-3" style="width: 50px; height: 50px;">
                            <div>
                                <div class="fw-bold">{{ $activity->user->name }}</div>
                                <div class="text-muted">{{ $activity->user->email }}</div>
                                <span class="badge bg-secondary">{{ ucfirst($activity->user->role) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($activity->ip_address)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>IP Address:</strong></div>
                    <div class="col-sm-9">
                        <code>{{ $activity->ip_address }}</code>
                        @if($activity->location)
                            <br><small class="text-muted">{{ $activity->location }}</small>
                        @endif
                    </div>
                </div>
                @endif
                
                @if($activity->user_agent)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>เบราว์เซอร์:</strong></div>
                    <div class="col-sm-9">
                        <div>{{ $activity->browser_info }}</div>
                        <details class="mt-2">
                            <summary class="text-muted small" style="cursor: pointer;">User Agent เต็ม</summary>
                            <code class="small">{{ $activity->user_agent }}</code>
                        </details>
                    </div>
                </div>
                @endif
                
                @if($activity->url)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>URL:</strong></div>
                    <div class="col-sm-9">
                        <code>{{ $activity->method ?? 'GET' }} {{ $activity->url }}</code>
                    </div>
                </div>
                @endif
                
                @if($activity->response_status)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>HTTP Status:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-{{ $activity->response_status >= 200 && $activity->response_status < 300 ? 'success' : ($activity->response_status >= 400 ? 'danger' : 'warning') }}">
                            {{ $activity->response_status }}
                        </span>
                        @if($activity->response_time)
                            <small class="text-muted ms-2">{{ $activity->response_time }}ms</small>
                        @endif
                    </div>
                </div>
                @endif
                
                @if($activity->session_id)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Session ID:</strong></div>
                    <div class="col-sm-9">
                        <code class="small">{{ Str::limit($activity->session_id, 30) }}</code>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- ข้อมูลเพิ่มเติม -->
        @if($activity->payload || $activity->properties)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-code-square me-2"></i>ข้อมูลเพิ่มเติม
                </h6>
            </div>
            <div class="card-body">
                @if($activity->payload)
                <div class="mb-4">
                    <h6 class="text-secondary">Payload:</h6>
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
                @endif
                
                @if($activity->properties)
                <div class="mb-4">
                    <h6 class="text-secondary">Properties:</h6>
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <!-- การจัดการ -->
        @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-gear me-2"></i>การจัดการ
                </h6>
            </div>
            <div class="card-body">
                @if($activity->is_suspicious)
                    <button type="button" class="btn btn-success btn-sm mb-2" 
                            onclick="unmarkSuspicious({{ $activity->id }})">
                        <i class="bi bi-check-circle me-1"></i>ยกเลิกการทำเครื่องหมาย
                    </button>
                @else
                    <button type="button" class="btn btn-warning btn-sm mb-2" 
                            onclick="markSuspicious({{ $activity->id }})">
                        <i class="bi bi-exclamation-triangle me-1"></i>ทำเครื่องหมายว่าน่าสงสัย
                    </button>
                @endif
                
                <button type="button" class="btn btn-info btn-sm mb-2" 
                        onclick="exportSingleActivity({{ $activity->id }})">
                    <i class="bi bi-download me-1"></i>ส่งออกข้อมูล
                </button>
                
                @if($activity->ip_address)
                <button type="button" class="btn btn-secondary btn-sm mb-2" 
                        onclick="searchByIP('{{ $activity->ip_address }}')">
                    <i class="bi bi-search me-1"></i>ค้นหากิจกรรมจาก IP นี้
                </button>
                @endif
            </div>
        </div>
        @endif
        
        <!-- ข้อมูลสรุป -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-info-circle me-2"></i>ข้อมูลสรุป
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="h6 mb-0 text-primary">
                            @if($activity->user)
                                {{ $activity->user->activityLogs()->count() }}
                            @else
                                -
                            @endif
                        </div>
                        <small class="text-muted">กิจกรรมทั้งหมด<br>ของผู้ใช้นี้</small>
                    </div>
                    <div class="col-6">
                        <div class="h6 mb-0 text-warning">
                            @if($activity->ip_address)
                                {{ App\Models\ActivityLog::where('ip_address', $activity->ip_address)->count() }}
                            @else
                                -
                            @endif
                        </div>
                        <small class="text-muted">กิจกรรมจาก<br>IP นี้</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- กิจกรรมที่เกี่ยวข้อง -->
        @if($activity->user)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-clock-history me-2"></i>กิจกรรมล่าสุดของผู้ใช้นี้
                </h6>
            </div>
            <div class="card-body">
                @php
                    $recentActivities = $activity->user->activityLogs()
                        ->where('id', '!=', $activity->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse($recentActivities as $recentActivity)
                <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                    <i class="{{ $recentActivity->activity_icon }} me-2 mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold small">{{ $recentActivity->friendly_description }}</div>
                        <div class="text-muted small">
                            {{ $recentActivity->created_at->diffForHumans() }}
                        </div>
                        @if($recentActivity->is_suspicious)
                            <span class="badge bg-warning text-dark small">น่าสงสัย</span>
                        @endif
                    </div>
                    <a href="{{ route('activities.show', $recentActivity->id) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
                @empty
                <div class="text-center text-muted">
                    <i class="bi bi-inbox"></i>
                    <div>ไม่พบกิจกรรมอื่น</div>
                </div>
                @endforelse
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function markSuspicious(activityId) {
    const reason = prompt('กรุณาระบุเหตุผลที่ทำเครื่องหมายว่าน่าสงสัย:');
    if (reason === null) return;
    
    fetch(`/activities/${activityId}/mark-suspicious`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถดำเนินการได้'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

function unmarkSuspicious(activityId) {
    if (!confirm('คุณต้องการยกเลิกการทำเครื่องหมายว่าน่าสงสัยใช่หรือไม่?')) return;
    
    fetch(`/activities/${activityId}/unmark-suspicious`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถดำเนินการได้'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

function exportSingleActivity(activityId) {
    window.location.href = `/activities/export?activity_id=${activityId}`;
}

function searchByIP(ipAddress) {
    window.location.href = `{{ route('activities.index') }}?ip_address=${encodeURIComponent(ipAddress)}`;
}
</script>
@endpush
