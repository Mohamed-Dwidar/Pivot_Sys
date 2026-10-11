<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Controllers\Concerns\DayClosingActions;
use Modules\PaymentModule\app\Http\Requests\DayReopenRequest;

// logged in account: close days (DayClosingActions) + the owner's reopen with a reason
class DayClosingAccountModuleController extends Controller
{
    use DayClosingActions;

    protected function area(): string
    {
        return 'account';
    }

    public function reopenForm($id)
    {
        $closing = $this->closingService->findOne($this->accountId(), $id);
        return view('paymentmodule::DayClosing.reopen', compact('closing'));
    }

    public function reopen(DayReopenRequest $request, $id)
    {
        $closing = $this->closingService->reopen($this->accountId(), $id, $request->validated('reopen_reason'), Auth::user());

        return response()->json([
            'message' => 'The day ' . $closing->date->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) . ' is open again, its payments can be changed.',
            'row' => $this->row($request, $id),
            'modal' => route('account.day-closings.show', $id),
            'redirect' => route('account.day-closings.show', $id),
        ]);
    }
}
