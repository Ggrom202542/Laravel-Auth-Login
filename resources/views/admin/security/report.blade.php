@extends('layouts.dashboard')

@section('title', 'รายงานความปลอดภัย')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                        รายงานความปลอดภัย
                    </h1>
                    <p class="text-muted mb-0">รายงานและสถิติความปลอดภัยระบบ</p>
                </div>
                <div>
                    <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> กลับ
                    </a>
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class="bi bi-download me-1"></i> ส่งออกรายงาน
                    </button>
                </div>
            </div>

            <!-- Report Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-graph-up text-success me-2"></i>
                                สรุปรายงาน
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($report))
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h4 class="text-primary">{{ $report['total_users'] ?? 0 }}</h4>
                                    <p class="text-muted">ผู้ใช้ทั้งหมด</p>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-danger">{{ $report['locked_accounts'] ?? 0 }}</h4>
                                    <p class="text-muted">บัญชีที่ถูกล็อก</p>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-warning">{{ $report['failed_attempts'] ?? 0 }}</h4>
                                    <p class="text-muted">ความพยายามล้มเหลว</p>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-success">{{ $report['successful_logins'] ?? 0 }}</h4>
                                    <p class="text-muted">เข้าสู่ระบบสำเร็จ</p>
                                </div>
                            </div>
                            @else
                            <p class="text-muted text-center">ไม่มีข้อมูลรายงาน</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Range Info -->
            @if(isset($dateRange))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-calendar me-2"></i>
                        <strong>ช่วงเวลา:</strong> {{ $dateRange['start'] ?? 'N/A' }} ถึง {{ $dateRange['end'] ?? 'N/A' }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Detailed Report -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-list-ul text-info me-2"></i>
                                รายละเอียดรายงาน
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">รายงานรายละเอียดจะแสดงที่นี่ เมื่อมีข้อมูลเพิ่มเติม</p>
                            
                            <!-- ตัวอย่างตาราง -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>วันที่</th>
                                            <th>กิจกรรม</th>
                                            <th>จำนวน</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ now()->format('d M Y') }}</td>
                                            <td>การเข้าสู่ระบบ</td>
                                            <td>25</td>
                                            <td><span class="badge bg-success">ปกติ</span></td>
                                        </tr>
                                        <tr>
                                            <td>{{ now()->format('d M Y') }}</td>
                                            <td>ความพยายามล้มเหลว</td>
                                            <td>3</td>
                                            <td><span class="badge bg-warning">ตรวจสอบ</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    // ฟังก์ชันส่งออกรายงาน
    alert('ฟีเจอร์ส่งออกรายงานกำลังพัฒนา');
}
</script>
@endsection
