<?php

namespace App\Helpers;

use Illuminate\Http\Request;

trait GeneralHelper
{
    public function listMonths()
    {
        return [
            ['name' => __('messages.january'), 'number' => 1],
            ['name' => __('messages.february'), 'number' => 2],
            ['name' => __('messages.march'), 'number' => 3],
            ['name' => __('messages.april'), 'number' => 4],
            ['name' => __('messages.may'), 'number' => 5],
            ['name' => __('messages.june'), 'number' => 6],
            ['name' => __('messages.july'), 'number' => 7],
            ['name' => __('messages.august'), 'number' => 8],
            ['name' => __('messages.september'), 'number' => 9],
            ['name' => __('messages.october'), 'number' => 10],
            ['name' => __('messages.november'), 'number' => 11],
            ['name' => __('messages.december'), 'number' => 12]
        ];
    }

    public function paymentMethods()
    {
        return [
            'cash' => __('messages.cash'),
            // 'credit_card' => __('messages.credit_card'),
            'bank_transfer' => __('messages.bank_transfer'),
            'check' => __('messages.check'),
            'mobile_payment' => __('messages.mobile_payment'),
            //'other' => __('messages.other')
        ];
    }

    public function contractStatuses()
    {
        return [
            'draft' => __('messages.draft'),
            'signed' => __('messages.signed'),
            'renewed' => __('messages.renewed'),
            'ended' => __('messages.ended'),
            'terminated' => __('messages.terminated'),
            'expired' => __('messages.expired'),
        ];
    }

    public function contractMainInformation()
    {
        return [
            'lessor_name' => __('messages.main_info.lessor_name'),
            'lessor_tax_reg_nu' => __('messages.main_info.lessor_tax_reg_nu'),
            'lessor_commercial_reg_nu' => __('messages.main_info.lessor_commercial_reg_nu'),
            'lessor_representative' => __('messages.main_info.lessor_representative'),
            'lessor_position' => __('messages.main_info.lessor_position'),
            'lessor_national_id' => __('messages.main_info.lessor_national_id'),
            'lessor_signature_name' => __('messages.main_info.lessor_signature'),
            'contract_duration' => __('messages.main_info.contract_duration'),
            "annual_amount" => __('messages.main_info.annual_amount'),

            'unit_address' => __('messages.main_info.unit_address'),
            'virtual_contract_terms' => file_get_contents(storage_path('app/private/virtual_contract_terms.html')),
            'virtual_contract_renewal_template' => file_get_contents(storage_path('app/private/virtual_contract_renewal_template.html')),
        ];
    }
}
