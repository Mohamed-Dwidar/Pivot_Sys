{{--
    The extras block of a package view: sales (revenue) and balance adjustments added to what it owes (due = net + Σ extras).
    @include('paymentmodule::Extra.partials.package-extras', ['package' => $package])
    The account adds / edits / deletes them; employees add extras (sales) and see them (2026-10-21).
--}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $extras = $package->extrasList();
    $dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT;
@endphp

<div class="form-section mt-8 payments-block__head">
    <span>Extras ({{ $extras->count() }})</span>
    {{-- the account and employees (employees: extras only, no edit / delete) --}}
    <a href="{{ route($area . '.extras.create', ['package_id' => $package->id]) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Add Extra</a>
</div>

@if ($extras->isNotEmpty())
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                {{-- user 2026-10-21: Amount, Date, Item, Qty × Price, Type --}}
                <tr>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Qty × Price</th>
                    <th>Type</th>
                    @if ($area === 'account')<th></th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($extras as $extra)
                    <tr>
                        <td class="font-medium {{ $extra->amount < 0 ? 'text-danger' : '' }}">{{ number_format($extra->amount, 2) }}</td>
                        <td class="whitespace-nowrap">{{ $extra->date?->format($dateFormat) }}</td>
                        <td>
                            {{ $extra->item }}
                            @if ($extra->notes)<div class="text-xs text-slate-500">{{ $extra->notes }}</div>@endif
                        </td>
                        <td class="whitespace-nowrap">{{ rtrim(rtrim(number_format($extra->quantity, 2), '0'), '.') }} × {{ number_format($extra->unit_price, 2) }}</td>
                        {{-- one line: the type badge · its income category (extras) --}}
                        <td class="whitespace-nowrap">
                            <span class="badge {{ $extra->type_badge_class }}">{{ $extra->type_label }}</span>
                            @if ($extra->type === 'extra' && $extra->incomeCategory)<span class="text-xs text-slate-500">· {{ $extra->incomeCategory->name }}</span>@endif
                        </td>
                        @if ($area === 'account')
                            {{-- edit + delete on one line (.payments-block__icons, like the Cash In list) --}}
                            <td class="whitespace-nowrap">
                                <span class="payments-block__icons">
                                    <a href="{{ route('account.extras.edit', $extra->id) }}" data-modal title="Edit"><i data-lucide="pencil" class="h-4 w-4"></i></a>
                                    @include('paymentmodule::Extra.partials.delete-form')
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
                <tr class="font-medium">
                    <td>{{ number_format($package->extrasTotal(), 2) }}</td>
                    <td colspan="4">Total extras</td>
                    @if ($area === 'account')<td></td>@endif
                </tr>
            </tbody>
        </table>
    </div>
@else
    <div class="text-slate-500">No extras.</div>
@endif
