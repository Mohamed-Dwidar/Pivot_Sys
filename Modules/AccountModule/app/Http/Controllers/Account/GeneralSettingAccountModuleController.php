<?php

namespace Modules\AccountModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AccountModule\app\Http\Requests\GeneralSettingRequest;
use Modules\AccountModule\app\Models\Account;

/**
 * Settings › General (the account only): withholding tax (default ON; OFF hides new withholding everywhere, the existing records
 * stay) and VAT (default ON; used by Phase 2: OFF = prices tax free). Saving never changes payments / certificates.
 */
class GeneralSettingAccountModuleController extends Controller
{
    public function edit()
    {
        return view('accountmodule::Setting.general', ['account' => Auth::user()->userable]);
    }

    public function update(GeneralSettingRequest $request)
    {
        $account = Auth::user()->userable;
        $account->update($request->validated());
        Account::forgetSettings($account->id);

        return response()->json([
            'message' => 'The settings have been saved.',
            'redirect' => route('account.settings.general.edit'),
        ]);
    }
}
