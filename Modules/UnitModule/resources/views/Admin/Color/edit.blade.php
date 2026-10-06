@extends('layoutmodule::admin.main')

@section('title')
    Edit Color: {{ $color->name }}
@endsection

@section('actions')
    @include('unitmodule::Admin.Color.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.colors.update', $color->id) }}">
        @csrf
        @method('PUT')

        @include('unitmodule::Admin.Color.partials.form', ['color' => $color])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('admin.colors.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
