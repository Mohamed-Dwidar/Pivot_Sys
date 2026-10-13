<?php

namespace Modules\AccountModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Settings › General of the logged in account (the routes are account only): the two switches
class GeneralSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'withholding_enabled' => 'required|boolean',
            'vat_enabled' => 'required|boolean',
        ];
    }

    public function attributes(): array
    {
        return ['withholding_enabled' => 'withholding tax', 'vat_enabled' => 'VAT'];
    }
}
