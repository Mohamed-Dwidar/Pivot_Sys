@extends('layoutmodule::admin.main')

@section('title')
    مسؤول النظام
@endsection


@section('content')

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        {{-- <div class="col-md-12">
                <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">الرئيسيه</a></li>
                  <li class="breadcrumb-item" aria-current="page">إداريين الإتحاد</li>
                </ul>
              </div> --}}


                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="page-header-title">
                                    <h2 class="mb-0">مسؤول النظام</h2>
                                </div>
                                {{-- <div>
                                    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary"> <i
                                            class="ti ti-plus f-18"></i> إضافة موظف جديد</a>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card table-card">
                        <div class="card-body mt-3 m-3">
                            <!-- Search Section -->
                            <div class="table-responsive">
                                <table class="table table-hover tbl-product" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>الإسم</th>
                                            <th>إسم المستخدم</th>
                                            <th class="text-end">البريد الالكتروني</th>
                                            <th class="text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($admins))
                                            @foreach ($admins as $admin)
                                                <tr>
                                                    <td class="text-end">{{ $admin->name }}</td>
                                                    <td class="text-end">{{ $admin->user->first()->username ?? null }}</td>
                                                    <td class="text-end">{{ $admin->email }}</td>
                                                    <td class="text-center">
                                                        <div class="">
                                                            <ul class="list-inline me-auto mb-0">


                                                                <li class="list-inline-item align-bottom"
                                                                    data-bs-toggle="tooltip" title="استعادة كلمة المرور">
                                                                    <a href="{{ route('admin.admins.resetPassword', $admin->id) }}"
                                                                        class="btn btn-sm btn-warning">
                                                                        تغيير كلمة المرور
                                                                    </a>
                                                                    &nbsp;&nbsp;&nbsp;
                                                                </li>

                                                                {{-- <li class="list-inline-item align-bottom"
                                                                    data-bs-toggle="tooltip" title="حذف">
                                                                    <form
                                                                        action="{{ route('admin.admins.delete', $admin->id) }}"
                                                                        method="POST" class="delete-form">
                                                                        @csrf
                                                                        @method('post')
                                                                        <button type="button"
                                                                            class="avtar avtar-xs btn-link-danger btn-pc-default delete-btn">
                                                                            <span class="btn btn-sm btn-danger">حذف</span>
                                                                        </button>
                                                                    </form>
                                                                </li> --}}
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ sample-page ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle delete action
            $(document).on('click', '.delete-btn', function() {
                const form = $(this).closest('.delete-form');
                Swal.fire({
                    title: 'هل انت متأكد من الحذف؟',
                    text: 'لن تتمكن من استعادة هذا السجل بعد الحذف',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، احذفه',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }

                });
            });


        });
    </script>
@endpush
