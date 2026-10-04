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
<div id="flash-message" class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<div class="row" style="margin-top: 50px; ">
    
 <iframe src="https://calendar.google.com/calendar/embed?src=meqias.com%40gmail.com&ctz=Africa%2FCairo" style="border: 0" width="80%" height="700" frameborder="0" scrolling="no"></iframe>


</div>



@push('scripts')
<script>
    $(document).ready(function () {
     
     
    });
</script>

@endpush



@endsection