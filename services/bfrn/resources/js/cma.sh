#!/usr/bin/env bash
set -euo pipefail

# Commands:
#   ./cma.sh tnt_events EQUIPMENT APZU4812090 10
#   ./cma.sh tnt_events TRANSPORT <carrierBookingReference> 10
# Notes:
# - Public trial uses API key only (keyId header)
# - The API may return [] if the reference has no visible public events

CMD="${1:-}"
EVENT_TYPE="${2:-EQUIPMENT}"
REF="${3:-}"
LIMIT="${4:-10}"

CMA_KEYID="${CMA_KEYID:-}"
CMA_BASE="${CMA_BASE:-https://apis.cma-cgm.net}"

case "$CMD" in
  tnt_events)
    if [[ -z "$CMA_KEYID" ]]; then
      echo "ERROR: CMA_KEYID not set"
      echo "Example: export CMA_KEYID='your_keyId_here'"
      exit 1
    fi

    if [[ -z "$REF" ]]; then
      echo "ERROR: Missing reference."
      echo "Provide equipmentReference (EQUIPMENT) or carrierBookingReference (TRANSPORT/SHIPMENT)."
      exit 1
    fi

    REF_PARAM="carrierBookingReference"
    if [[ "$EVENT_TYPE" == "EQUIPMENT" ]]; then
      REF_PARAM="equipmentReference"
    fi

    URL="${CMA_BASE}/operation/trackandtrace/v1/events"
    TS="$(date +%Y%m%d_%H%M%S)"
    OUT_DIR="./cma_logs"
    mkdir -p "$OUT_DIR"
    OUT_FILE="${OUT_DIR}/events_${EVENT_TYPE}_${TS}.json"

    echo "Request:"
    echo "  URL      : $URL"
    echo "  eventType: $EVENT_TYPE"
    echo "  $REF_PARAM: $REF"
    echo "  limit    : $LIMIT"
    echo "Saving to  : $OUT_FILE"
    echo

    curl -sS -G "$URL" \
      -H "Accept: application/json" \
      -H "keyId: ${CMA_KEYID}" \
      --data-urlencode "eventType=${EVENT_TYPE}" \
      --data-urlencode "${REF_PARAM}=${REF}" \
      --data-urlencode "limit=${LIMIT}" \
      | tee "$OUT_FILE"

    echo
    echo "Done."
    ;;
  *)
    echo "Usage:"
    echo "  ./cma.sh tnt_events EQUIPMENT APZU4812090 10"
    echo "  ./cma.sh tnt_events TRANSPORT <carrierBookingReference> 10"
    exit 1
    ;;
esac