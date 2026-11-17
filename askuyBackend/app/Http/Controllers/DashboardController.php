<?php

namespace App\Http\Controllers;

use App\Http\Resources\AiWeaknessMappingResource;
use App\Http\Resources\PracticePackageResource;
use App\Models\AiWeaknessMapping;
use App\Models\ExamSession;
use App\Models\PracticePackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Rata-rata nilai
        $averageScore = ExamSession::where('user_id', $user->id)->avg('score');

        // 2. 2-3 Topik terlemah
        $weakTopics = AiWeaknessMapping::with('subject')
            ->where('user_id', $user->id)
            ->orderBy('mastery_percentage', 'asc')
            ->limit(3)
            ->get();

        // 3. Modul / Paket soal terbaru
        $recentPackages = PracticePackage::with('subject')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return response()->json([
            'data' => [
                'study_streak' => (int) $user->study_streak,
                'last_study_date' => $user->last_study_date?->format('Y-m-d'),
                'average_score' => round((float) $averageScore, 2),
                'weak_topics' => AiWeaknessMappingResource::collection($weakTopics),
                'recent_packages' => PracticePackageResource::collection($recentPackages),
            ],
        ]);
    }
}