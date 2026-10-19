<?php

namespace Modules\PaymentModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Controllers\Concerns\MyShiftActions;
use Modules\PaymentModule\app\Http\Requests\ShiftStillWorkingRequest;

/**
 * The logged in employee's shift: My Shift (MyShiftActions, shared with the account), + their own closings only (show()).
 * Reminders (employees only, never a block): reminder() = the "close your shift" popup (middleware ShiftReminder),
 * logoutPrompt() = the logout popup (custom.js, an open shift with payments), stillWorking() = the answer, logged (shift_events).
 */
class ShiftClosingEmployeeModuleController extends Controller
{
    use MyShiftActions;

    protected function area(): string
    {
        return 'employee';
    }

    // the open shift is old (ShiftReminder): "Close My Shift Now" / "I'm Still Working" (reason)
    public function reminder()
    {
        return view('paymentmodule::Shift.reminder', ['period' => $this->shiftService->openPeriod($this->accountId(), Auth::id())]);
    }

    // logout with payments in the open shift: "Close My Shift and Log Out" / "Log Out, I'm Still Working"
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

    // one of the employee's closings (as it was saved), with its payments; the imported one is not theirs to see (404)
    public function show($id)
    {
        $closing = $this->shiftService->findOne($this->accountId(), $id, Auth::id());
        abort_if($closing->is_imported, 404);
        $closing->setRelation('payments', $this->shiftService->popupPayments($closing));
        $closing->setRelation('expenses', $this->shiftService->popupExpenses($closing));

        return view('paymentmodule::Shift.show', compact('closing'));
    }
}
