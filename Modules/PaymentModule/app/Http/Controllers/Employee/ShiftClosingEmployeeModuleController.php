<?php

namespace Modules\PaymentModule\app\Http\Controllers\Employee;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Requests\ShiftCloseRequest;
use Modules\PaymentModule\app\Http\Requests\ShiftStillWorkingRequest;
use Modules\PaymentModule\app\Services\ShiftClosingService;
use Yajra\DataTables\Facades\DataTables;

/**
 * The logged in employee's shift (docs/payments-plan.md §2b): ONLY their own payments not closed yet (the open period),
 * totals by method (withholding apart, refunds minus), "Close my shift" → confirm() popup → close();
 * their past closings below (data() / show()). Never another employee's payments.
 * Reminders (never a block): reminder() = the "close your shift" popup (middleware ShiftReminder), logoutPrompt() = the
 * logout popup (custom.js, an open period with payments), stillWorking() = the answer, logged (shift_events).
 */
class ShiftClosingEmployeeModuleController extends Controller
{
    private ShiftClosingService $shiftService;

    public function __construct(ShiftClosingService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    // the open period + the closings list
    public function index()
    {
        return view('paymentmodule::Shift.index', ['period' => $this->shiftService->openPeriod($this->accountId(), Auth::id())]);
    }

    // DataTables server side: the employee's own closings
    public function data(Request $request)
    {
        return DataTables::eloquent($this->shiftService->listQuery($this->accountId(), Auth::id()))
            ->setRowId(fn ($closing) => 'row-' . $closing->id)
            ->addColumn('closed_html', fn ($closing) => '<a href="' . route('employee.shift.show', $closing->id) . '" data-modal class="font-medium">'
                . $closing->closed_at?->format('d-m-Y h:i A') . '</a>')
            ->addColumn('period', fn ($closing) => view('paymentmodule::Shift.partials.period', compact('closing'))->render())
            ->addColumn('status_html', fn ($closing) => view('paymentmodule::DayClosing.partials.status', compact('closing'))->render())
            ->editColumn('cash_total', fn ($closing) => number_format($closing->cash_total, 2))
            ->editColumn('withholding_total', fn ($closing) => number_format($closing->withholding_total, 2))
            ->rawColumns(['closed_html', 'period', 'status_html'])
            ->only(['DT_RowId', 'closed_html', 'period', 'count', 'status_html', 'cash_total', 'withholding_total'])
            ->toJson();
    }

    // the confirmation popup: the totals to hand over + "Confirm closing" / "Cancel" (?logout=1: from the logout popup)
    public function confirm(Request $request)
    {
        return view('paymentmodule::Shift.confirm', [
            'period' => $this->shiftService->openPeriod($this->accountId(), Auth::id()),
            'logout' => $request->boolean('logout'),
        ]);
    }

    public function close(ShiftCloseRequest $request)
    {
        $logout = $request->boolean('logout');
        $closing = $this->shiftService->close($this->accountId(), Auth::user(), (int) $request->validated('up_to_id'), Auth::user(), $logout ? 'logout_closed' : 'close');

        return response()->json([
            'message' => 'Your shift has been closed: ' . number_format($closing->cash_total, 2) . ' collected.',
            // the logout popup: logged out after closing (custom.js submits the logout form)
            'logout' => $logout,
            'redirect' => route('employee.shift.index'),
        ]);
    }

    // the open period is old (ShiftReminder): "Close My Shift Now" / "I'm Still Working" (reason)
    public function reminder()
    {
        return view('paymentmodule::Shift.reminder', ['period' => $this->shiftService->openPeriod($this->accountId(), Auth::id())]);
    }

    // logout with payments in the open period: "Close My Shift and Log Out" / "Log Out, I'm Still Working"
    public function logoutPrompt()
    {
        return view('paymentmodule::Shift.logout', ['period' => $this->shiftService->openPeriod($this->accountId(), Auth::id())]);
    }

    public function stillWorking(ShiftStillWorkingRequest $request)
    {
        $action = $request->validated('action');
        $this->shiftService->stillWorking($this->accountId(), Auth::user(), $action, $request->validated('reason'));

        return response()->json($action === 'logout_still_working'
            // custom.js logs out
            ? ['message' => 'Your shift stays open.', 'logout' => true]
            : ['message' => 'Noted. Remember to close your shift when you finish.']);
    }

    // one of the employee's closings (as it was saved), with its payments
    public function show($id)
    {
        $closing = $this->shiftService->findOne($this->accountId(), $id, Auth::id());
        $closing->load(['payments' => fn ($query) => $query->with('payable', 'member', 'paymentMethod', 'incomeCategory')->orderBy('created_at')]);

        return view('paymentmodule::Shift.show', compact('closing'));
    }
}
