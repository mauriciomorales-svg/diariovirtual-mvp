# -*- coding: utf-8 -*-
"""Genera CSV — Contrata educación y salud — Municipalidad de Renaico, febrero 2026."""
from __future__ import annotations

import csv
import sys
from pathlib import Path

OUT = Path(__file__).resolve().parent
if str(OUT) not in sys.path:
    sys.path.insert(0, str(OUT))

from contrata_salud_data import build_rows_contrata_salud

COLS = [
    "anio",
    "mes",
    "estamento",
    "nombre_completo",
    "cargo_o_funcion",
    "grado_eus_o_jornada",
    "calificacion_profesional",
    "region",
    "asignaciones_especiales",
    "remuneracion_bruta",
    "remuneracion_liquida",
    "rem_adicionales",
    "rem_bonos_incentivos",
    "derecho_horas_extraordinarias",
    "he_diurnas",
    "he_nocturnas",
    "he_festivas",
    "fecha_inicio",
    "fecha_termino",
    "viaticos",
    "observaciones",
]

REG = "Región de La Araucanía"
ASIG = "(01)"


def wcsv(name: str, rows: list[list]) -> None:
    path = OUT / name
    with path.open("w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(COLS)
        for r in rows:
            w.writerow(r)


def _rows_contrata_educacion() -> list[list]:
    """36 filas — Personal a Contrata educación."""
    r: list[list] = []

    def row(
        est: str,
        nom: str,
        cargo: str,
        b: int,
        l: int,
        fi: str,
        ft: str,
    ) -> None:
        r.append(
            [
                2026,
                "Febrero",
                est,
                nom,
                cargo,
                "no asimilado a grado",
                "DOCENTE",
                REG,
                ASIG,
                b,
                l,
                0,
                0,
                "No",
                "No tiene",
                "No tiene",
                "No tiene",
                fi,
                ft,
                0,
                0,
            ]
        )

    row("Docente", "ACEVEDO CACERES, MARIA CATALINA", "DOCENTE", 1674142, 840993, "04/05/2022", "28/02/2026")
    row("Docente", "ACUÑA SEGUEL, CARLOS FELIPE", "DOCENTE", 628501, 518011, "11/03/2024", "28/02/2026")
    row("Docente", "ARANDA ORTIZ, SCARLETTE MARIA", "DOCENTE", 1533227, 1226770, "17/04/2023", "28/02/2026")
    row("Docente", "BARRALES RIQUELME, ROCIO EILEEN", "DOCENTE", 1744156, 1382065, "27/05/2021", "28/02/2026")
    row("Docente", "CATRIAN RODRIGUEZ, JAIME HECTOR", "DOCENTE", 1202675, 758792, "02/02/2020", "28/02/2026")
    row("Docente", "CONTRERAS SEGUEL, BARBARA SOLEDAD", "DOCENTE", 1591181, 926615, "01/03/2019", "28/02/2026")
    row("Docente", "CORREA FUENTES, MARLA ROUSS", "DOCENTE", 1252205, 759307, "14/03/2024", "28/02/2026")
    row("Docente", "CUEVAS ERICES, CESAR ANTONIO", "DOCENTE", 2450913, 1936496, "01/03/2019", "28/02/2026")
    row("Docente", "ESPINOZA HERRERA, MARIA FERNANDA", "DOCENTE", 1686609, 1372980, "01/03/2024", "28/02/2026")
    row("Docente", "FLORES SCHEVACH, KAREN NATALY", "DOCENTE", 1464562, 672771, "01/03/2025", "28/02/2026")
    row("Docente", "GALLEGOS GAETE, KIARA ANTONIA", "DOCENTE", 1466578, 1194314, "28/04/2025", "28/02/2026")
    row("Docente", "GOMEZ CORREA, ABIGAIL LICELOT", "DOCENTE", 2083662, 1607748, "01/05/2021", "28/02/2026")
    row("Docente-directivo", "GONZALEZ ESCOBAR, IGNACIO HERNAN", "DIRECTOR DAEM", 3607531, 2819032, "10/03/2025", "28/02/2026")
    row("Docente", "GUEVARA PONCE, ALEXANDRA MARGARITA", "DOCENTE", 447062, 276625, "15/03/2023", "28/02/2026")
    row("Docente", "INOSTROZA SEPULVEDA, DANIELA ALEJANDRA", "DOCENTE", 1464565, 840827, "03/04/2024", "28/02/2026")
    row("Docente", "JARA GODOY, CARIN DANIELA", "DOCENTE", 1909271, 1433021, "01/03/2019", "28/02/2026")
    row("Docente", "LAPORTE SEPULVEDA, JEANETTE ALEJANDRA", "DOCENTE", 1783502, 1424790, "09/03/2020", "28/02/2026")
    row("Docente", "LUNA GARRIDO, DANNY ALEXANDER", "DOCENTE", 1779188, 1430652, "12/03/2025", "28/02/2026")
    row("Docente", "MACHMAR VALERIA, VANIA BEATRIZ", "DOCENTE", 2514141, 1443878, "12/03/2020", "28/02/2026")
    row("Docente", "MONTOYA VARGAS, SERGIO HERNAN", "DOCENTE", 1518603, 970758, "01/03/2025", "28/02/2026")
    row("Docente", "MORA ARAYA, FRANCISCA DEL CARMEN", "DOCENTE", 1297522, 1033165, "11/04/2023", "28/02/2026")
    row("Docente", "MORENO BASCUR, CRISTOPHER DANILO", "DOCENTE", 439719, 304757, "08/03/2023", "28/02/2026")
    row("Docente-directivo", "MORONI SOTO, GIANINA DANIELA", "JEFE UTP", 2142159, 1304118, "06/04/2016", "28/02/2026")
    row("Docente", "OBREQUE ARAVENA, JOCELYN PAULETTE", "DOCENTE", 267787, 226501, "19/04/2022", "28/02/2026")
    row("Docente", "OSES ECHAURREN, MATIAS IVOR", "DOCENTE", 1376852, 768975, "01/06/2019", "28/02/2026")
    row("Docente", "PAZ TOLOZA, FABIOLA ANDREA", "DOCENTE", 1600899, 1077271, "01/03/2021", "28/02/2026")
    row("Docente", "PERRET AGUILERA, EMILIO JOSE", "DOCENTE", 754292, 615126, "04/03/2020", "28/02/2026")
    row("Docente", "POBLETE QUIROZ, YENNIFER ALEJANDRA", "DOCENTE", 843303, 689232, "18/03/2024", "28/02/2026")
    row("Docente-directivo", "QUIJON VENEGAS, SANDRA MIREYA", "DOCENTE", 2472616, 1973589, "01/03/2019", "28/02/2026")
    row("Docente", "ROJAS ROJAS, JACQUELINE ANDREA", "DOCENTE", 1840769, 1005104, "31/05/2022", "28/02/2026")
    row("Docente", "RUBIO CISTERNA, HUGO IVAN", "DOCENTE", 444549, 366398, "12/03/2025", "28/02/2026")
    row("Docente", "SOTO ESPINOZA, XIMENA DEL CARMEN", "DOCENTE", 1438854, 1949545, "01/04/2019", "28/02/2026")
    row("Docente", "TAPIA ALVEAL, SCARLETTE VERONICA", "DOCENTE", 1678022, 1144685, "01/03/2024", "28/02/2026")
    row("Docente", "VASQUEZ ROBLES, XIMENA ALEJANDRA", "DOCENTE", 1533227, 1122357, "01/04/2023", "28/02/2026")
    row("Docente", "VELASQUEZ LOPEZ, LUIS PEDRO", "DOCENTE", 1850298, 1486329, "01/03/2021", "28/02/2026")
    row("Docente", "VENEGAS ARELLANO, MARIO ANTONIO", "DOCENTE", 1648510, 1055970, "05/03/2025", "28/02/2026")

    assert len(r) == 36, len(r)
    return r


def main() -> None:
    wcsv("contrata_educacion_febrero_2026.csv", _rows_contrata_educacion())
    wcsv("contrata_salud_febrero_2026.csv", build_rows_contrata_salud())
    print("OK:", OUT)


if __name__ == "__main__":
    main()
