<?php

namespace Modules\ReservationModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\ReservationModule\app\Services\ReservationService;
use Modules\SpaceModule\app\Services\SpaceService;
use Yajra\DataTables\Facades\DataTables;

/**
 * The Subscriptions page (docs/reservations-list-plan.md), the account and its employees: the counted reservations of the
 * auto renew subscription types, by tab: Active Now / Ending in 7 Days / Ending in 30 Days / Ended, Not Renewed.
 * Renewed = the member has a later subscription in the same space (Reservation::renewedSql()).
 * "Renew" opens the reservation form pre-filled from the subscription (ReservationActions::create(?renew=)).
 * The controller gives area(): "account" | "employee".
 */
trait SubscriptionActions
{
    private ReservationService $reservationService;
    private SpaceService $spaceService;

    abstract protected function area(): string;

    public function __construct(ReservationService $reservationService, SpaceService $spaceService)
    {
        $this->reservationService = $reservationService;
        $this->spaceService = $spaceService;
    }

    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    public function index()
    {
        return view('reservationmodule::Subscription.index', [
            'spaces' => $this->spaceService->options($this->accountId()),
            'counts' => $this->counts([]),
        ]);
    }

    private function tab(array $filters): string
    {
        $tab = (string) ($filters['tab'] ?? '');
        return isset(Reservation::SUBSCRIPTION_TABS[$tab]) ? $tab : 'active';
    }

    // the subscriptions of a tab with the search / space filters (+ the end date range on "ended")
    private function query(string $tab, array $filters)
    {
        return $this->reservationService->listQuery($this->accountId(), [
            'search' => trim((string) ($filters['search'] ?? '')),
            'space_id' => $filters['space_id'] ?? null,
        ])->subscriptionTab($tab, $filters);
    }

    // every tab's count (the same search / space filters)
    private function counts(array $filters): array
    {
        return collect(array_keys(Reservation::SUBSCRIPTION_TABS))
            ->mapWithKeys(fn ($tab) => ['subscriptions-' . $tab => $this->query($tab, $filters)->count()])->all();
    }

    public function data(Request $request)
    {
        $filters = ['search' => $request->input('search.value')] + $request->all();
        $tab = $this->tab($filters);
        $query = $this->query($tab, $filters)->withRenewed();
        if (!$request->filled('order')) {
            // ending soonest first; ended: the latest first
            $tab === 'ended' ? $query->orderByDesc('end_at') : $query->orderBy('end_at');
        }
        $area = $this->area();

        return DataTables::eloquent($query)
            ->setRowId(fn ($reservation) => 'row-' . $reservation->id)
            ->addColumn('member_html', fn ($reservation) => view('reservationmodule::Reservation.partials.row-member', compact('reservation'))->render())
            ->addColumn('place', fn ($reservation) => view('reservationmodule::Reservation.partials.row-place', compact('reservation'))->render())
            ->addColumn('plan', fn ($reservation) => e($reservation->plan?->name ?? '-'))
            ->addColumn('start', fn ($reservation) => $reservation->start_at?->format(Reservation::DATE_FORMAT))
            ->addColumn('end', fn ($reservation) => $reservation->realEnd()?->format(Reservation::DATE_FORMAT) ?? '-')
            ->addColumn('days', fn ($reservation) => view('reservationmodule::Subscription.partials.days', compact('reservation'))->render())
            ->editColumn('net_amount', fn ($reservation) => number_format($reservation->net_amount, 2))
            ->addColumn('remaining_html', fn ($reservation) => view('paymentmodule::Payment.partials.row-remaining', ['payable' => $reservation])->render())
            ->addColumn('renewed_html', fn ($reservation) => $reservation->renewed
                ? '<span class="badge badge-status-success">Renewed</span>' : '<span class="badge badge-status-warning">Not renewed</span>')
            ->addColumn('actions', fn ($reservation) => view('layoutmodule::partials.row-menu', ['links' => [
                ['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.reservations.show', $reservation->id), 'modal' => true],
                ['label' => 'Renew', 'icon' => 'refresh-cw', 'url' => route($area . '.reservations.create', ['renew' => $reservation->id]), 'modal' => true],
            ]])->render())
            ->rawColumns(['member_html', 'place', 'end', 'days', 'remaining_html', 'renewed_html', 'actions'])
            ->only(['DT_RowId', 'member_html', 'place', 'plan', 'start', 'end', 'days', 'net_amount', 'remaining_html', 'renewed_html', 'actions'])
            ->with('counts', $this->counts($filters))
            ->toJson();
    }
}
