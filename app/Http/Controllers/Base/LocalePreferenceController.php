<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Middleware\LanguageMiddleware;
use Pterodactyl\Http\Requests\Base\LocalePreferenceRequest;

class LocalePreferenceController extends Controller
{
    public function __invoke(LocalePreferenceRequest $request): JsonResponse
    {
        return (new JsonResponse([], Response::HTTP_NO_CONTENT))
            ->withCookie(cookie()->forever(LanguageMiddleware::COOKIE_NAME, $request->input('locale')));
    }
}
