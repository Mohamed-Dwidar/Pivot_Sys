{{-- Admin create / edit account form. $account is null on create. --}}
<div class="form-section">Account Data</div>
@include('accountmodule::Account.partials.profile-fields', ['account' => $account])

<div class="form-section mt-8">Login Details</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $account?->user?->email])
    <div></div>
    @include('layoutmodule::partials.field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => !$account, 'hint' => $account ? 'Leave empty to keep the current password.' : null, 'attrs' => 'autocomplete=new-password'])
    @include('layoutmodule::partials.field', ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password', 'required' => !$account, 'attrs' => 'autocomplete=new-password'])
</div>

<div class="form-section mt-8">Status</div>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @include('layoutmodule::partials.field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => \Modules\AccountModule\app\Models\Account::STATUSES, 'value' => $account?->status ?? 'active'])
</div>
