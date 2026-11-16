"""Self-check cepat: python3 check.py (tanpa server, pakai TestClient).

Bukan test suite lengkap — hanya penjaga 4 hal yang bikin demo gagal:
bank soal utuh, /analyze menghitung benar, /generate tidak mengulang soal,
dan kunci jawaban tidak menumpuk di satu huruf.
"""
import collections
import csv

from fastapi.testclient import TestClient

import main

c = TestClient(main.app)

# 1. bank soal
rows = list(csv.DictReader(open("data/bank_soal.csv", encoding="utf-8")))
assert rows, "bank soal kosong"
assert len({r["id"] for r in rows}) == len(rows), "ada id duplikat"
for r in rows:
    assert r["jawaban"] in "ABCDE", f"jawaban invalid: {r['id']}"
    assert r["opsi_" + r["jawaban"].lower()], f"opsi jawaban kosong: {r['id']}"

# 2. /analyze
a = c.post("/analyze", json={"user_id": "u", "scores": [
    {"sub_bab": "Aturan Rantai", "benar": 2, "total": 10},
    {"sub_bab": "Limit Aljabar", "benar": 10, "total": 10}]}).json()
assert len(a["weak_topics"]) == 1, "topik 0% salah tidak boleh muncul"
assert a["weak_topics"][0]["persentase_salah"] == 80.0
assert a["weak_topics"][0]["prioritas"] == 1
assert a["rekomendasi"], "rekomendasi kosong"

# 3. /generate tidak mengulang soal, jumlah pas
g = c.post("/generate", json={"user_id": "u",
                              "weak_topics": ["Aturan Rantai"], "jumlah_soal": 8}).json()
ids = [q["id"] for q in g["questions"]]
assert len(ids) == 8, f"jumlah soal {len(ids)} != 8"
assert len(set(ids)) == 8, "ada soal dobel"

# 4. kunci jawaban tidak menumpuk (shuffle bekerja)
keys = collections.Counter(q["jawaban"] for q in g["questions"])
assert len(keys) >= 2, f"kunci jawaban menumpuk: {dict(keys)}"

print(f"OK — {len(rows)} soal, /analyze + /generate lulus, sebaran kunci {dict(keys)}")
