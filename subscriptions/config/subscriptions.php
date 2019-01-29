<?php
return [
    'environment_sandbox' => env('SUB_SANDBOX', true),
    'exlude-old-transactions' => env('SUB_EXLUDE_OLD_TRANSACTION', true),
    'password' => env('SUB_PASSWORD', null),
    'appsflyer' => [
        'APP_ID' => env('APPSFLYER_APP_ID', null),
        'DEV_TOKEN' => env('APPSFLYER_DEV_TOKEN', null),
        'BUNDLE' => env('APPSFLYER_DEV_TOKEN', null),
    ]
];
