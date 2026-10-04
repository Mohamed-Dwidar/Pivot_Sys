@extends('layoutmodule::main')

@section('title')
    {{ __('messages.dashboard') }}
@endsection

@section('content')
    <div class="text-base font-medium">
        {{ __('messages.welcome') }}, {{ Auth::guard('admin')->user()->name }}
    </div>
    <div class="mt-1 text-slate-500">{{ config('app.name') }}</div>
@endsection
