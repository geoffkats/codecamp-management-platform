<?php

return [
    /*
    | Default Code Camp membership application fee in UGX. Admins can override it
    | under System Settings (key: membership_application_fee).
    */
    'application_fee' => (int) env('MEMBERSHIP_APPLICATION_FEE', 200000),

    'currency' => 'UGX',
];
