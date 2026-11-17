<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlashcardRequest;
use App\Http\Requests\UpdateFlashcardProgressRequest;
use App\Http\Requests\UpdateFlashcardRequest;
use App\Http\Resources\FlashcardProgressResource;
use App\Http\Resources\FlashcardResource;
use App\Models\Flashcard;
use App\Models\FlashcardProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FlashcardController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;
        $query = Flashcard::with([
            'subject',
            'progresses' => fn ($q) => $q->where('user_id', $userId),
        ]);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $flashcards = $query->orderBy('id')->paginate($perPage);

        return FlashcardResource::collection($flashcards);
    }

    public function store(StoreFlashcardRequest $request): JsonResponse
    {
        $flashcard = Flashcard::create($request->validated());

        return response()->json([
            'message' => 'Flashcard berhasil ditambahkan.',
            'data' => new FlashcardResource($flashcard->load('subject')),
        ], 201);
    }

    public function show(Request $request, Flashcard $flashcard): JsonResponse
    {
        $userId = $request->user()->id;
        $flashcard->load([
            'subject',
            'progresses' => fn ($q) => $q->where('user_id', $userId),
        ]);

        return response()->json([
            'data' => new FlashcardResource($flashcard),
        ]);
    }

    public function update(UpdateFlashcardRequest $request, Flashcard $flashcard): JsonResponse
    {
        $flashcard->update($request->validated());

        return response()->json([
            'message' => 'Flashcard berhasil diperbarui.',
            'data' => new FlashcardResource($flashcard->load('subject')),
        ]);
    }

    public function destroy(Flashcard $flashcard): JsonResponse
    {
        $flashcard->delete();

        return response()->json([
            'message' => 'Flashcard berhasil dihapus.',
        ]);
    }

    public function updateProgress(UpdateFlashcardProgressRequest $request, Flashcard $flashcard): JsonResponse
    {
        $userId = $request->user()->id;

        $progress = FlashcardProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'flashcard_id' => $flashcard->id,
            ],
            [
                'status' => $request->status,
                'last_reviewed_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Progress flashcard berhasil diperbarui.',
            'data' => new FlashcardProgressResource($progress->load('flashcard')),
        ]);
    }

    public function myProgress(Request $request): AnonymousResourceCollection
    {
        $query = FlashcardProgress::with(['flashcard.subject'])
            ->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $progresses = $query->orderByDesc('last_reviewed_at')->paginate($perPage);

        return FlashcardProgressResource::collection($progresses);
    }
}