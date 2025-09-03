<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark">
            <tr>
                <th>ผู้ใช้</th>
                <th>Session Info</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>กิจกรรมล่าสุด</th>
                <th>ระยะเวลา</th>
                <th>การดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $session)
            <tr class="session-row">
                <td class="user-info">
                    <div class="d-flex align-items-center">
                        @if($session->user->avatar)
                        <img src="{{ $session->user->avatar }}" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                        @else
                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <span class="text-white small">{{ strtoupper(substr($session->user->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        @endif
                        <div>
                            <div class="fw-bold">{{ $session->user->name ?? 'ไม่ระบุ' }}</div>
                            <small class="text-muted">{{ $session->user->email ?? 'ไม่ระบุ' }}</small>
                            <br>
                            <span class="badge bg-{{ 
                                $session->user->role == 'super_admin' ? 'danger' : 
                                ($session->user->role == 'admin' ? 'warning' : 'primary') 
                            }} session-status">
                                {{ $session->user->role ?? 'user' }}
                            </span>
                        </div>
                    </div>
                </td>
                
                <td class="session-info">
                    <div class="small">
                        <div><strong>Session ID:</strong></div>
                        <code class="text-muted">{{ substr($session->session_id ?? 'N/A', 0, 12) }}...</code>
                        <br>
                        <div class="mt-1">
                            @if($session->login_method)
                            <span class="badge bg-info">{{ $session->login_method }}</span>
                            @endif
                        </div>
                    </div>
                </td>
                
                <td>
                    <div class="small">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <code>{{ $session->ip_address ?? 'ไม่ระบุ' }}</code>
                        @if($session->country || $session->city)
                        <br>
                        <small class="text-muted">
                            {{ $session->city ? $session->city . ', ' : '' }}{{ $session->country ?? '' }}
                        </small>
                        @endif
                    </div>
                </td>
                
                <td>
                    <div class="text-truncate-custom small" title="{{ $session->user_agent ?? 'ไม่ระบุ' }}">
                        <i class="fas fa-desktop me-1"></i>
                        {{ $session->user_agent ? (strlen($session->user_agent) > 50 ? substr($session->user_agent, 0, 50) . '...' : $session->user_agent) : 'ไม่ระบุ' }}
                    </div>
                </td>
                
                <td class="activity-info">
                    <div class="small">
                        <div class="fw-bold">{{ $session->last_activity ? $session->last_activity->format('d/m/Y') : 'ไม่ระบุ' }}</div>
                        <div class="text-muted">{{ $session->last_activity ? $session->last_activity->format('H:i:s') : '' }}</div>
                        <div class="text-primary">{{ $session->last_activity ? $session->last_activity->diffForHumans() : '' }}</div>
                    </div>
                </td>
                
                <td>
                    <div class="small">
                        @php
                            $duration = $session->created_at && $session->last_activity 
                                ? $session->created_at->diffInMinutes($session->last_activity) 
                                : 0;
                            $hours = intval($duration / 60);
                            $minutes = $duration % 60;
                        @endphp
                        
                        @if($duration > 0)
                            <div class="text-info">
                                @if($hours > 0)
                                    {{ $hours }}h {{ $minutes }}m
                                @else
                                    {{ $minutes }}m
                                @endif
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                        
                        @if($session->created_at)
                        <div class="text-muted" style="font-size: 0.7rem;">
                            เริ่ม: {{ $session->created_at->format('H:i') }}
                        </div>
                        @endif
                    </div>
                </td>
                
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('super-admin.users.show', $session->user_id) }}" 
                           class="btn btn-sm btn-outline-primary" title="ดูข้อมูลผู้ใช้">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        @if($session->user->role !== 'super_admin' || auth()->user()->role === 'super_admin')
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger terminate-session-btn"
                                data-user-id="{{ $session->user_id }}"
                                data-user-name="{{ $session->user->name }}"
                                title="ยกเลิกเซสชันทั้งหมด">
                            <i class="fas fa-power-off"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <br>
                        ไม่มี Sessions ที่ใช้งานในขณะนี้
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($sessions->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3 px-3">
    <div class="text-muted small">
        แสดง {{ $sessions->firstItem() }} ถึง {{ $sessions->lastItem() }} 
        จากทั้งหมด {{ number_format($sessions->total()) }} รายการ
    </div>
    <div class="pagination-container">
        {{ $sessions->appends(request()->query())->links() }}
    </div>
</div>
@endif
