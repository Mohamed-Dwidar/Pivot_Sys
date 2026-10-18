<?php

namespace Modules\MemberModule\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class MemberModuleServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'MemberModule';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'membermodule';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        // to_run STEP 5c: members sharing one phone (phone_key) → one member
        \Modules\MemberModule\app\Console\MergeDuplicatesCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
