<?php

namespace Pterodactyl\Http\ViewComposers;

use Illuminate\View\View;
use Pterodactyl\Services\Helpers\AssetHashService;
use Pterodactyl\Services\Translation\FrontendTranslationService;

class AssetComposer
{
    /**
     * AssetComposer constructor.
     */
    public function __construct(
        private AssetHashService $assetHashService,
        private FrontendTranslationService $translations,
    ) {
    }

    /**
     * Provide access to the asset service in the views.
     */
    public function compose(View $view): void
    {
        $currentLocale = app()->getLocale() ?: 'en';
        $fallbackLocale = config('app.fallback_locale', 'en');

        $view->with('asset', $this->assetHashService);
        $view->with('localeData', $this->translations->loadLocales([$currentLocale, $fallbackLocale]));
        $view->with('siteConfiguration', [
            'name' => config('app.name') ?? 'Pterodactyl',
            'locale' => config('app.panel_locale', config('app.locale') ?? 'en'),
            'recaptcha' => [
                'enabled' => config('recaptcha.enabled', false),
                'siteKey' => config('recaptcha.website_key') ?? '',
            ],
        ]);
    }
}
