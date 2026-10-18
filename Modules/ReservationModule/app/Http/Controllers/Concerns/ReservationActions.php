<?php

namespace Modules\ReservationModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Modules\CompanyModule\app\Services\CompanyService;
use Modules\PackageModule\app\Services\PackageService;
use Modules\ReservationModule\app\Http\Requests\ReservationRequest;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\ReservationModule\app\Services\ReservationService;
use Modules\ReservationModule\app\Services\ReservationStatusService;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Yajra\DataTables\Facades\DataTables;

/**
 * Reservations actions shared by the account and the employee controllers (both manage the account's reservations).
 * The controller gives area(): "account" | "employee" (route names {area}.reservations.*), the views read it from the route.
 * show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms),
 * The form's place chain (type → space → unit → plan) comes with the form (ReservationService::placeTree(), custom.js
 * [data-place-tree]); availability() = the form's live check (custom.js reservationAvailability()).
 */
trait ReservationActions
{

    private ReservationService $reservationService;
    private ReservationStatusService $statusService;
    private SubscriptionTypeService $subscriptionTypeService;
    private SpaceService $spaceService;
    private PackageService $packageService;
    private CompanyService $companyService;

    abstract protected function area(): string;

    public function __construct(
        ReservationService $reservationService, ReservationStatusService $statusService, SubscriptionTypeService $subscriptionTypeService,
        SpaceService $spaceService, PackageService $packageService, CompanyService $companyService
    ) {
        $this->reservationService = $reservationService;
        $this->statusService = $statusService;
        $this->subscriptionTypeService = $subscriptionTypeService;
        $this->spaceService = $spaceService;
        $this->packageService = $packageService;
        $this->companyService = $companyService;
    }

    // the account itself, or the account of the logged in employee
    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    private function route($name, $parameters = [])
    {
        return route($this->area() . '.reservations.' . $name, $parameters);
    }

    // the drop menus of the form; the place chain = placeTree (all of it, filtered in the browser); on edit / pre-filled the
    // saved choices' lists are rendered from it already (only the active spaces / units / plans: an inactive saved one must be changed)
    private function formOptions($reservation = null): array
    {
        $accountId = $this->accountId();
        // the Renew form: member / package / type are fixed hidden inputs, their drop menus are not shown
        $renewing = $reservation && !$reservation->exists && $reservation->renewal_of;
        $options = [
            // with can repeat: the form shows the repeat options only for these types
            'subscriptionTypes' => $renewing ? [] : $this->subscriptionTypeService->reservationOptions($accountId),
            'packages' => $renewing ? [] : $this->packageService->options($accountId, $reservation?->package_id),
            // the Member field searches the server (member-select); its quick add offers the account's companies
            'companies' => $renewing ? [] : $this->companyService->options($accountId),
            'placeTree' => $tree = $this->reservationService->placeTree($accountId),
            'spaces' => [], 'units' => [], 'plans' => [],
        ];

        if ($reservation) {
            $options['spaces'] = collect($tree['spaces'][$reservation->subscription_type_id] ?? [])->pluck('name', 'id')->all();
            $options['units'] = $tree['units'][$reservation->subscription_type_id . '-' . $reservation->space_id] ?? [];
            $options['plans'] = $reservation->unit_id ? ($tree['plans'][$reservation->unit_id] ?? []) : [];
        }

        return $options;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        $accountId = $this->accountId();
        $activeOnly = $this->area() === 'employee'; // employees: only the active statuses / spaces
        return view('reservationmodule::Reservation.index', [
            'statuses' => $this->statusService->filterOptions($accountId, $activeOnly),
            'spaces' => $this->spaceService->filterOptions($accountId, $activeOnly),
            // the type filter (Day Use / Meeting / Subscriptions ...): a contract type shows its rows on every tab
            'types' => $this->subscriptionTypeService->options($accountId),
            'counts' => $this->tabCounts([]),
        ]);
    }

    // the list tab: today (default, also '') | upcoming | all (docs/reservations-list-plan.md)
    // days picked in the date field (date_from / date_to) = 'days': the Today rule (running, no contracts) for those days
    private function tab(array $filters): string
    {
        if (!empty($filters['date_from']) && strtotime($filters['date_from'])) {
            // the business date alone (the Today tab's date) = the Today tab (its night after midnight included)
            $today = Reservation::businessToday()[0]->toDateString();
            return $filters['date_from'] === $today && ($filters['date_to'] ?? null ?: $today) === $today ? 'today' : 'days';
        }
        $tab = (string) ($filters['tab'] ?? '');
        return isset(Reservation::TABS[$tab]) ? $tab : 'today';
    }

