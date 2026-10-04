@extends('layoutmodule::admin.main')

@section('title')
استعادة كلمة مرور للموظف 
@endsection


@section('content')

    <section class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0"> استعادة كلمة مرور للموظف : {{ $admin->name }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="post"
                                action="{{ route('admin.admins.resetPasswordPost') }}">
                                @csrf

                                <input type="hidden" name="id" value="{{ $admin->user->first()->id }}">

                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">كلمة المرور الجديدة <span class="text-danger">(مطلوب *)</span></label>

                                        <input type="text" class="form-control"
                                            placeholder="ادخل كلمة المرور الجديدة" name="new_password">

                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="text-center mt-5">
                                    <a href="{{route('admin.admins.index')}}" class="btn btn-outline-secondary mx-2">
                                        <i class="ti ti-arrow-right"></i> الرجوع إلى القائمة
                                    </a>
                                    <button type="submit" class="btn btn-primary mx-2">
                                        <i class="ti ti-pencil"></i> حفظ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </section>

@endsection

@push('scripts')
    <script>
         
    </script>
@endpush
