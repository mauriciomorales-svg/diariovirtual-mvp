# -*- coding: utf-8 -*-
"""Infografía legible: estimación se va vs se queda (Renaico 2026)."""
from pathlib import Path

import matplotlib.pyplot as plt
import matplotlib.patches as mpatches

OUT = Path(__file__).parent / "assets" / "renaico-dinero-se-queda-se-va-estimacion.png"
OUT.parent.mkdir(parents=True, exist_ok=True)

TOTAL = 290
SE_VA = 255
SE_QUEDA = 35
PCT_VA = round(SE_VA / TOTAL * 100)
PCT_Q = round(SE_QUEDA / TOTAL * 100)

RED = "#b91c1c"
GREEN = "#0f766e"
MUTED = "#64748b"
TEXT = "#0f172a"

fig, ax = plt.subplots(figsize=(12, 7.5), facecolor="white")
ax.set_xlim(0, 100)
ax.set_ylim(0, 100)
ax.axis("off")

# Encabezado
ax.text(
    50,
    95,
    "Renaico · Aniversario 2026",
    ha="center",
    va="top",
    fontsize=11,
    color=MUTED,
    fontweight="600",
)
ax.text(
    50,
    89,
    "¿Cuánto del presupuesto se queda en la comuna?",
    ha="center",
    va="top",
    fontsize=22,
    color=TEXT,
    fontweight="bold",
)
ax.text(
    50,
    83,
    f"Licitación verificada: $290.000.000 IVA inc. · 3908-28-LP25 · {PCT_VA}% / {PCT_Q}% estimados",
    ha="center",
    va="top",
    fontsize=11,
    color=MUTED,
)

# Barra sin texto dentro (evita franja ilegible del 12%)
x0, bar_y, bar_h, total_w = 6, 58, 10, 88
w_red = total_w * (SE_VA / TOTAL)
w_green = total_w * (SE_QUEDA / TOTAL)

ax.add_patch(
    mpatches.FancyBboxPatch(
        (x0, bar_y),
        w_red,
        bar_h,
        boxstyle="round,pad=0.02,rounding_size=0.8",
        facecolor=RED,
        edgecolor="white",
        linewidth=2,
    )
)
ax.add_patch(
    mpatches.FancyBboxPatch(
        (x0 + w_red, bar_y),
        w_green,
        bar_h,
        boxstyle="round,pad=0.02,rounding_size=0.8",
        facecolor=GREEN,
        edgecolor="white",
        linewidth=2,
    )
)

# Etiquetas debajo de cada tramo (legibles)
cx_red = x0 + w_red / 2
cx_green = x0 + w_red + w_green / 2
label_y = 48

ax.text(cx_red, label_y, f"SE VA DE RENAICO", ha="center", va="top", fontsize=14, fontweight="bold", color=RED)
ax.text(
    cx_red,
    label_y - 6,
    f"${SE_VA} millones  ·  ~{PCT_VA}%",
    ha="center",
    va="top",
    fontsize=20,
    fontweight="bold",
    color=TEXT,
)
ax.text(
    cx_red,
    label_y - 14,
    "Productora · cartel · técnica externa",
    ha="center",
    va="top",
    fontsize=10,
    color=MUTED,
)

ax.text(cx_green, label_y, "SE QUEDA EN RENAICO", ha="center", va="top", fontsize=12, fontweight="bold", color=GREEN)
ax.text(
    cx_green,
    label_y - 6,
    f"${SE_QUEDA} millones  ·  ~{PCT_Q}%",
    ha="center",
    va="top",
    fontsize=16,
    fontweight="bold",
    color=TEXT,
)
ax.text(
    cx_green,
    label_y - 13,
    "Comercio local · empleos del evento",
    ha="center",
    va="top",
    fontsize=9,
    color=MUTED,
)

# Caja resumen
box = mpatches.FancyBboxPatch(
    (6, 8),
    88,
    16,
    boxstyle="round,pad=0.4,rounding_size=1.2",
    facecolor="#f8fafc",
    edgecolor="#e2e8f0",
    linewidth=1.5,
)
ax.add_patch(box)
ax.text(
    50,
    19,
    "Estimación ilustrativa para el debate · NO es auditoría municipal",
    ha="center",
    va="center",
    fontsize=11,
    color=MUTED,
    fontstyle="italic",
)
ax.text(
    50,
    12,
    "El municipio no publica desglose · Fuente monto total: Mercado Público",
    ha="center",
    va="center",
    fontsize=9,
    color=MUTED,
)

fig.savefig(OUT, dpi=180, bbox_inches="tight", facecolor="white", pad_inches=0.35)
plt.close(fig)
print(f"OK: {OUT} ({OUT.stat().st_size // 1024} KB)")
