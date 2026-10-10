<?php

namespace Modules\PaymentModule\app\Console;

use Illuminate\Console\Command;
use Modules\PaymentModule\app\Services\PaymentService;

// paid_amount / remaining of every reservation / package from their payments (after an import, a fix by hand ...)
class RefreshPaymentsCommand extends Command
{
    protected $signature = 'payments:refresh {--account= : only this account id}';

    protected $description = 'Recalculate the paid amount / remaining of all reservations and packages from their payments';

    public function handle(PaymentService $paymentService): int
    {
        $changed = $paymentService->refreshAll($this->option('account') ?: null);
        $this->info($changed . ' reservation(s) / package(s) updated.');

        return self::SUCCESS;
    }
}
