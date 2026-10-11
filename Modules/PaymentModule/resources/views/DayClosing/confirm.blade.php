@php($dateText = $date->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT))
@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

{{-- the final close of a day (app popup): totals per method, withholding, grand total; "Confirm closing" / "Cancel" --}}
@section('title')
    Close {{ $dateText }}?
@endsection

@section('content')
    @if ($blocking)
        <div class="text-warning">
            Shifts with payments of this day are not closed yet:
            {{ collect($blocking['open'])->map(fn ($period) => $period['user']?->userable?->displayName())->merge(collect($blocking['reopened'])->map(fn ($shift) => $shift->user?->userable?->displayName()))->filter()->implode(', ') }}.
        </div>
    @else
        <div class="mb-4">The payments of {{ $dateText }} will be locked: no adding, changing or deleting them until the day is reopened.</div>

        @if (empty($summary['count']))
            <div class="text-slate-500">No payments on this day.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Method</th><th>Net</th></tr></thead>
                    <tbody>
                        @foreach ($summary['methods'] as $method)
                            <tr><td>{{ $method['name'] }}</td><td>{{ number_format($method['net'], 2) }}</td></tr>
                        @endforeach
                        <tr class="font-medium"><td>Total collected</td><td class="text-success">{{ number_format($summary['cash_total'], 2) }}</td></tr>
                        <tr><td>Withholding <span class="text-slate-400">(not cash, {{ $summary['withholding']['count'] }})</span></td><td>{{ number_format($summary['withholding']['total'], 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        <form method="POST" action="{{ route('account.day-closings.store') }}" data-ajax>
            @csrf
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i data-lucide="lock"></i> Confirm Closing</button>
                @if (request()->ajax())
                    <button type="button" data-tw-dismiss="modal" class="btn btn-secondary">Cancel</button>
                @else
                    <a href="{{ route('account.day-closings.create', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-secondary">Cancel</a>
                @endif
            </div>
        </form>
    @endif
@endsection
