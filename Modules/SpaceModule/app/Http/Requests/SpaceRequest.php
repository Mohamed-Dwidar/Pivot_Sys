<?php

namespace Modules\SpaceModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpaceRequest extends FormRequest
{
    public function rules(): array
    {
        $accountId = $this->user()->userable_id;

        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'boolean',
            // only the account's own subscription types
            'subscription_types' => 'nullable|array',
            'subscription_types.*' => ['integer', Rule::exists('subscription_types', 'id')->where('account_id', $accountId)],
            // only the account's own plans
            'plans' => 'nullable|array',
            'plans.*' => ['integer', Rule::exists('plans', 'id')->where('account_id', $accountId)->whereNull('deleted_at')],
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image must not be greater than 2 MB.',
            'images.max' => 'You can upload up to 10 images at once.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name_ar' => 'name (Arabic)',
            'name_en' => 'name (English)',
            'subscription_types.*' => 'subscription type',
            'plans.*' => 'plan',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
