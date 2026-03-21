<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Base\LocaleRequest;
use Pterodactyl\Services\Translation\FrontendTranslationService;

class LocaleController extends Controller
{
    public function __construct(private FrontendTranslationService $translations)
    {
    }

    /**
     * Returns translation data given a specific locale and namespace.
     */
    public function __invoke(LocaleRequest $request): JsonResponse
    {
        $locale = $request->input('locale');
        $namespace = $request->input('namespace');
        $response[$locale][$namespace] = $this->translations->loadNamespace($locale, $namespace);

        return new JsonResponse($response, 200, [
            // Cache this in the browser for an hour, and allow the browser to use a stale
            // cache for up to a day after it was created while it fetches an updated set
            // of translation keys.
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400',
            'ETag' => md5(json_encode($response, JSON_THROW_ON_ERROR)),
        ]);
    }
}
