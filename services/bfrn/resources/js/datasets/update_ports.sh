#!/usr/bin/env bash
set -euo pipefail

RAW_DIR="$(cd "$(dirname "$0")" && pwd)/raw"
OUT_DIR="$(cd "$(dirname "$0")" && pwd)/json"
mkdir -p "$RAW_DIR" "$OUT_DIR"

# OurAirports (open data) — contains airports, but we treat as "ports/terminals list" for free mapping
CSV_URL="https://davidmegginson.github.io/ourairports-data/airports.csv"
CSV_FILE="$RAW_DIR/airports.csv"
OUT_JSON="$OUT_DIR/ports.json"

echo "[ports] Downloading airports.csv..."
curl -fsSL "$CSV_URL" -o "$CSV_FILE"

echo "[ports] Normalizing to ports.json..."
python3 - <<PY
import csv, json
from pathlib import Path

csv_file = Path("$CSV_FILE")
out_file = Path("$OUT_JSON")

ports=[]
with csv_file.open("r", encoding="utf-8", errors="ignore", newline="") as f:
    r = csv.DictReader(f)
    for row in r:
        # keep only bigger facilities (optional)
        t = (row.get("type") or "").lower()
        if t in ("heliport","balloonport","closed"):
            continue

        ports.append({
            "id": row.get("id"),
            "name": row.get("name"),
            "type": row.get("type"),
            "iata": row.get("iata_code"),
            "icao": row.get("ident"),
            "country": row.get("iso_country"),
            "region": row.get("iso_region"),
            "municipality": row.get("municipality"),
            "latitude": row.get("latitude_deg"),
            "longitude": row.get("longitude_deg"),
            "elevation_ft": row.get("elevation_ft"),
            "home_link": row.get("home_link"),
            "wikipedia_link": row.get("wikipedia_link"),
        })

out_file.write_text(json.dumps(ports, ensure_ascii=False), encoding="utf-8")
print(f"[ports] Wrote {len(ports)} rows -> {out_file}")
PY

echo "[ports] Done."