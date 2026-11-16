"""Buat preview.html dari bank_soal.csv — untuk baca & verifikasi tanpa WPS.

Jalankan: python3 preview.py
Buka preview.html di browser. Read-only; CSV tidak diubah.
Jangan edit CSV lewat WPS/Excel: keduanya bisa menulis ulang UTF-8 jadi
Windows-1252 dan merusak simbol ∫ √ ² ∞.
"""
import csv
import html
from pathlib import Path

rows = list(csv.DictReader(open("data/bank_soal.csv", encoding="utf-8")))

by_sub = {}
for r in rows:
    by_sub.setdefault(r["sub_bab"], []).append(r)

out = ["""<!doctype html><meta charset="utf-8">
<title>bank_soal.csv</title>
<style>
 body{font:15px/1.55 system-ui,sans-serif;max-width:900px;margin:2rem auto;padding:0 1rem;color:#1a1a1a}
 h2{margin:2.2rem 0 .6rem;font-size:1.05rem;border-bottom:2px solid #ddd;padding-bottom:.3rem}
 .q{border-left:3px solid #4a7;padding:.5rem 0 .5rem .9rem;margin:.9rem 0}
 .id{font:12px ui-monospace,monospace;color:#888}
 .o{color:#444}
 .key{color:#0a7a3f;font-weight:600}
 .t{background:#eef;border-radius:3px;padding:0 5px;font-size:12px;color:#335}
</style>"""]

out.append(f"<h1>bank_soal.csv — {len(rows)} soal, {len(by_sub)} sub-bab</h1>")

for sub, items in by_sub.items():
    out.append(f"<h2>{html.escape(sub)} <span class='t'>{len(items)} soal</span></h2>")
    for r in items:
        opts = " &nbsp; ".join(
            (f"<b>{k.upper()}.</b> {html.escape(r['opsi_'+k])}" if r["jawaban"].lower() == k
             else f"{k.upper()}. {html.escape(r['opsi_'+k])}")
            for k in "abcde" if r["opsi_" + k].strip()
        )
        out.append(
            f"<div class='q'><span class='id'>{r['id']} · tingkat {r['tingkat']}</span><br>"
            f"{html.escape(r['soal'])}<br><span class='o'>{opts}</span><br>"
            f"<span class='key'>Jawaban: {r['jawaban']}</span></div>"
        )

Path("preview.html").write_text("\n".join(out), encoding="utf-8")
print(f"preview.html dibuat — {len(rows)} soal, {len(by_sub)} sub-bab")