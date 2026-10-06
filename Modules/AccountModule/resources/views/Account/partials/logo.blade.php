@if ($account->logo_url)
    <img src="{{ $account->logo_url }}" alt="{{ $account->name }}" class="account-logo">
@else
    <div class="account-logo account-logo--placeholder">{{ mb_substr($account->name, 0, 1) }}</div>
@endif
