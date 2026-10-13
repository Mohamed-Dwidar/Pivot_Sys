<?php

namespace Modules\PaymentModule\app\Console;

use Illuminate\Console\Command;
use Modules\PaymentModule\app\Models\ShiftClosing;
use Modules\PaymentModule\app\Models\ShiftEvent;
use Modules\PaymentModule\app\Services\ShiftClosingService;

// the totals of closed shifts from their payments + expenses now (after an import: to_run STEP 5); the same code as the app
class RecalculateShiftsCommand extends Command
{
    protected $signature = 'shifts:recalculate {--imported : only the "Imported from the old system" shifts} {--account= : only this account id}';

    protected $description = 'Recalculate the totals (count, collected, expenses, net, per method) of closed shifts from their payments and expenses';

    public function handle(ShiftClosingService $shiftService): int
    {
        $query = ShiftClosing::query()
            ->when($this->option('imported'), fn ($query) => $query->where('notes', ShiftClosingService::IMPORTED_NOTE))
            ->when($this->option('account'), fn ($query, $accountId) => $query->where('account_id', (int) $accountId));
        $count = 0;
        foreach ($query->cursor() as $closing) {
            $shiftService->recalculate($closing);
            // an imported shift: its "close" event shows the same totals (to_run 3.15 creates it without)
            if ($closing->notes === ShiftClosingService::IMPORTED_NOTE) {
                ShiftEvent::where('shift_closing_id', $closing->id)->where('action', 'close')->update(['totals' => json_encode($closing->totals)]);
            }
            $count++;
        }
        $this->info($count . ' shift(s) recalculated.');

        return self::SUCCESS;
    }
}
