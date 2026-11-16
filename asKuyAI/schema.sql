-- Tabel bersama AI Service <-> Robot UiPath.
-- Pemilik: Anggota 3. Penulis baris: AI Service (/generate). Pembaca: robot.
-- Jalankan sekali sebelum integrasi:
--   psql "$DATABASE_URL" -f schema.sql

CREATE TABLE IF NOT EXISTS generation_jobs (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id     TEXT        NOT NULL,
    weak_topics JSONB       NOT NULL,
    jumlah_soal INTEGER     NOT NULL,
    tingkat     INTEGER,
    -- questions disimpan agar robot TIDAK perlu mengulang pemilihan soal.
    -- Sekali dipilih, isinya beku: robot hanya merender.
    questions   JSONB       NOT NULL,
    status      TEXT        NOT NULL DEFAULT 'queued'
                CHECK (status IN ('queued', 'processing', 'completed', 'failed')),
    pdf_url     TEXT,
    error       TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Robot menanyakan "ada kerjaan baru?" tiap beberapa detik -> butuh index ini.
CREATE INDEX IF NOT EXISTS idx_generation_jobs_status
    ON generation_jobs (status, created_at);

-- Kontrak untuk robot:
--   ambil : SELECT id, questions FROM generation_jobs
--           WHERE status = 'queued' ORDER BY created_at LIMIT 1;
--   klaim : UPDATE generation_jobs SET status='processing', updated_at=now()
--           WHERE id = :id AND status = 'queued';   -- cegah dua robot ambil job sama
--   selesai: UPDATE generation_jobs
--            SET status='completed', pdf_url=:url, updated_at=now() WHERE id = :id;
--   gagal : UPDATE generation_jobs
--            SET status='failed', error=:msg, updated_at=now() WHERE id = :id;