<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\StoreChatSessionRequest;
use App\Http\Requests\UpdateChatSessionRequest;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatSessionResource;
use App\Models\ChatSession;
use App\Services\FastApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatSessionController extends Controller
{
    public function __construct(
        protected FastApiService $fastApiService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->get('per_page', 15), 100);

        $sessions = ChatSession::with('subject')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return ChatSessionResource::collection($sessions);
    }

    public function store(StoreChatSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $session = ChatSession::create([
            'user_id' => $request->user()->id,
            'subject_id' => $validated['subject_id'] ?? null,
            'title' => !empty($validated['title']) ? $validated['title'] : 'Sesi baru',
        ]);

        return response()->json([
            'message' => 'Sesi chat berhasil dibuat.',
            'data' => new ChatSessionResource($session->load('subject')),
        ], 201);
    }

    public function show(Request $request, ChatSession $chatSession): JsonResponse
    {
        if ($chatSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $chatSession->load(['subject', 'messages' => fn ($q) => $q->orderBy('created_at', 'asc')]);

        return response()->json([
            'data' => new ChatSessionResource($chatSession),
        ]);
    }

    public function update(UpdateChatSessionRequest $request, ChatSession $chatSession): JsonResponse
    {
        if ($chatSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $chatSession->update($request->validated());

        return response()->json([
            'message' => 'Sesi chat berhasil diperbarui.',
            'data' => new ChatSessionResource($chatSession->load('subject')),
        ]);
    }

    public function destroy(Request $request, ChatSession $chatSession): JsonResponse
    {
        if ($chatSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $chatSession->delete();

        return response()->json([
            'message' => 'Sesi chat berhasil dihapus.',
        ]);
    }

    public function storeMessage(StoreChatMessageRequest $request, ChatSession $chatSession): JsonResponse
    {
        if ($chatSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validated();

        // 1. Simpan pesan dari user
        $userMessage = $chatSession->messages()->create([
            'role' => 'user',
            'content' => $validated['content'],
            'image_path' => $validated['image_path'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        // Auto update judul sesi chat jika masih default
        if ($chatSession->title === 'Sesi baru') {
            $shortTitle = mb_substr($validated['content'], 0, 40);
            $chatSession->update(['title' => $shortTitle]);
        }

        // 2. Siapkan riwayat chat untuk AI
        $recentMessages = $chatSession->messages()
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        // 3. Panggil FastAPI AI Tutor (/explain atau /chat)
        $subjectName = $chatSession->subject?->name;
        $aiReply = $this->fastApiService->explainOrChat(
            $validated['content'],
            $subjectName,
            $recentMessages
        );

        $assistantMessage = null;
        if ($aiReply) {
            $assistantMessage = $chatSession->messages()->create([
                'role' => 'assistant',
                'content' => $aiReply,
                'image_path' => null,
                'metadata' => ['provider' => 'fastapi_ai'],
            ]);
        }

        $chatSession->touch();

        return response()->json([
            'message' => 'Pesan berhasil diproses.',
            'data' => [
                'user_message' => new ChatMessageResource($userMessage),
                'assistant_message' => $assistantMessage ? new ChatMessageResource($assistantMessage) : null,
            ],
        ], 201);
    }
}
