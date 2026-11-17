<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePracticePackageRequest;
use App\Http\Resources\PracticePackageResource;
use App\Models\PracticePackage;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PracticePackageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PracticePackage::with('subject')
            ->where('user_id', $request->user()->id);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $packages = $query->orderByDesc('id')->paginate($perPage);

        return PracticePackageResource::collection($packages);
    }

    public function store(GeneratePracticePackageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $title = $validated['title'] ?? null;
        if (empty($title)) {
            $subject = Subject::find($validated['subject_id']);
            $subjectName = $subject ? $subject->name : 'Mata Pelajaran';
            $title = "Paket Soal {$subjectName} - {$validated['topic_name']} (" . ucfirst($validated['difficulty']) . ")";
        }

        $package = PracticePackage::create([
            'user_id' => $user->id,
            'subject_id' => $validated['subject_id'],
            'title' => $title,
            'topic_name' => $validated['topic_name'],
            'difficulty' => $validated['difficulty'],
            'total_questions' => (int) $validated['total_questions'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Permintaan generate paket soal berhasil dibuat (antrean pending).',
            'data' => new PracticePackageResource($package->load('subject')),
        ], 201);
    }

    public function show(Request $request, PracticePackage $practicePackage): JsonResponse
    {
        if ($practicePackage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'data' => new PracticePackageResource($practicePackage->load('subject')),
        ]);
    }

    public function download(Request $request, PracticePackage $practicePackage): JsonResponse|BinaryFileResponse
    {
        if ($practicePackage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($practicePackage->status !== 'completed') {
            return response()->json([
                'message' => 'Paket soal belum selesai diproses oleh sistem.',
                'status' => $practicePackage->status,
            ], 400);
        }

        $pdfPath = $practicePackage->pdf_path;
        if (! $pdfPath) {
            return response()->json(['message' => 'File PDF belum tersedia.'], 404);
        }

        if (Storage::disk('local')->exists($pdfPath)) {
            return Storage::disk('local')->download($pdfPath, basename($pdfPath));
        }

        if (file_exists(public_path($pdfPath))) {
            return response()->download(public_path($pdfPath));
        }

        if (file_exists($pdfPath)) {
            return response()->download($pdfPath);
        }

        return response()->json(['message' => 'File PDF tidak ditemukan di storage server.'], 404);
    }
}