<?php

namespace Pterodactyl\Services\Translation;

use Illuminate\Translation\Translator;
use Illuminate\Contracts\Translation\Loader;

class FrontendTranslationService
{
    /**
     * Namespaces used by the React frontend.
     */
    public const DEFAULT_NAMESPACES = ['activity', 'auth', 'strings', 'ui'];

    protected Loader $loader;

    public function __construct(Translator $translator)
    {
        $this->loader = $translator->getLoader();
    }

    /**
     * Load a single translation namespace for the given locale.
     */
    public function loadNamespace(string $locale, string $namespace): array
    {
        return $this->normalize($this->loader->load($locale, $namespace));
    }

    /**
     * Load all requested namespaces for the given locales.
     */
    public function loadLocales(array $locales, array $namespaces = self::DEFAULT_NAMESPACES): array
    {
        $response = [];

        foreach (array_unique($locales) as $locale) {
            foreach ($namespaces as $namespace) {
                $response[$locale][$namespace] = $this->loadNamespace($locale, $namespace);
            }
        }

        return $response;
    }

    /**
     * Convert Laravel style replacements like ":name" into "{{name}}"
     * so they can be consumed by i18next on the frontend.
     */
    protected function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalize($value);
            } else {
                $data[$key] = preg_replace('/:([\w.-]+\w)([^\w:]?|$)/m', '{{$1}}$2', $value);
            }
        }

        return $data;
    }
}
