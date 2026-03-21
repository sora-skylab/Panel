<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Pterodactyl\Traits\Helpers\AvailableLanguages;

class LanguageMiddleware
{
    use AvailableLanguages;

    public const COOKIE_NAME = 'pterodactyl_locale';

    /**
     * LanguageMiddleware constructor.
     */
    public function __construct(private Application $app)
    {
    }

    /**
     * Handle an incoming request and set the user's preferred language.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $this->app->setLocale($this->resolveLocale($request));

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        $default = config('app.panel_locale', config('app.locale', 'en'));
        $available = array_keys($this->getAvailableLanguages());

        foreach ([$request->user()?->language, $request->cookie(self::COOKIE_NAME), $default] as $locale) {
            if (is_string($locale) && in_array($locale, $available, true)) {
                return $locale;
            }
        }

        return $default;
    }
}
