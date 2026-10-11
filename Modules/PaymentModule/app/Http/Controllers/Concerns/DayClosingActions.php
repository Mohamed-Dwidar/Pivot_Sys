<?php

namespace Modules\PaymentModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Requests\DayClosingRequest;
use Modules\PaymentModule\app\Services\DayClosingService;
use Modules\PaymentModule\app\Services\ShiftClosingService;
use Modules\ReservationModule\app\Models\Reservation;
use Yajra\DataTables\Facades\DataTables;

/**
 * Day closing actions (the account only since the two levels, docs/payments-plan.md §2b; employees close their shift).
 * The controller gives area(): "account" (route names {area}.day-closings.*).
 * create(?date=) = the day screen: the employees' shifts with payments of that day (closed / open / reopened),
 * the day's totals, "Close this Day" (the confirm() popup, refused while a shift with its payments is not closed);
 * store() closes it (JSON, custom.js ajax form).
 */
trait DayClosingActions
{
    private DayClosingService $closingService;
    private ShiftClosingService $shiftService;

    abstract protected function area(): string;

    public function __construct(DayClosingService $closingService, ShiftClosingService $shiftService)
    {
        $this->closingService = $closingService;
        $this->shiftService = $shiftService;
    }

    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    private function route($name, $parameters = [])
    {
        return route($this->area() . '.day-closings.' . $name, $parameters);
    }

    // the closed days + the flags: long open shifts ("still working" answers), reopened shifts
    public function index()
    {
        return view('paymentmodule::DayClosing.index', [
            'longOpen' => $this->shiftService->longOpenPeriods(Auth::user()->userable),
            'reopenedShifts' => $this->shiftService->reopenedClosings($this->accountId()),
        ]);
    }

    // DataTables server side: the closed / reopened days
    public function data(Request $request)
    {
        return $this->table($request->all())->toJson();
    }

    private function table(array $filters, $id = null)
    {
        $query = $this->closingService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            ->filter(fn ($query) => $query->when(in_array($filters['status'] ?? '', ['closed', 'reopened'], true), fn ($query) => $query->where('status', $filters['status'])))
            ->setRowId(fn ($closing) => 'row-' . $closing->id)
            ->addColumn('date_html', fn ($closing) => '<a href="' . $this->route('show', $closing->id) . '" data-modal class="font-medium">' . $closing->date->format(Reservation::DATE_FORMAT) . '</a>')
            ->addColumn('status_html', fn ($closing) => view('paymentmodule::DayClosing.partials.status', compact('closing'))->render())
            ->editColumn('cash_total', fn ($closing) => number_format($closing->cash_total, 2))
            ->editColumn('withholding_total', fn ($closing) => number_format($closing->withholding_total, 2))
            ->addColumn('closed', fn ($closing) => e($closing->closedBy?->userable?->displayName() ?? '-') . '<div class="text-xs text-slate-500">' . $closing->closed_at?->format('d-m-Y h:i A') . '</div>')
            ->rawColumns(['date_html', 'status_html', 'closed'])
            ->only(['DT_RowId', 'date_html', 'status_html', 'cash_total', 'withholding_total', 'closed']);
    }

    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    // the day's totals now + "Close this day" (?date=, default today)
    public function create(Request $request)
    {
        $date = rescue(fn () => Carbon::parse($request->input('date', today()))->startOfDay(), today(), false);
        $date = $date->isFuture() ? today() : $date;

        return view('paymentmodule::DayClosing.create', [
            'date' => $date,
            'closing' => $this->closingService->forDate($this->accountId(), $date),
            'summary' => $this->closingService->summary($this->accountId(), $date),
            // the employees' closings with payments of that day, and the open periods holding payments of that day
            'shifts' => $this->shiftService->closingsForDate($this->accountId(), $date),
            'openPeriods' => $this->shiftService->openPeriods($this->accountId(), $date),
        ]);
    }

    // the final close popup: the day's totals per method + withholding + grand total, "Confirm closing" / "Cancel"
    public function confirm(Request $request)
    {
        $date = rescue(fn () => Carbon::parse($request->input('date', today()))->startOfDay(), today(), false);

        return view('paymentmodule::DayClosing.confirm', [
            'date' => $date,
            'summary' => $this->closingService->summary($this->accountId(), $date),
            'blocking' => $this->shiftService->blockingForDate($this->accountId(), $date),
        ]);
    }

    public function store(DayClosingRequest $request)
    {
        $closing = $this->closingService->close($this->accountId(), $request->validated('date'), Auth::user());

        return response()->json([
            'message' => 'The day ' . $closing->date->format(Reservation::DATE_FORMAT) . ' has been closed, its payments are locked.',
            'reload' => true,
            'redirect' => $this->route('create', ['date' => $closing->date->format('Y-m-d')]),
        ]);
    }

    // the saved totals (as they were when the day was closed)
    public function show($id)
    {
        $closing = $this->closingService->findOne($this->accountId(), $id);
        return view('paymentmodule::DayClosing.show', ['closing' => $closing, 'summary' => $closing->totals ?? []]);
    }
}
