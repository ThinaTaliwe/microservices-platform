<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <title>BFRN Operations UI — Mermaid Diagrams</title>

  <!-- ✅ Materialize (reliable CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/materialize-css@1.0.0/dist/css/materialize.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/materialize-css@1.0.0/dist/js/materialize.min.js"></script>

  <!-- ✅ Mermaid 10.9.1 (jsDelivr, often not blocked) -->
  <script defer src="https://cdn.jsdelivr.net/npm/mermaid@10.9.1/dist/mermaid.min.js"></script>

  <style>
    body { background:#fafafa; }
    .wrap { max-width: 100%; margin: 24px auto; padding: 0 12px; }
    .card { border-radius: 14px; }
    .card-title { font-weight: 700 !important; }
    .hint { color:#666; font-size:.95rem; margin-top:.35rem; }
    .mermaid {
      overflow: auto;
      padding: 12px 6px;
    }
    /* Make the diagram scroll instead of stretching page */
    .mermaid svg { max-width: none !important; height: auto; }
  </style>
</head>

<body>
  <div class="wrap">
    <h5 style="margin:0 0 8px 0;">BFRN Operations UI — Flow Diagrams</h5>
    <div class="hint">If you still see “blocked by client”, disable adblock for this page or use the jsDelivr links exactly as below.</div>

    <!-- ===================== 1) INIT + SHIPMENTS INDEX ===================== -->
    <div class="card">
      <div class="card-content">
        <span class="card-title">Diagram 1 — Init + Shipments Index</span>

        <div class="mermaid">
flowchart TD
  A0([Page Load]) --> A1["Define API endpoints + sidebar endpoint subtitles"]
  A1 --> A2["Mount operation sections (loading/movement/offloading/storage/documents/children)"]
  A2 --> A3["Show Shipments List View (hide sidebar + operations)"]
  A3 --> A4["Load Shipments Index"]

  subgraph S0["Shipments Index (View 1)"]
    direction TB

    S1["Reload click OR initial load"] --> S2["Abort previous shipments fetch (if exists)"]
    S2 --> S3["Spinner ON + Status = Loading"]
    S3 --> S4["FETCH shipments endpoint"]
    S4 --> S5{"Parse JSON?"}
    S5 -- Yes --> S6["payload = JSON"]
    S5 -- No --> S7["payload = raw text"]
    S6 --> S8["Render Debug JSON (raw-shipments)"]
    S7 --> S8

    S8 --> S9{"HTTP OK?"}
    S9 -- No --> S10["Status = HTTP code (bad) + render empty table"]
    S10 --> S11["Spinner OFF"]

    S9 -- Yes --> S12["items = pickArray(payload)"]
    S12 --> S13["allShipments = map(extractShipmentSummary)"]

    S13 --> S14["Try fetch shipment-has-shipment relationships"]
    S14 --> S15["FETCH children relationships endpoint"]
    S15 --> S16{"Parse JSON?"}
    S16 -- Yes --> S17["relPayload = JSON"]
    S16 -- No --> S18["relPayload = empty object"]
    S17 --> S19["relItems = pickArray(relPayload)"]
    S18 --> S19

    S19 --> S20["shipmentRelationships = map(parentId, childId, name, desc, code)"]
    S20 --> S21["childIds = Set(childId)"]
    S21 --> S22["parentShipments = allShipments EXCLUDING childIds"]
    S22 --> S23["Remove duplicates by signature"]
    S23 --> S24["Render Shipments Index (parents only)"]
    S24 --> S25["Status = OK + Spinner OFF"]

    S26["User types shipments search"] --> S27{"Query empty?"}
    S27 -- Yes --> S24
    S27 -- No --> S28["Filter parentShipments by text match"]
    S28 --> S24
  end

  A4 --> S1
        </div>
      </div>
    </div>

    <!-- ===================== 2) SELECT SHIPMENT + VIEW SWITCHING + OPERATIONS ===================== -->
    <div class="card" style="margin-top:18px;">
      <div class="card-content">
        <span class="card-title">Diagram 2 — Select Shipment + Views + Operations Tabs</span>

        <div class="mermaid">
flowchart TD
  subgraph S30["Select Shipment"]
    direction TB
    T1["Render table rows from parentShipments"] --> T2["Row click"]
    T2 --> T3["selectedShipment = clicked shipment summary"]
    T3 --> T4["Fill Selected Shipment header + badge"]
    T4 --> T5["Show Operations View (View 2) + show sidebar"]
    T5 --> T6["Default tab = loading"]
  end

  subgraph V0["Views"]
    direction TB
    V1["showShipmentsListView()"] --> V2["Show shipments list"]
    V1 --> V3["Hide operations view + hide sidebar"]
    V1 --> V4["selectedShipment = null"]
    V1 --> V5["Reset sidebar active tab to loading"]

    V6["showOperationsView()"] --> V7["Hide shipments list"]
    V6 --> V8["Show operations view + show sidebar"]
  end

  C1["User clicks Change Shipment"] --> V1

  subgraph O0["Operations View (View 2)"]
    direction TB
    O1["Sidebar tab click"] --> O2["showPanel(panelKey)"]
    O2 --> O3["Hide all panels"]
    O2 --> O4["Show panelKey panel"]
    O2 --> O5["Highlight sidebar active tab"]
    O2 --> O6["loadPanel(panelKey)"]

    O7["Dataset switch click (loadings vs items etc.)"] --> O8["activeDataset[panelKey] = chosen dataset"]
    O8 --> O6
  end

  T6 --> O6
        </div>
      </div>
    </div>

    <!-- ===================== 3) ENDPOINT RESOLUTION + PANEL LOAD + MODAL ===================== -->
    <div class="card" style="margin-top:18px;">
      <div class="card-content">
        <span class="card-title">Diagram 3 — Endpoint Resolve + Load Panel + Render Modal</span>

        <div class="mermaid">
