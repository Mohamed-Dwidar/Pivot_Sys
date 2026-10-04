@extends('layoutmodule::admin.main')

@section('title')
{{ __('messages.update_admin') }} 
@endsection

@section('content')

<section class="section">
    <div class="section-header">
        <i class="fas fa-building fa-size mr-2"></i>
        <h1>{{__('messages.update_admin')}}</h1>
        <!-- Breadcrumb -->
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard')}}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{route('admin.admins.index')}}">{{__('messages.admins')}}</a></div>
            <div class="breadcrumb-item">{{__('messages.update_admin')}}</div>
        </div>
    </div>

    @include('layoutmodule::admin.flash')

    <div class="section-body">
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    {{-- <div class="card-header">
                        <div class="row">
                            <div class="col-5">
                                <h2> Salon Information</h2>
                            </div>
                        </div>
                    </div> --}}
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 col-12">
                                <form class="card-form side-form" method="POST"
                                    action='{{ route("admin.admins.update")  }}' enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ old('id', $admin->id) }}">

                                    <div class="row">
                                        <div class="col-lg-6 col-sm-12 col-xs-12 col-6">
                                            <label for="name">{{ __('messages.name') }}</label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{old('name' ,$admin->name)}}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-sm-12 col-xs-12 col-6">
                                            <label for="email">{{ __('messages.email') }}</label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="email" name="email"
                                                    value="{{old('email' ,$admin->email)}}">
                                            </div>
                                        </div>
                                    </div> 
                                   




                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-1 col-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')

@endpush


@endsection