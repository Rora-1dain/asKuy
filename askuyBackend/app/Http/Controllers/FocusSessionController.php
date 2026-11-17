<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFocusSessionRequest;
use App\Http\Resources\FocusSessionResource;
use App\Models\FocusSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FocusSessionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->get('per_page', 15), 100);

        $sessions = FocusSession::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate($perPage);

        return FocusSessionResource::collection($sessions);
    }

    public function store(StoreFocusSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['completed_at'] = $validated['completed_at'] ?? now();

        $session = FocusSession::create($validated);

        return response()->json([
            'message' => 'Sesi fokus berhasil disimpan.',
            'data' => new FocusSessionResource($session),
        ], 201);
    }

    public function show(Request $request, FocusSession $focusSession): JsonResponse
    {
        if ($focusSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'data' => new FocusSessionResource($focusSession),
        ]);
    }

    public function destroy(Request $request, FocusSession $focusSession): JsonResponse
    {
        if ($focusSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $focusSession->delete();

        return response()->json([
            'message' => 'Sesi fokus berhasil dihapus.',
        ]);
    }
}