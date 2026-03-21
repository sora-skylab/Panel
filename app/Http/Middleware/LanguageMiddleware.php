<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Pterodactyl\Traits\Helpers\AvailableLanguages;

class LanguageMiddleware
{
    use AvailableLanguages;

    public const COOKIE_NAME = 'pterodactyl_locale';
    public const QUERY_PARAMETER = 'locale';

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
        $requestedLocale = $this->getRequestedLocale($request);

        $this->app->setLocale($this->resolveLocale($request, $requestedLocale));

        $response = $next($request);

        if (filled($requestedLocale)) {
            if ($request->user() && $request->user()->language !== $requestedLocale) {
                $request->user()->forceFill(['language' => $requestedLocale])->save();
            }

            if (method_exists($response, 'withCookie')) {
                return $response->withCookie(cookie()->forever(self::COOKIE_NAME, $requestedLocale));
            }

            cookie()->queue(cookie()->forever(self::COOKIE_NAME, $requestedLocale));
        }

        return $response;
    }

    protected function resolveLocale(Request $request, ?string $requestedLocale = null): string
    {
        $default = config('app.panel_locale', config('app.locale', 'ja'));
        $available = array_keys($this->getAvailableLanguages());

        foreach ([$requestedLocale, $request->user()?->language, $request->cookie(self::COOKIE_NAME), $default] as $locale) {
            if (is_string($locale) && in_array($locale, $available, true)) {
                return $locale;
            }
        }

        return $default;
    }

    protected function getRequestedLocale(Request $request): ?string
    {
        $locale = $request->query(self::QUERY_PARAMETER);

        if (!is_string($locale)) {
            return null;
        }

        return in_array($locale, array_keys($this->getAvailableLanguages()), true) ? $locale : null;
    }
}
