<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitExamRequest;
use App\Http\Resources\ExamAnswerResource;
use App\Http\Resources\ExamSessionResource;
use App\Models\ExamAnswer;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExamSessionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->get('per_page', 15), 100);

        $sessions = ExamSession::with('practicePackage')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate($perPage);

        return ExamSessionResource::collection($sessions);
    }

    public function store(SubmitExamRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $answersInput = $validated['answers'];

        $totalQuestions = count($answersInput);
        $correctCount = 0;
        $evaluatedAnswers = [];

        foreach ($answersInput as $item) {
            $questionId = (int) $item['question_id'];
            $selectedOptionId = isset($item['selected_option_id']) ? (int) $item['selected_option_id'] : null;
            $isCorrect = false;

            if ($selectedOptionId) {
                $option = QuestionOption::where('id', $selectedOptionId)
                    ->where('question_id', $questionId)
                    ->first();
                $isCorrect = $option ? (bool) $option->is_correct : false;
            }

            if ($isCorrect) {
                $correctCount++;
            }

            $evaluatedAnswers[] = [
                'question_id' => $questionId,
                'selected_option_id' => $selectedOptionId,
                'is_correct' => $isCorrect,
            ];
        }

        $accuracy = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;
        $score = $accuracy;

        $gradeCategory = match (true) {
            $score >= 85 => 'Sangat Baik (A)',
            $score >= 70 => 'Baik (B)',
            $score >= 55 => 'Cukup (C)',
            default => 'Perlu Remedial (D)',
        };

        $session = ExamSession::create([
            'user_id' => $user->id,
            'package_id' => $validated['package_id'] ?? null,
            'score' => $score,
            'accuracy_percentage' => $accuracy,
            'duration_seconds' => (int) $validated['duration_seconds'],
            'grade_category' => $gradeCategory,
        ]);

        foreach ($evaluatedAnswers as $ans) {
            $session->examAnswers()->create($ans);
        }

        // Update Study Streak
        $today = now()->toDateString();
        $lastStudy = $user->last_study_date ? $user->last_study_date->toDateString() : null;

        if ($lastStudy !== $today) {
            $yesterday = now()->subDay()->toDateString();
            if ($lastStudy === $yesterday) {
                $user->study_streak = ((int) $user->study_streak) + 1;
            } else {
                $user->study_streak = 1;
            }
            $user->last_study_date = $today;
            $user->save();
        }

        return response()->json([
            'message' => 'Ujian berhasil diselesaikan.',
            'data' => new ExamSessionResource($session->load([
                'practicePackage',
                'examAnswers.question.options',
                'examAnswers.selectedOption',
            ])),
        ], 201);
    }

    public function show(Request $request, ExamSession $examSession): JsonResponse
    {
        if ($examSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $examSession->load([
            'practicePackage',
            'examAnswers.question.options',
            'examAnswers.selectedOption',
        ]);

        return response()->json([
            'data' => new ExamSessionResource($examSession),
        ]);
    }

    public function remedial(Request $request, ExamSession $examSession): AnonymousResourceCollection|JsonResponse
    {
        if ($examSession->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $wrongAnswers = $examSession->examAnswers()
            ->where('is_correct', false)
            ->with(['question.options', 'question.subject', 'selectedOption'])
            ->get();

        return ExamAnswerResource::collection($wrongAnswers);
    }
}