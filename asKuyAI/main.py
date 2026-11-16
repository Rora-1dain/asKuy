"""asKuy AI Service — v0.1

Kontrak: lihat ai-contract.md (root repo).
Fase 1 (sekarang): rule-based, tanpa LLM. Fase 2: /explain + rekomendasi via 9router.

Jalankan:  uvicorn main:app --reload --port 8000
Cek cepat: curl -s localhost:8000/health
"""
import csv
import os
import random
from pathlib import Path
from typing import Optional

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field

BANK = Path(__file__).parent / "data" / "bank_soal.csv"

app = FastAPI(title="asKuy AI Service", version="0.1")


# ---------------------------------------------------------------- model

class Score(BaseModel):
    sub_bab: str
    benar: int = Field(ge=0)
    total: int = Field(gt=0)


class AnalyzeRequest(BaseModel):
    user_id: str
    scores: list[Score]


class WeakTopic(BaseModel):
    sub_bab: str
    persentase_salah: float
    prioritas: int


class AnalyzeResponse(BaseModel):
    weak_topics: list[WeakTopic]
    rekomendasi: str


class GenerateRequest(BaseModel):
    user_id: str
    weak_topics: list[str]
    jumlah_soal: int = Field(default=10, gt=0, le=50)
    tingkat: Optional[int] = Field(default=None, ge=1, le=3)


# ---------------------------------------------------------------- bank soal

def load_bank() -> list[dict]:
    with BANK.open(encoding="utf-8") as f:
        return list(csv.DictReader(f))


BANK_CACHE = load_bank()


# ---------------------------------------------------------------- endpoint

@app.get("/health")
def health():
    return {"status": "ok", "jumlah_soal": len(BANK_CACHE)}


@app.post("/analyze", response_model=AnalyzeResponse)
def analyze(req: AnalyzeRequest):
    """Peta titik lemah. Rule-based — persentase salah dihitung di sini, bukan oleh LLM."""
    if not req.scores:
        raise HTTPException(400, "scores tidak boleh kosong")

    weak = []
    for s in req.scores:
        if s.benar > s.total:
            raise HTTPException(400, f"benar > total pada sub_bab {s.sub_bab!r}")
        salah = round((s.total - s.benar) / s.total * 100, 1)
        if salah > 0:
            weak.append(WeakTopic(sub_bab=s.sub_bab, persentase_salah=salah, prioritas=0))

    weak.sort(key=lambda w: w.persentase_salah, reverse=True)
    weak = weak[:5]
    for i, w in enumerate(weak, start=1):
        w.prioritas = i

    # TODO fase 2: rekomendasi satu kalimat dari LLM (9router). Gagal LLM -> "".
    rekomendasi = ""
    if weak:
        t = weak[0]
        rekomendasi = (
            f"Fokus ke {t.sub_bab} dulu: {t.persentase_salah}% soal salah di topik ini."
        )

    return AnalyzeResponse(weak_topics=weak, rekomendasi=rekomendasi)


def acak_opsi(r: dict) -> tuple[dict, str]:
    """Acak urutan opsi satu soal, sesuaikan huruf jawaban.

    Tanpa ini, kunci jawaban menumpuk di satu huruf (soal Turunan di bank
    hampir semua berkunci A) dan itu kelihatan curang saat demo.
    """
    opts = [r[f"opsi_{k}"] for k in "abcde"]
    benar = "abcde".index(r["jawaban"].lower())
    urutan = list(range(5))
    random.shuffle(urutan)
    baru = {k: opts[i] for k, i in zip("abcde", urutan)}
    huruf = "abcde"[urutan.index(benar)]
    return {k: v for k, v in baru.items() if v}, huruf.upper()


@app.post("/generate")
def generate(req: GenerateRequest):
    """Pilih soal sesuai titik lemah. Rule-based supaya hasil demo bisa diprediksi.

    Mengembalikan soal langsung (varian B di kontrak). Kalau anggota 3 memilih
    antrean UiPath (varian A), endpoint ini jadi penulis baris generation_jobs.
    """
    pilih = [r for r in BANK_CACHE
             if r["sub_bab"] in req.weak_topics
             and (req.tingkat is None or int(r["tingkat"]) == req.tingkat)]
    random.shuffle(pilih)  # tanpa ini, soal keluar urut CSV

    if len(pilih) < req.jumlah_soal:  # kurang -> ambil dari sub_bab lain, tingkat sama dulu
        sisa = [r for r in BANK_CACHE
                if r not in pilih and (req.tingkat is None or int(r["tingkat"]) == req.tingkat)]
        random.shuffle(sisa)
        pilih += sisa[: req.jumlah_soal - len(pilih)]

    out = []
    for r in pilih[: req.jumlah_soal]:
        opsi, jawaban = acak_opsi(r)
        out.append({"id": r["id"], "sub_bab": r["sub_bab"], "tingkat": int(r["tingkat"]),
                    "soal": r["soal"], "opsi": opsi, "jawaban": jawaban})

    return {"user_id": req.user_id, "jumlah_soal": len(out), "questions": out}


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=int(os.getenv("PORT", 8000)))
