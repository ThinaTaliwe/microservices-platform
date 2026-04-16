#!/usr/bin/env bash
set -euo pipefail

RAW_DIR="$(cd "$(dirname "$0")" && pwd)/raw"
OUT_DIR="$(cd "$(dirname "$0")" && pwd)/json"
mkdir -p "$RAW_DIR" "$OUT_DIR"

# SARS ACM code tables (public CSV endpoints)
SEA_URL="https://tools.sars.gov.za/ACM_code_Tables/CargoCarrierSea.csv"
ROAD_URL="https://tools.sars.gov.za/ACM_code_Tables/CarrierRoad.csv"

RAW_SEA="$RAW_DIR/CargoCarrierSea.csv"
RAW_ROAD="$RAW_DIR/CarrierRoad.csv"
OUT_JSON="$OUT_DIR/clearing_agents.json"

echo "[agents] Downloading SARS CargoCarrierSea..."
curl -fsSL "$SEA_URL" -o "$RAW_SEA"

echo "[agents] Downloading SARS CarrierRoad..."
curl -fsSL "$ROAD_URL" -o "$RAW_ROAD"

echo "[agents] Extracting 'Customs Brokers' entries (plus carriers) into JSON..."
python3 - <<PY
import csv, json
from pathlib import Path

sea = Path("$RAW_SEA")
road = Path("$RAW_ROAD")
out = Path("$OUT_JSON")

def read_csv(path):
    # These SARS CSVs are often 2-column: Name, Code (or similar), but not guaranteed.
    rows = []
    with path.open(newline="", encoding="utf-8", errors="ignore") as f:
        reader = csv.reader(f)
        for r in reader:
            if not r: 
                continue
            # best effort: first col label, second col code
            name = (r[0] if len(r) > 0 else "").strip()
            code = (r[1] if len(r) > 1 else "").strip()
            rows.append({"name": name, "code": code})
    return rows

sea_rows = read_csv(sea)
road_rows = read_csv(road)

# Pull out "Customs Brokers" group rows (SARS has lines like: "Customs Brokers <code>,<name> <code>, ...")
def classify(rows, mode):
    out = []
    for x in rows:
        nm = x["name"]
        code = x["code"]
        t = nm.lower()
        kind = "carrier"
        if "customs broker" in t or "customs brokers" in t:
            kind = "customs_brokers_header"
        elif any(k in t for k in ["clearing", "customs", "broker", "forward", "logistics"]):
            # heuristic: treat as potential clearing agent / broker
            kind = "possible_clearing_agent"
        out.append({
            "source": f"sars_{mode}",
            "kind": kind,
            "name": nm,
            "code": code
        })
    return out

data = classify(sea_rows, "sea") + classify(road_rows, "road")
out.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
print(f"[agents] Wrote {len(data)} rows -> {out}")
PY

echo "[agents] Done."
