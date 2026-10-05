<?php

namespace Modules\AccountModule\app\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\AccountModule\app\Models\Account;

// admin: create / update an account with its login
class AccountRequest extends UpdateAccountProfileRequest
{
    public function rules(): array
    {
        $userId = null;
        if ($this->route('id')) {
            $userId = Account::with('user')->findOrFail($this->route('id'))->user?->id;
        }

        return parent::rules() + [
            'status' => ['required', Rule::in(array_keys(Account::STATUSES))],
            'email' => ['required', 'email:rfc,filter', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
