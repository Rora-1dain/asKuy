# Kontrak AI Service — asKuy

**Pemilik:** Anggota 2 (AI Engineer)
**Status:** v0.1 — draf untuk disepakati bertiga
**Base URL dev:** `http://localhost:8000`
**Format:** JSON, `Content-Type: application/json`

> **Asumsi yang harus dikonfirmasi anggota 3 (paling lambat besok):**
> 1. AI **tidak menunggu** robot. `/generate` mengembalikan `job_id` dengan status `queued`, lalu frontend polling `GET /jobs/{id}`.
> 2. Robot dipicu dengan membaca tabel `generation_jobs` di Postgres (bukan AI yang memanggil UiPath).
>
> Kalau salah satu asumsi ini keliru, yang berubah hanya bagian **§3 Generate**. Bagian lain tidak terpengaruh.

---

## 1. Data kontrak — skema bank soal (CSV)

File: `data/bank_soal.csv`, UTF-8, delimiter koma, header wajib.

| Kolom | Tipe | Contoh | Catatan |
|---|---|---|---|
| `id` | string | `MTK-TUR-001` | unik, prefix mapel-bab |
| `mapel` | string | `Matematika` | satu nilai dulu untuk demo |
| `bab` | string | `Turunan` | |
| `sub_bab` | string | `Aturan Rantai` | **kunci utama analisis titik lemah** |
| `tingkat` | int | `2` | 1=mudah, 2=sedang, 3=sulit |
| `soal` | string | `Turunan dari f(x)=...` | pakai `"..."` jika ada koma |
| `opsi_a` | string | `2x` | |
| `opsi_b` | string | `2x+1` | |
| `opsi_c` | string | `x^2` | |
| `opsi_d` | string | `x` | |
| `opsi_e` | string | `1` | boleh kosong jika bukan 5 opsi |
| `jawaban` | char | `B` | **fakta final, tidak boleh diubah AI** |

**Tidak ada kolom `pembahasan`.** Pembahasan digenerate AI saat runtime (§2.2).
Alasan: menulis 200 pembahasan = 3x lebih lama dari menulis soalnya, dan itu justru pekerjaan AI.

Contoh baris:

```csv
id,mapel,bab,sub_bab,tingkat,soal,opsi_a,opsi_b,opsi_c,opsi_d,opsi_e,jawaban
MTK-TUR-001,Matematika,Turunan,Aturan Rantai,1,"Turunan dari f(x)=(2x+1)^3 adalah...",6(2x+1)^2,3(2x+1)^2,2(2x+1)^3,6x+3,2x+1,A
```

---

## 2. Endpoint AI Service (FastAPI, milik Anggota 2)

### 2.1 `POST /analyze` — peta titik lemah

**Request**
```json
{
  "user_id": "u_001",
  "scores": [
    { "sub_bab": "Aturan Rantai", "benar": 2, "total": 10 },
    { "sub_bab": "Limit Fungsi",  "benar": 9, "total": 10 },
    { "sub_bab": "Integral Tentu","benar": 4, "total": 10 }
  ]
}
```

**Response 200**
```json
{
  "weak_topics": [
    { "sub_bab": "Aturan Rantai",  "persentase_salah": 80.0, "prioritas": 1 },
    { "sub_bab": "Integral Tentu", "persentase_salah": 60.0, "prioritas": 2 }
  ],
  "rekomendasi": "Fokus ke Aturan Rantai dulu: 8 dari 10 soal salah."
}
```

**Aturan:**
- `persentase_salah` = `(total - benar) / total * 100`, dibulatkan 1 desimal. **Dihitung rule-based, bukan LLM.**
- Topik dengan `persentase_salah` = 0 **tidak dikembalikan**.
- Urut menurun berdasarkan `persentase_salah`. Maksimal 5 item.
- `rekomendasi` = **satu kalimat** dari LLM. Kalau LLM gagal, isi string kosong `""` — endpoint tetap 200.

---

### 2.2 `POST /explain` — pembahasan langkah-demi-langkah

**Request**
```json
{
  "question_id": "MTK-TUR-001",
  "jawaban_benar": "A",
  "jawaban_siswa": "C"
}
```

**Response 200**
```json
{
  "pembahasan": "Langkah 1: ...\nLangkah 2: ...\nJadi jawabannya A.",
  "benar": false,
  "sumber": "llm"
}
```

**Aturan:**
- `jawaban_benar` dikirim sebagai **fakta final**. Prompt melarang model mengubah atau menghitung ulang dari nol.
- `sumber` = `"llm"` | `"fallback"`.
- Kalau LLM gagal/timeout (>20 s), kembalikan `sumber: "fallback"` dan `pembahasan` berisi `"Jawaban yang benar adalah A. Pembahasan detail belum tersedia."` → **status tetap 200**, aplikasi tidak error.

---

### 2.3 `POST /generate` — minta robot menyusun paket latihan

> **Bagian ini bergantung pada jawaban anggota 3.** Dua varian, pilih satu setelah konfirmasi.

**Request**
```json
{
  "user_id": "u_001",
  "weak_topics": ["Aturan Rantai", "Integral Tentu"],
  "jumlah_soal": 10,
  "tingkat": 2
}
```

**Response 200 (varian A — AI menulis ke antrean, yang direkomendasikan)**
```json
{
  "job_id": "job_7f3a",
  "status": "queued"
}
```

Lalu frontend polling:

`GET /jobs/{job_id}` → `{ "id": "job_7f3a", "status": "queued|processing|completed|failed", "pdf_url": "/files/paket_7f3a.pdf", "jumlah_soal": 10 }`

**Response 200 (varian B — AI memilih soal sendiri, robot hanya render PDF)**
```json
{
  "questions": [ { "id": "MTK-TUR-001", "soal": "...", "opsi": {"a":"...","b":"..."}, "jawaban": "A" } ]
}
```

**Pemilihan soal (dipakai di kedua varian):**
filter `sub_bab IN weak_topics AND tingkat = tingkat`, acak, ambil `jumlah_soal`. Kalau kurang dari `jumlah_soal`, tambah dari `sub_bab` prioritas berikutnya. **Rule-based, bukan LLM** — supaya hasilnya bisa diprediksi saat demo.

---

## 3. Tanggung jawab per orang

| Hal | Pemilik |
|---|---|
| Bank soal CSV | Anggota 2 |
| `/analyze`, `/explain`, `/generate` | Anggota 2 |
| Tabel `generation_jobs` + job runner UiPath | Anggota 3 |
| Render PDF | Anggota 3 |
| Halaman upload nilai, halaman pembahasan, polling status | Anggota 1 |
| Auth, proxy REST, simpan hasil ke Postgres | Anggota 1 (Laravel) |

**Batas jelas:** AI Service **tidak** menyimpan data user, **tidak** punya autentikasi sendiri, **tidak** menyentuh UiPath langsung. Semua masuk lewat Laravel.

---

## 4. Yang tidak boleh masuk repo

- API key (9router, provider LLM) → `.env`, dan `.env` masuk `.gitignore`
- Data nilai siswa asli → pakai data contoh
- File `.uip` yang berisi kredensial

Yang dikirim ke panitia: `.env.example` saja.

---

## 5. Urutan pengerjaan

1. Skema CSV + 50 soal ← **memblokir anggota 3**
2. `/analyze` rule-based (tanpa LLM) ← **memblokir anggota 1**
3. `/explain` dengan 9router + fallback
4. `/generate` setelah anggota 3 konfirmasi varian A/B