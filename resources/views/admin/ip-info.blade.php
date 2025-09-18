@extends('layouts.dashboard')

@section('title', 'ข้อมูล IP Address')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- การแจ้งเตือน -->
            @if($ipInfo['is_development'] && $ipInfo['is_private_ip'])
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    โหมดการพัฒนา (Development Mode)
                </h5>
                <p class="mb-2">
                    <strong>ปัญหา:</strong> IP Address ที่แสดงเป็น Private/Local IP ({{ $ipInfo['real_ip'] }}) 
                    ซึ่งไม่ใช่ IP ที่แท้จริงของผู้ใช้งาน
                </p>
                <p class="mb-2">
                    <strong>ผลกระทบ:</strong> หากมีการบล็อค IP นี้ จะส่งผลต่อการใช้งานของเว็บไซต์ทั้งหมด
                </p>
                <p class="mb-0">
                    <strong>แนะนำ:</strong> ใช้ข้อมูล Mock IP สำหรับการทดสอบ หรือปรับการตั้งค่า Proxy/Load Balancer
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- ข้อมูล IP -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-globe2 me-2"></i>ข้อมูล IP Address ปัจจุบัน
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">IP Address ที่ตรวจพบ</h6>
                            <table class="table table-sm table-striped">
                                <tr>
                                    <td><strong>Real IP</strong></td>
                                    <td>
                                        <span class="badge {{ $ipInfo['is_private_ip'] ? 'bg-warning' : 'bg-success' }}">
                                            {{ $ipInfo['real_ip'] }}
                                        </span>
                                        @if($ipInfo['is_private_ip'])
                                            <small class="text-muted d-block">Private/Local IP</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Request IP</strong></td>
                                    <td><code>{{ $ipInfo['request_ip'] }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Environment</strong></td>
                                    <td>
                                        <span class="badge {{ $ipInfo['is_development'] ? 'bg-info' : 'bg-success' }}">
                                            {{ $ipInfo['is_development'] ? 'Development' : 'Production' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">HTTP Headers</h6>
                            <table class="table table-sm table-striped">
                                @foreach($ipInfo['headers'] as $header => $value)
                                <tr>
                                    <td><strong>{{ strtoupper(str_replace('_', '-', $header)) }}</strong></td>
                                    <td>
                                        @if($value)
                                            <code>{{ $value }}</code>
                                        @else
                                            <span class="text-muted">ไม่มีข้อมูล</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- การทดสอบ IP -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>การทดสอบระบบ IP Security
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p>ทดสอบการบล็อคและปลดบล็อค IP Address เพื่อตรวจสอบการทำงานของระบบ</p>
                            
                            <!-- ข้อมูลการทดสอบ -->
                            <div class="mb-3">
                                <label for="testIp" class="form-label">IP Address สำหรับทดสอบ</label>
                                <select class="form-select" id="testIp">
                                    <option value="{{ $ipInfo['real_ip'] }}">IP ปัจจุบัน ({{ $ipInfo['real_ip'] }})</option>
                                    <option value="1.1.1.1">1.1.1.1 (Cloudflare DNS)</option>
                                    <option value="8.8.8.8">8.8.8.8 (Google DNS)</option>
                                    <option value="203.144.144.144">203.144.144.144 (True Internet)</option>
                                    <option value="180.180.180.180">180.180.180.180 (AIS Thailand)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="blockReason" class="form-label">เหตุผลในการบล็อค</label>
                                <input type="text" class="form-control" id="blockReason" 
                                       placeholder="เช่น: Suspicious Activity, Multiple Failed Logins" 
                                       value="การทดสอบระบบ">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger" onclick="testBlockIp()">
                                    <i class="bi bi-shield-x me-1"></i>ทดสอบบล็อค IP
                                </button>
                                <button type="button" class="btn btn-success" onclick="testUnblockIp()">
                                    <i class="bi bi-shield-check me-1"></i>ทดสอบปลดบล็อค IP
                                </button>
                                <button type="button" class="btn btn-info" onclick="checkIpStatus()">
                                    <i class="bi bi-search me-1"></i>ตรวจสอบสถานะ IP
                                </button>
                                <button type="button" class="btn btn-warning" onclick="getIpInfo()">
                                    <i class="bi bi-info-circle me-1"></i>ข้อมูลเพิ่มเติม
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ผลลัพธ์การทดสอบ -->
                    <div id="testResults" class="mt-4" style="display: none;">
                        <h6>ผลลัพธ์การทดสอบ:</h6>
                        <div id="testOutput" class="alert alert-info">
                            <!-- Results will be shown here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- รายการ IP ที่ถูกบล็อค -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-x me-2"></i>IP Addresses ที่ถูกบล็อค
                    </h4>
                </div>
                <div class="card-body">
                    @if($blockedIps->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>IP Address</th>
                                        <th>ประเภท</th>
                                        <th>เหตุผล</th>
                                        <th>วันที่บล็อค</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blockedIps as $ip)
                                    <tr>
                                        <td><code>{{ $ip->ip_address }}</code></td>
                                        <td>
                                            <span class="badge bg-{{ $ip->type === 'blacklist' ? 'danger' : 'success' }}">
                                                {{ $ip->type === 'blacklist' ? 'Blacklist' : 'Whitelist' }}
                                            </span>
                                        </td>
                                        <td>{{ $ip->reason ?: 'ไม่ระบุ' }}</td>
                                        <td>{{ $ip->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="unblockIp('{{ $ip->ip_address }}', {{ $ip->id }})">
                                                <i class="bi bi-unlock me-1"></i>ปลดบล็อค
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check fa-3x text-success mb-3"></i>
                            <p class="text-muted">ไม่มี IP Address ที่ถูกบล็อคในขณะนี้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showResults(message, type = 'info') {
    const resultsDiv = document.getElementById('testResults');
    const outputDiv = document.getElementById('testOutput');
    
    outputDiv.className = `alert alert-${type}`;
    outputDiv.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'x-circle' : 'info-circle'} me-2 mt-1"></i>
            <div>${message}</div>
        </div>
    `;
    resultsDiv.style.display = 'block';
    
    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function testBlockIp() {
    const ip = document.getElementById('testIp').value;
    const reason = document.getElementById('blockReason').value;
    
    if (!ip) {
        showResults('กรุณาเลือก IP Address สำหรับทดสอบ', 'warning');
        return;
    }
    
    fetch('{{ route("admin.ip-restrictions.block") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ip_address: ip,
            reason: reason || 'การทดสอบระบบ',
            description: 'ทดสอบการบล็อค IP จากหน้า IP Information'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResults(`
                <strong>บล็อค IP สำเร็จ!</strong><br>
                IP: <code>${ip}</code><br>
                เหตุผล: ${reason || 'การทดสอบระบบ'}<br>
                เวลา: ${new Date().toLocaleString('th-TH')}
            `, 'success');
        } else {
            showResults(`เกิดข้อผิดพลาด: ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResults('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
    });
}

function testUnblockIp() {
    const ip = document.getElementById('testIp').value;
    
    if (!ip) {
        showResults('กรุณาเลือก IP Address สำหรับทดสอบ', 'warning');
        return;
    }
    
    fetch('{{ route("admin.ip-restrictions.unblock") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ip_address: ip
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResults(`
                <strong>ปลดบล็อค IP สำเร็จ!</strong><br>
                IP: <code>${ip}</code><br>
                เวลา: ${new Date().toLocaleString('th-TH')}
            `, 'success');
        } else {
            showResults(`เกิดข้อผิดพลาด: ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResults('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
    });
}

function checkIpStatus() {
    const ip = document.getElementById('testIp').value;
    
    if (!ip) {
        showResults('กรุณาเลือก IP Address สำหรับทดสอบ', 'warning');
        return;
    }
    
    fetch(`{{ route("admin.ip-restrictions.check") }}?ip=${encodeURIComponent(ip)}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const status = data.data;
            showResults(`
                <strong>สถานะ IP: <code>${ip}</code></strong><br>
                ถูกบล็อค: ${status.is_blocked ? '<span class="badge bg-danger">ใช่</span>' : '<span class="badge bg-success">ไม่</span>'}<br>
                อยู่ใน Whitelist: ${status.is_whitelisted ? '<span class="badge bg-success">ใช่</span>' : '<span class="badge bg-secondary">ไม่</span>'}<br>
                ประเภท IP: ${status.is_private ? 'Private/Local' : 'Public'}<br>
                ข้อมูลเพิ่มเติม: ${status.additional_info || 'ไม่มี'}
            `, 'info');
        } else {
            showResults(`เกิดข้อผิดพลาด: ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResults('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
    });
}

function getIpInfo() {
    showResults(`
        <strong>ข้อมูล IP ปัจจุบัน:</strong><br>
        Real IP: <code>{{ $ipInfo['real_ip'] }}</code><br>
        Request IP: <code>{{ $ipInfo['request_ip'] }}</code><br>
        Environment: {{ $ipInfo['is_development'] ? 'Development' : 'Production' }}<br>
        Is Private: {{ $ipInfo['is_private_ip'] ? 'Yes' : 'No' }}<br>
        User Agent: <small>{{ substr($ipInfo['user_agent'], 0, 100) }}...</small>
    `, 'info');
}

function unblockIp(ip, id) {
    if (confirm(`คุณต้องการปลดบล็อค IP: ${ip} หรือไม่?`)) {
        fetch(`{{ route("admin.ip-restrictions.destroy", ":id") }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
.table code {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.alert .badge {
    font-size: 0.75rem;
}

#testResults {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush