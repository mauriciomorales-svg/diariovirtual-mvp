# Investigación IA — Transcripciones Concejo Municipal de Renaico

> **Uso:** Manus, Cursor, ChatGPT, Gemini u otra IA con acceso a YouTube / texto pegado.  
> **Fuente principal de videos:** [Sesiones Concejo Municipal — Municipalidad de Renaico](https://municipalidadrenaico.cl/sesiones-concejo-municipal/)  
> **Complemento:** actas en Transparencia Activa, Mercado Público, comunicados oficiales del municipio.

---

## 1. Qué se busca

Responder con **evidencia en texto** (no suposiciones):

- ¿Cuándo y con qué votación se aprobaron fondos para **shows de verano / aniversario**?
- Montos en **pesos ($), UTM, UF**; concepto (producción técnica, artistas, trato directo, FNDR).
- Si hubo **suspensión, caída o inconsistencias** en licitaciones (ej. Show Aniversario 2026).
- Quién propuso y si hubo **aprobación, rechazo o abstención** (solo si la transcripción lo permite).

**Regla de oro:** si el monto o la fecha no aparecen en la transcripción ni en documento oficial citado, decir **«no consta en la fuente entregada»** — no inventar.

---

## 2. Fuentes oficiales (orden recomendado)

| Prioridad | Fuente | URL / lugar |
|-----------|--------|-------------|
| 1 | Videos + transcripción YouTube | [municipalidadrenaico.cl/sesiones-concejo-municipal](https://municipalidadrenaico.cl/sesiones-concejo-municipal/) |
| 2 | Actas / citaciones | Misma sección + portal Transparencia Activa municipal |
| 3 | Licitaciones | [Mercado Público](https://www.mercadopublico.cl) — buscar organismo «Municipalidad de Renaico», palabras: `Servicios de Producción de Eventos`, `audio`, `iluminación`, `aniversario` |
| 4 | Comunicados | Instagram/Facebook `@munirenaico`, sitio municipal |
| 5 | Presencial | Municipalidad, Calle Comercio 287, Lun–Vie 08:30–14:00 (oficina de partes / transparencia) |

### Sesiones útiles para Show Aniversario / verano (orientación)

Revisar en este orden si el objetivo es **dinero del show 2025–2026**:

1. Sesiones **extraordinarias diciembre 2025** (presupuesto / licitación producción).
2. **Primera sesión ordinaria enero 2026** (parrilla artistas, ajustes).
3. Comunicado **19-01-2026** (suspensión licitación por inconsistencias — cruzar con lo dicho en concejo).
4. Histórico **Show Aniversario 2025** (Instagram/Facebook municipal, ene 2025) para comparar montos año a año.

Rango típico citado en licitaciones de producción de eventos municipales: **100 UTM – 1.000 UTM** (solo como pista de búsqueda; el monto real debe salir del acta o ficha de licitación).

---

## 3. Flujo de trabajo (3 pasos)

### Paso A — Obtener transcripción

1. Abrir el video en YouTube (desde el enlace embebido en la página del concejo).
2. Activar **subtítulos automáticos** → «Mostrar transcripción» → copiar texto.
3. Guardar con nombre: `concejo-renaico-YYYY-MM-DD-sesion-N.txt`
4. Si es posible, anotar **duración del video** y **marca de tiempo** al copiar bloques relevantes.

### Paso B — Limpiar transcripción (prompt corto)

Pegar primero este prompt con el texto en bruto:

```text
Actúa como secretario municipal experto. Te entregaré una transcripción en bruto de un Concejo Municipal de Renaico (Chile), extraída de YouTube.

Tareas:
1) Puntuación, párrafos y mayúsculas donde corresponda.
2) Separar diálogos por orador cuando el contexto lo permita: Alcalde (Claudio Musre Contreras), Secretario/a, Concejales (usar apellido si se menciona).
3) Corregir fonética: FNDR, UTM, UF, licitación, trato directo, DAEM, SECPLA, etc.
4) Conservar marcas de tiempo [HH:MM:SS] si las traigo en el texto.
5) NO agregar datos que no estén en el texto.

Devuelve solo el texto limpio, listo para auditoría.
```

### Paso C — Investigar dinero y shows (prompt principal)

Pegar **después** del texto limpio (o en bruto si no hay tiempo para limpiar):

```text
Actúa como un Auditor Municipal y Analista de Datos experto. Te entregaré la transcripción de un Concejo Municipal de Renaico (Región de La Araucanía, Chile), extraída de YouTube.

Fuente del video: https://municipalidadrenaico.cl/sesiones-concejo-municipal/

Tu objetivo es investigar a fondo el texto y extraer exclusivamente información financiera y presupuestaria relacionada con eventos, shows artísticos, fiestas de verano, aniversario comunal o contrataciones vinculadas a esos espectáculos.

Reglas estrictas:
- Cita literal corta (máx. 25 palabras) cuando identifiques un monto o acuerdo.
- Si hay marca de tiempo en el texto, inclúyela en cada hallazgo.
- Si un dato no aparece, escribe: «No consta en esta transcripción».
- No uses cifras de otros años o de noticias externas salvo que este mismo texto las mencione.
- Distingue: gasto municipal directo vs FNDR / Gobierno Regional vs patrocinio privado.

Investiga siguiendo estas líneas:

1. FILTRO DE DINERO
   Busca $, pesos chilenos, UTM, UF, «millones», «presupuesto», «licitación», «trato directo», «subvención», «adjudicación», «imputación presupuestaria».

2. CONTEXTO DE ARTISTAS Y PRODUCCIÓN
   Productoras, sonido, iluminación, escenario, pantallas, generadores, nombres de artistas o «show aniversario».

3. CONCEJALES Y VOTACIÓN
   Propuesta, segundo, votación a favor/en contra/abstención, quorum, «se aprueba», «se rechaza».

4. ALERTAS
   Licitación suspendida, caída, inconsistencias, observaciones de contraloría, modificación de monto.

Formato de respuesta:

## Resumen ejecutivo
(Un párrafo: qué se aprobó o no, y nivel de certeza.)

## Montos identificados
| Cifra | Concepto | Minuto (si hay) | Cita literal breve |
|-------|----------|-----------------|-------------------|

## Acuerdos y votaciones
- Ítem de tabla / punto tratado
- Resultado (aprobado / rechazado / no consta)

## Vacíos y próximos pasos
- Qué falta confirmar en acta escrita o Mercado Público
- Qué otra sesión conviene revisar (fecha o número)

## Nivel de confianza
Alto / Medio / Bajo — y por qué.
```

---

## 4. Plantilla — Solicitud Transparencia (si el video no trae el monto)

```text
Señores
Ilustre Municipalidad de Renaico
Oficina de Partes / Unidad de Transparencia

Yo, [NOMBRE], RUT [RUT], solicito conforme a la Ley N° 20.285:

1. Copia del acta de la sesión [ordinaria/extraordinaria] N° [___] de [fecha], en que se trató el presupuesto, licitación o trato directo del Show Aniversario Renaico [año].
2. Resolución(es) o decreto(s) que aprueben el gasto y monto adjudicado.
3. Ficha o bases de licitación en Mercado Público vinculadas a «Servicios de Producción de Eventos» o equivalente para el período [mes/año].

Forma de entrega: [correo / retiro presencial]
Plazo: según art. 14 Ley 20.285.

Atentamente,
[Firma]
```

---

## 5. Salida para Diario Zona Sur (si hay hallazgo verificable)

Solo publicar si hay **al menos dos fuentes** (ej. transcripción + acta, o transcripción + ficha Mercado Público):

- Título tipo: `🚨 Concejo de Renaico: [qué se aprobó o cuestionó] en sesión del [fecha]`
- Atribuir: Municipalidad de Renaico / transmisión oficial
- `external_url`: enlace al video o página de sesiones
- No incluir teléfonos en el cuerpo; enlace a fuente oficial
- Pie: `Fuente: Municipalidad de Renaico | Leer sesión original`

---

## 6. Contexto ya conocido (no sustituye el acta)

| Fecha | Hito | Verificar en concejo |
|-------|------|----------------------|
| Dic 2025 | Bases stands / procesos licitación producción técnica | Sesiones dic 2025 |
| 14-01-2026 | Anuncio parrilla artistas (29 ene – 1 feb 2026) | Comunicado + sesión ene 2026 |
| 19-01-2026 | Comunicado: licitación show suspendida por inconsistencias | Sesión que trató el punto |
| Ene 2025 | Show Aniversario 2025 (referencia histórica) | Comparar montos con 2026 |

---

## 7. Checklist antes de dar por cerrada la investigación

- [ ] ¿Leí la transcripción completa o busqué en el texto las palabras clave (`licitación`, `UTM`, `show`, `aniversario`)?
- [ ] ¿Cruzé con Mercado Público si se mencionó un ID o nombre de licitación?
- [ ] ¿Marqué claramente lo que es inferencia vs cita literal?
- [ ] ¿Indiqué qué sesión falta revisar si esta no tenía el dato?

---

*Documento para uso editorial y ciudadano — Diario Zona Sur / data-municipalidad-renaico. Actualizar cuando haya nuevas sesiones en la [página oficial del concejo](https://municipalidadrenaico.cl/sesiones-concejo-municipal/).*
