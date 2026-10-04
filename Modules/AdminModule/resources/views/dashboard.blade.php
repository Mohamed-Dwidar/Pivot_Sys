@extends('layoutmodule::admin.main')
{{-- @extends('layouts.admin.main') --}}

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Whoops!</strong>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if ($message = Session::get('success'))
<div  id="flash-message" class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<div class="row">
    <!-- Users card -->
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="card card-statistic-2">
            <div class="card-stats">
                <div class="card-stats-title">
                    Users
                </div>
            </div>
            <div class="card-icon shadow-primary bg-primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Users</h4>
                </div>
                <div class="card-body">
                    {{$users}}
                </div>
            </div>
        </div>
    </div>



    <!-- Questions card -->
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="card card-statistic-2">
            <div class="card-stats">
                <div class="card-stats-title">
                    Questions
                </div>
            </div>
            <div class="card-icon shadow-primary bg-primary">
                <i class="fas fa-comment-dollar"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Questions</h4>
                </div>
                <div class="card-body">
                    {{$questions}}
                </div>
            </div>
        </div>
    </div>
  


</div>



@push('scripts')
<script>
    $(document).ready(function () {
     
     
    });
</script>

@endpush



@endsection