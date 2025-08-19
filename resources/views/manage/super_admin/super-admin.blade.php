@php
    $userType = auth()->user()->user_type;
    $layout = $userType === 'super_admin' ? 'layouts.super-admin' : 'layouts.admin';
@endphp
@extends($layout)