@extends('layouts.admin')

@section('title', 'ข้อมูลผู้ดูแลระบบ')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/chart/chart.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <div  class="box-user-management">
            <h5><i class="bi bi-graph-up"></i>กราฟแสดงจำนวนผู้ใช้งานภายในระบบ</h5>
            <article class="chart-title">
                <div class="chart-container">
                    <canvas id="myChart"></canvas>
                </div>
                <div class="person-info">
                    <img src="{{ asset('images/icon/man.png') }}" alt="icon">
                    <div>
                        <h5>จำนวนแอดมิน</h5>
                        <h1>{{ $count_admin }}</h1>
                    </div>
                    <p style="color: var(--color-8); margin-top: 15px;"></p>
                </div>
                <div class="person-info">
                    <img src="{{ asset('images/icon/plus.png') }}" alt="icon">
                    <div style="color: var(--color-8);">
                        <h5>เพิ่มข้อมูล</h5>
                        <h1>-</h1>
                    </div>
                </div>
            </article><br>
            <h5><i class="bi bi-person-circle"></i>ข้อมูลแอดมิน</h5>
            <article class="tab-user-management">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-black" id="admins-tab" data-bs-toggle="tab"
                            data-bs-target="#admins-tab-pane" type="button" role="tab"
                            aria-controls="admins-tab-pane" aria-selected="true">ผู้ลงทะเบียนเข้าใช้งาน</button>
                    </li>
                </ul>
                <div class="tab-content p-4" id="myTabContent">
                    <div class="tab-pane fade show active" id="admins-tab-pane" role="tabpanel"
                        aria-labelledby="admins-tab" tabindex="0">
                        <table id="admins-table" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th>บัญชีผู้ใช้งาน</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th>วันที่ลงทะเบียน</th>
                                    <th>ข้อมูล</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $item)
                                    <tr>
                                        <td style="text-align: center">{{ $item->id }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td style="text-align: center">{{ $item->username }}</td>
                                        <td style="text-align: center">{{ $item->phone }}</td>
                                        <td style="text-align: center">{{ $item->created_at }}</td>
                                        <td style="text-align: center">
                                            <button type="button" class="btn-checked"
                                                onclick="location.href='{{ route('admin.adminInfo', $item->id) }}'">
                                                <i class="bi bi-pencil-square"></i>ข้อมูล
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
                        text: 'สัดส่วนแอดมิน',
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
            $("#admins-table").DataTable();
        });

    </script>
@endsection