<?php

namespace App\Http\Controllers;

use App\Models\PracticePackage;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RpaJobController extends Controller
{
    /**
     * Klaim 1 pekerjaan paket soal pending secara atomik (SELECT ... FOR UPDATE).
     * Mengubah status menjadi 'processing' agar tidak dikerjakan dobel.
     */
    public function claim(): JsonResponse
    {
        $claimedPackage = DB::transaction(function () {
            $package = PracticePackage::with('subject')
                ->where('status', 'pending')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->first();

            if (! $package) {
                return null;
            }

            $package->update([
                'status' => 'processing',
                'error_message' => null,
            ]);

            return $package;
        });

        if (! $claimedPackage) {
            return response()->json([
                'message' => 'Tidak ada antrean paket soal berstatus pending saat ini.',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'message' => 'Berhasil mengklaim antrean pekerjaan.',
            'data' => [
                'job_id' => $claimedPackage->id,
                'package_id' => $claimedPackage->id,
                'title' => $claimedPackage->title,
                'subject_id' => $claimedPackage->subject_id,
                'subject_name' => $claimedPackage->subject?->name,
                'subject_code' => $claimedPackage->subject?->code,
                'topic_name' => $claimedPackage->topic_name,
                'difficulty' => $claimedPackage->difficulty,
                'total_questions' => $claimedPackage->total_questions,
                'status' => $claimedPackage->status,
                'created_at' => $claimedPackage->created_at,
            ],
        ], 200);
    }

    /**
     * Intip daftar antrean pending (opsional untuk monitoring/debug oleh robot/admin).
     */
    public function pending(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 10), 50);

        $packages = PracticePackage::with('subject')
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        return response()->json([
            'total' => $packages->count(),
            'data' => $packages,
        ]);
    }

    /**
     * Tandai job selesai:
     * Simpan file PDF (jika lewat pdf_url / path),
     * Simpan soal dan opsi ke database,
     * Update status jadi 'completed'.
     */
    public function complete(Request $request, PracticePackage $practicePackage): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pdf_path' => ['nullable', 'string'],
            'pdf_url' => ['nullable', 'string'],
            'questions' => ['nullable', 'array'],
            'questions.*.question_text' => ['required_with:questions', 'string'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.difficulty' => ['nullable', 'in:mudah,sedang,hots'],
            'questions.*.options' => ['required_with:questions', 'array', 'min:2'],
            'questions.*.options.*.label' => ['required', 'string', 'max:5'],
            'questions.*.options.*.text' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi payload gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $pdfPath = $validated['pdf_path'] ?? $validated['pdf_url'] ?? $practicePackage->pdf_path;

        DB::transaction(function () use ($practicePackage, $validated, $pdfPath) {
            $insertedQuestionsCount = 0;

            if (!empty($validated['questions'])) {
                foreach ($validated['questions'] as $qData) {
                    $question = Question::create([
                        'subject_id' => $practicePackage->subject_id,
                        'topic_name' => $practicePackage->topic_name ?: ($practicePackage->subject?->name ?? 'Umum'),
                        'difficulty' => $qData['difficulty'] ?? $practicePackage->difficulty ?? 'sedang',
                        'question_text' => $qData['question_text'],
                        'explanation' => $qData['explanation'] ?? null,
                    ]);

                    foreach ($qData['options'] as $opt) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_label' => $opt['label'],
                            'option_text' => $opt['text'],
                            'is_correct' => (bool) $opt['is_correct'],
                        ]);
                    }

                    $insertedQuestionsCount++;
                }
            }

            $practicePackage->update([
                'status' => 'completed',
                'pdf_path' => $pdfPath,
                'total_questions' => $insertedQuestionsCount > 0 ? $insertedQuestionsCount : $practicePackage->total_questions,
                'completed_at' => now(),
                'error_message' => null,
            ]);
        });

        return response()->json([
            'message' => 'Paket soal berhasil diselesaikan dan soal telah diimpor.',
            'data' => $practicePackage->fresh()->load('subject'),
        ]);
    }

    /**
     * Tandai job gagal.
     */
    public function fail(Request $request, PracticePackage $practicePackage): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'error_message' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $practicePackage->update([
            'status' => 'failed',
            'error_message' => $request->input('error_message'),
        ]);

        return response()->json([
            'message' => 'Status pekerjaan berhasil diperbarui menjadi failed.',
            'data' => $practicePackage,
        ]);
    }

    /**
     * Opsi upload file PDF langsung dari robot via multipart/form-data.
     */
    public function uploadPdf(Request $request, PracticePackage $practicePackage): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // max 20MB
        ]);

        $file = $request->file('file');
        $fileName = "paket_{$practicePackage->id}.pdf";
        $path = $file->storeAs('files', $fileName, 'public');

        $relativeUrl = "/storage/{$path}";

        $practicePackage->update([
            'pdf_path' => $relativeUrl,
        ]);

        return response()->json([
            'message' => 'Berkas PDF berhasil diunggah.',
            'pdf_path' => $relativeUrl,
        ]);
    }
}
