@extends('layoutmodule::main')

@section('title')
    {{ __('messages.admin') }}
@endsection


@section('content')
    <div class="pc-container">
        <div class="pc-content">

            <div style="text-align: center">
            <img src="{{ asset('assets/images/logo.png') }}" alt="شعار الإتحاد المصري للإسكواش" class="logo" style="    width: 270px;">
            {{-- <h3>الإتحاد المصري للإسكواش</h3>
            <p>نحن نعمل حالياً على التطوير . سنكون هنا قريباً بالجديد والمميز.</p> --}}
        </div>
            
        </div>
    </div>
@endsection
