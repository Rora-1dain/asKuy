<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FastApiService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.fastapi.base_url', 'http://localhost:8000'), '/');
        $this->timeout = (int) config('services.fastapi.timeout', 20);
    }

    /**
     * Kirim file nilai untuk dianalisis oleh AI Engine (FastAPI).
     * Rule-based analisis sinkron (<100ms).
     */
    public function analyzeGrade(string $absoluteFilePath, string $originalFileName, ?string $subjectName = null): ?array
    {
        try {
            $client = Http::timeout($this->timeout);

            if (file_exists($absoluteFilePath)) {
                $client = $client->attach(
                    'file',
                    file_get_contents($absoluteFilePath),
                    $originalFileName
                );
            }

            $response = $client->post("{$this->baseUrl}/analyze", [
                'subject_name' => $subjectName,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('FastAPI /analyze returned error status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('FastAPI /analyze connection failed', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Minta penjelasan / chat balasan dari AI Tutor (FastAPI /explain atau /chat).
     */
    public function explainOrChat(string $prompt, ?string $subjectName = null, array $history = []): ?string
    {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/explain", [
                'prompt' => $prompt,
                'subject' => $subjectName,
                'history' => $history,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                return $json['reply'] ?? $json['explanation'] ?? $json['content'] ?? null;
            }

            // Fallback ke endpoint /chat jika /explain 404
            if ($response->status() === 404) {
                $chatResponse = Http::timeout($this->timeout)->post("{$this->baseUrl}/chat", [
                    'prompt' => $prompt,
                    'subject' => $subjectName,
                    'history' => $history,
                ]);

                if ($chatResponse->successful()) {
                    $json = $chatResponse->json();
                    return $json['reply'] ?? $json['content'] ?? null;
                }
            }

            Log::warning('FastAPI /explain returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('FastAPI /explain connection failed', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
