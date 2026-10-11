@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::employee.main')

{{-- logout with payments not closed (custom.js [data-logout-prompt]): close the shift first, or log out and keep it open (logged) --}}
@section('title')
    Log Out
@endsection

@section('content')
    <div class="mb-4">
        You have {{ $period['summary']['count'] }} payments not closed since {{ $period['since']?->format('d-m-Y h:i A') ?? '-' }}
        ({{ number_format($period['summary']['cash_total'], 2) }} collected).
    </div>

    <form method="POST" action="{{ route('employee.shift.still-working') }}" data-ajax>
        @csrf
        <input type="hidden" name="action" value="logout_still_working">
        <div class="form-actions">
            <a href="{{ route('employee.shift.confirm', ['logout' => 1]) }}" data-modal class="btn btn-primary"><i data-lucide="lock"></i> Close My Shift and Log Out</a>
            <button type="submit" class="btn btn-secondary"><i data-lucide="log-out"></i> Log Out, I'm Still Working</button>
        </div>
    </form>
@endsection
