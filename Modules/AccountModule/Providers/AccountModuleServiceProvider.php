<?php

namespace Modules\AccountModule\Providers;

use Illuminate\Support\Facades\View;
use Modules\AccountModule\app\Models\Account;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AccountModuleServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'AccountModule';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'accountmodule';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // pending requests badge in the admin sidebar
        View::composer('layoutmodule::admin.sidebar', function ($view) {
            $view->with('pendingAccountsCount', Account::where('status', Account::STATUS_PENDING)->count());
        });
    }
}
