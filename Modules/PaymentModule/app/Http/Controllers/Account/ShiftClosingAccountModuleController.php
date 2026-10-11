<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Requests\DayReopenRequest;
use Modules\PaymentModule\app\Http\Requests\ShiftCloseRequest;
use Modules\PaymentModule\app\Services\ShiftClosingService;
use Modules\UserModule\app\Models\User;

/**
 * The account and its employees' shifts (docs/payments-plan.md §2b), from the day screen (DayClosing create):
 * view a closing, close an open period on the employee's behalf (confirmation popup), reopen (reason) / close again.
 * ?date= / date: the day screen it was opened from, ?index= / index: the closings list (long open / reopened shifts);
 * the answers go back to it.
 */
class ShiftClosingAccountModuleController extends Controller
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

    // back to the day screen / the closings list it came from, else the closing
    private function back(Request $request, $closingId = null): string
    {
        if ($request->boolean('index')) {
            return route('account.day-closings.index');
        }

        return $request->filled('date')
            ? route('account.day-closings.create', ['date' => $request->input('date')])
            : route('account.shifts.show', $closingId);
    }

    public function show($id)
    {
        $closing = $this->shiftService->findOne($this->accountId(), $id);
        $closing->load(['payments' => fn ($query) => $query->with('payable', 'member', 'paymentMethod', 'incomeCategory')->orderBy('created_at')]);

        return view('paymentmodule::Shift.show', compact('closing'));
    }

    // close on behalf: the employee's open period + "Confirm closing" (?user_id=&date=)
    public function confirm(Request $request)
    {
        $employee = $this->employee($request->input('user_id'));

        return view('paymentmodule::Shift.confirm', [
            'period' => $this->shiftService->openPeriod($this->accountId(), $employee->id),
            'employee' => $employee,
            'logout' => false,
        ]);
    }

    public function close(ShiftCloseRequest $request)
    {
        $employee = $this->employee($request->validated('user_id'));
        $closing = $this->shiftService->close($this->accountId(), $employee, (int) $request->validated('up_to_id'), Auth::user());

        return response()->json([
            'message' => 'The shift of ' . $employee->userable->displayName() . ' has been closed: ' . number_format($closing->cash_total, 2) . ' collected.',
            'redirect' => $this->back($request, $closing->id),
        ]);
    }

    public function reopenForm($id)
    {
        $closing = $this->shiftService->findOne($this->accountId(), $id);
        return view('paymentmodule::Shift.reopen', compact('closing'));
    }

    public function reopen(DayReopenRequest $request, $id)
    {
        $closing = $this->shiftService->reopen($this->accountId(), $id, $request->validated('reopen_reason'), Auth::user());

        return response()->json([
            'message' => 'The shift is open again, its payments can be changed. Close it again when they are checked.',
            'redirect' => $this->back($request, $closing->id),
        ]);
    }

    public function reclose(Request $request, $id)
    {
        $closing = $this->shiftService->reclose($this->accountId(), $id, Auth::user());

        return response()->json([
            'message' => 'The shift has been closed again: ' . number_format($closing->cash_total, 2) . ' collected.',
            'redirect' => $this->back($request, $closing->id),
        ]);
    }

    // a login of one of the account's employees (404 otherwise; the service checks it again)
    private function employee($userId): User
    {
        $user = User::with('userable')->whereIn('id', $this->shiftService->employeeUserIds($this->accountId()))->find((int) $userId);
        abort_unless($user, 404);

        return $user;
    }
}
