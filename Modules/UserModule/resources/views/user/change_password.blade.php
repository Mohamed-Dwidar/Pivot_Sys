@extends('layoutmodule::admin.main')

@section('title')
    {{ __('messages.admin') }}
@endsection


@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        {{-- <div class="col-md-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="/">الرئيسيه</a></li>
              <li class="breadcrumb-item"><a href="{{route('admin.federations')}}"> النوادي</a></li>
              <li class="breadcrumb-item" aria-current="page">إضافة نادي</li>
            </ul>
          </div> --}}
                        <div class="col-md-12 mt-3">
                            <div class="page-header-title">
                                <h2 class="mb-0"> تغيير كلمة المرور</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            @if (session('success'))
                <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-md-12">
                    <form method="post" action="{{ route('updatePassword') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card">

                            <div class="card-body">
                                <!-- Validation Errors -->
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="row mb-3">
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-12 col-xs-12 col-6">
                                            <label class="form-label"> كلمة المرور الحالية </label>
                                            <div class="form-group">
                                                <input type="password"
                                                    class="form-control @error('current_password') is-invalid @enderror"
                                                    id="current_password" name="current_password"
                                                    value="{{ old('current_password') }}">
                                                @error('current_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-4">
                                        <div class="col-lg-6 col-sm-12 col-xs-12 col-6">
                                            <label class="form-label" for="password">كلمة المرور الجديدة</label>
                                            <div class="form-group">
                                                <input type="password"
                                                    class="form-control @error('new_password') is-invalid @enderror "
                                                    id="new_password" name="new_password" value="{{ old('new_password') }}">
                                                @error('new_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror

                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-4">
                                        <div class="col-lg-6 col-sm-12 col-xs-12 col-6">
                                            <label class="form-label" for="password_confirmation">تأكيد كلمة المرور</label>
                                            <div class="form-group">
                                                <input type="password"
                                                    class="form-control @error('confirm_password') is-invalid @enderror "
                                                    id="confirm_password" name="confirm_password"
                                                    value="{{ old('confirm_password') }}">

                                                @error('confirm_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror

                                            </div>


                                        </div>

                                    </div>


                                </div>




                            </div>
                        </div>



                        <button type="submit" class="btn btn-primary mb-4">حفظ</button>

                    </form>


                    <!-- [ form-element ] end -->
                </div>
            </div>
            <!-- [ Main Content ] end -->
    </section>
@endsection
