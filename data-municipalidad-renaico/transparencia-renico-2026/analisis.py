# -*- coding: utf-8 -*-
"""Análisis completo Renaico febrero 2026 — todos los archivos."""
import csv
import re
from io import StringIO
from pathlib import Path

BASE = Path(__file__).resolve().parent

FILES = {
    "planta_municipal":   "planta_municipal_febrero_2026.csv",
    "planta_educacion":   "planta_educacion_febrero_2026.csv",
    "planta_salud":       "planta_salud_febrero_2026.csv",
    "contrata_municipal": "contrata_municipal_febrero_2026.csv",
    "contrata_educacion": "contrata_educacion_raw_febrero_2026.csv",
    "contrata_salud":     "contrata_salud_raw_febrero_2026.csv",
}

RE_MONTO = re.compile(r"\$\s*([\d.]+)")
RE_HRS   = re.compile(r":\s*([\d,]+)\s*hrs?", re.I)


def clean_num(s: str) -> int:
    if not s:
        return 0
    # quitar todo menos dígitos y puntos
    s = s.strip()
    # formato portal: "$ 3.346.152" — punto es separador de miles
    clean = s.replace(" ", "").replace("$", "").replace("\xa0", "").replace("-", "")
    # si tiene punto(s), asumir separador de miles → quitar puntos
    if "." in clean:
        clean = clean.replace(".", "")
    # si tiene coma, asumir decimal → quitar coma
    clean = clean.replace(",", "")
    try:
        return int(clean)
    except Exception:
        return 0


def parse_he(s: str):
    if not s or "No tiene" in s or "$" not in s:
        return 0, 0.0
    m_m = RE_MONTO.search(s)
    m_h = RE_HRS.search(s)
    pesos = int(m_m.group(1).replace(".", "")) if m_m else 0
    hrs   = float(m_h.group(1).replace(",", ".")) if m_h else 0.0
    return pesos, hrs


def load(key: str, fname: str):
    p = BASE / fname
    if not p.exists():
        print(f"  [!] No encontrado: {fname}")
        return []
    enc = "latin-1"
    content = p.read_text(encoding=enc)
    sep = ";" if ";" in content.split("\n")[0] else ","
    rows = list(csv.DictReader(StringIO(content), delimiter=sep))
    # normalizar columnas clave
    result = []
    for r in rows:
        keys = list(r.keys())
        def col(*opts):
            for o in opts:
                for k in keys:
                    if o.lower() in k.lower():
                        return r[k]
            return ""
        result.append({
            "origen":  key,
            "nombre":  col("Nombre completo"),
            "estamento": col("Estamento"),
            "cargo":   col("Cargo o funci"),
            "bruta":   clean_num(col("bruta")),
            "liquida": clean_num(col("quida")),
            "he_d":    parse_he(col("diurnas")),
            "he_n":    parse_he(col("nocturnas")),
            "he_f":    parse_he(col("festivas")),
        })
    return result


# Cargar todo
todos = []
conteos = {}
for key, fname in FILES.items():
    rows = load(key, fname)
    conteos[key] = len(rows)
    todos.extend(rows)

print("=" * 72)
print("RENAICO — FEBRERO 2026 — TODOS LOS FUNCIONARIOS")
print("=" * 72)
print()
print(f"{'Fuente':<22} {'Personas':>8} {'Bruta total':>14} {'Líquida total':>14}")
print("-" * 62)

gran_br = gran_liq = 0
for key in FILES:
    sub = [r for r in todos if r["origen"] == key]
    sb  = sum(r["bruta"]   for r in sub)
    sl  = sum(r["liquida"] for r in sub)
    gran_br  += sb
    gran_liq += sl
    print(f"{key:<22} {len(sub):>8} ${sb:>13,} ${sl:>13,}")

print("-" * 62)
print(f"{'TOTAL':<22} {len(todos):>8} ${gran_br:>13,} ${gran_liq:>13,}")

# TOP 20 por bruta
print()
print("=" * 72)
print("TOP 20 — REMUNERACIÓN BRUTA (todos los sectores y modalidades)")
print("=" * 72)
top20 = sorted(todos, key=lambda x: x["bruta"], reverse=True)[:20]
for i, r in enumerate(top20, 1):
    origen = r["origen"].replace("_", " ")
    print(f"{i:2}. ${r['bruta']:>12,}  liq ${r['liquida']:>11,} | {r['nombre'][:38]:<38} | {r['cargo'][:28]:<28} | {origen}")

# HE destacadas
print()
print("=" * 72)
print("TOP HORAS EXTRAS — monto total (diurna + nocturna + festiva)")
print("=" * 72)
he_list = []
for r in todos:
    total_m = r["he_d"][0] + r["he_n"][0] + r["he_f"][0]
    total_h = r["he_d"][1] + r["he_n"][1] + r["he_f"][1]
    if total_m > 0:
        he_list.append({**r, "he_total_m": total_m, "he_total_h": total_h})

he_list.sort(key=lambda x: x["he_total_m"], reverse=True)
print(f"{'Nombre':<38} {'Cargo':<25} {'Total $HE':>12} {'Hrs':>6} {'Origen'}")
print("-" * 100)
for r in he_list[:20]:
    print(
        f"{r['nombre'][:38]:<38} {r['cargo'][:25]:<25} "
        f"${r['he_total_m']:>11,} {r['he_total_h']:>5.0f}h  {r['origen'].replace('_',' ')}"
    )

# HE nocturnas
noc = [(r, r["he_n"][0], r["he_n"][1]) for r in todos if r["he_n"][0] > 0]
noc.sort(key=lambda x: -x[1])
if noc:
    print()
    print("--- Con HE nocturnas ---")
    for r, m, h in noc[:10]:
        print(f"  {r['nombre'][:40]:<40} noct ${m:,} / {h:.0f}h  {r['cargo'][:28]}  [{r['origen']}]")
