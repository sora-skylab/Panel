<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Helpers\PanelUpdateService;

class PanelUpdateController extends Controller
{
    public function __construct(private PanelUpdateService $panelUpdateService)
    {
    }

    /**
     * Starts an automatic Panel update in the background.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function store(): JsonResponse
    {
        return response()->json([
            'data' => $this->panelUpdateService->startAutomaticUpdate(),
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->panelUpdateService->getOverview($request->boolean('refresh_version')),
        ]);
    }
}
