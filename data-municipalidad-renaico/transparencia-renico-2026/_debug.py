# -*- coding: utf-8 -*-
import csv
from io import StringIO
from pathlib import Path

p = Path(r"C:\wamp64\www\muni renaico\planta municpailidad.csv")
content = p.read_text(encoding="latin-1")
rows = list(csv.DictReader(StringIO(content), delimiter=";"))
r = rows[0]

# Buscar columna bruta exacta
for k, v in r.items():
    if "bruta" in k.lower():
        print(f"BRUTA col: {repr(k)}")
        print(f"BRUTA val: {repr(v)}")
        # parse
        clean = v.replace(" ", "").replace("$", "").replace("\xa0", "").replace("-", "")
        print(f"clean: {repr(clean)}")
        if "." in clean:
            clean = clean.replace(".", "")
        print(f"final: {repr(clean)}")
        try:
            print(f"int: {int(clean)}")
        except Exception as e:
            print(f"ERROR: {e}")