    // the counted reservations of the Today tab with the other filters (not the tab / dates)
    private function tabCounts(array $filters): array
    {
        $filters = ['search' => trim((string) ($filters['search'] ?? ''))]
            + Arr::only($filters, ['reservation_status_id', 'space_id', 'subscription_type_id', 'payment_status']);

        return ['reservations-today' => $this->reservationService->listQuery($this->accountId(), $filters)->counted()->tab('today', $filters)->count()];
    }

    // DataTables server side: tab + search (member) + status / space / type / payment / dates filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $tab = $this->tab($filters);
        // the not counted ones (cancelled, pending ...) always after the counted ones
        // + who added it (the "By" column)
        $query = $this->reservationService->listQuery($this->accountId())->with('creatable.userable', 'renewedBy')->countedFirst();
        if ($id) {
            $query->whereKey($id);
        } elseif (!request()->filled('order')) {
            // the tab's order until a column is clicked (the list has data-order='[]')
            $query->tabOrder($tab, Arr::only($filters, ['date_from', 'date_to']));
        }

        $table = DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the reservations
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]
                + Arr::only($filters, ['reservation_status_id', 'space_id', 'subscription_type_id', 'payment_status']))
                ->tab($tab, Arr::only($filters, ['subscription_type_id', 'date_from', 'date_to'])))
            ->setRowId(fn ($reservation) => 'row-' . $reservation->id)
            // not counted (cancelled, pending ...): muted (custom.css)
            ->setRowClass(fn ($reservation) => $reservation->isCounted() ? '' : 'row-muted')
            // the columns (docs/reservations-list-plan.md, 2026-10-13): Member | Space › Unit | Plan | Period | Amount | Paid | Remaining | Created By | Status
            ->addColumn('member_html', fn ($reservation) => view('reservationmodule::Reservation.partials.row-member', compact('reservation'))->render())
            // (the unit color) space / › unit (two lines)
            ->addColumn('place', fn ($reservation) => view('reservationmodule::Reservation.partials.row-place', ['reservation' => $reservation, 'stacked' => true])->render())
            ->addColumn('plan', fn ($reservation) => e($reservation->plan?->name ?? '-'))
            // by the subscription type: full day = one date, auto renew / contract = From + Ends, else date | time → (date |) time
            ->addColumn('period', fn ($reservation) => view('reservationmodule::Reservation.partials.row-period', compact('reservation'))->render())
            // the reservation status + the payment status (two badges)
            ->addColumn('status', fn ($reservation) => view('reservationmodule::Reservation.partials.row-status', compact('reservation'))->render())
            ->editColumn('net_amount', fn ($reservation) => '<span class="font-bold text-success">' . number_format($reservation->net_amount, 2) . '</span>')
            ->editColumn('paid_amount', fn ($reservation) => number_format($reservation->paid_amount, 2))
            // red when owed, a credit (negative) in blue
            ->addColumn('remaining_html', fn ($reservation) => view('reservationmodule::Reservation.partials.row-remaining', compact('reservation'))->render())
            ->addColumn('by', fn ($reservation) => e($reservation->creatable?->userable?->displayName() ?? '-'))
            ->addColumn('actions', fn ($reservation) => view('reservationmodule::Reservation.partials.row-actions', compact('reservation'))->render())
            ->rawColumns(['member_html', 'place', 'period', 'status', 'net_amount', 'remaining_html', 'actions'])
            ->only(['DT_RowId', 'DT_RowClass', 'member_html', 'place', 'plan', 'period', 'status', 'net_amount', 'paid_amount', 'remaining_html', 'by', 'actions']);

        // the Today tab count (datatables.js → [data-counter]); not for one row
        return $id ? $table : $table->with('counts', $this->tabCounts($filters));
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    // the form's live availability line: ?unit_id, plan_id, start_at, end_at, start_time, end_time, ignore (edit: itself) —
    // {state: free | busy | shared, ...ReservationService::availability()} or {state: null} when not enough is chosen yet
    public function availability(Request $request)
    {
        $data = $request->only(['unit_id', 'plan_id', 'start_at', 'end_at', 'start_time', 'end_time']);
        $valid = fn ($date) => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
        $time = fn ($value) => !isset($value) || $value === '' || (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value));
        if (!$valid($data['start_at'] ?? null) || !$valid($data['end_at'] ?? null) || !$time($data['start_time'] ?? null) || !$time($data['end_time'] ?? null)) {
            return response()->json(['state' => null]);
        }
        $result = $this->reservationService->availability($this->accountId(), $data, $request->integer('ignore') ?: null);
        if (!$result) {
            return response()->json(['state' => null]);
        }
        $state = $result['used'] === 0 ? 'free' : ($result['limit'] > 1 && !$result['full'] ? 'shared' : 'busy');

        return response()->json(['state' => $state] + $result);
    }

    // ?renew=ID (Renew: the Schedule page, the view, the list): the form pre-filled from that period, starting the day after it ends
    // (renewal_of = it); else ?subscription_type_id / space_id / unit_id / start (the Schedule page: + New, a timeline slot)
    public function create(Request $request)
    {
        $reservation = $request->filled('renew')
            ? $this->reservationService->renewalOf($this->reservationService->findOne($this->accountId(), (int) $request->input('renew')))
            : $this->reservationService->prefill($this->accountId(), $request->only(['subscription_type_id', 'space_id', 'unit_id', 'start']));

        return view('reservationmodule::Reservation.create', ['reservation' => $reservation] + $this->formOptions($reservation));
    }

    public function store(ReservationRequest $request)
    {
        $reservation = $this->reservationService->create($this->accountId(), $request->validated(), Auth::user());
        $repeats = $reservation->repeats()->count();

        return response()->json([
            'message' => $repeats ? 'The reservation has been added with ' . $repeats . ' repeated reservation(s).' : 'The reservation has been added successfully.',
            'reload' => true,
            'redirect' => $this->route('show', $reservation->id),
        ]);
    }

    public function show($id)
    {
        $reservation = $this->reservationService->findOne($this->accountId(), $id);
        // the status menu (the header of the view)
        $statuses = $this->statusService->menuOptions($this->accountId(), $reservation->reservation_status_id);
        return view('reservationmodule::Reservation.show', compact('reservation', 'statuses'));
    }

    // the status menu of the view (ajax): the new status badge + the list row
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'reservation_status_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('reservation_statuses', 'id')
                ->where('account_id', $this->accountId())->where('is_active', true)->whereNull('deleted_at')],
        ], ['reservation_status_id.exists' => 'This status is not available, choose another one.']);

        $reservation = $this->reservationService->changeStatus($this->accountId(), $id, (int) $request->input('reservation_status_id'));

        return response()->json([
            'message' => 'The status has been changed to "' . $reservation->status?->name . '".',
            'status' => ['id' => $reservation->reservation_status_id, 'name' => $reservation->status?->name, 'class' => $reservation->status?->badge_class],
            'row' => $this->row($request, $id),
        ]);
    }

    // the Restore popup (a not counted reservation back to the default counted status): the slot is checked first
    public function restoreForm($id)
    {
        $reservation = $this->reservationService->findOne($this->accountId(), $id);

        return view('reservationmodule::Reservation.restore', [
            'reservation' => $reservation,
            'status' => $this->reservationService->restoreStatus($this->accountId()),
            'error' => $reservation->isCounted() ? null : $this->reservationService->restoreError($reservation),
        ]);
    }

    public function restore(Request $request, $id)
    {
        $reservation = $this->reservationService->restore($this->accountId(), $id);

        return response()->json([
            'message' => 'The reservation has been restored to "' . $reservation->status?->name . '", its amount is due again.',
            'row' => $this->row($request, $id),
            'modal' => $request->boolean('back_to_view') ? route($this->area() . '.reservations.show', $id) : null,
        ]);
    }

    // Mark left / undo (the Schedule page's Not renewed list, the view): the list row, back to the view when asked
    public function markLeft(Request $request, $id)
    {
        $this->reservationService->markLeft($this->accountId(), $id, Auth::user());

        return response()->json([
            'message' => 'The period has been marked as left.',
            'row' => $this->row($request, $id),
            'modal' => $request->boolean('back_to_view') ? route($this->area() . '.reservations.show', $id) : null,
        ]);
    }

    public function undoLeft(Request $request, $id)
    {
        $this->reservationService->undoLeft($this->accountId(), $id);

        return response()->json([
            'message' => 'The period is no longer marked as left.',
            'row' => $this->row($request, $id),
            'modal' => $request->boolean('back_to_view') ? route($this->area() . '.reservations.show', $id) : null,
        ]);
    }

    public function edit($id)
    {
        $reservation = $this->reservationService->findOne($this->accountId(), $id);
        return view('reservationmodule::Reservation.edit', compact('reservation') + $this->formOptions($reservation));
    }

    public function update(ReservationRequest $request, $id)
    {
        $this->reservationService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The reservation has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => $this->route('show', $id),
        ]);
    }

    public function destroy($id)
    {
        if ($error = $this->reservationService->deleteOne($this->accountId(), $id)) {
            return response()->json(['message' => $error], 422);
        }

        return response()->json([
            'message' => 'The reservation has been deleted successfully.',
            'row' => null,
            'redirect' => $this->route('index'),
        ]);
    }
}
