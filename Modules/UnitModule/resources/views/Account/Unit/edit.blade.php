@extends('layoutmodule::account.main')

@section('title')
    {{ $space->name }} &rsaquo; Edit Unit: {{ $unit->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.spaces.units.update', [$space->id, $unit->id]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('unitmodule::Account.Unit.partials.form', ['unit' => $unit])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.spaces.units.show', [$space->id, $unit->id]) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
