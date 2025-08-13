@extends('layouts.super-admin')

@section('title', 'หน้าหลัก')
@section('content')
    <section class="mt-5">
        <div class="card">
            <div class="card-header">
                ยินดีต้อนรับ {{ auth()->user()->prefix . auth()->user()->name }}
            </div>
            <div class="card-body">
                <figure>
                    <blockquote class="blockquote">
                        <p>A well-known quote, contained in a blockquote element.</p>
                    </blockquote>
                    <figcaption class="blockquote-footer">
                        Someone famous in <cite>Source Title</cite>
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>
    @if (session('success'))
        <script>
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif
@endsection