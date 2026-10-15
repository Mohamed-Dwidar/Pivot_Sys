<?php

namespace Modules\ReservationModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\AccountModule\app\Models\Account;
use Modules\PaymentModule\app\Services\ShiftClosingService;
use Modules\ReservationModule\app\Services\ScheduleService;

/**
 * The Schedule page, the same for the account and its employees (ScheduleService): ?date=Y-m-d (default: the business date).
 * index() = the page; sections() = its content only (custom.js todayPage: ‹ › and after every save in a popup).
 * The shift strip: the logged in user's open shift (ShiftClosingService::openTotals(), hidden when nothing is open).
 */
trait ScheduleActions
{
    private ScheduleService $scheduleService;
    private ShiftClosingService $shiftService;

    abstract protected function area(): string;

    public function __construct(ScheduleService $scheduleService, ShiftClosingService $shiftService)
    {
        $this->scheduleService = $scheduleService;
        $this->shiftService = $shiftService;
    }

    public function index(Request $request)
    {
        return view('reservationmodule::Schedule.index', $this->pageData($request));
    }

    public function sections(Request $request)
    {
        return view('reservationmodule::Schedule.partials.sections', $this->pageData($request));
    }

    private function pageData(Request $request): array
    {
        $accountId = Auth::user()->userable->ownerAccountId();
        $account = Account::settingsOf($accountId);
        $input = (string) $request->query('date', '');
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $input) && strtotime($input) ? Carbon::parse($input) : $account->businessDate();

        return $this->scheduleService->page($accountId, $account, $date) + [
            'area' => $this->area(),
            'shift' => $this->shiftService->openTotals($accountId, Auth::id()),
        ];
    }
}
