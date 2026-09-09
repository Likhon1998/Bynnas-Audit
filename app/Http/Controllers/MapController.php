<?php

namespace App\Http\Controllers;

use App\Services\AuditMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        return view('map.index', [
            'title' => 'Map',
        ]);
    }

    public function live(Request $request, AuditMapService $map): JsonResponse
    {
        return response()
            ->json($map->live($request->user()))
            ->header('Cache-Control', 'private, no-store');
    }
}
