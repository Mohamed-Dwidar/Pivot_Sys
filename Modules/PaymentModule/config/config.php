<?php

return [
    'name' => 'PaymentModule',

    // the first id / payment_no of the payments added in the app: the numbers below are kept for the old data import
    // (old payments / incomes keep their old ids); the payments AUTO_INCREMENT is set to the same value by hand (to_run)
    'number_start' => (int) env('PAYMENT_NUMBER_START', 100001),
];
