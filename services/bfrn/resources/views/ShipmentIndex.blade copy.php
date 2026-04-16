<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BFRN — Operations</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --bg: #0b0b0f;
      --panel: rgba(255, 255, 255, 0.06);
      --panel2: rgba(255, 255, 255, 0.08);
      --border: rgba(255, 255, 255, 0.12);
      --text: #eaeaf2;
      --muted: rgba(234, 234, 242, 0.75);
      --input-bg: rgba(255, 255, 255, 0.06);
      --json-bg: rgba(0, 0, 0, 0.35);
      --btn-text: #fff;
      --ok: #0f9d58;
      --bad: #db4437;
    }

    [data-theme="light"] {
      --bg: #f4f6f8;
      --panel: rgba(255, 255, 255, 0.65);
      --panel2: rgba(255, 255, 255, 0.85);
      --border: rgba(0, 0, 0, 0.08);
      --text: #212529;
      --muted: #6c757d;
      --input-bg: #ffffff;
      --json-bg: #f8f9fa;
      --btn-text: #212529;
    }

    [data-theme="light"] body {
      background: var(--bg);
      color: var(--text);
    }

    [data-theme="light"] .glass {
      background: var(--panel);
      border: 1px solid var(--border);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    [data-theme="light"] .form-control,
    [data-theme="light"] .form-select {
      background: var(--input-bg) !important;
      border-color: var(--border) !important;
      color: var(--text) !important;
    }

    [data-theme="light"] pre.json-box {
      background: var(--json-bg);
      border: 1px solid var(--border);
      color: #212529;
    }

    [data-theme="light"] .btn-glass {
      background: rgba(0, 0, 0, 0.05);
      border: 1px solid var(--border);
      color: var(--btn-text);
    }

    [data-theme="light"] .btn-glass:hover {
      background: rgba(0, 0, 0, 0.1);
    }

    [data-theme="light"] .json-key {
      color: #333;
      font-weight: 700;
    }

    [data-theme="light"] .json-str {
      color: #c7254e;
    }

    [data-theme="light"] .json-num {
      color: #008080;
    }

    body {
      background: var(--bg);
      color: var(--text);
    }

    .glass {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 14px;
    }

    .sidebar {
      position: sticky;
      top: 0;
      height: 100vh;
      overflow: auto;
      border-right: 1px solid var(--border);
      background: rgba(10, 10, 14, .65);
      backdrop-filter: blur(10px);
    }

    .nav-pills .nav-link {
      color: var(--text);
      border: 1px solid var(--border);
      background: var(--panel);
      border-radius: 14px;
      padding: 12px 12px;
      margin-bottom: 10px;
      text-align: left;
      transition: .15s;
    }

    .nav-pills .nav-link:hover {
      background: rgba(255, 255, 255, .08);
    }

    .nav-pills .nav-link.active {
      background: rgba(67, 240, 138, .12);
      border-color: rgba(67, 240, 138, .35);
      box-shadow: 0 0 0 2px rgba(67, 240, 138, .15) inset;
    }

    .nav-pills .nav-link.disabled {
      opacity: .5;
      pointer-events: none;
    }

    .nav-sub {
      font-size: 12px;
      color: var(--muted);
      margin-top: 2px;
      word-break: break-all;
    }

    .badge-soft {
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, .08);
      font-weight: 600;
    }

    .badge-ok {
      color: var(--ok);
    }

    .badge-bad {
      color: var(--bad);
    }

    .btn-glass {
      border: 1px solid rgba(255, 255, 255, .18);
      background: rgba(255, 255, 255, .06);
      color: #fff;
      border-radius: 10px;
    }

    .btn-glass:hover {
      background: rgba(255, 255, 255, .10);
      color: #fff;
    }

    pre.json-box {
      background: rgba(0, 0, 0, .35);
      border: 1px solid rgba(255, 255, 255, .10);
      border-radius: 12px;
      padding: 12px;
      max-height: 440px;
      overflow: auto;
      color: #eaeaf2;
      line-height: 1.35;
      font-size: 13px;
      white-space: pre;
    }

    pre.json-box::-webkit-scrollbar {
      height: 10px;
      width: 10px;
    }

    pre.json-box::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, .14);
      border-radius: 999px;
    }

    pre.json-box::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, .22);
    }

    .json-key {
      color: rgba(234, 234, 242, .92);
      font-weight: 650;
    }

    .json-str {
      color: #9ad1ff;
    }

    .json-num {
      color: #ffd48a;
    }

    .json-bool {
      color: #b9a6ff;
      font-weight: 650;
    }

    .json-null {
      color: rgba(255, 255, 255, .55);
      font-style: italic;
    }

    code {
      color: rgba(234, 234, 242, .9);
    }

    .muted {
      color: var(--muted);
    }

    .section-panel {
      display: none;
    }

    .section-panel.active {
      display: block;
    }

    .ds-switch .btn {
      border: 1px solid rgba(255, 255, 255, .16);
      background: rgba(255, 255, 255, .06);
      color: var(--text);
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 13px;
    }

    .ds-switch .btn:hover {
      background: rgba(255, 255, 255, .10);
    }

    .ds-switch .btn.active {
      background: rgba(67, 240, 138, .12);
      border-color: rgba(67, 240, 138, .35);
      box-shadow: 0 0 0 2px rgba(67, 240, 138, .12) inset;
    }

    .ship-card {
      border-radius: 16px;
      cursor: pointer;
      transition: transform .12s ease, background .12s ease, border-color .12s ease;
      background: rgba(255, 255, 255, .06);
      border: 1px solid rgba(255, 255, 255, .12);
      height: 100%;
    }

    .ship-card:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, .08);
      border-color: rgba(255, 255, 255, .18);
    }

    .ship-pill {
      border: 1px solid rgba(255, 255, 255, .12);
      background: rgba(255, 255, 255, .07);
      border-radius: 999px;
      padding: 4px 10px;
      font-size: 12px;
      color: rgba(234, 234, 242, .9);
      white-space: nowrap;
    }

    .ship-pill.ok {
      border-color: rgba(67, 240, 138, .35);
      background: rgba(67, 240, 138, .10);
      color: var(--ok);
    }

    .ship-pill.bad {
      border-color: rgba(255, 107, 107, .35);
      background: rgba(255, 107, 107, .10);
      color: var(--bad);
    }

    .ship-meta {
      font-size: 13px;
      color: rgba(234, 234, 242, .82);
    }

    .ship-meta .k {
      color: rgba(234, 234, 242, .60);
      font-size: 12px;
    }

    .modal-content.glass {
      background: rgba(10, 10, 14, .85);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, .12);
    }

    .modal-header,
    .modal-footer {
      background: rgba(10, 10, 14, .75);
      backdrop-filter: blur(10px);
    }

    .modal-body {
      background: rgba(10, 10, 14, .55);
    }

    .modal-sticky {
      position: sticky;
      top: 0;
      z-index: 2;
    }

    .modal-sticky-footer {
      position: sticky;
      bottom: 0;
      z-index: 2;
    }

    .debug-wrap {
      display: none;
    }

    .debug-wrap.show {
      display: block;
    }

    .click-row {
      cursor: pointer;
    }

    .click-row:hover {
      background: rgba(255, 255, 255, .06);
    }

    .table-dark-glass {
      --bs-table-bg: transparent;
      --bs-table-color: var(--text);
      --bs-table-border-color: rgba(255, 255, 255, .12);
    }

    .table thead th {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: rgba(234, 234, 242, .85);
    }

    .view-hidden {
      display: none !important;
    }

    .kv-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 10px;
    }

    @media (min-width: 992px) {
      .kv-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    .kv-item {
      background: rgba(255, 255, 255, .05);
      border: 1px solid rgba(255, 255, 255, .10);
      border-radius: 12px;
      padding: 10px 12px;
      min-height: 62px;
    }

    .kv-item .k {
      color: rgba(234, 234, 242, .65);
      font-size: 12px;
      margin-bottom: 4px;
    }

    .kv-item .v {
      font-weight: 600;
      color: rgba(234, 234, 242, .95);
      word-break: break-word;
      line-height: 1.25;
    }

    .v .line {
      display: block;
      font-weight: 600;
    }

    .v .line+.line {
      margin-top: 2px;
    }

    .ship-name {
      font-weight: 650;
      font-size: 14px;
      color: rgba(234, 234, 242, .95);
    }

    .ship-desc {
      font-size: 12px;
      color: rgba(234, 234, 242, .70);
      margin-top: 2px;
      max-width: 900px;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row g-0">
      <aside id="opsSidebar" class="col-12 col-md-3 col-xl-2 p-3 sidebar view-hidden">
        <div class="nav nav-pills flex-column" id="sideTabs" role="tablist">
          <button class="nav-link active" data-key="loading" type="button">
            <div class="fw-semibold">Loading</div>
            <div class="nav-sub" id="ep-loading"></div>
          </button>
          <button class="nav-link" data-key="movement" type="button">
            <div class="fw-semibold">Movement</div>
            <div class="nav-sub" id="ep-movement"></div>
          </button>
          <button class="nav-link" data-key="offloading" type="button">
            <div class="fw-semibold">Offloading</div>
            <div class="nav-sub" id="ep-offloading"></div>
          </button>
          <button class="nav-link" data-key="storage" type="button">
            <div class="fw-semibold">Storage</div>
            <div class="nav-sub" id="ep-storage"></div>
          </button>
          <button class="nav-link" data-key="documents" type="button">
            <div class="fw-semibold">Documents</div>
            <div class="nav-sub" id="ep-documents"></div>
          </button>
          <button class="nav-link" data-key="children" type="button" id="tab-children">
            <div class="fw-semibold">Children</div>
            <div class="nav-sub" id="ep-children"></div>
          </button>
        </div>
      </aside>

      <main class="col-12 col-md-9 col-xl-10 p-3 p-lg-4 mx-auto my-auto">
        <div class="container" style="max-width: 1320px;">
          <section id="shipmentsListView" class="section-panel active">
            <div class="glass p-3 p-lg-4 mb-3">
              <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
                <div>
                  <h4 class="mb-1">Search by Shipment</h4>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <button class="btn btn-glass" id="btnReload-shipments">
                    <span class="spinner-border spinner-border-sm d-none" id="spin-shipments" role="status"
                      aria-hidden="true"></span>
                    Reload
                  </button>
                  <span class="badge badge-soft px-3 py-2" id="status-shipments">Idle</span>
                </div>
              </div>
              <div class="mt-3">
                <div class="row g-2">
                  <div class="col-12 col-lg-6">
                    <input id="shipmentsSearch" class="form-control"
                      style="background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.12); color: var(--text);"
                      placeholder="Search by name, description, container, ref, from, to, status...">
                  </div>
                  <div class="col-12 col-lg-6 d-flex justify-content-lg-end align-items-center gap-2">
                    <div class="muted">Source Endpoint:</div>
                    <code id="endpoint-shipments"></code>
                  </div>
                </div>
              </div>
            </div>
            <div class="glass p-3 mb-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div><strong>Shipments:</strong> <span id="count-shipments">0</span></div>
                <div class="muted" id="hint-shipments">Click a row to open that shipment.</div>
              </div>
            </div>
            <div class="glass p-3">
              <div class="table-responsive">
                <table class="table table-sm table-dark-glass align-middle mb-0">
                  <thead>
                    <tr>
                      <th style="min-width: 260px;">Shipment</th>
                      <th>Container</th>
                      <th>Ref / Tracking</th>
                      <th>Status</th>
                      <th>Mode</th>
                      <th>From</th>
                      <th>To</th>
                    </tr>
                  </thead>
                  <tbody id="tbody-shipments"></tbody>
                </table>
              </div>
              <div class="debug-wrap mt-3" id="debug-shipments">
                <div class="fw-semibold mb-2">Raw JSON (Debug)</div>
                <pre class="json-box mb-0" id="raw-shipments">{}</pre>
              </div>
              <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-glass" id="btnDebug-shipments" type="button">Debug JSON</button>
              </div>
            </div>
          </section>

          <section id="operationsView" class="section-panel">
            <div class="glass p-3 p-lg-4 mb-3">
              <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                <div>
                  <div class="muted" style="font-size:12px;">Selected Shipment</div>
                  <div class="fs-5 fw-semibold" id="selectedShipmentTitle">—</div>
                  <div class="muted" style="font-size:13px;" id="selectedShipmentSubtitle">—</div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <span class="badge badge-soft px-3 py-2" id="selectedShipmentBadge">—</span>
                  <button class="btn btn-glass" id="btnChangeShipment">Change Shipment</button>
                </div>
              </div>
            </div>

            <section id="panel-loading" class="section-panel active"></section>
            <section id="panel-movement" class="section-panel"></section>
            <section id="panel-offloading" class="section-panel"></section>
            <section id="panel-storage" class="section-panel"></section>
            <section id="panel-documents" class="section-panel"></section>
            <section id="panel-children" class="section-panel"></section>
          </section>
        </div>
      </main>
    </div>
  </div>

  <div class="modal fade" id="shipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content glass" style="border-radius:0;">
        <div class="modal-header modal-sticky" style="border-bottom:1px solid rgba(255,255,255,.12);">
          <div class="pe-2">
            <h5 class="modal-title mb-0" id="shipmentModalTitle">Shipment Details</h5>
            <div class="muted" style="font-size:12px;" id="shipmentModalSub">—</div>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-glass" id="btnCopyShipmentJson">Copy JSON</button>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
              aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="container py-3" style="max-width: 1150px;">
            <div class="glass p-3 mb-3">
              <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                <div>
                  <div class="muted" style="font-size:12px;">Shipment Summary</div>
                  <div class="fs-5 fw-semibold" id="sumTitle">—</div>
                  <div class="muted" style="font-size:13px;" id="sumSubtitle">—</div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <span class="badge badge-soft px-3 py-2" id="sumStatusBadge">Status: —</span>
                  <span class="badge badge-soft px-3 py-2" id="sumModeBadge">Mode: —</span>
                </div>
              </div>
              <hr style="border-color: rgba(255,255,255,.10);" class="my-3">
              <div class="row g-2">
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">Container</div>
                  <div class="fw-semibold" id="sumContainer">—</div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">Tracking / Ref</div>
                  <div class="fw-semibold" id="sumTracking">—</div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">From</div>
                  <div class="fw-semibold" id="sumFrom">—</div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">To</div>
                  <div class="fw-semibold" id="sumTo">—</div>
                </div>
              </div>
              <div class="row g-2 mt-2" id="sumDatesRow" style="display:none;">
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">ETD</div>
                  <div class="fw-semibold" id="sumEtd">—</div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">ETA</div>
                  <div class="fw-semibold" id="sumEta">—</div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">Created</div>
                  <div class="fw-semibold" id="sumCreated">—</div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <div class="muted" style="font-size:12px;">Updated</div>
                  <div class="fw-semibold" id="sumUpdated">—</div>
                </div>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <div class="glass p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">More Details</div>
                    <span class="muted" style="font-size:12px;">Only this selected row</span>
                  </div>
                  <div id="shipmentDetailsKv" class="kv-grid"></div>
                </div>
              </div>

              <div class="col-12 col-lg-6" hidden>
                <div class="glass p-3">
                  <div class="fw-semibold mb-2">Selected Shipment JSON</div>
                  <pre class="json-box mb-0" id="shipmentDetailsRaw">{}</pre>
                </div>
              </div>

              <div class="glass p-3 mt-3 col-6" id="nestedWrapOuter" style="display:none;">
                <div class="fw-semibold mb-2">Nested / Extra Info</div>
                <div class="muted" style="font-size:13px;" id="shipmentExtraHint">Nested objects/arrays (if any) will appear here.</div>
                <div id="shipmentNestedBlocks" class="mt-2"></div>
              </div>

            </div>
          </div>
        </div>
        <div class="modal-footer modal-sticky-footer" style="border-top:1px solid rgba(255,255,255,.12);">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    /* =========================================================
    ROUTES
    ========================================================= */
    const API = {
      shipments: {
        shipments: "http://192.168.1.9/siya/api/shipments/shipments/",
        documents: "http://192.168.1.9/siya/api/shipments/shipments/",
        // IMPORTANT: base list endpoint (NO /{id}/)
        children: "http://192.168.1.9/siya/api/shipments/shipment-has-shipment/"
      },
      loading: {
        loadings: "http://192.168.1.9/siya/api/loading/loadings/",
        loadingsItems: "http://192.168.1.9/siya/api/loading/loading-items/"
      },
      movement: {
        movements: "http://192.168.1.9/siya/api/movement/movements/",
        movementItems: "http://192.168.1.9/siya/api/movement/movement-items/"
      },
      offloading: {
        offloadings: "http://192.168.1.9/siya/api/movement/offloadings/",
        offloadingItems: "http://192.168.1.9/siya/api/movement/offloading-items/"
      },
      storage: {
        storage: "http://192.168.1.9/siya/api/storage/storage/",
        storageItems: "http://192.168.1.9/siya/api/storage/storage-items/"
      }
    };

    /* Sidebar subtitles */
    document.getElementById('ep-loading').textContent = API.loading.loadings;
    document.getElementById('ep-movement').textContent = API.movement.movements;
    document.getElementById('ep-offloading').textContent = API.offloading.offloadings;
    document.getElementById('ep-storage').textContent = API.storage.storage;
    document.getElementById('ep-documents').textContent = "…/{shipment_id}/documents/";
    document.getElementById('ep-children').textContent = "…/shipment-has-shipment/ (filtered by parent_shipment)";

    /* =========================================================
    PRETTY JSON
    ========================================================= */
    function escapeHtml(str) {
      return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function syntaxHighlightJson(jsonString) {
      const re =
        /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\btrue\b|\bfalse\b|\bnull\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g;
      return escapeHtml(jsonString).replace(re, (match) => {
        if (match.startsWith('"') && match.endsWith('":')) return `<span class="json-key">${match}</span>`;
        if (match.startsWith('"')) return `<span class="json-str">${match}</span>`;
        if (match === 'true' || match === 'false') return `<span class="json-bool">${match}</span>`;
        if (match === 'null') return `<span class="json-null">${match}</span>`;
        return `<span class="json-num">${match}</span>`;
      });
    }

    function setRawPrettyById(elId, payload) {
      const raw = document.getElementById(elId);
      if (!raw) return;
      if (typeof payload === "string") {
        raw.textContent = payload;
        return;
      }
      raw.innerHTML = syntaxHighlightJson(JSON.stringify(payload, null, 2));
    }

    /* =========================================================
    SHARED HELPERS
    ========================================================= */
    const aborters = {};
    const activeDataset = {
      loading: "loadings",
      movement: "movements",
      offloading: "offloadings",
      storage: "storage",
      documents: "documents",
      children: "children"
    };

    function setStatusById(elId, text, ok = true) {
      const el = document.getElementById(elId);
      if (!el) return;
      el.textContent = text;
      el.classList.remove('badge-ok', 'badge-bad');
      el.classList.add(ok ? 'badge-ok' : 'badge-bad');
    }

    function setSpinnerById(elId, on) {
      const sp = document.getElementById(elId);
      if (!sp) return;
      sp.classList.toggle('d-none', !on);
    }

    function isObject(v) {
      return v && typeof v === 'object' && !Array.isArray(v);
    }

    function pickArray(payload) {
      if (Array.isArray(payload)) return payload;
      if (isObject(payload)) {
        const candidates = [
          'data', 'results', 'items',
          'shipments', 'shipment', 'shipment_list',
          'movements', 'movementItems', 'movement_items',
          'offloadings', 'offloadingItems', 'offloading_items',
          'loadings', 'loadingItems', 'loading_items',
          'storage', 'storageItems', 'storage_items',
          'documents',
          'children'
        ];
        for (const k of candidates)
          if (Array.isArray(payload[k])) return payload[k];
      }
      return [];
    }

    // NEW: if API returns a SINGLE relationship object, treat it as [obj]
    function normalizeToArray(payload) {
      if (Array.isArray(payload)) return payload;
      if (isObject(payload)) {
        const arr = pickArray(payload);
        if (arr.length) return arr;
        // if it looks like a relationship row, wrap it
        if ('parent_shipment' in payload && 'child_shipment' in payload) return [payload];
      }
      return [];
    }

    /* =========================================================
    SUMMARY EXTRACTOR
    ========================================================= */
    function titleCase(s) {
      return String(s)
        .replace(/_/g, ' ')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/\b\w/g, c => c.toUpperCase());
    }

    function normKey(k) {
      return String(k || "").toLowerCase().replace(/[\s\-]/g, "_");
    }

    function getFirstValue(obj, keys) {
      for (const k of keys) {
        if (obj && Object.prototype.hasOwnProperty.call(obj, k)) {
          const v = obj[k];
          if (v !== null && v !== undefined && String(v).trim() !== "") return v;
        }
      }
      return null;
    }

    function extractShipmentSummary(rowObj) {
      const keys = Object.keys(rowObj || {});
      const map = {};
      keys.forEach(k => map[normKey(k)] = k);
      const pick = (...candidateNormKeys) => {
        const actual = candidateNormKeys.map(nk => map[nk]).filter(Boolean);
        return getFirstValue(rowObj, actual);
      };

      const name = pick("shipment_name", "name", "shipment", "shipment_title", "title");
      const description = pick("description", "shipment_description", "desc", "notes", "note", "remarks", "comment", "comments");
      const container = pick("container_no", "container", "container_number", "containerid", "container_id");
      const tracking = pick("tracking_no", "tracking", "tracking_number", "trackingid", "tracking_id", "reference", "ref", "shipment_no", "shipmentno", "waybill", "awb", "bol", "billoflading", "bill_of_lading", "id", "uuid");
      const status = pick("status", "shipment_status", "state", "movement_status", "stage", "current_status");
      const mode = pick("mode", "transport_mode", "shipment_mode", "type", "movement_type");
      const origin = pick("origin", "from", "from_location", "from_site", "from_warehouse", "load_port", "port_of_loading", "pol", "pickup", "pickup_location");
      const dest = pick("destination", "to", "to_location", "to_site", "to_warehouse", "discharge_port", "port_of_discharge", "pod", "dropoff", "dropoff_location");
      const eta = pick("eta", "estimated_arrival", "estimated_arrival_date");
      const etd = pick("etd", "estimated_departure", "estimated_departure_date");
      const created = pick("created_at", "created", "date_created", "created_date");
      const updated = pick("updated_at", "updated", "date_updated", "updated_date");

      const titleParts = [];
      if (name) titleParts.push(String(name));
      if (!name && container) titleParts.push(`Container ${container}`);
      if (!name && !container && tracking) titleParts.push(`Ref ${tracking}`);
      if (!titleParts.length) titleParts.push("Selected Shipment");

      const subtitleParts = [];
      if (description) subtitleParts.push(String(description));
      if (origin || dest) subtitleParts.push(`${origin || "—"} → ${dest || "—"}`);

      const dateBits = [];
      if (etd) dateBits.push(`ETD ${etd}`);
      if (eta) dateBits.push(`ETA ${eta}`);
      if (!dateBits.length && (created || updated)) {
        if (created) dateBits.push(`Created ${created}`);
        if (updated) dateBits.push(`Updated ${updated}`);
      }
      if (dateBits.length) subtitleParts.push(dateBits.join(" • "));

      return {
        name: name ?? "—",
        description: description ?? "—",
        container: container ?? "—",
        tracking: tracking ?? "—",
        status: status ?? "—",
        mode: mode ?? "—",
        from: origin ?? "—",
        to: dest ?? "—",
        eta: eta ?? null,
        etd: etd ?? null,
        created: created ?? null,
        updated: updated ?? null,
        title: titleParts.join(" • "),
        subtitle: subtitleParts.join(" • ") || "—",
        id: rowObj.id ?? null,
        parent_shipment: rowObj.parent_shipment ?? null
      };
    }

    function pillClassFromStatus(statusText) {
      const t = String(statusText || "").toLowerCase();
      if (t.includes("complete") || t.includes("completed") || t.includes("done") || t.includes("delivered") || t.includes("arrived")) return "ok";
      if (t.includes("fail") || t.includes("error") || t.includes("cancel") || t.includes("rejected")) return "bad";
      return "";
    }

    /* =========================================================
    APP STATE
    ========================================================= */
    let selectedShipment = null;
    let allShipments = [];
    let parentShipments = [];

    const ShipmentContext = {
      mode: "parent",
      parentId: null,
    };

    function setShipmentContext(mode, parentId = null) {
      ShipmentContext.mode = mode;
      ShipmentContext.parentId = parentId;

      const childrenTab = document.getElementById("tab-children");
      if (childrenTab) {
        if (mode === "child") {
          childrenTab.classList.add("disabled");
          document.getElementById('ep-children').textContent = "Disabled (child context)";
        } else {
          childrenTab.classList.remove("disabled");
          document.getElementById('ep-children').textContent = "…/shipment-has-shipment/ (filtered by parent_shipment)";
        }
      }
    }

    function normalizeId(v) {
      return String(v || "").trim().toLowerCase().replace(/\s+/g, "");
    }

    function rowMatchesSelected(rowObj, datasetKey = null) {
      const childDatasets = ['loadingsItems', 'loadingItems', 'movementItems', 'offloadingItems', 'storageItems'];
      if (childDatasets.includes(datasetKey)) return true;
      if (!selectedShipment) return true;

      const s = extractShipmentSummary(rowObj);
      const selContainer = normalizeId(selectedShipment.container);
      const selTracking = normalizeId(selectedShipment.tracking);
      const rowContainer = normalizeId(s.container);
      const rowTracking = normalizeId(s.tracking);

      const containerMatch = selContainer && rowContainer && selContainer !== "—" && rowContainer !== "—" && rowContainer.includes(selContainer);
      const trackingMatch = selTracking && rowTracking && selTracking !== "—" && rowTracking !== "—" && rowTracking.includes(selTracking);

      if ((!selContainer || selContainer === "—") && (!selTracking || selTracking === "—")) return true;
      return containerMatch || trackingMatch;
    }

    /* =========================================================
    VIEW SWITCHING
    ========================================================= */
    function showShipmentsListView() {
      document.getElementById("shipmentsListView").classList.add("active");
      document.getElementById("operationsView").classList.remove("active");
      document.getElementById("opsSidebar").classList.add("view-hidden");
      selectedShipment = null;

      setShipmentContext("parent", null);

      document.querySelectorAll('#sideTabs .nav-link').forEach(btn => btn.classList.remove('active'));
      document.querySelector('#sideTabs .nav-link[data-key="loading"]').classList.add('active');
    }

    function showOperationsView() {
      document.getElementById("shipmentsListView").classList.remove("active");
      document.getElementById("operationsView").classList.add("active");
      document.getElementById("opsSidebar").classList.remove("view-hidden");
    }

    function applySelectedShipmentUI(s) {
      document.getElementById("selectedShipmentTitle").textContent = (s.name !== "—" ? s.name : s.title);
      document.getElementById("selectedShipmentSubtitle").textContent = (s.description !== "—" ? s.description : s.subtitle);

      const badgeParts = [];
      if (s.container && s.container !== "—") badgeParts.push(`Container: ${s.container}`);
      if (s.tracking && s.tracking !== "—") badgeParts.push(`Ref: ${s.tracking}`);
      if (s.status && s.status !== "—") badgeParts.push(`Status: ${s.status}`);
      if (ShipmentContext.mode === "child" && ShipmentContext.parentId) badgeParts.push(`Parent: ${ShipmentContext.parentId}`);
      document.getElementById("selectedShipmentBadge").textContent = badgeParts.join(" • ") || "—";
    }

    /* =========================================================
    SHIPMENTS LIST (Parents Only)
    ========================================================= */
    document.getElementById("endpoint-shipments").textContent = API.shipments.shipments;

    function renderShipmentsIndexTable(filteredList) {
      const tbody = document.getElementById("tbody-shipments");
      tbody.innerHTML = "";
      const list = filteredList || parentShipments;
      document.getElementById("count-shipments").textContent = String(list.length);

      if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="muted">No shipments found.</td></tr>`;
        return;
      }

      list.forEach((s) => {
        const tr = document.createElement("tr");
        tr.className = "click-row";
        const statusCls = pillClassFromStatus(s.status);
        const statusBadge = `<span class="badge badge-soft ${statusCls ? ("badge-" + statusCls) : ""}">${escapeHtml(s.status)}</span>`;

        tr.innerHTML = `
          <td>
            <div class="ship-name">${escapeHtml(s.name !== "—" ? s.name : s.title)}</div>
            <div class="ship-desc">${escapeHtml(s.description !== "—" ? s.description : (s.subtitle || "—"))}</div>
          </td>
          <td>${escapeHtml(s.container)}</td>
          <td>${escapeHtml(s.tracking)}</td>
          <td>${statusBadge}</td>
          <td>${escapeHtml(s.mode)}</td>
          <td>${escapeHtml(s.from)}</td>
          <td>${escapeHtml(s.to)}</td>
        `;

        tr.addEventListener("click", () => {
          setShipmentContext("parent", null);
          selectedShipment = s;
          applySelectedShipmentUI(s);
          showOperationsView();
          showPanel("loading");
        });

        tbody.appendChild(tr);
      });
    }

    // Load shipments + identify parents by looking at ALL relationships list (not /{id}/)
    async function loadShipmentsIndex() {
      const key = "shipments";
      if (aborters[key]) aborters[key].abort();
      aborters[key] = new AbortController();

      try {
        setSpinnerById("spin-shipments", true);
        setStatusById("status-shipments", "Loading…", true);

        const [shipRes, relRes] = await Promise.all([
          fetch(API.shipments.shipments, { headers: { 'Accept': 'application/json' }, signal: aborters[key].signal }),
          fetch(API.shipments.children, { headers: { 'Accept': 'application/json' }, signal: aborters[key].signal })
        ]);

        const shipText = await shipRes.text();
        const relText = await relRes.text();

        let shipPayload, relPayload;
        try { shipPayload = JSON.parse(shipText); } catch { shipPayload = { raw: shipText }; }
        try { relPayload = JSON.parse(relText); } catch { relPayload = { raw: relText }; }

        setRawPrettyById("raw-shipments", shipPayload);

        if (!shipRes.ok) {
          setStatusById("status-shipments", `HTTP ${shipRes.status}`, false);
          parentShipments = [];
          renderShipmentsIndexTable([]);
          return;
        }

        const shipItems = normalizeToArray(shipPayload);
        allShipments = shipItems.map(row => extractShipmentSummary(isObject(row) ? row : { value: row }));

        // Relationships list (ALL)
        const relItems = normalizeToArray(relPayload);

        // Children IDs = rel.child_shipment
        const childShipmentIds = new Set(
          relItems
            .map(r => (isObject(r) ? (r.child_shipment ?? r.child_id ?? null) : null))
            .filter(v => v !== null && v !== undefined)
            .map(v => String(v))
        );

        parentShipments = allShipments.filter(s => !childShipmentIds.has(String(s.id)));

        // Remove duplicates
        const map = new Map();
        parentShipments.forEach(s => {
          const keyId = [
            normalizeId(s.name),
            normalizeId(s.container),
            normalizeId(s.tracking),
            normalizeId(s.from),
            normalizeId(s.to)
          ].join("|");
          if (!map.has(keyId)) map.set(keyId, s);
        });
        parentShipments = Array.from(map.values());

        renderShipmentsIndexTable(parentShipments);
        setStatusById("status-shipments", "OK", true);
      } catch (e) {
        if (e.name === "AbortError") return;
        setStatusById("status-shipments", "Fetch failed", false);
        setRawPrettyById("raw-shipments", String(e));
        parentShipments = [];
        renderShipmentsIndexTable([]);
      } finally {
        setSpinnerById("spin-shipments", false);
      }
    }

    document.getElementById("btnReload-shipments").addEventListener("click", loadShipmentsIndex);
    document.getElementById("btnDebug-shipments").addEventListener("click", () => {
      const wrap = document.getElementById("debug-shipments");
      wrap.classList.toggle("show");
      document.getElementById("btnDebug-shipments").textContent = wrap.classList.contains("show") ? "Hide Debug JSON" : "Debug JSON";
    });

    document.getElementById("shipmentsSearch").addEventListener("input", (e) => {
      const q = normalizeId(e.target.value);
      if (!q) return renderShipmentsIndexTable(parentShipments);
      const filtered = parentShipments.filter(s => {
        const blob = normalizeId([s.name, s.description, s.container, s.tracking, s.status, s.mode, s.from, s.to, s.title, s.subtitle].join(" "));
        return blob.includes(q);
      });
      renderShipmentsIndexTable(filtered);
    });

    document.getElementById("btnChangeShipment").addEventListener("click", () => {
      showShipmentsListView();
    });

    /* =========================================================
    OPERATIONS UI
    ========================================================= */
    function sectionShell(panelKey, title, subtitle, hasSwitch = false) {
      return `
        <div class="glass p-3 p-lg-4 mb-3">
          <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
            <div>
              <h4 class="mb-1">${title}</h4>
              <div class="muted">${subtitle}</div>
              ${hasSwitch ? `<div class="ds-switch mt-3 d-flex flex-wrap gap-2" data-switch-for="${panelKey}"></div>` : ``}
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <button class="btn btn-glass" id="btnReload-${panelKey}">
                <span class="spinner-border spinner-border-sm d-none" id="spin-${panelKey}" role="status" aria-hidden="true"></span>
                Reload
              </button>
              <button class="btn btn-glass" id="btnDebug-${panelKey}" type="button">Debug JSON</button>
              <span class="badge badge-soft px-3 py-2" id="status-${panelKey}">Idle</span>
            </div>
          </div>
          <div class="mt-3 muted">Endpoint: <code id="endpoint-${panelKey}"></code></div>
        </div>

        <div class="glass p-3 mb-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><strong>Rows:</strong> <span id="count-${panelKey}">0</span></div>
            <div class="muted" id="hint-${panelKey}"></div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <div class="glass p-3">
              <div class="row g-3" id="cards-${panelKey}"></div>
              <div class="debug-wrap mt-3" id="debug-${panelKey}">
                <div class="fw-semibold mb-2">Raw JSON (Debug)</div>
                <pre class="json-box mb-0" id="raw-${panelKey}">{}</pre>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function mountSections() {
      document.getElementById("panel-loading").innerHTML = sectionShell("loading", "Loading", "Switch dataset: loadings, loading-items.", true);
      document.getElementById("panel-movement").innerHTML = sectionShell("movement", "Movement", "Switch dataset: movements, movement-items.", true);
      document.getElementById("panel-offloading").innerHTML = sectionShell("offloading", "Offloading", "Switch dataset: offloadings, offloading-items.", true);
      document.getElementById("panel-storage").innerHTML = sectionShell("storage", "Storage", "Switch dataset: storage, storage-items.", true);
      document.getElementById("panel-documents").innerHTML = sectionShell("documents", "Documents", "View shipment documents and attachments.", false);
      document.getElementById("panel-children").innerHTML = sectionShell("children", "Children Shipments", "Sub-shipments belonging to this parent.", false);

      document.querySelector('[data-switch-for="loading"]').innerHTML = `
        <button class="btn active" type="button" data-panel="loading" data-ds="loadings">Loadings</button>
        <button class="btn" type="button" data-panel="loading" data-ds="loadingsItems">Loading Items</button>
      `;
      document.querySelector('[data-switch-for="movement"]').innerHTML = `
        <button class="btn active" type="button" data-panel="movement" data-ds="movements">Movements</button>
        <button class="btn" type="button" data-panel="movement" data-ds="movementItems">Movement Items</button>
      `;
      document.querySelector('[data-switch-for="offloading"]').innerHTML = `
        <button class="btn active" type="button" data-panel="offloading" data-ds="offloadings">Offloadings</button>
        <button class="btn" type="button" data-panel="offloading" data-ds="offloadingItems">Offloading Items</button>
      `;
      document.querySelector('[data-switch-for="storage"]').innerHTML = `
        <button class="btn active" type="button" data-panel="storage" data-ds="storage">Storage</button>
        <button class="btn" type="button" data-panel="storage" data-ds="storageItems">Storage Items</button>
      `;
    }
    mountSections();

    /* =========================================================
    MODAL + DETAILS (unchanged)
    ========================================================= */
    let shipmentModalInstance = null;
    let lastSelectedShipment = null;

    function setText(id, value) {
      const el = document.getElementById(id);
      if (!el) return;
      el.textContent = (value === null || value === undefined || String(value).trim() === "") ? "—" : String(value);
    }

    function setStatusBadge(text) {
      const el = document.getElementById("sumStatusBadge");
      el.textContent = `Status: ${text || "—"}`;
      el.classList.remove("badge-ok", "badge-bad");
      const t = String(text || "").toLowerCase();
      if (t.includes("complete") || t.includes("completed") || t.includes("done") || t.includes("delivered") || t.includes("arrived")) el.classList.add("badge-ok");
      else if (t.includes("fail") || t.includes("error") || t.includes("cancel") || t.includes("rejected")) el.classList.add("badge-bad");
    }

    function isPrimitive(v) {
      return v === null || v === undefined || typeof v === 'string' || typeof v === 'number' || typeof v === 'boolean';
    }

    function inferShipmentTitle(rowObj, panelKey) {
      const s = extractShipmentSummary(rowObj);
      return `${titleCase(panelKey)} — ${(s.name !== "—" ? s.name : s.title)}`;
    }

    function toDisplayLines(val) {
      if (val === null || val === undefined) return [];
      if (Array.isArray(val)) {
        const flat = val.map(v => (v === null || v === undefined) ? "" : String(v)).filter(x => x.trim() !== "");
        return flat.length ? flat : [];
      }
      if (typeof val === "string") {
        const s = val.trim();
        if (!s) return [];
        if (s.includes("|")) return s.split("|").map(x => x.trim()).filter(Boolean);
        if (s.includes(",")) {
          const parts = s.split(",").map(x => x.trim()).filter(Boolean);
          return parts.length > 1 ? parts : [s];
        }
        return [s];
      }
      return [String(val)];
    }

    function renderValueLinesHtml(val) {
      const lines = toDisplayLines(val);
      if (!lines.length) return `<span class="muted">—</span>`;
      return lines.map(l => `<span class="line">${escapeHtml(l)}</span>`).join("");
    }

    function renderKvGrid(containerEl, entries) {
      containerEl.innerHTML = "";
      entries.forEach(({ k, v }) => {
        const lines = toDisplayLines(v);
        if (!lines.length) return;
        const div = document.createElement("div");
        div.className = "kv-item";
        div.innerHTML = `<div class="k">${escapeHtml(titleCase(k))}</div><div class="v">${renderValueLinesHtml(v)}</div>`;
        containerEl.appendChild(div);
      });
      if (!containerEl.children.length) {
        containerEl.innerHTML = `<div class="muted">No available details found.</div>`;
      }
    }

    function renderShipmentModal(panelKey, datasetKey, rowObj) {
      lastSelectedShipment = rowObj;
      if (!shipmentModalInstance) {
        shipmentModalInstance = new bootstrap.Modal(document.getElementById('shipmentModal'), { backdrop: true, keyboard: true });
      }
      document.getElementById('shipmentModalTitle').textContent = inferShipmentTitle(rowObj, panelKey);
      document.getElementById('shipmentModalSub').textContent = ` • ${panelKey} • Dataset: ${datasetKey || 'default'}`;

      const sum = extractShipmentSummary(rowObj);
      setText("sumTitle", sum.name !== "—" ? sum.name : sum.title);
      setText("sumSubtitle", sum.description !== "—" ? sum.description : sum.subtitle);
      setStatusBadge(sum.status);
      setText("sumModeBadge", `Mode: ${sum.mode}`);
      setText("sumContainer", sum.container);
      setText("sumTracking", sum.tracking);
      setText("sumFrom", sum.from);
      setText("sumTo", sum.to);

      const hasDates = !!(sum.etd || sum.eta || sum.created || sum.updated);
      document.getElementById("sumDatesRow").style.display = hasDates ? "" : "none";
      setText("sumEtd", sum.etd || "—");
      setText("sumEta", sum.eta || "—");
      setText("sumCreated", sum.created || "—");
      setText("sumUpdated", sum.updated || "—");

      const detailsKv = document.getElementById("shipmentDetailsKv");
      const nestedWrapOuter = document.getElementById("nestedWrapOuter");
      const nestedWrap = document.getElementById('shipmentNestedBlocks');
      detailsKv.innerHTML = "";
      nestedWrap.innerHTML = "";
      nestedWrapOuter.style.display = "none";

      if (!rowObj || typeof rowObj !== 'object') {
        detailsKv.innerHTML = `<div class="muted">No details available</div>`;
        document.getElementById('shipmentDetailsRaw').textContent = String(rowObj ?? '');
        shipmentModalInstance.show();
        return;
      }

      const keys = Object.keys(rowObj);
      const primitiveKeys = [];
      const nestedKeys = [];
      keys.forEach(k => (isPrimitive(rowObj[k]) ? primitiveKeys : nestedKeys).push(k));
      primitiveKeys.sort((a, b) => a.localeCompare(b));
      const kvEntries = primitiveKeys.map(k => ({ k, v: rowObj[k] }));
      renderKvGrid(detailsKv, kvEntries);
      document.getElementById('shipmentDetailsRaw').innerHTML = syntaxHighlightJson(JSON.stringify(rowObj, null, 2));

      if (nestedKeys.length) {
        nestedWrapOuter.style.display = "";
        nestedKeys.forEach(k => {
          const v = rowObj[k];
          const card = document.createElement('div');
          card.className = "glass p-3 mb-2";
          const header = document.createElement('div');
          header.className = "d-flex justify-content-between align-items-center";
          header.innerHTML = `
            <div class="fw-semibold">${escapeHtml(titleCase(k))}</div>
            <span class="badge badge-soft">${Array.isArray(v) ? `Array (${v.length})` : 'Object'}</span>
          `;
          const pre = document.createElement('pre');
          pre.className = "json-box mt-2 mb-0";
          pre.innerHTML = syntaxHighlightJson(JSON.stringify(v, null, 2));
          card.appendChild(header);
          card.appendChild(pre);
          nestedWrap.appendChild(card);
        });
      }

      shipmentModalInstance.show();
    }

    document.getElementById('btnCopyShipmentJson').addEventListener('click', async (e) => {
      const btn = e.currentTarget;
      try {
        const text = JSON.stringify(lastSelectedShipment ?? {}, null, 2);
        await navigator.clipboard.writeText(text);
        btn.textContent = "Copied!";
        setTimeout(() => (btn.textContent = "Copy JSON"), 900);
      } catch {
        btn.textContent = "Copy failed";
        setTimeout(() => (btn.textContent = "Copy JSON"), 900);
      }
    });

    /* =========================================================
    OPEN CHILD OPERATIONS
    ========================================================= */
    async function openShipmentOperationsFromId(shipmentId, parentId) {
      try {
        const res = await fetch(`${API.shipments.shipments}${shipmentId}/`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const full = await res.json();
        const summary = extractShipmentSummary(isObject(full) ? full : { id: shipmentId });

        setShipmentContext("child", parentId || null);
        selectedShipment = summary;
        applySelectedShipmentUI(summary);

        showOperationsView();
        showPanel("loading");
      } catch (e) {
        alert(`Failed to open child shipment operations: ${e.message}`);
      }
    }

    /* =========================================================
    CHILDREN: fetch ALL relations + filter by parent_shipment + join shipments
    ========================================================= */
    function findShipmentSummaryById(id) {
      const sid = String(id ?? "");
      const found = allShipments.find(s => String(s.id) === sid);
      return found || null;
    }

    async function loadChildrenPanel() {
      const panelKey = "children";

      if (!selectedShipment?.id) {
        setStatusById(`status-${panelKey}`, "No parent selected", false);
        document.getElementById(`endpoint-${panelKey}`).textContent = API.shipments.children;
        renderCards(panelKey, []);
        return;
      }

      const parentId = String(selectedShipment.id);
      document.getElementById(`endpoint-${panelKey}`).textContent = `${API.shipments.children} (filter parent_shipment=${parentId})`;

      if (aborters[panelKey]) aborters[panelKey].abort();
      aborters[panelKey] = new AbortController();

      try {
        setSpinnerById(`spin-${panelKey}`, true);
        setStatusById(`status-${panelKey}`, "Loading…", true);

        // Ensure we have shipments list cached (used for join)
        if (!allShipments || !allShipments.length) {
          const sr = await fetch(API.shipments.shipments, { headers: { 'Accept': 'application/json' }, signal: aborters[panelKey].signal });
          const st = await sr.text();
          let sp; try { sp = JSON.parse(st); } catch { sp = { raw: st }; }
          if (sr.ok) {
            const shipItems = normalizeToArray(sp);
            allShipments = shipItems.map(row => extractShipmentSummary(isObject(row) ? row : { value: row }));
          }
        }

        // Fetch ALL relationships
        const rr = await fetch(API.shipments.children, { headers: { 'Accept': 'application/json' }, signal: aborters[panelKey].signal });
        const rt = await rr.text();
        let relPayload; try { relPayload = JSON.parse(rt); } catch { relPayload = { raw: rt }; }

        setRawPrettyById(`raw-${panelKey}`, relPayload);

        if (!rr.ok) {
          setStatusById(`status-${panelKey}`, `HTTP ${rr.status}`, false);
          renderCards(panelKey, []);
          return;
        }

        const relItems = normalizeToArray(relPayload);

        // Filter: parent_shipment == selectedShipment.id
        const filtered = relItems.filter(r => {
          const p = isObject(r) ? (r.parent_shipment ?? r.parent_id ?? null) : null;
          return String(p) === parentId;
        });

        // Enrich each relationship row with child shipment summary (if found)
        const enriched = filtered.map(r => {
          const childId = isObject(r) ? (r.child_shipment ?? r.child_id ?? r.id ?? null) : null;
          const childSum = childId != null ? findShipmentSummaryById(childId) : null;
          return {
            __rel: r,
            __childId: childId,
            __parentId: parentId,
            __childSummary: childSum
          };
        });

        renderCards(panelKey, enriched);
        setStatusById(`status-${panelKey}`, "OK", true);
      } catch (e) {
        if (e.name === "AbortError") return;
        setStatusById(`status-${panelKey}`, "Fetch failed", false);
        document.getElementById(`hint-${panelKey}`).textContent = String(e);
        setRawPrettyById(`raw-${panelKey}`, String(e));
        renderCards(panelKey, []);
      } finally {
        setSpinnerById(`spin-${panelKey}`, false);
      }
    }

    /* =========================================================
    CARDS RENDER
    ========================================================= */
    function renderCards(panelKey, items) {
      const grid = document.getElementById(`cards-${panelKey}`);
      const count = document.getElementById(`count-${panelKey}`);
      const hint = document.getElementById(`hint-${panelKey}`);
      grid.innerHTML = "";

      if (panelKey === "children" && ShipmentContext.mode === "child") {
        count.textContent = "0";
        hint.textContent = "Children tab is disabled while viewing a child shipment.";
        grid.innerHTML = `
          <div class="col-12">
            <div class="glass p-4 text-center">
              <div class="fw-semibold">Children not available</div>
              <div class="muted mt-1">You are currently viewing a child shipment.</div>
            </div>
          </div>`;
        return;
      }

      if (panelKey === "children") {
        const list = items || [];
        if (!list.length) {
          count.textContent = "0";
          hint.textContent = "No child shipments for this parent.";
          grid.innerHTML = `
            <div class="col-12">
              <div class="glass p-4 text-center">
                <div class="fw-semibold">No child shipments</div>
                <div class="muted mt-1">This shipment has no sub-shipments.</div>
              </div>
            </div>`;
          return;
        }

        count.textContent = String(list.length);
        hint.textContent = "Click a child shipment to open its operations.";

        list.forEach((bundle, idx) => {
          const rel = bundle.__rel || {};
          const childId = bundle.__childId;
          const parentId = bundle.__parentId;
          const childSum = bundle.__childSummary;

          const name = childSum?.name && childSum.name !== "—"
            ? childSum.name
            : (rel.name || rel.shipment_name || `Child #${childId ?? (idx + 1)}`);

          const description = childSum?.description && childSum.description !== "—"
            ? childSum.description
            : (rel.description || rel.code || "—");

          const card = document.createElement("div");
          card.className = "col-12 col-md-6 col-xl-4";
          card.innerHTML = `
            <div class="ship-card p-3 h-100">
              <div class="d-flex justify-content-between gap-2">
                <div class="fw-semibold">${escapeHtml(name)}</div>
                <span class="ship-pill">Child #${escapeHtml(childId ?? "—")}</span>
              </div>
              <div class="muted mt-1" style="font-size:13px;">
                ${escapeHtml(description)}
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="ship-pill">Parent: ${escapeHtml(parentId ?? "—")}</span>
                <span class="ship-pill">Child: ${escapeHtml(childId ?? "—")}</span>
              </div>
              <div class="muted mt-3" style="font-size:12px;">Click to open Operations for this child</div>
            </div>`;

          card.querySelector(".ship-card").addEventListener("click", () => {
            if (!childId) return;
            openShipmentOperationsFromId(childId, parentId);
          });

          grid.appendChild(card);
        });

        return;
      }

      const filtered = (items || []).filter(row => rowMatchesSelected(isObject(row) ? row : { value: row }));
      if (!filtered.length) {
        count.textContent = "0";
        hint.textContent = "No matching rows for this shipment in this dataset.";
        grid.innerHTML = `
          <div class="col-12">
            <div class="glass p-4 text-center">
              <div class="fw-semibold">No related data here</div>
              <div class="muted mt-1">Try another tab or dataset switch.</div>
            </div>
          </div>`;
        return;
      }

      const datasetKey = (panelKey === "loading") ? (activeDataset.loading || "loadings") : (activeDataset[panelKey] || "default");

      filtered.forEach((row, idx) => {
        const rowObj = isObject(row) ? row : { value: row, index: idx };
        const s = extractShipmentSummary(rowObj);
        const statusCls = pillClassFromStatus(s.status);

        const previewPairs = [];
        const rawKeys = Object.keys(rowObj || {});
        rawKeys
          .filter(k => {
            const v = rowObj[k];
            if (v === null || v === undefined) return false;
            if (typeof v === "string" && !v.trim()) return false;
            if (typeof v === "object") return false;
            return true;
          })
          .slice(0, 8)
          .forEach(k => previewPairs.push({ k, v: rowObj[k] }));

        const dateBits = [];
        if (s.etd) dateBits.push(`ETD ${s.etd}`);
        if (s.eta) dateBits.push(`ETA ${s.eta}`);
        if (s.created) dateBits.push(`Created ${s.created}`);
        if (s.updated) dateBits.push(`Updated ${s.updated}`);

        const card = document.createElement("div");
        card.className = "col-12 col-md-6 col-xl-4";
        card.innerHTML = `
          <div class="ship-card p-3 h-100">
            <div class="d-flex justify-content-between gap-2">
              <div class="fw-semibold">${escapeHtml(s.name !== "—" ? s.name : s.title)}</div>
              <span class="ship-pill ${statusCls}">${escapeHtml(s.status)}</span>
            </div>
            <div class="muted mt-1" style="font-size:13px;">
              ${escapeHtml(s.description !== "—" ? s.description : s.subtitle)}
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              ${s.mode && s.mode !== "—" ? `<span class="ship-pill">Mode: ${escapeHtml(s.mode)}</span>` : ``}
              ${s.container && s.container !== "—" ? `<span class="ship-pill">Container: ${escapeHtml(s.container)}</span>` : ``}
              ${s.tracking && s.tracking !== "—" ? `<span class="ship-pill">Ref: ${escapeHtml(s.tracking)}</span>` : ``}
            </div>

            ${(s.from !== "—" || s.to !== "—") ? `
              <div class="ship-meta mt-3">
                ${s.from !== "—" ? `<div class="k">From</div><div>${escapeHtml(s.from)}</div>` : ``}
                ${s.to !== "—" ? `<div class="k mt-2">To</div><div>${escapeHtml(s.to)}</div>` : ``}
              </div>
            ` : ``}

            ${dateBits.length ? `<div class="muted mt-3" style="font-size:12px;">${escapeHtml(dateBits.join(" • "))}</div>` : ``}

            ${previewPairs.length ? `
              <div class="mt-3" style="border-top: 1px solid rgba(255,255,255,.10); padding-top: 10px;">
                <div class="muted" style="font-size:12px; margin-bottom:6px;">Key fields</div>
                <div class="kv-grid" style="grid-template-columns: 1fr 1fr; gap:8px;">
                  ${previewPairs.map(p => `
                    <div class="kv-item" style="padding:8px 10px; min-height:auto;">
                      <div class="k">${escapeHtml(titleCase(p.k))}</div>
                      <div class="v">${renderValueLinesHtml(p.v)}</div>
                    </div>
                  `).join("")}
                </div>
              </div>
            ` : ``}

            <div class="muted mt-3" style="font-size:12px;">Click to view full shipment details</div>
          </div>
        `;

        card.querySelector(".ship-card").addEventListener("click", () => {
          renderShipmentModal(panelKey, datasetKey, rowObj);
        });

        grid.appendChild(card);
      });

      count.textContent = String(filtered.length);
      hint.textContent = "Click any shipment card to view it in full-screen.";
    }

    /* =========================================================
    ENDPOINT RESOLVER
    ========================================================= */
    function resolveEndpoint(panelKey) {
      if (panelKey === "children") {
        if (ShipmentContext.mode === "child") return "";
        return API.shipments.children; // base list; we filter locally
      }

      if (panelKey === "documents") {
        if (selectedShipment) {
          const docId = selectedShipment.id ||
            selectedShipment.tracking !== "—" ? selectedShipment.tracking :
              selectedShipment.container !== "—" ? selectedShipment.container :
                null;
          if (docId) return `${API.shipments.documents}${docId}/documents/`;
        }
        return "";
      }

      if (panelKey === "loading") return (activeDataset.loading === "loadingsItems") ? API.loading.loadingsItems : API.loading.loadings;
      if (panelKey === "movement") return (activeDataset.movement === "movementItems") ? API.movement.movementItems : API.movement.movements;
      if (panelKey === "offloading") return (activeDataset.offloading === "offloadingItems") ? API.offloading.offloadingItems : API.offloading.offloadings;
      if (panelKey === "storage") return (activeDataset.storage === "storageItems") ? API.storage.storageItems : API.storage.storage;

      return "";
    }

    /* =========================================================
    LOADERS
    ========================================================= */
    async function loadPanel(panelKey) {
      if (panelKey === "children") {
        // SPECIAL: children uses combined logic (relations + shipments join)
        await loadChildrenPanel();
        return;
      }

      const endpoint = resolveEndpoint(panelKey);
      document.getElementById(`endpoint-${panelKey}`).textContent = endpoint || "—";

      if (panelKey === "children" && ShipmentContext.mode === "child") {
        setRawPrettyById(`raw-${panelKey}`, { detail: "Disabled in child context" });
        setStatusById(`status-${panelKey}`, "Disabled", true);
        renderCards(panelKey, []);
        return;
      }

      if (!endpoint) {
        setStatusById(`status-${panelKey}`, "No endpoint", false);
        renderCards(panelKey, []);
        return;
      }

      if (aborters[panelKey]) aborters[panelKey].abort();
      aborters[panelKey] = new AbortController();

      try {
        setSpinnerById(`spin-${panelKey}`, true);
        setStatusById(`status-${panelKey}`, "Loading…", true);

        const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' }, signal: aborters[panelKey].signal });
        const text = await res.text();
        let payload;
        try { payload = JSON.parse(text); } catch { payload = { raw: text }; }

        setRawPrettyById(`raw-${panelKey}`, payload);

        if (!res.ok) {
          setStatusById(`status-${panelKey}`, `HTTP ${res.status}`, false);
          renderCards(panelKey, []);
          return;
        }

        const items = normalizeToArray(payload);

        if (panelKey === "documents") {
          renderDocuments(panelKey, items);
        } else {
          renderCards(panelKey, items);
        }

        setStatusById(`status-${panelKey}`, "OK", true);
      } catch (e) {
        if (e.name === "AbortError") return;
        setStatusById(`status-${panelKey}`, "Fetch failed", false);
        document.getElementById(`hint-${panelKey}`).textContent = String(e);
        setRawPrettyById(`raw-${panelKey}`, String(e));
        renderCards(panelKey, []);
      } finally {
        setSpinnerById(`spin-${panelKey}`, false);
      }
    }

    /* =========================================================
    TAB SWITCHING
    ========================================================= */
    function showPanel(panelKey) {
      document.querySelectorAll('#operationsView > section.section-panel').forEach(p => p.classList.remove('active'));
      document.getElementById(`panel-${panelKey}`).classList.add('active');
      document.querySelectorAll('#sideTabs .nav-link').forEach(btn => btn.classList.remove('active'));
      document.querySelector(`#sideTabs .nav-link[data-key="${panelKey}"]`).classList.add('active');
      loadPanel(panelKey);
    }

    document.querySelectorAll('#sideTabs .nav-link').forEach(btn => {
      btn.addEventListener('click', () => {
        if (btn.dataset.key === "children" && ShipmentContext.mode === "child") return;
        showPanel(btn.dataset.key);
      });
    });

    /* =========================================================
    DATASET SWITCHING
    ========================================================= */
    function bindDatasetSwitch(panelKey) {
      const panel = document.getElementById(`panel-${panelKey}`);
      if (!panel) return;
      panel.querySelectorAll('.ds-switch .btn').forEach(btn => {
        btn.addEventListener('click', () => {
          panel.querySelectorAll('.ds-switch .btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          activeDataset[panelKey] = btn.dataset.ds;
          loadPanel(panelKey);
        });
      });
    }
    bindDatasetSwitch("loading");
    bindDatasetSwitch("movement");
    bindDatasetSwitch("offloading");
    bindDatasetSwitch("storage");

    /* =========================================================
    BUTTON BINDINGS
    ========================================================= */
    ["loading", "movement", "offloading", "storage", "documents", "children"].forEach(k => {
      const rb = document.getElementById(`btnReload-${k}`);
      const db = document.getElementById(`btnDebug-${k}`);
      if (rb) rb.addEventListener('click', () => loadPanel(k));
      if (db) db.addEventListener('click', () => {
        const wrap = document.getElementById(`debug-${k}`);
        wrap.classList.toggle("show");
        db.textContent = wrap.classList.contains("show") ? "Hide Debug JSON" : "Debug JSON";
      });
    });

    /* =========================================================
    INIT
    ========================================================= */
    showShipmentsListView();
    loadShipmentsIndex();

    /* =========================================================
    DOCUMENTS RENDERER
    ========================================================= */
    function formatFileSize(bytes) {
      if (!bytes || bytes === 0) return "0 B";
      const k = 1024;
      const sizes = ["B", "KB", "MB", "GB"];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
    }

    function renderDocuments(panelKey, items) {
      const grid = document.getElementById(`cards-${panelKey}`);
      const count = document.getElementById(`count-${panelKey}`);
      const hint = document.getElementById(`hint-${panelKey}`);
      grid.innerHTML = "";

      if (!items || !items.length) {
        count.textContent = "0";
        hint.textContent = "No documents found for this shipment.";
        grid.innerHTML = `
          <div class="col-12">
            <div class="glass p-4 text-center">
              <div class="fw-semibold">No documents</div>
              <div class="muted mt-1">Upload documents via the Siya API or admin panel.</div>
            </div>
          </div>`;
        return;
      }

      count.textContent = String(items.length);
      hint.textContent = "Click to view or download document.";

      items.forEach((doc, idx) => {
        const filename = doc.name || doc.filename || doc.file || `Document ${idx + 1}`;
        const filesize = doc.size_bytes || doc.size || doc.file_size || 0;
        const url = doc.url || doc.download_url || "";
        const ext = (filename.split('.').pop() || "").toUpperCase();
        const isPdf = ext === "PDF";
        const isImage = ["JPG", "JPEG", "PNG", "GIF", "WEBP"].includes(ext);

        const card = document.createElement("div");
        card.className = "col-12 col-md-6 col-xl-4";
        card.innerHTML = `
          <div class="ship-card p-3 h-100">
            <div class="d-flex justify-content-between gap-2">
              <div class="fw-semibold text-truncate" title="${escapeHtml(filename)}">${escapeHtml(filename)}</div>
              <span class="ship-pill">${escapeHtml(ext)}</span>
            </div>
            <div class="muted mt-1" style="font-size:13px;">${formatFileSize(filesize)}</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
              <a href="${escapeHtml(url)}" target="_blank" class="ship-pill" style="text-decoration:none;">📥 Download</a>
              ${isPdf || isImage ? `<a href="${escapeHtml(url)}" target="_blank" class="ship-pill" style="text-decoration:none;">👁️ View</a>` : ''}
            </div>
            <div class="muted mt-3" style="font-size:12px;">Shipment #${selectedShipment?.id || '—'}</div>
          </div>`;
        grid.appendChild(card);
      });
    }
  </script>
</body>

</html>
