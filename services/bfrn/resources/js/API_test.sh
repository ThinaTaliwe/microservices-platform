#!/bin/bash

# API_KEY="ajlolJnAiakAECUZFMnQRRCaMONshiUx"

# curl -sS -G "https://apis.cma-cgm.net/operation/trackandtrace/v1/events" \
#   -H "Accept: application/json" \
#   -H "keyId: ajlolJnAiakAECUZFMnQRRCaMONshiUx" \
#   --data-urlencode "eventType=EQUIPMENT" \
#   --data-urlencode "equipmentReference=APZU4812090" \
#   --data-urlencode "limit=10"

# echo "Results: $TOKEN"

# curl -X GET "https://apis.cma-cgm.net/operation/trackandtrace/v1/events?equipmentReference=APZU4812090&eventType=EQUIPMENT&limit=10" \
#   -H "Authorization: Bearer $TOKEN" \
#   -H "keyId: $API_KEY" \
#   -H "Accept: application/json"




#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./cma_events.sh EQUIPMENT APZU4812090 10
#   ./cma_events.sh TRANSPORT "carrierBookingReferenceHere" 20
#
# Notes:
# - Public trial: use API key only (keyId header)
# - You must provide at least ONE of:
#   - carrierBookingReference
#   - equipmentReference

KEY_ID="${CMA_KEYID:-}"
BASE_URL="${CMA_BASE_URL:-https://apis.cma-cgm.net/operation/trackandtrace/v1}"

EVENT_TYPE="${1:-EQUIPMENT}"
REF="${2:-}"
LIMIT="${3:-10}"

if [[ -z "$KEY_ID" ]]; then
  echo "ERROR: Missing API key. Set env CMA_KEYID first."
  echo "Example: export CMA_KEYID='your_key_here'"
  exit 1
fi

if [[ -z "$REF" ]]; then
  echo "ERROR: Missing reference."
  echo "Provide equipmentReference (e.g. APZU4812090) or carrierBookingReference."
  exit 1
fi

# Decide which reference parameter to send based on eventType (simple rule):
# - If EQUIPMENT -> equipmentReference
# - Otherwise -> carrierBookingReference (you can adjust as you learn)
REF_PARAM="equipmentReference"
if [[ "$EVENT_TYPE" != "EQUIPMENT" ]]; then
  REF_PARAM="carrierBookingReference"
fi

TS="$(date +%Y%m%d_%H%M%S)"
OUT_DIR="./cma_logs"
mkdir -p "$OUT_DIR"
OUT_FILE="${OUT_DIR}/events_${EVENT_TYPE}_${TS}.json"

echo "Request:"
echo "  BASE_URL : $BASE_URL"
echo "  eventType: $EVENT_TYPE"
echo "  $REF_PARAM: $REF"
echo "  limit    : $LIMIT"
echo "Saving to  : $OUT_FILE"
echo

curl -sS -G "${BASE_URL}/events" \
  -H "Accept: application/json" \
  -H "keyId: ${KEY_ID}" \
  --data-urlencode "eventType=${EVENT_TYPE}" \
  --data-urlencode "${REF_PARAM}=${REF}" \
  --data-urlencode "limit=${LIMIT}" \
  | tee "$OUT_FILE"

echo
echo "Done."