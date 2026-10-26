<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Overdue QR behavior (SOA scan → payment-request)
    |--------------------------------------------------------------------------
    |
    | Preferred boolean (Starita / NovuStream QR):
    |   DUE_DATE_QR_PENALTY=true  → past due: charge amount_after_due / amount+penalty
    |   DUE_DATE_QR_PENALTY=false → past due: invalidate QR, redirect to expired page
    |
    | Legacy string (used only when DUE_DATE_QR_PENALTY is unset):
    |   NOVUPAY_OVERDUE_QR_MODE=void|penalty
    |
    */
    'due_date_qr_penalty' => env('DUE_DATE_QR_PENALTY'),

    'overdue_qr_mode' => env('NOVUPAY_OVERDUE_QR_MODE', 'void'),

    /*
    | Optional full URL override for void redirects. Use {reference_no} placeholder.
    | When empty, uses route('payment.expired', ...).
    */
    'overdue_redirect_url' => env('NOVUPAY_OVERDUE_REDIRECT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Other Services (NovuStream / NovuSurge white papers)
    |--------------------------------------------------------------------------
    |
    | Set to false to hide the nav menu and redirect those URLs to home.
    |
    */
    'other_services_enabled' => env('NOVUPAY_OTHER_SERVICES_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Hosted Checkout API
    |--------------------------------------------------------------------------
    |
    | Credentials from Link Store after signing in at https://novu-pay.com.
    | Client Secret is for webhook signature verification only.
    |
    */
    'api_key' => env('NOVUPAY_API_KEY'),
    'client_id' => env('NOVUPAY_CLIENT_ID'),
    'client_secret' => env('NOVUPAY_CLIENT_SECRET'),
    'api_base' => rtrim((string) env('NOVUPAY_API_BASE', 'https://api.novu-pay.com'), '/'),
    'api_resolve_ip' => env('NOVUPAY_API_RESOLVE_IP'),
    'hosted_base' => rtrim((string) env('NOVUPAY_HOSTED_BASE', 'https://novu-pay.com'), '/'),
    'notification_url' => env('NOVUPAY_NOTIFICATION_URL', 'https://portal.staritawaterdistrictpamp.gov.ph/webhooks/novupay'),
    'redirect_url' => env('NOVUPAY_REDIRECT_URL', 'https://portal.staritawaterdistrictpamp.gov.ph/payments/novupay/return'),
    'min_amount' => 50.00,

];
