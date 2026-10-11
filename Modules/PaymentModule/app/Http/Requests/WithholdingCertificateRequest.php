<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// withholding certificate create / edit; WithholdingCertificateService checks the payer / payments ownership again
class WithholdingCertificateRequest extends FormRequest
{
    public function rules(): array
    {
        $accountId = $this->user()->userable->ownerAccountId();

        return [
            'certificate_no' => 'required|string|max:100',
            'issued_at' => 'required|date',
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')->where('account_id', $accountId)->whereNull('deleted_at')],
            // (members are not soft deleted)
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('account_id', $accountId)],
            'payer_name' => 'required|string|max:255',
            'payer_tax_id' => 'nullable|string|max:30',
            'period_from' => 'nullable|date',
            'period_to' => 'nullable|date|after_or_equal:period_from',
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'notes' => 'nullable|string|max:5000',
            // the withholding payments it covers
            'payments' => 'nullable|array',
            'payments.*' => 'integer',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
            'delete_attachment' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'certificate_no' => 'certificate number', 'issued_at' => 'issue date', 'company_id' => 'company', 'member_id' => 'member',
            'payer_tax_id' => 'payer tax id', 'period_from' => 'period from', 'period_to' => 'period to',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
