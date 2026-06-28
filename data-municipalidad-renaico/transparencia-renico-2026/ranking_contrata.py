# -*- coding: utf-8 -*-
"""
Top N remuneraciones por mes y análisis de horas extras (contrata educación + salud).

Uso:
  python ranking_contrata.py
  python ranking_contrata.py --top 20 --por liquida

Mes a mes: coloca en esta carpeta CSV con columnas estándar (mismo esquema que
contrata_*_febrero_2026.csv). Puedes tener varios archivos por mes (educación + salud);
el script los fusiona por (anio, mes).
"""
from __future__ import annotations

import argparse
import csv
import re
from collections import defaultdict
from pathlib import Path

OUT = Path(__file__).resolve().parent

# "$ 124.553 : 13,00 hrs" o variantes
RE_HE = re.compile(
    r"\$\s*([\d.]+)\s*:\s*([\d,]+)\s*(?:hrs?)?",
    re.IGNORECASE,
)


def parse_he_field(text: str) -> tuple[int | None, float | None]:
    if not text or "No tiene" in text or "$" not in text:
        return None, None
    m = RE_HE.search(text.replace("\xa0", " "))
    if not m:
        return None, None
    raw_money = m.group(1).replace(".", "")
    raw_h = m.group(2).replace(".", "").replace(",", ".")
    try:
        pesos = int(raw_money)
        hrs = float(raw_h)
        return pesos, hrs
    except ValueError:
        return None, None


def load_all_rows() -> list[dict[str, str]]:
    rows: list[dict[str, str]] = []
    for p in sorted(OUT.glob("contrata_*.csv")):
        if p.name.startswith("."):
            continue
        with p.open(encoding="utf-8-sig", newline="") as f:
            rows.extend(csv.DictReader(f))
    return rows


def top_by_month(
    rows: list[dict[str, str]],
    *,
    n: int,
    key: str,
) -> dict[tuple[str, str], list[dict[str, str]]]:
    by_period: dict[tuple[str, str], list[dict[str, str]]] = defaultdict(list)
    for r in rows:
        period = (r.get("anio", ""), r.get("mes", ""))
        by_period[period].append(r)

    field = "remuneracion_liquida" if key == "liquida" else "remuneracion_bruta"
    result: dict[tuple[str, str], list[dict[str, str]]] = {}
    for period, lst in sorted(by_period.items()):
        def sort_key(x: dict[str, str]) -> int:
            try:
                return int(x.get(field, "0") or "0")
            except ValueError:
                return 0

        sorted_lst = sorted(lst, key=sort_key, reverse=True)[:n]
        result[period] = sorted_lst
    return result


def he_report(rows: list[dict[str, str]]) -> None:
    """Solo filas con HE en columnas diurna/nocturna/festiva (salud)."""
    detail: list[dict] = []
    for r in rows:
        d_m, d_h = parse_he_field(r.get("he_diurnas", ""))
        n_m, n_h = parse_he_field(r.get("he_nocturnas", ""))
        f_m, f_h = parse_he_field(r.get("he_festivas", ""))
        if d_m is None and n_m is None and f_m is None:
            continue
        total_m = (d_m or 0) + (n_m or 0) + (f_m or 0)
        total_h = (d_h or 0) + (n_h or 0) + (f_h or 0)
        detail.append(
            {
                "nombre": r.get("nombre_completo", ""),
                "cargo": r.get("cargo_o_funcion", ""),
                "sector": "salud" if "19.378" in r.get("estamento", "") or "Médicos" in r.get("estamento", "") or "Salud" in r.get("estamento", "") else "educacion",
                "diurna_$": d_m,
                "diurna_h": d_h,
                "noct_$": n_m,
                "noct_h": n_h,
                "fest_$": f_m,
                "fest_h": f_h,
                "total_$": total_m,
                "total_h": total_h,
            }
        )

    if not detail:
        print("(No hay montos parseables de horas extras en los CSV cargados.)")
        return

    detail.sort(key=lambda x: x["total_$"], reverse=True)
    print("\n--- Horas extras: ranking por monto total HE (diurna+nocturna+festiva) ---\n")
    for i, x in enumerate(detail[:15], 1):
        print(
            f"{i:2}. {x['nombre'][:40]:<40} | ${x['total_$']:,} | {x['total_h']:.1f} h tot | "
            f"{x['cargo'][:35]}"
        )

    # Atención: muchas horas diurnas
    hi_h = sorted(detail, key=lambda x: x["total_h"], reverse=True)[:5]
    print("\n--- Mayor cantidad de horas (total HE declaradas) ---\n")
    for x in hi_h:
        print(f"  {x['nombre'][:40]} | {x['total_h']:.1f} h | ${x['total_$']:,}")

    # Nocturnas con monto
    noct = [x for x in detail if (x["noct_$"] or 0) > 0]
    if noct:
        print("\n--- Con HE nocturnas (pocas filas suelen llamar la atención) ---\n")
        for x in sorted(noct, key=lambda x: x["noct_$"] or 0, reverse=True):
            print(
                f"  {x['nombre'][:38]} | noct ${x['noct_$']:,} / {x['noct_h'] or 0:.1f} h | "
                f"{x['cargo'][:30]}"
            )


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--top", type=int, default=20, help="Cantidad por mes")
    ap.add_argument(
        "--por",
        choices=("bruta", "liquida"),
        default="bruta",
        help="Criterio de orden",
    )
    args = ap.parse_args()

    rows = load_all_rows()
    if not rows:
        print(f"No se encontraron contrata_*.csv en {OUT}")
        return

    key = "liquida" if args.por == "liquida" else "bruta"
    tops = top_by_month(rows, n=args.top, key=key)
    field = "remuneracion_liquida" if key == "liquida" else "remuneracion_bruta"
    label = "líquida" if key == "liquida" else "bruta"

    periods = sorted(tops.keys())
    print(f"Periodos encontrados: {len(periods)}")
    for anio, mes in periods:
        print(f"\n{'='*72}")
        print(f"Top {args.top} — {mes} {anio} — por remuneración {label}")
        print("=" * 72)
        for i, r in enumerate(tops[(anio, mes)], 1):
            try:
                monto = int(r.get(field, "0") or "0")
            except ValueError:
                monto = 0
            nom = r.get("nombre_completo", "")
            cargo = r.get("cargo_o_funcion", "")
            est = r.get("estamento", "")[:50]
            print(f"{i:2}. ${monto:>12,}  | {nom} | {cargo} | {est}")

    he_report(rows)


if __name__ == "__main__":
    main()
