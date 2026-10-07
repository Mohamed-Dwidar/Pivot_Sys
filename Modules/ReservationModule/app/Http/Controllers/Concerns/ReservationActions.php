<?php

namespace Modules\ReservationModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\MemberModule\app\Services\MemberService;
use Modules\PackageModule\app\Services\PackageService;
use Modules\PlanModule\app\Services\PlanService;
use Modules\ReservationModule\app\Http\Requests\ReservationRequest;
use Modules\ReservationModule\app\Services\ReservationService;
use Modules\ReservationModule\app\Services\ReservationStatusService;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Modules\UnitModule\app\Services\UnitService;
use Yajra\DataTables\Facades\DataTables;

/**
 * Reservations actions shared by the account and the employee controllers (both manage the account's reservations).
 * The controller gives area(): "account" | "employee" (route names {area}.reservations.*), the views read it from the route.
 * show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms),
 * spaces() / units() / plans() fill the dependent drop menus of the form (custom.js [data-options-url]).
 */
trait ReservationActions
{
    private ReservationService $reservationService;
    private ReservationStatusService $statusService;
    private SubscriptionTypeService $subscriptionTypeService;
    private SpaceService $spaceService;
    private UnitService $unitService;
    private PlanService $planService;
    private PackageService $packageService;
    private MemberService $memberService;

    abstract protected function area(): string;

    public function __construct(
        ReservationService $reservationService, ReservationStatusService $statusService, SubscriptionTypeService $subscriptionTypeService,
        SpaceService $spaceService, UnitService $unitService, PlanService $planService, PackageService $packageService, MemberService $memberService
    ) {
        $this->reservationService = $reservationService;
        $this->statusService = $statusService;
        $this->subscriptionTypeService = $subscriptionTypeService;
        $this->spaceService = $spaceService;
        $this->unitService = $unitService;
        $this->planService = $planService;
        $this->packageService = $packageService;
        $this->memberService = $memberService;
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

    // the drop menus of the form; on edit the chain (spaces / units / plans) of the saved choices is filled already,
    // only the active spaces / units / plans (an inactive saved one must be changed)
    private function formOptions($reservation = null): array
    {
        $accountId = $this->accountId();
        $options = [
            // with can repeat / can continue: the form shows those options only for these types
            'subscriptionTypes' => $this->subscriptionTypeService->reservationOptions($accountId),
            'packages' => $this->packageService->options($accountId, $reservation?->package_id),
            'members' => $this->memberService->options($accountId),
            'spaces' => [], 'units' => [], 'plans' => [],
        ];

        if ($reservation) {
            $options['spaces'] = $this->spaceService->optionsForSubscriptionType($accountId, $reservation->subscription_type_id);
            if ($space = $reservation->space) {
                $options['units'] = $this->unitService->optionsForSpace($space);
            }
            if ($unit = $reservation->unit) {
                $options['plans'] = $this->planService->optionsForUnit($unit);
            }
        }

        return $options;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        $accountId = $this->accountId();
        return view('reservationmodule::Reservation.index', [
            'statuses' => $this->statusService->filterOptions($accountId),
            'spaces' => $this->spaceService->options($accountId),
        ]);
    }

    // DataTables server side: search (member) + status / space / dates filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->reservationService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the reservations
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]
                + Arr::only($filters, ['reservation_status_id', 'space_id', 'date_from', 'date_to'])))
            ->setRowId(fn ($reservation) => 'row-' . $reservation->id)
            ->addColumn('member_html', fn ($reservation) => view('reservationmodule::Reservation.partials.row-member', compact('reservation'))->render())
            ->addColumn('place', fn ($reservation) => e(($reservation->space?->name ?? '-') . ' › ' . ($reservation->unit?->name ?? '-')))
            ->addColumn('plan', fn ($reservation) => e($reservation->plan?->name ?? '-'))
            ->addColumn('period', fn ($reservation) => e($reservation->period))
            ->addColumn('status', fn ($reservation) => '<span class="badge badge-inactive">' . e($reservation->status?->name ?? '-') . '</span>')
            ->editColumn('net_amount', fn ($reservation) => number_format($reservation->net_amount, 2))
            ->addColumn('actions', fn ($reservation) => view('reservationmodule::Reservation.partials.row-actions', compact('reservation'))->render())
            ->rawColumns(['member_html', 'status', 'actions'])
            ->only(['DT_RowId', 'member_html', 'place', 'plan', 'period', 'status', 'net_amount', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    // dependent drop menus (json [{id, name, ...}])
    // the option texts are humanized like the server rendered drop menus ("day_use" => "Day Use")
    public function spaces(Request $request)
    {
        $spaces = $this->spaceService->optionsForSubscriptionType($this->accountId(), (int) $request->input('subscription_type_id'));
        return response()->json(collect($spaces)->map(fn ($name, $id) => ['id' => $id, 'name' => Str::humanize($name)])->values());
    }

    public function units(Request $request)
    {
        $space = $this->spaceService->findOne($this->accountId(), (int) $request->input('space_id'));
        return response()->json(collect($this->unitService->optionsForSpace($space))->map(fn ($unit) => ['name' => Str::humanize($unit['name'])] + $unit)->values());
    }

    public function plans(Request $request)
    {
        $unit = $this->unitService->findForAccount($this->accountId(), (int) $request->input('unit_id'));
        return response()->json(collect($this->planService->optionsForUnit($unit))->map(fn ($plan) => ['name' => Str::humanize($plan['name'])] + $plan)->values());
    }

    public function create()
    {
        return view('reservationmodule::Reservation.create', $this->formOptions());
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
        return view('reservationmodule::Reservation.show', compact('reservation'));
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
        $this->reservationService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The reservation has been deleted successfully.',
            'row' => null,
            'redirect' => $this->route('index'),
        ]);
    }
}
