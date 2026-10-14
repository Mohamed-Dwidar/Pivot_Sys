<?php

namespace Modules\ReservationModule\app\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\ReservationModule\app\Models\ReservationStatus;
use Modules\SpaceModule\app\Models\SubscriptionType;

/**
 * ONE-TIME guess for history (to_run STEP 5b, user 2026-10-14): link each imported period to the period it renews (renewal_of).
 * Periods = counted reservations of the period types with a member. For each period (oldest start first) without renewal_of:
 * the closest previous period of the same member + type that is not linked yet, same unit first, then same space (never
 * another space), starting after it, from 7 days before to 15 days after its end (the days of the dates). Each old period
 * is linked at most once; existing links are kept, so it can run again. The member is used only here (the app links by Renew).
 */
class LinkRenewalsCommand extends Command
{
    const DAYS_BEFORE = 7;
    const DAYS_AFTER = 15;

    protected $signature = 'reservations:link-renewals {--dry-run : count only, nothing is saved} {--account= : only this account id}';

    protected $description = 'Link the imported subscription periods to the period each one renews (renewal_of), a one-time guess for history';

    public function handle(): int
    {
        $rows = Reservation::query()
            ->whereIn('subscription_type_id', SubscriptionType::idsOf('period') ?: [0])
            ->whereNotIn('reservation_status_id', ReservationStatus::notCountedIds() ?: [0])
            ->where('member_id', '>', 0)->whereNotNull('start_at')
            ->when($this->option('account'), fn ($query, $accountId) => $query->where('account_id', (int) $accountId))
            ->orderBy('start_at')->orderBy('id')
            ->get(['id', 'account_id', 'member_id', 'subscription_type_id', 'space_id', 'unit_id', 'start_at', 'end_at', 'renewal_of']);

        // the old periods already renewed (by any not deleted reservation: the links of the app are kept)
        $used = array_flip(Reservation::whereNotNull('renewal_of')->pluck('renewal_of')->all());
        $groups = $rows->groupBy(fn ($row) => $row->account_id . '-' . $row->member_id . '-' . $row->subscription_type_id);
        $links = [];
        $by = ['unit' => 0, 'space' => 0];

        foreach ($rows as $new) {
            if ($new->renewal_of) {
                continue;
            }
            $best = null;
            foreach ($groups[$new->account_id . '-' . $new->member_id . '-' . $new->subscription_type_id] as $old) {
                if ($old->id === $new->id || isset($used[$old->id]) || !$old->start_at->lt($new->start_at) || (int) $old->space_id !== (int) $new->space_id) {
                    continue;
                }
                $gap = $this->gapDays($old, $new);
                if ($gap < -self::DAYS_BEFORE || $gap > self::DAYS_AFTER) {
                    continue;
                }
                // same unit first, then the nearest end, then the newest
                $rank = [(int) $old->unit_id === (int) $new->unit_id ? 0 : 1, abs($gap), -$old->id];
                if (!$best || $rank < $best[0]) {
                    $best = [$rank, $old];
                }
            }
            if ($best) {
                $used[$best[1]->id] = true;
                $links[$new->id] = $best[1]->id;
                $by[$best[0][0] === 0 ? 'unit' : 'space']++;
            }
        }

        $now = now();
        $running = $rows->filter(fn ($row) => $row->start_at->lte($now) && ($row->end_at === null || $row->end_at->gt($now)));
        $runningLinked = $running->filter(fn ($row) => $row->renewal_of || isset($links[$row->id]))->count();

        if (!$this->option('dry-run')) {
            DB::transaction(function () use ($links) {
                // a query update: no updated_at, no events
                foreach ($links as $id => $periodId) {
                    Reservation::whereKey($id)->whereNull('renewal_of')->toBase()->update(['renewal_of' => $periodId]);
                }
            });
        }

        $this->info(($this->option('dry-run') ? '[dry run, nothing saved] ' : '') . count($links) . ' period(s) linked: '
            . $by['unit'] . ' by unit, ' . $by['space'] . ' by space.');
        $this->info($runningLinked . ' of the ' . $running->count() . ' running period(s) are linked to the period they renew.');

        return self::SUCCESS;
    }

    // days from the old period's last day to the new one's first day (an end before the start = the start)
    private function gapDays(Reservation $old, Reservation $new): int
    {
        $end = $old->end_at && $old->end_at->gte($old->start_at) ? $old->end_at : $old->start_at;
        return (int) floor(Carbon::parse($end)->startOfDay()->diffInDays($new->start_at->copy()->startOfDay(), false));
    }
}
