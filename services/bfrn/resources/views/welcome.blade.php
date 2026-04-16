<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BFRN — Operations</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --bg:#0b0b0f;
      --panel: rgba(255,255,255,.06);
      --panel2: rgba(255,255,255,.08);
      --border: rgba(255,255,255,.12);
      --text:#eaeaf2;
      --muted: rgba(234,234,242,.75);
      --ok:#43f08a;
      --bad:#ff6b6b;
      --hover: rgba(255,255,255,.16);
      --btn: rgba(255,255,255,.10);
    }

    body { background:var(--bg); color:var(--text); }

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
      background: rgba(10,10,14,.65);
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
    .nav-pills .nav-link:hover { background: rgba(255,255,255,.08); }
    .nav-pills .nav-link.active {
      background: rgba(67,240,138,.12);
      border-color: rgba(67,240,138,.35);
      box-shadow: 0 0 0 2px rgba(67,240,138,.15) inset;
    }

    .nav-sub { font-size: 12px; color: var(--muted); margin-top: 2px; word-break: break-all; }

    .badge-soft {
      border: 1px solid var(--border);
      background: rgba(255,255,255,.08);
      font-weight: 600;
    }
    .badge-ok { color: var(--ok); }
    .badge-bad { color: var(--bad); }

    .btn-glass {
      border: 1px solid rgba(255,255,255,.18);
      background: var(--btn);
      color: #fff;
      border-radius: 10px;
    }
    .btn-glass:hover { background: var(--hover); color:#fff; }

    .table-dark-glass {
      --bs-table-bg: transparent;
      --bs-table-color: var(--text);
      --bs-table-border-color: rgba(255,255,255,.12);
    }
    .table thead th {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: rgba(234,234,242,.85);
    }

    tbody tr { transition: .12s ease; }
    tbody tr:hover { background: rgba(255,255,255,.06); }

    pre.json-box{
      background: rgba(0,0,0,.35);
      border: 1px solid rgba(255,255,255,.10);
      border-radius: 12px;
      padding: 12px;
      max-height: 440px;
      overflow: auto;
      color: #eaeaf2;
      line-height: 1.35;
      font-size: 13px;
      white-space: pre;
    }
    pre.json-box::-webkit-scrollbar { height: 10px; width: 10px; }
    pre.json-box::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); border-radius: 999px; }
    pre.json-box::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.22); }

    .json-key   { color: rgba(234,234,242,.92); font-weight: 650; }
    .json-str   { color: #9ad1ff; }
    .json-num   { color: #ffd48a; }
    .json-bool  { color: #b9a6ff; font-weight: 650; }
    .json-null  { color: rgba(255,255,255,.55); font-style: italic; }

    code { color: rgba(234,234,242,.9); }
    .muted { color: var(--muted); }

    .section-panel { display: none; }
    .section-panel.active { display: block; }

    .ds-switch .btn {
      border: 1px solid rgba(255,255,255,.16);
      background: rgba(255,255,255,.06);
      color: var(--text);
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 13px;
    }
    .ds-switch .btn:hover { background: rgba(255,255,255,.10); }
    .ds-switch .btn.active {
      background: rgba(67,240,138,.12);
      border-color: rgba(67,240,138,.35);
      box-shadow: 0 0 0 2px rgba(67,240,138,.12) inset;
    }

    /* Modal polish */
    .modal-content.glass {
      background: rgba(10,10,14,.85);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.12);
    }
    .modal-header, .modal-footer {
      background: rgba(10,10,14,.75);
      backdrop-filter: blur(10px);
    }
    .modal-body { background: rgba(10,10,14,.55); }
    .modal-sticky { position: sticky; top: 0; z-index: 2; }
    .modal-sticky-footer { position: sticky; bottom: 0; z-index: 2; }
  </style>
</head>

<body>
<div class="container-fluid">
  <div class="row g-0">

    <!-- LEFT SIDEBAR -->
    <aside class="col-12 col-md-3 col-xl-2 p-3 sidebar">
      <div class="nav nav-pills flex-column" id="sideTabs" role="tablist">
        <button class="nav-link" data-key="loading" type="button">
          <div class="fw-semibold">Loading</div>
          <div class="nav-sub" id="ep-loading"></div>
        </button>

        <button class="nav-link active" data-key="movement" type="button">
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
      </div>
    </aside>

    <!-- RIGHT CONTENT -->
    <main class="col-12 col-md-9 col-xl-10 p-3 p-lg-4">
      <div class="container" style="max-width: 1320px;">

        <!-- LOADING -->
        <section id="panel-loading" class="section-panel">
          <div class="glass p-3 p-lg-4 mb-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
              <div>
                <h4 class="mb-1">Loading</h4>
                <div class="muted">Loads from Siya “loadings”.</div>
              </div>

              <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-glass" id="btnReload-loading">
                  <span class="spinner-border spinner-border-sm d-none" id="spin-loading" role="status" aria-hidden="true"></span>
                  Reload
                </button>
                <span class="badge badge-soft px-3 py-2" id="status-loading">Idle</span>
              </div>
            </div>
            <div class="mt-3 muted">Endpoint: <code id="endpoint-loading"></code></div>
          </div>

          <div class="glass p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div><strong>Rows:</strong> <span id="count-loading">0</span></div>
              <div class="muted" id="hint-loading"></div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12 col-lg-5">
              <div class="glass p-3">
                <div class="table-responsive">
                  <table class="table table-sm table-dark-glass align-middle mb-0">
                    <thead><tr id="thead-loading"></tr></thead>
                    <tbody id="tbody-loading"></tbody>
                  </table>
                </div>
                <div class="muted mt-2" style="font-size:13px;">Click a row to view shipment details.</div>
              </div>
            </div>
            <div class="col-12 col-lg-7">
              <div class="glass p-3">
                <div class="fw-semibold mb-2">Raw JSON</div>
                <pre class="json-box mb-0" id="raw-loading">{}</pre>
              </div>
            </div>
          </div>
        </section>

        <!-- MOVEMENT -->
        <section id="panel-movement" class="section-panel active">
          <div class="glass p-3 p-lg-4 mb-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
              <div>
                <h4 class="mb-1">Movement</h4>
                <div class="muted">Switch dataset: movements, movement-items.</div>

                <div class="ds-switch mt-3 d-flex flex-wrap gap-2">
                  <button class="btn active" type="button" data-panel="movement" data-ds="movements">Movements</button>
                  <button class="btn" type="button" data-panel="movement" data-ds="movementItems">Movement Items</button>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-glass" id="btnReload-movement">
                  <span class="spinner-border spinner-border-sm d-none" id="spin-movement" role="status" aria-hidden="true"></span>
                  Reload
                </button>
                <span class="badge badge-soft px-3 py-2" id="status-movement">Idle</span>
              </div>
            </div>
            <div class="mt-3 muted">Endpoint: <code id="endpoint-movement"></code></div>
          </div>

          <div class="glass p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div><strong>Rows:</strong> <span id="count-movement">0</span></div>
              <div class="muted" id="hint-movement"></div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12 col-lg-5">
              <div class="glass p-3">
                <div class="table-responsive">
                  <table class="table table-sm table-dark-glass align-middle mb-0">
                    <thead><tr id="thead-movement"></tr></thead>
                    <tbody id="tbody-movement"></tbody>
                  </table>
                </div>
                <div class="muted mt-2" style="font-size:13px;">Click a row to view shipment details.</div>
              </div>
            </div>
            <div class="col-12 col-lg-7">
              <div class="glass p-3">
                <div class="fw-semibold mb-2">Raw JSON</div>
                <pre class="json-box mb-0" id="raw-movement">{}</pre>
              </div>
            </div>
          </div>
        </section>

        <!-- OFFLOADING -->
        <section id="panel-offloading" class="section-panel">
          <div class="glass p-3 p-lg-4 mb-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
              <div>
                <h4 class="mb-1">Offloading</h4>
                <div class="muted">Switch dataset: offloadings, offloading-items.</div>

                <div class="ds-switch mt-3 d-flex flex-wrap gap-2">
                  <button class="btn active" type="button" data-panel="offloading" data-ds="offloadings">Offloadings</button>
                  <button class="btn" type="button" data-panel="offloading" data-ds="offloadingItems">Offloading Items</button>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-glass" id="btnReload-offloading">
                  <span class="spinner-border spinner-border-sm d-none" id="spin-offloading" role="status" aria-hidden="true"></span>
                  Reload
                </button>
                <span class="badge badge-soft px-3 py-2" id="status-offloading">Idle</span>
              </div>
            </div>
            <div class="mt-3 muted">Endpoint: <code id="endpoint-offloading"></code></div>
          </div>

          <div class="glass p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div><strong>Rows:</strong> <span id="count-offloading">0</span></div>
              <div class="muted" id="hint-offloading"></div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12 col-lg-5">
              <div class="glass p-3">
                <div class="table-responsive">
                  <table class="table table-sm table-dark-glass align-middle mb-0">
                    <thead><tr id="thead-offloading"></tr></thead>
                    <tbody id="tbody-offloading"></tbody>
                  </table>
                </div>
                <div class="muted mt-2" style="font-size:13px;">Click a row to view shipment details.</div>
              </div>
            </div>
            <div class="col-12 col-lg-7">
              <div class="glass p-3">
                <div class="fw-semibold mb-2">Raw JSON</div>
                <pre class="json-box mb-0" id="raw-offloading">{}</pre>
              </div>
            </div>
          </div>
        </section>

        <!-- STORAGE -->
        <section id="panel-storage" class="section-panel">
          <div class="glass p-3 p-lg-4 mb-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
              <div>
                <h4 class="mb-1">Storage</h4>
                <div class="muted">Switch dataset: storage, storage-items.</div>

                <div class="ds-switch mt-3 d-flex flex-wrap gap-2">
                  <button class="btn active" type="button" data-panel="storage" data-ds="storage">Storage</button>
                  <button class="btn" type="button" data-panel="storage" data-ds="storageItems">Storage Items</button>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-glass" id="btnReload-storage">
                  <span class="spinner-border spinner-border-sm d-none" id="spin-storage" role="status" aria-hidden="true"></span>
                  Reload
                </button>
                <span class="badge badge-soft px-3 py-2" id="status-storage">Idle</span>
              </div>
            </div>
            <div class="mt-3 muted">Endpoint: <code id="endpoint-storage"></code></div>
          </div>

          <div class="glass p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div><strong>Rows:</strong> <span id="count-storage">0</span></div>
              <div class="muted" id="hint-storage"></div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12 col-lg-5">
              <div class="glass p-3">
                <div class="table-responsive">
                  <table class="table table-sm table-dark-glass align-middle mb-0">
                    <thead><tr id="thead-storage"></tr></thead>
                    <tbody id="tbody-storage"></tbody>
                  </table>
                </div>
                <div class="muted mt-2" style="font-size:13px;">Click a row to view shipment details.</div>
              </div>
            </div>
            <div class="col-12 col-lg-7">
              <div class="glass p-3">
                <div class="fw-semibold mb-2">Raw JSON</div>
                <pre class="json-box mb-0" id="raw-storage">{}</pre>
              </div>
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>
</div>

<!-- FULLSCREEN SHIPMENT DETAILS MODAL (SUMMARY-FIRST) -->
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
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body">
        <div class="container py-3" style="max-width: 1150px;">

          <!-- SUMMARY CARD -->
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
          </div>

          <div class="row g-3">
            <!-- DETAILS -->
            <div class="col-12 col-lg-6">
              <div class="glass p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="fw-semibold">More Details</div>
                  <span class="muted" style="font-size:12px;">Only this selected row</span>
                </div>

                <div class="table-responsive">
                  <table class="table table-sm table-dark-glass align-middle mb-0">
                    <tbody id="shipmentDetailsTbody"></tbody>
                  </table>
                </div>
              </div>

              <div class="glass p-3 mt-3">
                <div class="fw-semibold mb-2">Nested / Extra Info</div>
                <div class="muted" style="font-size:13px;" id="shipmentExtraHint">
                  Nested objects/arrays (if any) will appear here.
                </div>
                <div id="shipmentNestedBlocks" class="mt-2"></div>
              </div>
            </div>

            <!-- JSON -->
            <div class="col-12 col-lg-6">
              <div class="glass p-3">
                <div class="fw-semibold mb-2">Selected Shipment JSON</div>
                <pre class="json-box mb-0" id="shipmentDetailsRaw">{}</pre>
              </div>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* =========================================================
   ROUTES (as provided)
========================================================= */
const API = {
  loading: {
    loadings: "http://192.168.1.9/siya/api/loading/loadings/"
  },
  movement: {
    movements:     "http://192.168.1.9/siya/api/movement/movements/",
    movementItems: "http://192.168.1.9/siya/api/movement/movement-items/"
  },
  offloading: {
    offloadings:     "http://192.168.1.9/siya/api/movement/offloadings/",
    offloadingItems: "http://192.168.1.9/siya/api/movement/offloading-items/"
  },
  storage: {
    storage:      "http://192.168.1.9/siya/api/storage/storage/",
    storageItems: "http://192.168.1.9/siya/api/storage/storage-items/"
  }
};

/* Sidebar subtitles */
document.getElementById('ep-loading').textContent    = API.loading.loadings;
document.getElementById('ep-movement').textContent   = API.movement.movements;
document.getElementById('ep-offloading').textContent = API.offloading.offloadings;
document.getElementById('ep-storage').textContent    = API.storage.storage;

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
  const re = /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\btrue\b|\bfalse\b|\bnull\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g;
  return escapeHtml(jsonString).replace(re, (match) => {
    if (match.startsWith('"') && match.endsWith('":')) return `<span class="json-key">${match}</span>`;
    if (match.startsWith('"')) return `<span class="json-str">${match}</span>`;
    if (match === 'true' || match === 'false') return `<span class="json-bool">${match}</span>`;
    if (match === 'null') return `<span class="json-null">${match}</span>`;
    return `<span class="json-num">${match}</span>`;
  });
}

function setRawPretty(panelKey, payload) {
  const raw = document.getElementById(`raw-${panelKey}`);
  if (typeof payload === "string") {
    raw.textContent = payload;
    return;
  }
  const pretty = JSON.stringify(payload, null, 2);
  raw.innerHTML = syntaxHighlightJson(pretty);
}

/* =========================================================
   SHARED HELPERS
========================================================= */
const aborters = {};
const activeDataset = {
  movement: "movements",
  offloading: "offloadings",
  storage: "storage"
};

function setStatus(panelKey, text, ok=true) {
  const el = document.getElementById(`status-${panelKey}`);
  el.textContent = text;
  el.classList.remove('badge-ok','badge-bad');
  el.classList.add(ok ? 'badge-ok' : 'badge-bad');
}

function setSpinner(panelKey, on) {
  const sp = document.getElementById(`spin-${panelKey}`);
  if (!sp) return;
  sp.classList.toggle('d-none', !on);
}

function isObject(v) { return v && typeof v === 'object' && !Array.isArray(v); }

function pickArray(payload) {
  if (Array.isArray(payload)) return payload;
  if (isObject(payload)) {
    const candidates = [
      'data','results','items',
      'movements','movementItems','movement_items',
      'offloadings','offloadingItems','offloading_items',
      'loadings',
      'storage','storageItems','storage_items'
    ];
    for (const k of candidates) if (Array.isArray(payload[k])) return payload[k];
  }
  return [];
}

/* =========================================================
   MODAL + SHIPMENT SUMMARY (SMART)
========================================================= */
let shipmentModalInstance = null;
let lastSelectedShipment = null;

function titleCase(s) {
  return String(s)
    .replace(/_/g, ' ')
    .replace(/([a-z])([A-Z])/g, '$1 $2')
    .replace(/\b\w/g, c => c.toUpperCase());
}

function isPrimitive(v) {
  return v === null || v === undefined || typeof v === 'string' || typeof v === 'number' || typeof v === 'boolean';
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

  const container = pick("container_no","container","container_number","containerid","container_id");
  const tracking  = pick("tracking_no","tracking","tracking_number","trackingid","tracking_id","reference","ref","shipment_no","shipmentno","waybill","awb","bol","billoflading","bill_of_lading","id","uuid");
  const status    = pick("status","shipment_status","state","movement_status","stage","current_status");
  const mode      = pick("mode","transport_mode","shipment_mode","type","movement_type");

  const origin    = pick("origin","from","from_location","from_site","from_warehouse","load_port","port_of_loading","pol","pickup","pickup_location");
  const dest      = pick("destination","to","to_location","to_site","to_warehouse","discharge_port","port_of_discharge","pod","dropoff","dropoff_location");

  const eta       = pick("eta","estimated_arrival","estimated_arrival_date");
  const etd       = pick("etd","estimated_departure","estimated_departure_date");
  const created   = pick("created_at","created","date_created","created_date");
  const updated   = pick("updated_at","updated","date_updated","updated_date");

  const titleParts = [];
  if (container) titleParts.push(`Container ${container}`);
  if (!container && tracking) titleParts.push(`Ref ${tracking}`);
  if (!titleParts.length) titleParts.push("Selected Shipment");

  const subtitleParts = [];
  if (origin || dest) subtitleParts.push(`${origin || "—"} → ${dest || "—"}`);
  if (eta || etd) subtitleParts.push(`ETD ${etd || "—"} • ETA ${eta || "—"}`);
  if (!eta && !etd && (created || updated)) subtitleParts.push(`Created ${created || "—"} • Updated ${updated || "—"}`);

  return {
    container: container ?? "—",
    tracking: tracking ?? "—",
    status: status ?? "—",
    mode: mode ?? "—",
    from: origin ?? "—",
    to: dest ?? "—",
    title: titleParts.join(" • "),
    subtitle: subtitleParts.join(" • ") || "—"
  };
}

function inferShipmentTitle(rowObj, panelKey) {
  const s = extractShipmentSummary(rowObj);
  return `${titleCase(panelKey)} — ${s.title}`;
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = (value === null || value === undefined || String(value).trim() === "") ? "—" : String(value);
}

function setStatusBadge(text) {
  const el = document.getElementById("sumStatusBadge");
  el.textContent = `Status: ${text || "—"}`;

  el.classList.remove("badge-ok","badge-bad");
  const t = String(text || "").toLowerCase();

  if (t.includes("complete") || t.includes("completed") || t.includes("done") || t.includes("delivered") || t.includes("arrived")) {
    el.classList.add("badge-ok");
  } else if (t.includes("fail") || t.includes("error") || t.includes("cancel") || t.includes("rejected")) {
    el.classList.add("badge-bad");
  }
}

function renderShipmentModal(panelKey, datasetKey, rowObj) {
  lastSelectedShipment = rowObj;

  if (!shipmentModalInstance) {
    shipmentModalInstance = new bootstrap.Modal(document.getElementById('shipmentModal'), { backdrop: true, keyboard: true });
  }

  document.getElementById('shipmentModalTitle').textContent = inferShipmentTitle(rowObj, panelKey);
  document.getElementById('shipmentModalSub').textContent =
    `Panel: ${panelKey} • Dataset: ${datasetKey || 'default'} • Showing clicked record only`;

  // Summary
  const sum = extractShipmentSummary(rowObj);
  setText("sumTitle", sum.title);
  setText("sumSubtitle", sum.subtitle);
  setStatusBadge(sum.status);
  setText("sumModeBadge", `Mode: ${sum.mode}`);
  setText("sumContainer", sum.container);
  setText("sumTracking", sum.tracking);
  setText("sumFrom", sum.from);
  setText("sumTo", sum.to);

  // Details + nested
  const tbody = document.getElementById('shipmentDetailsTbody');
  const nestedWrap = document.getElementById('shipmentNestedBlocks');
  const nestedHint = document.getElementById('shipmentExtraHint');

  tbody.innerHTML = "";
  nestedWrap.innerHTML = "";

  if (!rowObj || typeof rowObj !== 'object') {
    tbody.innerHTML = `<tr><td class="muted">No details available</td><td></td></tr>`;
    document.getElementById('shipmentDetailsRaw').textContent = String(rowObj ?? '');
    nestedHint.textContent = "No nested content.";
    shipmentModalInstance.show();
    return;
  }

  const keys = Object.keys(rowObj);
  const primitiveKeys = [];
  const nestedKeys = [];
  keys.forEach(k => (isPrimitive(rowObj[k]) ? primitiveKeys : nestedKeys).push(k));

  primitiveKeys.sort((a,b)=> a.localeCompare(b));

  const priorityNorm = new Set([
    "container_no","container","container_number",
    "tracking_no","tracking","tracking_number",
    "reference","ref","shipment_no","shipmentno",
    "status","state","stage","current_status",
    "origin","from","destination","to","eta","etd",
    "created_at","updated_at"
  ]);

  const priority = [];
  const others = [];
  primitiveKeys.forEach(k => {
    const nk = normKey(k);
    if (priorityNorm.has(nk)) priority.push(k);
    else others.push(k);
  });

  const finalKeys = [...priority, ...others];

  if (!finalKeys.length) {
    tbody.innerHTML = `<tr><td class="muted">No primitive fields</td><td></td></tr>`;
  } else {
    finalKeys.forEach(k => {
      const tr = document.createElement('tr');

      const tdK = document.createElement('td');
      tdK.className = "muted";
      tdK.style.width = "36%";
      tdK.textContent = titleCase(k);

      const tdV = document.createElement('td');
      tdV.textContent = (rowObj[k] === null || rowObj[k] === undefined) ? "" : String(rowObj[k]);
      tdV.style.cursor = "text";

      tr.appendChild(tdK);
      tr.appendChild(tdV);
      tbody.appendChild(tr);
    });
  }

  const pretty = JSON.stringify(rowObj, null, 2);
  document.getElementById('shipmentDetailsRaw').innerHTML = syntaxHighlightJson(pretty);

  if (!nestedKeys.length) {
    nestedHint.textContent = "No nested objects/arrays found for this record.";
  } else {
    nestedHint.textContent = "Nested objects/arrays detected:";
    nestedKeys.forEach(k => {
      const v = rowObj[k];

      const card = document.createElement('div');
      card.className = "glass p-3 mb-2";

      const header = document.createElement('div');
      header.className = "d-flex justify-content-between align-items-center";
      header.innerHTML = `
        <div class="fw-semibold">${titleCase(k)}</div>
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
   TABLE RENDER
========================================================= */
function renderTable(panelKey, items) {
  const thead = document.getElementById(`thead-${panelKey}`);
  const tbody = document.getElementById(`tbody-${panelKey}`);
  const count = document.getElementById(`count-${panelKey}`);
  const hint  = document.getElementById(`hint-${panelKey}`);

  thead.innerHTML = "";
  tbody.innerHTML = "";

  if (!items.length) {
    count.textContent = "0";
    hint.textContent = "No rows found (or API returned non-array shape). See Raw JSON.";
    return;
  }

  const first = items[0];
  const cols = isObject(first) ? Object.keys(first) : ['value'];

  cols.forEach(c => {
    const th = document.createElement('th');
    th.textContent = c;
    thead.appendChild(th);
  });

  const datasetKey = panelKey === "loading" ? "loadings" : (activeDataset[panelKey] || "default");

  items.forEach((row, idx) => {
    const tr = document.createElement('tr');
    tr.style.cursor = "pointer";
    tr.title = "Click to view full shipment details";

    cols.forEach(c => {
      const td = document.createElement('td');
      let val = isObject(row) ? row[c] : row;
      if (isObject(val) || Array.isArray(val)) val = "[nested]";
      td.textContent = (val === null || val === undefined) ? "" : String(val);
      tr.appendChild(td);
    });

    tr.addEventListener('click', () => {
      const rowObj = isObject(row) ? row : { value: row, index: idx };
      renderShipmentModal(panelKey, datasetKey, rowObj);
    });

    tbody.appendChild(tr);
  });

  count.textContent = String(items.length);
  hint.textContent = "Click any row to view shipment details in full-screen.";
}

/* =========================================================
   ENDPOINT RESOLVER
========================================================= */
function resolveEndpoint(panelKey) {
  if (panelKey === "loading") return API.loading.loadings;

  if (panelKey === "movement") {
    return (activeDataset.movement === "movementItems")
      ? API.movement.movementItems
      : API.movement.movements;
  }

  if (panelKey === "offloading") {
    return (activeDataset.offloading === "offloadingItems")
      ? API.offloading.offloadingItems
      : API.offloading.offloadings;
  }

  if (panelKey === "storage") {
    return (activeDataset.storage === "storageItems")
      ? API.storage.storageItems
      : API.storage.storage;
  }

  return "";
}

/* =========================================================
   LOADERS
========================================================= */
async function loadPanel(panelKey) {
  const endpoint = resolveEndpoint(panelKey);
  document.getElementById(`endpoint-${panelKey}`).textContent = endpoint;

  if (aborters[panelKey]) aborters[panelKey].abort();
  aborters[panelKey] = new AbortController();

  try {
    setSpinner(panelKey, true);
    setStatus(panelKey, "Loading…", true);

    const res = await fetch(endpoint, {
      headers: { 'Accept': 'application/json' },
      signal: aborters[panelKey].signal
    });

    const text = await res.text();
    let payload;
    try { payload = JSON.parse(text); }
    catch { payload = { raw: text }; }

    setRawPretty(panelKey, payload);

    if (!res.ok) {
      setStatus(panelKey, `HTTP ${res.status}`, false);
      renderTable(panelKey, []);
      return;
    }

    const items = pickArray(payload);
    renderTable(panelKey, items);
    setStatus(panelKey, "OK", true);

  } catch (e) {
    if (e.name === "AbortError") return;
    setStatus(panelKey, "Fetch failed", false);
    document.getElementById(`hint-${panelKey}`).textContent = String(e);
    document.getElementById(`raw-${panelKey}`).textContent = String(e);
    renderTable(panelKey, []);
  } finally {
    setSpinner(panelKey, false);
  }
}

/* =========================================================
   TAB SWITCHING
========================================================= */
function showPanel(panelKey) {
  document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`panel-${panelKey}`).classList.add('active');

  document.querySelectorAll('#sideTabs .nav-link').forEach(btn => btn.classList.remove('active'));
  document.querySelector(`#sideTabs .nav-link[data-key="${panelKey}"]`).classList.add('active');

  loadPanel(panelKey);
}

document.querySelectorAll('#sideTabs .nav-link').forEach(btn => {
  btn.addEventListener('click', () => showPanel(btn.dataset.key));
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
bindDatasetSwitch("movement");
bindDatasetSwitch("offloading");
bindDatasetSwitch("storage");

/* =========================================================
   BUTTON BINDINGS
========================================================= */
document.getElementById('btnReload-loading').addEventListener('click', () => loadPanel("loading"));
document.getElementById('btnReload-movement').addEventListener('click', () => loadPanel("movement"));
document.getElementById('btnReload-offloading').addEventListener('click', () => loadPanel("offloading"));
document.getElementById('btnReload-storage').addEventListener('click', () => loadPanel("storage"));

/* =========================================================
   INITIAL LOAD
========================================================= */
showPanel("movement");
</script>

</body>
</html>
