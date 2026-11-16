# asKuy RPA Worker (UiPath Automation)

Modul otomatisasi Robotic Process Automation (RPA) untuk platform **asKuy**.  
Robot ini bertindak sebagai **Background Worker (Unattended / Polling)** yang memantau antrean pembuatan paket latihan dan men-generate dokumen PDF siap unduh untuk siswa.

---

## 🏗️ Arsitektur & Alur Kerja (Varian A)

```text
[Siswa di React Web]
       │ (1) Klik "Generate Paket Latihan"
       ▼
[Laravel / FastAPI] ─── (2) INSERT INTO generation_jobs (status='queued')
                              │
                              ▼
                      [PostgreSQL Database]
                              │
       ┌──────────────────────┴──────────────────────┐
       │ (3) Robot UiPath Polling (SELECT ... WHERE status = 'queued')
       ▼
[asKuy UiPath Robot]
       │ (4) Update status -> 'processing'
       │ (5) Susun soal ke Template HTML & Render PDF
       │ (6) Simpan PDF ke storage (/files/paket_{job_id}.pdf)
       │ (7) Update status -> 'completed' + isi pdf_url
       ▼
[PostgreSQL Database]
       ▲
       │ (8) React polling GET /jobs/{id} melihat status 'completed'
[Siswa di React Web] ─── (9) Tombol "Unduh PDF" aktif!
```

---

## 📁 Struktur Folder `askuyRPA/`

```text
askuyRPA/
├── Data/
│   ├── Input/                 # Folder data uji coba
│   ├── Output/                # Output PDF yang dihasilkan robot
│   ├── Templates/
│   │   └── soal_template.html # Template dokumen soal (responsive & print-friendly)
│   ├── generate_pdf.ps1       # Script render PDF otomatis (Edge Headless)
│   └── schema_generation_jobs.sql # Skema tabel database PostgreSQL
├── Main.xaml                  # Alur kerja utama UiPath Studio
├── project.json               # Konfigurasi dependensi UiPath
└── README.md                  # Dokumentasi ini
```

---

## ⚙️ Persyaratan & Prasyarat Sistem

1. **UiPath Studio** (Community Edition atau Enterprise).
2. **PostgreSQL ODBC Driver (`psqlODBC`)** untuk koneksi database:
   - Unduh dari: https://www.postgresql.org/ftp/odbc/versions/msi/
   - Install versi 64-bit (*psqlodbc_xx_xx_x64.msi*).
3. **Microsoft Edge** (Bawaan Windows 10/11) untuk konversi HTML ke PDF berkecepatan tinggi.

---

## 🚀 Cara Uji Mandiri (Testing Tanpa Menunggu Tim)

### 1. Uji Coba Generator PDF (Mandiri)
Jalankan perintah ini di PowerShell untuk melihat hasil cetak PDF:
```powershell
powershell -ExecutionPolicy Bypass -File "askuyRPA/Data/generate_pdf.ps1" -JobId "demo_001" -UserId "siswa_01" -WeakTopics "Limit Aljabar, Limit Trigonometri" -Tingkat 2
```
Hasil PDF akan langsung tersimpan di: `askuyRPA/Data/Output/paket_demo_001.pdf`.

### 2. Setup Tabel Database di PostgreSQL
Buka pgAdmin atau psql, lalu jalankan isi file:
```sql
askuyRPA/Data/schema_generation_jobs.sql
```
Ini akan membuat tabel `generation_jobs` dan 1 contoh baris data antrean (`job_test_001`).

### 3. Membuka di UiPath Studio
1. Buka aplikasi **UiPath Studio**.
2. Klik **Open a Local Project** -> pilih file `askuyRPA/project.json`.
3. Buka `Main.xaml`.
4. Sesuaikan connection string database PostgreSQL di variabel `str_DbConnection` (default: `Host=localhost;Port=5432;Database=askuy;Username=postgres;Password=postgres;`).
5. Klik **Run** atau **Debug**. Robot akan mulai memantau tabel database.

---

## 🤝 Kontrak Data dengan Tim Lain

* **FastAPI (Anggota 2):** Menyiapkan data titik lemah & daftar soal terkurasi.
* **Laravel / Backend (Anggota 1 & 2):** Menyediakan endpoint polling `/jobs/{id}` untuk frontend.
* **UiPath (Anggota 3):** Memastikan proses pembuatan PDF selesai dalam hitungan detik dan link PDF siap diakses.
