<?php

namespace Modules\MemberModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Models\Member;

class MemberRequest extends FormRequest
{
    // remove the spaces from the phone before it is validated and saved
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => Member::cleanPhone($this->input('phone'))]);
        }
    }

    public function rules(): array
    {
        $accountId = $this->user()->userable_id;

        // company / job must be one of the account's own (not deleted) records
        $ownedBy = fn ($table) => Rule::exists($table, 'id')->where('account_id', $accountId)->whereNull('deleted_at');

        return [
            'name' => 'required|string|max:255',
            'phone' => [
                'required', 'regex:' . Account::PHONE_REGEX,
                Rule::unique('members', 'phone')->where('account_id', $accountId)->ignore($this->route('id')),
            ],
            'email' => 'nullable|email:rfc,filter|max:255',
            'national_number' => 'nullable|digits_between:5,15',
            'company_id' => ['nullable', 'integer', $ownedBy('companies')],
            'job_id' => ['nullable', 'integer', $ownedBy('job_positions')],
            'from_where' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone must be a valid phone number: digits only (8-15), optionally starting with +.',
            'phone.unique' => 'There is already a member with this phone number.',
        ];
    }

    public function attributes(): array
    {
        return [
            'company_id' => 'company',
            'job_id' => 'job',
            'from_where' => 'source',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
