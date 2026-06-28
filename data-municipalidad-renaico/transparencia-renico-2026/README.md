# Transparencia — Municipalidad de Renaico

Datos desde **04. Personal y remuneraciones → Personal a Contrata** (portal de transparencia activa).

| Archivo | Registros | Última actualización (según portal) |
|---------|-----------|-------------------------------------|
| `contrata_educacion_febrero_2026.csv` | 36 | 23/03/2026 |
| `contrata_salud_febrero_2026.csv` | 54 | 23/03/2026 |

**Comuna:** Renaico (Región de La Araucanía).  
**Periodo:** Febrero 2026.

- **Educación:** asignación (01); docencia / directivo DAEM / jefe UTP; contratos con término frecuente 28/02/2026.
- **Salud:** Ley **19.378** (APS); columnas de horas extras, viáticos “No informa” en este volcado.

**Totales (contrata salud, bruta / líquida):** $68.978.502 / $54.520.837.

**Archivos de soporte:** `contrata_salud_data.py` (datos de salud), `generate_csv.py` (genera ambos CSV).

Generación (desde cualquier directorio):

`python data/transparencia-renico-2026/generate_csv.py`
