@php
    $userType = auth()->user()->user_type;
    $layout = $userType === 'super_admin' ? 'layouts.super-admin' : 'layouts.admin';
@endphp
@extends($layout)

@section('title', 'จัดการผู้ใช้งาน')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/chart/chart.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <div class="box-user-management">
            <h5><i class="bi bi-graph-up"></i>กราฟแสดงจำนวนผู้ใช้งานภายในระบบ</h5>
            <article class="chart-title">
                <div class="chart-container">
                    <canvas id="myChart"></canvas>
                </div>
                <div class="person-info">
                    <img src="{{ asset('images/icon/man.png') }}" alt="icon">
                    <div>
                        <h5>จำนวนผู้ใช้งานทั่วไป</h5>
                        <h1>{{ $count_user }}</h1>
                        <p style="color: var(--color-8);">คน</p>
                    </div>
                    <p style="color: var(--color-8); margin-top: 15px;"></p>
                </div>
                <div class="person-info">
                    <img src="{{ asset('images/icon/man.png') }}" alt="icon">
                    <div>
                        <h5>จำนวนผู้ลงทะเบียน</h5>
                        <h1>{{ $count_registration }}</h1>
                        <p style="color: var(--color-8);">คน</p>
                    </div>
                </div>
                <div class="person-info">
                    <img src="{{ asset('images/icon/plus.png') }}" alt="icon">
                    <div style="color: var(--color-8);">
                        <h5>เพิ่มข้อมูล</h5>
                        <h1>-</h1>
                        <p style="color: var(--color-8);"></p>
                    </div>
                </div>
            </article><br>
            <h5><i class="bi bi-person-circle"></i>ข้อมูลผู้ใช้งาน</h5>
            <article class="tab-user-management">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-black" id="registration-tab" data-bs-toggle="tab"
                            data-bs-target="#registration-tab-pane" type="button" role="tab"
                            aria-controls="registration-tab-pane" aria-selected="true">ผู้ลงทะเบียนเข้าใช้งาน</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-black" id="user-tab" data-bs-toggle="tab"
                            data-bs-target="#user-tab-pane" type="button" role="tab" aria-controls="user-tab-pane"
                            aria-selected="false">ผู้ใช้งานทั้งหมด</button>
                    </li>
                </ul>
                <div class="tab-content p-4" id="myTabContent">
                    <div class="tab-pane fade show active" id="registration-tab-pane" role="tabpanel"
                        aria-labelledby="registration-tab" tabindex="0">
                        <table id="registration-table" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th>บัญชีผู้ใช้งาน</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th>วันที่ลงทะเบียน</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($registration as $item)
                                    <tr>
                                        <td style="text-align: center">{{ $item->id }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td style="text-align: center">{{ $item->username }}</td>
                                        <td style="text-align: center">{{ $item->phone }}</td>
                                        <td style="text-align: center">{{ $item->created_at }}</td>
                                        <td style="text-align: center">
                                            @php
                                                $userType = auth()->user()->user_type;
                                                $registerRoute = $userType === 'super_admin' ? 'super_admin.registerUser' : 'admin.registerUser';
                                            @endphp
                                            <button type="button" class="btn-checked"
                                                onclick="location.href='{{ route($registerRoute, $item->id) }}'">
                                                <i class="bi bi-pencil-square"></i>จัดการ
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="user-tab-pane" role="tabpanel" aria-labelledby="user-tab" tabindex="0">
                        <table id="user-table" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th>บัญชีผู้ใช้งาน</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th>อนุมัติเข้าใช้งาน เมื่อ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user as $item)
                                    <tr>
                                        <td style="text-align: center">{{ $item->id }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td style="text-align: center">{{ $item->username }}</td>
                                        <td style="text-align: center">{{ $item->phone }}</td>
                                        <td style="text-align: center">{{ $item->created_at }}</td>
                                        <td style="text-align: center">
                                            @php
                                                $userType = auth()->user()->user_type;
                                                $userRoute = $userType === 'super_admin' ? 'super_admin.userInfo' : 'admin.userInfo';
                                            @endphp
                                            <button type="button" class="btn-checked"
                                                onclick="location.href='{{ route($userRoute, $item->id) }}'">
                                                <i class="bi bi-pencil-square"></i>จัดการ
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </div>
    </section>
    @if (session('success'))
        <script>
            Swal.fire({
                title: "สำเร็จ!",
                icon: "success",
                text: "{{ session('success') }}",
                draggable: true
            });
        </script>
    @endif
@endsection
@section('scripts')
    <script>
        const ctx = document.getElementById('myChart');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['ชาย', 'หญิง'],
                datasets: [{
                    label: 'จำนวน',
                    data: [{{ $male_count }}, {{ $female_count }}],
                    borderWidth: 1,
                    backgroundColor: ['#00509D', '#FFD500'],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: ['สัดส่วนผู้ใช้งาน', '(ชาย : {{ $male_count }} คน, หญิง : {{ $female_count }} คน)'],
                        font: {
                            size: 20,
                            weight: 'normal',
                            family: 'Noto Serif Thai, sans-serif',
                        },
                        position: 'bottom',
                        color: '#000',
                    }
                },
            },
        });

        $(document).ready(function () {
            $("#registration-table").DataTable();
        });

        $(document).ready(function () {
            $("#user-table").DataTable();
        });
    </script>
@endsection