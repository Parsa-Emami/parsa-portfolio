<?php

return [
    'enabled' => env('PORTFOLIO_STATIC_EXPORT', false),

    // Optional endpoint such as Formspree. Leave empty to use a mailto fallback.
    'contact_endpoint' => env('STATIC_CONTACT_ENDPOINT'),

    // Optional custom domain written to the generated CNAME file.
    'cname' => env('GITHUB_PAGES_CNAME'),

    'output_path' => env('STATIC_EXPORT_PATH', storage_path('app/static-export')),
];
