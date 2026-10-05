@if ($account->logo_url)
    <img src="{{ $account->logo_url }}" alt="{{ $account->name_ar }}" class="account-logo">
@else
    <div class="account-logo account-logo--placeholder">{{ mb_substr($account->name_ar, 0, 1) }}</div>
@endif
