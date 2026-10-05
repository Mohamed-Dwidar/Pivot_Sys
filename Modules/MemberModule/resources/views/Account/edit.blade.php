@extends('layoutmodule::account.main')

@section('title')
    Edit Member: {{ $member->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.members.update', $member->id) }}">
        @csrf
        @method('PUT')

        @include('membermodule::Account.partials.form', ['member' => $member])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.members.show', $member->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
