<?php

return [
    /*
     * The captcha provider to use on authentication routes.
     * Supported values: none, recaptcha, turnstile
     */
    'provider' => env('CAPTCHA_PROVIDER', env('RECAPTCHA_ENABLED', true) ? 'recaptcha' : 'none'),

    /*
     * Enable or disable captchas.
     * This is retained for backwards compatibility and is normalized from the provider setting at runtime.
     */
    'enabled' => env('RECAPTCHA_ENABLED', true),

    /*
     * API endpoint for recaptcha checks. You should not edit this.
     */
    'domain' => env('RECAPTCHA_DOMAIN', 'https://www.google.com/recaptcha/api/siteverify'),

    /*
     * Use a custom secret key, we use our public one by default
     */
    'secret_key' => env('RECAPTCHA_SECRET_KEY', '6LcJcjwUAAAAALOcDJqAEYKTDhwELCkzUkNDQ0J5'),
    '_shipped_secret_key' => '6LcJcjwUAAAAALOcDJqAEYKTDhwELCkzUkNDQ0J5',

    /*
     * Use a custom website key, we use our public one by default
     */
    'website_key' => env('RECAPTCHA_WEBSITE_KEY', '6LcJcjwUAAAAAO_Xqjrtj9wWufUpYRnK6BW8lnfn'),
    '_shipped_website_key' => '6LcJcjwUAAAAAO_Xqjrtj9wWufUpYRnK6BW8lnfn',

    /*
     * API endpoint for Cloudflare Turnstile checks. You should not edit this.
     */
    'turnstile_domain' => env('TURNSTILE_DOMAIN', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),

    /*
     * Use a custom Turnstile secret key.
     */
    'turnstile_secret_key' => env('TURNSTILE_SECRET_KEY', ''),

    /*
     * Use a custom Turnstile website key.
     */
    'turnstile_website_key' => env('TURNSTILE_WEBSITE_KEY', ''),

    /*
     * Domain verification is enabled by default and compares the domain used when solving the captcha
     * as public keys cannot have domain verification enabled on the provider side.
     */
    'verify_domain' => true,
];
