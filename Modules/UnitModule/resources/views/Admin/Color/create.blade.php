@extends('layoutmodule::admin.main')

@section('title')
    Add Color
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.colors.store') }}">
        @csrf

        @include('unitmodule::Admin.Color.partials.form', ['color' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('admin.colors.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
