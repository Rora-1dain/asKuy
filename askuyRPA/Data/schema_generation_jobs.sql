-- Skema Tabel generation_jobs untuk PostgreSQL (asKuy)
-- Digunakan oleh:
-- 1. FastAPI / Laravel: INSERT job baru saat user minta generate paket soal
-- 2. UiPath RPA: Polling SELECT job status 'queued', update 'processing', lalu 'completed'

CREATE TABLE IF NOT EXISTS generation_jobs (
    id VARCHAR(64) PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'queued', -- queued, processing, completed, failed
    weak_topics JSONB,                            -- ["Aturan Rantai", "Integral Tentu"]
    questions JSONB,                              -- Array list soal yang dipilih oleh AI / Rule-based
    jumlah_soal INT DEFAULT 10,
    tingkat INT DEFAULT 2,
    pdf_url VARCHAR(255),                         -- Path file hasil generate, misal: /files/paket_job_001.pdf
    error_message TEXT,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indeks untuk mempercepat polling robot UiPath
CREATE INDEX IF NOT EXISTS idx_generation_jobs_status ON generation_jobs(status);

-- Contoh Data Uji Coba (Dummy Job untuk Testing Mandiri UiPath)
INSERT INTO generation_jobs (id, user_id, status, weak_topics, questions, jumlah_soal, tingkat)
VALUES (
    'job_test_001',
    'u_001',
    'queued',
    '["Limit Aljabar", "Limit Trigonometri"]'::jsonb,
    '[
        {
            "id": "MTK-LIM-001",
            "sub_bab": "Limit Aljabar",
            "tingkat": 1,
            "soal": "Nilai dari lim x→2 (3x + 1) adalah ...",
            "opsi": {"a": "5", "b": "6", "c": "7", "d": "8", "e": "9"},
            "jawaban": "C"
        },
        {
            "id": "MTK-LIM-002",
            "sub_bab": "Limit Aljabar",
            "tingkat": 2,
            "soal": "Nilai dari lim x→3 (x² − 9)/(x − 3) adalah ...",
            "opsi": {"a": "0", "b": "3", "c": "6", "d": "9", "e": "12"},
            "jawaban": "C"
        },
        {
            "id": "MTK-LIM-007",
            "sub_bab": "Limit Trigonometri",
            "tingkat": 2,
            "soal": "Nilai dari lim x→0 (sin 3x)/x adalah ...",
            "opsi": {"a": "0", "b": "1", "c": "1/3", "d": "3", "e": "∞"},
            "jawaban": "D"
        }
    ]'::jsonb,
    3,
    2
) ON CONFLICT (id) DO NOTHING;
