<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Requests\ShiftSettingRequest;

// the account's shift reminder settings (accounts.shift_reminder_hours / shift_snooze_hours, docs/payments-plan.md §2b)
class ShiftSettingAccountModuleController extends Controller
{
    public function edit()
    {
        return view('paymentmodule::Shift.settings', ['account' => Auth::user()->userable]);
    }

    public function update(ShiftSettingRequest $request)
    {
        Auth::user()->userable->update($request->validated());

        return response()->json([
            'message' => 'The shift reminder settings have been saved.',
            'redirect' => route('account.shift-settings.edit'),
        ]);
    }
}
