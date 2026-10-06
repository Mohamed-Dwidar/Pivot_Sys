@extends('layoutmodule::account.main')

@section('title')
    Edit Space: {{ $space->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.spaces.update', $space->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('spacemodule::Account.Space.partials.form', ['space' => $space])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.spaces.show', $space->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
