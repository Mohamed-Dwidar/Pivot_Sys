<?php

return [
    'name' => 'PaymentModule',

    // the first id / payment_no of the payments added in the app: the numbers below are kept for the old data import
    // (old payments / incomes keep their old ids); the payments AUTO_INCREMENT is set to the same value by hand (to_run)
    'number_start' => (int) env('PAYMENT_NUMBER_START', 100001),

    // employees' shift reminders (docs/payments-plan.md §2b): the oldest payment of an open shift older than this → the
    // "close your shift" popup; after "I'm still working" (a reason is required) it comes back after the snooze
    'shift_reminder_hours' => 12,
    'shift_snooze_hours' => 4,
];
