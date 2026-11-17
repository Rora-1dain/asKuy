<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Question::with(['subject', 'options']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('topic_name')) {
            $query->where('topic_name', 'ilike', '%' . $request->topic_name . '%');
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'ilike', '%' . $request->search . '%');
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $questions = $query->orderByDesc('id')->paginate($perPage);

        return QuestionResource::collection($questions);
    }

    public function store(StoreQuestionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $optionsData = $validated['options'];
        unset($validated['options']);

        $question = Question::create($validated);

        foreach ($optionsData as $option) {
            $question->options()->create([
                'option_label' => $option['option_label'],
                'option_text' => $option['option_text'],
                'is_correct' => (bool) $option['is_correct'],
            ]);
        }

        return response()->json([
            'message' => 'Soal berhasil ditambahkan.',
            'data' => new QuestionResource($question->load(['subject', 'options'])),
        ], 201);
    }

    public function show(Question $question): JsonResponse
    {
        return response()->json([
            'data' => new QuestionResource($question->load(['subject', 'options'])),
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['options'])) {
            $optionsData = $validated['options'];
            unset($validated['options']);

            $question->options()->delete();
            foreach ($optionsData as $option) {
                $question->options()->create([
                    'option_label' => $option['option_label'],
                    'option_text' => $option['option_text'],
                    'is_correct' => (bool) $option['is_correct'],
                ]);
            }
        }

        $question->update($validated);

        return response()->json([
            'message' => 'Soal berhasil diperbarui.',
            'data' => new QuestionResource($question->load(['subject', 'options'])),
        ]);
    }

    public function destroy(Question $question): JsonResponse
    {
        $question->delete();

        return response()->json([
            'message' => 'Soal berhasil dihapus.',
        ]);
    }
}