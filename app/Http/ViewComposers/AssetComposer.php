<?php

namespace Pterodactyl\Http\ViewComposers;

use Illuminate\View\View;
use Pterodactyl\Traits\Helpers\AvailableLanguages;
use Pterodactyl\Services\Helpers\AssetHashService;
use Pterodactyl\Services\Translation\FrontendTranslationService;

class AssetComposer
{
    use AvailableLanguages;

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
        $currentLocale = app()->getLocale() ?: config('app.panel_locale', config('app.locale', 'ja'));
        $fallbackLocale = config('app.fallback_locale', 'en');
        $captchaProvider = config('recaptcha.provider', 'none');

        $view->with('asset', $this->assetHashService);
        $view->with('localeData', $this->translations->loadLocales([$currentLocale, $fallbackLocale]));
        $view->with('siteConfiguration', [
            'name' => config('app.name') ?? 'Pterodactyl',
            'footerCustomText' => config('app.footer_custom_text') ?? '',
            'locale' => $currentLocale,
            'availableLanguages' => $this->getAvailableLanguages(true),
            'recaptcha' => [
                'enabled' => $captchaProvider !== 'none',
                'provider' => $captchaProvider,
                'siteKey' => match ($captchaProvider) {
                    'turnstile' => config('recaptcha.turnstile_website_key') ?? '',
                    'recaptcha' => config('recaptcha.website_key') ?? '',
                    default => '',
                },
            ],
        ]);
    }
}
