"""Verifikasi kunci jawaban bank_soal.csv secara numerik dengan SymPy.

Jalankan: python3 verify.py
Butuh: pip install sympy

Cara kerja: setiap soal dihitung ulang dari ekspresinya sendiri (bukan dari
string soal), lalu semua opsi dibandingkan secara numerik untuk mencari opsi
mana yang benar. Kalau opsi yang ditandai di CSV bukan yang benar -> dilaporkan.

Soal statistika & peluang TIDAK dicek di sini (tidak ada ekspresi simbolik);
keduanya diverifikasi manual.
"""
import csv
import sympy as sp

x = sp.symbols("x")


def norm(s: str) -> str:
    """Teks gaya sekolah -> sintaks sympy. '6(2x+1)^2' -> '6*(2*x+1)**2'."""
    import re

    supers = str.maketrans("⁰¹²³⁴⁵⁶⁷⁸⁹", "0123456789")
    t = s.strip().replace("−", "-").replace("–", "-").replace("·", "*").replace("×", "*")
    # superskrip -> pangkat: x² -> x**2, )² -> )**2
    t = re.sub(r"([0-9xX)a-zA-Z\)])([⁰¹²³⁴⁵⁶⁷⁸⁹]+)",
               lambda m: m.group(1) + "**" + m.group(2).translate(supers), t)
    t = t.replace("^", "**").replace(" ", "")
    t = t.replace("√", "sqrt")
    t = re.sub(r"(\d)(?=[a-zA-Z(])", r"\1*", t)      # 6( -> 6*(
    t = re.sub(r"\)(?=[a-zA-Z(0-9])", ")*", t)        # )( -> )*(
    t = re.sub(r"(\d)(?=x)", r"\1*", t)
    t = re.sub(r"x(?=\()", "x*", t)
    t = re.sub(r"(?<=[a-zA-Z0-9\)])(?=(sin|cos|tan|log|sqrt|exp)\()", "*", t)

    def ex(m):
        body = re.sub(r"(\d)(?=[a-zA-Z])", r"\1*", m.group(1))
        return "exp(" + body + ")"

    t = re.sub(r"e\*\*\(([^()]*)\)", lambda m: "exp(" + re.sub(r"(\d)(?=[a-zA-Z])", r"\1*", m.group(1)) + ")", t)
    t = re.sub(r"e\*\*(-?[0-9a-zA-Z\*\+\-\.]+)", ex, t)
    return t


# (id, ekspresi, jenis, argumen)  -- ekspresi = yang soalnya minta
CASES = [
    ("MTK-LIM-001", "3x+1", "limit", 2),
    ("MTK-LIM-002", "(x**2-9)/(x-3)", "limit", 3),
    ("MTK-LIM-003", "(x**2-1)/(x-1)", "limit", 1),
    ("MTK-LIM-004", "(x**2-4)/(x**2-5x+6)", "limit", 2),
    ("MTK-LIM-005", "(3x+5)/(2x-1)", "limit", sp.oo),
    ("MTK-LIM-006", "(2x**2+3x)/(x**2-1)", "limit", sp.oo),
    ("MTK-LIM-007", "sin(3x)/x", "limit", 0),
    ("MTK-LIM-008", "(1-cos(2x))/x**2", "limit", 0),
    ("MTK-LIM-009", "tan(5x)/(2x)", "limit", 0),
    ("MTK-LIM-010", "(sqrt(x+1)-2)/(x-3)", "limit", 3),
    ("MTK-TUR-001", "(2x+1)**3", "diff", None),
    ("MTK-TUR-002", "(3x-2)**4", "diff", None),
    ("MTK-TUR-003", "sqrt(x**2+1)", "diff", None),
    ("MTK-TUR-004", "(x**2+3x)**5", "diff", None),
    ("MTK-TUR-005", "sin(2x)", "diff", None),
    ("MTK-TUR-006", "cos(3x)", "diff", None),
    ("MTK-TUR-007", "exp(2x)", "diff", None),
    ("MTK-TUR-008", "log(3x)", "diff", None),
    ("MTK-TUR-009", "(5x-1)**2", "diff", None),
    ("MTK-TUR-010", "sin(x**2)", "diff", None),
    ("MTK-INT-001", "2x", "int", (0, 1)),
    ("MTK-INT-002", "3x**2", "int", (0, 2)),
    ("MTK-INT-003", "2x", "int", (1, 2)),
    ("MTK-INT-004", "4x", "int", (1, 3)),
    ("MTK-INT-005", "2x+1", "int", (0, 1)),
    ("MTK-INT-006", "3x**2+2x", "int", (0, 1)),
    ("MTK-INT-007", "6x**2", "int", (1, 2)),
    ("MTK-INT-008", "x+1", "int", (0, 2)),
    ("MTK-INT-009", "sin(x)", "int", (0, sp.pi)),
    ("MTK-INT-010", "cos(x)", "int", (0, sp.pi / 2)),
]

_bank = {r["id"]: r for r in csv.DictReader(open("data/bank_soal.csv", encoding="utf-8"))}

masalah = []
cek = 0

for qid, expr, kind, arg in CASES:
    row = _bank[qid]
    E = sp.sympify(norm(expr), locals={"x": x})

    if kind == "limit":
        true = sp.limit(E, x, arg)
    elif kind == "diff":
        true = sp.simplify(sp.diff(E, x))
    else:
        true = sp.nsimplify(sp.integrate(E, (x, arg[0], arg[1])))

    # cari opsi mana yang secara numerik sama dengan nilai benar
    cocok = []
    for k in "abcde":
        opsi = row["opsi_" + k].strip()
        if not opsi:
            continue
        try:
            O = sp.sympify(norm(opsi), locals={"x": x})
            sama = sp.simplify(true - O) == 0
        except Exception:
            # opsi tak bisa diparse (mis. 'oo', '∞') -> coba nilai numerik
            try:
                sama = abs(complex(sp.N(true)) - complex(sp.N(sp.sympify(norm(opsi))))) < 1e-9
            except Exception:
                sama = False
        if sama:
            cocok.append(k.upper())

    cek += 1
    ditandai = row["jawaban"].strip().upper()
    if ditandai not in cocok:
        masalah.append((qid, true, ditandai, row["opsi_" + ditandai.lower()], cocok))

print(f"{cek} soal ekspresi dicek")
if masalah:
    print(f"\n{len(masalah)} PERLU DIPERIKSA:")
    for qid, true, ditandai, opt, cocok in masalah:
        print(f"  {qid}: CSV bilang {ditandai} ({opt}), nilai benar = {true},"
              f" opsi yang cocok = {cocok or 'tidak ada'}")
else:
    print("Semua opsi bertanda cocok dengan hasil hitung SymPy.")

sisa = len(_bank) - cek
print(f"\n{sisa} soal statistika/peluang tidak dicek otomatis (verifikasi manual).")