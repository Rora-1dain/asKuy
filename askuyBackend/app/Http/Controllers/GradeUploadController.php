<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradeUploadRequest;
use App\Http\Resources\GradeUploadResource;
use App\Models\AiWeaknessMapping;
use App\Models\GradeUpload;
use App\Models\Subject;
use App\Services\FastApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class GradeUploadController extends Controller
{
    public function __construct(
        protected FastApiService $fastApiService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->get('per_page', 15), 100);

        $uploads = GradeUpload::with('subject')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate($perPage);

        return GradeUploadResource::collection($uploads);
    }

    public function store(StoreGradeUploadRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->store('grade_uploads', 'local');
        $absolutePath = Storage::disk('local')->path($path);

        $subject = null;
        if ($request->filled('subject_id')) {
            $subject = Subject::find($request->subject_id);
        }

        $upload = GradeUpload::create([
            'user_id' => $user->id,
            'subject_id' => $request->subject_id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $extension,
            'status' => 'pending',
            'analysis_result' => null,
            'error_message' => null,
        ]);

        // Kirim sinkron ke FastAPI /analyze (<100ms)
        $aiResult = $this->fastApiService->analyzeGrade(
            $absolutePath,
            $file->getClientOriginalName(),
            $subject?->name
        );

        if ($aiResult) {
            $upload->update([
                'status' => 'completed',
                'analysis_result' => $aiResult,
            ]);

            // Jika ada data weaknesses / topik lemah dari AI, simpan ke ai_weakness_mappings
            $weaknesses = $aiResult['weaknesses'] ?? $aiResult['weak_topics'] ?? [];
            if (is_array($weaknesses) && $request->filled('subject_id')) {
                foreach ($weaknesses as $w) {
                    $topicName = is_array($w) ? ($w['topic_name'] ?? $w['topic'] ?? null) : $w;
                    $mastery = is_array($w) ? (float) ($w['mastery_percentage'] ?? $w['score'] ?? 40) : 40.0;
                    $status = is_array($w) ? ($w['status'] ?? 'critical') : 'critical';

                    if ($topicName) {
                        AiWeaknessMapping::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'subject_id' => $request->subject_id,
                                'weak_topic' => $topicName,
                            ],
                            [
                                'mastery_percentage' => $mastery,
                                'status' => in_array($status, ['critical', 'improving', 'mastered']) ? $status : 'critical',
                                'last_evaluated_at' => now(),
                            ]
                        );
                    }
                }
            }

            return response()->json([
                'message' => 'Berkas nilai berhasil diunggah dan dianalisis oleh AI.',
                'data' => new GradeUploadResource($upload->fresh()->load('subject')),
            ], 201);
        }

        // Jika FastAPI belum aktif / timeout, tetap simpan status pending
        return response()->json([
            'message' => 'Berkas nilai berhasil diunggah (analisis AI sedang dalam antrean).',
            'data' => new GradeUploadResource($upload->load('subject')),
        ], 201);
    }

    public function show(Request $request, GradeUpload $gradeUpload): JsonResponse
    {
        if ($gradeUpload->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'data' => new GradeUploadResource($gradeUpload->load('subject')),
        ]);
    }
}