flowchart TD
  subgraph E0["resolveEndpoint(panelKey)"]
    direction TB
    E1{"panelKey = children?"} -- Yes --> E2{"selectedShipment.id exists?"}
    E2 -- Yes --> E3["endpoint = childrenEndpoint + parentId + slash"]
    E2 -- No --> E4["endpoint = empty"]
    E1 -- No --> E5{"panelKey = documents?"}
    E5 -- Yes --> E6["docId = shipment.id OR tracking OR container"]
    E6 --> E7{"docId exists?"}
    E7 -- Yes --> E8["endpoint = documentsEndpoint + docId + documentsPath"]
    E7 -- No --> E4

    E5 -- No --> E9{"panelKey = loading?"}
    E9 -- Yes --> E10{"dataset = loadingItems?"}
    E10 -- Yes --> E11["endpoint = loadingItems endpoint"]
    E10 -- No --> E12["endpoint = loadings endpoint"]

    E9 -- No --> E13{"panelKey = movement?"}
    E13 -- Yes --> E14{"dataset = movementItems?"}
    E14 -- Yes --> E15["endpoint = movementItems endpoint"]
    E14 -- No --> E16["endpoint = movements endpoint"]

    E13 -- No --> E17{"panelKey = offloading?"}
    E17 -- Yes --> E18{"dataset = offloadingItems?"}
    E18 -- Yes --> E19["endpoint = offloadingItems endpoint"]
    E18 -- No --> E20["endpoint = offloadings endpoint"]

    E17 -- No --> E21{"panelKey = storage?"}
    E21 -- Yes --> E22{"dataset = storageItems?"}
    E22 -- Yes --> E23["endpoint = storageItems endpoint"]
    E22 -- No --> E24["endpoint = storage endpoint"]
    E21 -- No --> E4
  end

  subgraph L0["loadPanel(panelKey)"]
    direction TB
    L1["endpoint = resolveEndpoint(panelKey)"] --> L2["Show endpoint label in UI"]
    L2 --> L3["Abort previous fetch for panelKey"]
    L3 --> L4["Spinner ON + Status = Loading"]
    L4 --> L5["FETCH endpoint"]
    L5 --> L6{"Parse JSON?"}
    L6 -- Yes --> L7["payload = JSON"]
    L6 -- No --> L8["payload = raw text"]
    L7 --> L9["Render Debug JSON (raw-panelKey)"]
    L8 --> L9
    L9 --> L10{"HTTP OK?"}
    L10 -- No --> L11["Status = HTTP code (bad) + render empty state"]
    L11 --> L12["Spinner OFF"]
    L10 -- Yes --> L13["items = pickArray(payload)"]
    L13 --> L14{"panelKey = documents?"}
    L14 -- Yes --> L15["renderDocuments(items)"]
    L14 -- No --> L16["renderCards(panelKey, items)"]
    L15 --> L17["Status = OK + Spinner OFF"]
    L16 --> L17
  end

  subgraph M0["renderShipmentModal(panelKey, datasetKey, rowObj)"]
    direction TB
    M1["lastSelectedShipment = rowObj"] --> M2["Ensure modal instance exists"]
    M2 --> M3["Compute modal title (panelKey + shipment name fallback)"]
    M3 --> M4["sum = extractShipmentSummary(rowObj)"]
    M4 --> M5["Fill summary fields (status/mode/container/tracking/from/to)"]
    M5 --> M6{"Any dates exist?"}
    M6 -- Yes --> M7["Show dates row (ETD/ETA/Created/Updated)"]
    M6 -- No --> M8["Hide dates row"]
    M7 --> M9["Split keys (primitive vs nested)"]
    M8 --> M9
    M9 --> M10["Render primitive keys as KV grid"]
    M10 --> M11{"Nested keys exist?"}
    M11 -- Yes --> M12["Render nested blocks (object/array JSON boxes)"]
    M11 -- No --> M13["Hide nested section"]
    M12 --> M14["Show modal"]
    M13 --> M14
    M15["Copy JSON click"] --> M16["clipboard.writeText(JSON stringify)"]
    M16 --> M17["Show Copied message briefly"]
  end

  E0 --> L0
  L0 --> M0
        </div>
      </div>
    </div>

  </div>

  <script>
    // ✅ Robust init that works even if some other scripts fail
    window.addEventListener('DOMContentLoaded', function () {
      if (!window.mermaid) {
        console.error("Mermaid did not load. If you see 'blocked by client', disable adblock for this page.");
        return;
      }

      mermaid.initialize({
        startOnLoad: true,
        securityLevel: "loose",
        theme: "default",
        flowchart: { useMaxWidth: false }
      });

      // Force render (helps when scripts are deferred)
      try {
        mermaid.init(undefined, document.querySelectorAll(".mermaid"));
      } catch (e) {
        console.error("Mermaid render error:", e);
      }
    });
  </script>
</body>
</html>
