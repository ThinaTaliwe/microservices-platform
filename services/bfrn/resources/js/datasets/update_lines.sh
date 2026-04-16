#!/usr/bin/env bash
set -euo pipefail

RAW_DIR="$(cd "$(dirname "$0")" && pwd)/raw"
OUT_DIR="$(cd "$(dirname "$0")" && pwd)/json"
mkdir -p "$RAW_DIR" "$OUT_DIR"

# 1) Ocean carriers SCAC JSON (public gist)
OCEAN_SCAC_JSON_URL="https://gist.githubusercontent.com/cl3mcg/be760cb5687b2636c6ca7df97c64271a/raw/ocean_carrier_scac.json"
RAW_OCEAN="$RAW_DIR/ocean_carrier_scac.json"

# 2) UN/CEFACT SCAC codes CSV (public)
UNCEFACT_SCAC_CSV_URL="https://raw.githubusercontent.com/UNCEFACT-TL/SCAC/master/data/scac-codes.csv"
RAW_UNCEFACT="$RAW_DIR/scac-codes.csv"

OUT_JSON="$OUT_DIR/shipping_lines.json"

echo "[lines] Downloading ocean carrier SCAC JSON..."
curl -fsSL "$OCEAN_SCAC_JSON_URL" -o "$RAW_OCEAN"

echo "[lines] Downloading UN/CEFACT SCAC CSV..."
curl -fsSL "$UNCEFACT_SCAC_CSV_URL" -o "$RAW_UNCEFACT"

echo "[lines] Normalizing to one JSON list..."
python3 - <<PY
import csv, json
from pathlib import Path

ocean_path = Path("$RAW_OCEAN")
un_path = Path("$RAW_UNCEFACT")
out_path = Path("$OUT_JSON")

items = []

# ocean list
try:
    ocean = json.loads(ocean_path.read_text(encoding="utf-8"))
    for r in ocean:
        items.append({
            "source": "ocean_carrier_scac",
            "name": r.get("carrierName") or r.get("name") or "",
            "scac": (r.get("scac") or r.get("SCAC") or "").strip(),
        })
except Exception as e:
    print("[lines] WARN ocean parse:", e)

# UN/CEFACT list (schema varies; we map best-effort)
with un_path.open(newline="", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    for r in reader:
        # find likely columns
        name = r.get("Company Name") or r.get("company_name") or r.get("name") or ""
        scac = r.get("SCAC") or r.get("scac") or ""
        items.append({
            "source": "uncefact_scac",
            "name": name.strip(),
            "scac": scac.strip(),
        })

# Deduplicate by (scac,name)
seen = set()
dedup = []
for it in items:
    k = (it["scac"].upper(), it["name"].lower())
    if k in seen: 
        continue
    seen.add(k)
    if it["scac"] or it["name"]:
        dedup.append(it)

out_path.write_text(json.dumps(dedup, ensure_ascii=False), encoding="utf-8")
print(f"[lines] Wrote {len(dedup)} rows -> {out_path}")
PY

echo "[lines] Done."