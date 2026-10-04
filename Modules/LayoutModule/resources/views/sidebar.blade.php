<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header d-flex">
            <a href="{{ url('/') }}" target="_blank" class="b-brand text-primary d-inline-block">
                <img src="{{ asset('assets/images/logo-h.png') }}" alt="logo" class="logo" style="height: 60px;">
            </a>
        </div>

        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="&nbsp;">&nbsp;</label>
                    <i class="ph-duotone ph-gauge"></i>
                </li>

                <li class="pc-item">
                    <a href="{{ route('dashboard.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph-duotone ph-gauge"></i>
                        </span>
                        <span class="pc-mtext"
                            data-i18n="{{ __('messages.dashboard') }}">{{ __('messages.dashboard') }}</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('invoices') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph-duotone ph-files"></i>
                        </span>
                        <span class="pc-mtext"
                            data-i18n="{{ __('messages.invoices') }}">{{ __('messages.invoices') }}</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('contracts') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph-duotone ph-file-text"></i>
                        </span>
                        <span class="pc-mtext"
                            data-i18n="{{ __('messages.contracts') }}">{{ __('messages.contracts') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
