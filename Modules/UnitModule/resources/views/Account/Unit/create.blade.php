@extends('layoutmodule::account.main')

@section('title')
    {{ $space->name }} &rsaquo; Add Unit
@endsection

@section('content')
    <form method="POST" action="{{ route('account.spaces.units.store', $space->id) }}" enctype="multipart/form-data">
        @csrf

        @include('unitmodule::Account.Unit.partials.form', ['unit' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.spaces.show', $space->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
