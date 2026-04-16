<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BFRN — Shipments</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

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
    .glass { background: var(--panel); border: 1px solid var(--border); border-radius: 14px; }
    .btn-glass { border: 1px solid rgba(255,255,255,.18); background: var(--btn); color: #fff; border-radius: 10px; }
    .btn-glass:hover { background: var(--hover); color:#fff; }
    .muted { color: var(--muted); }

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
      white-space: nowrap;
    }
    .table td { vertical-align: middle; }

    /* JSON pretty */
    pre.json-box{
      background: rgba(0,0,0,.35);
      border: 1px solid rgba(255,255,255,.10);
      border-radius: 12px;
      padding: 12px;
      max-height: 380px;
      overflow: auto;
      color: #eaeaf2;
      line-height: 1.35;
      font-size: 13px;
      white-space: pre;
    }
    .json-key   { color: rgba(234,234,242,.92); font-weight: 650; }
    .json-str   { color: #9ad1ff; }
    .json-num   { color: #ffd48a; }
    .json-bool  { color: #b9a6ff; font-weight: 650; }
    .json-null  { color: rgba(255,255,255,.55); font-style: italic; }
    .json-url   { color: #7fffd4; text-decoration: underline; }

    /* Tabs inside modal */
    .nav-pills .nav-link {
      color: var(--text);
      border: 1px solid var(--border);
      background: var(--panel);
      border-radius: 14px;
      padding: 10px 12px;
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

    .badge-soft { border: 1px solid var(--border); background: rgba(255,255,255,.08); font-weight: 600; }
    .badge-ok { color: var(--ok); }
    .badge-bad { color: var(--bad); }

    .section-panel { display:none; }
    .section-panel.active { display:block; }

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

    /* Modal glass */
    .modal-content {
      background: rgba(12,12,18,.92);
      border: 1px solid rgba(255,255,255,.12);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      color: var(--text);
    }

    .truncate {
      max-width: 520px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* make danger outline readable on dark */
    .btn-outline-danger{
      border-color: rgba(255,107,107,.75);
      color: #ffb3b3;
    }
    .btn-outline-danger:hover{
      background: rgba(255,107,107,.12);
      color:#fff;
    }
  </style>
</head>

<body>
<div class="container py-4" style="max-width: 1320px;">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h3 class="mb-0">Shipments</h3>
      <div class="muted">View Loading / Movement / Offloading / Storage per shipment (via BFRN proxy).</div>
    </div>
    <a class="btn btn-glass" href="/bfrn/shipcreate">+ Create Shipment</a>
  </div>

  <div class="glass p-3 mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <button class="btn btn-glass" id="btnShipmentsReload">
        <span class="spinner-border spinner-border-sm d-none" id="spinShipments" role="status" aria-hidden="true"></span>
        Reload shipments
      </button>
      <span class="muted" id="shipmentsHint"></span>
    </div>
  </div>

  <div class="glass p-3">
    <div class="table-responsive">
      <table class="table table-sm table-dark-glass align-middle mb-0">
        <thead>
          <tr>
            <th style="width:90px;">ID</th>
            <th>Name</th>
            <th>Description</th>
            <th style="width:90px;">BU</th>
            <th style="width:180px;">Created</th>
            <th style="width:180px;"></th>
          </tr>
        </thead>
        <tbody id="shipmentsTbody">
          <tr><td colspan="6" class="muted">Loading…</td></tr>
        </tbody>
      </table>
    </div>
    <div class="muted mt-2" style="font-size:13px;">
      Use the <strong>View</strong> button to open the workflow modal.
    </div>
  </div>
</div>

<!-- SHIPMENT DETAILS MODAL -->
<div class="modal fade" id="shipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <div>
          <h5 class="modal-title mb-1" id="modalTitle">Shipment</h5>
          <div class="muted" id="modalSub">Select a dataset tab to load details.</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body pt-0">
        <div class="row g-3">
          <!-- LEFT tabs (4) -->
          <div class="col-12 col-lg-3">
            <div class="nav nav-pills flex-column" id="opsTabs">
              <button class="nav-link active" data-key="loading" type="button">
                <div class="fw-semibold">Loading</div>
                <div class="muted" style="font-size:12px;" id="ep-loading"></div>
              </button>

              <button class="nav-link" data-key="movement" type="button">
                <div class="fw-semibold">Movement</div>
                <div class="muted" style="font-size:12px;" id="ep-movement"></div>
              </button>

              <button class="nav-link" data-key="offloading" type="button">
                <div class="fw-semibold">Offloading</div>
                <div class="muted" style="font-size:12px;" id="ep-offloading"></div>
              </button>

              <button class="nav-link" data-key="storage" type="button">
                <div class="fw-semibold">Storage</div>
                <div class="muted" style="font-size:12px;" id="ep-storage"></div>
              </button>
            </div>
          </div>

          <!-- RIGHT panel content -->
          <div class="col-12 col-lg-9">

            <!-- LOADING -->
            <section id="panel-loading" class="section-panel active">
              <div class="glass p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <h5 class="mb-1">Loading</h5>
                    <div class="muted">Filtered by selected shipment.</div>
                    <div class="muted mt-2">Endpoint: <code id="endpoint-loading"></code></div>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-glass" id="btnReload-loading">
                      <span class="spinner-border spinner-border-sm d-none" id="spin-loading" aria-hidden="true"></span>
                      Reload
                    </button>
                    <span class="badge badge-soft px-3 py-2" id="status-loading">Idle</span>
                  </div>
                </div>
              </div>

              <div class="glass p-3 mb-3 d-flex justify-content-between">
                <div><strong>Rows:</strong> <span id="count-loading">0</span></div>
                <div class="muted" id="hint-loading"></div>
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
            <section id="panel-movement" class="section-panel">
              <div class="glass p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <h5 class="mb-1">Movement</h5>
                    <div class="muted">Filtered by selected shipment.</div>

                    <div class="ds-switch mt-3 d-flex flex-wrap gap-2">
                      <button class="btn active" type="button" data-panel="movement" data-ds="movements">Movements</button>
                      <button class="btn" type="button" data-panel="movement" data-ds="movementItems">Movement Items</button>
                    </div>

                    <div class="muted mt-2">Endpoint: <code id="endpoint-movement"></code></div>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-glass" id="btnReload-movement">
                      <span class="spinner-border spinner-border-sm d-none" id="spin-movement" aria-hidden="true"></span>
                      Reload
                    </button>
                    <span class="badge badge-soft px-3 py-2" id="status-movement">Idle</span>
                  </div>
                </div>
              </div>

              <div class="glass p-3 mb-3 d-flex justify-content-between">
                <div><strong>Rows:</strong> <span id="count-movement">0</span></div>
                <div class="muted" id="hint-movement"></div>
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
              <div class="glass p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <h5 class="mb-1">Offloading</h5>
                    <div class="muted">Filtered by selected shipment.</div>

                    <div class="ds-switch mt-3 d-flex flex-wrap gap-2">
                      <button class="btn active" type="button" data-panel="offloading" data-ds="offloadings">Offloadings</button>
                      <button class="btn" type="button" data-panel="offloading" data-ds="offloadingItems">Offloading Items</button>
                    </div>

                    <div class="muted mt-2">Endpoint: <code id="endpoint-offloading"></code></div>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-glass" id="btnReload-offloading">
                      <span class="spinner-border spinner-border-sm d-none" id="spin-offloading" aria-hidden="true"></span>
                      Reload
                    </button>
                    <span class="badge badge-soft px-3 py-2" id="status-offloading">Idle</span>
                  </div>
                </div>
              </div>

              <div class="glass p-3 mb-3 d-flex justify-content-between">
                <div><strong>Rows:</strong> <span id="count-offloading">0</span></div>
                <div class="muted" id="hint-offloading"></div>
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
              <div class="glass p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <h5 class="mb-1">Storage</h5>
                    <div class="muted">Filtered by selected shipment.</div>

                    <div class="ds-switch mt-3 d-flex flex-wrap gap-2">
                      <button class="btn active" type="button" data-panel="storage" data-ds="storage">Storage</button>
                      <button class="btn" type="button" data-panel="storage" data-ds="storageItems">Storage Items</button>
                    </div>

                    <div class="muted mt-2">Endpoint: <code id="endpoint-storage"></code></div>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-glass" id="btnReload-storage">
                      <span class="spinner-border spinner-border-sm d-none" id="spin-storage" aria-hidden="true"></span>
                      Reload
                    </button>
                    <span class="badge badge-soft px-3 py-2" id="status-storage">Idle</span>
                  </div>
                </div>
              </div>

              <div class="glass p-3 mb-3 d-flex justify-content-between">
                <div><strong>Rows:</strong> <span id="count-storage">0</span></div>
                <div class="muted" id="hint-storage"></div>
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
        </div>
      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- EDIT SHIPMENT MODAL -->
<div class="modal fade" id="editShipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <div>
          <h5 class="modal-title mb-1">Edit Shipment</h5>
          <div class="muted" style="font-size:13px;" id="editShipmentSub">—</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="glass p-3">
          <div class="mb-3">
            <label class="form-label muted">Name</label>
            <input class="form-control bg-transparent text-white border-0" style="border:1px solid rgba(255,255,255,.12)!important;border-radius:12px;" id="editName" />
          </div>

          <div class="mb-2">
            <label class="form-label muted">Description</label>
            <textarea class="form-control bg-transparent text-white border-0" rows="3"
              style="border:1px solid rgba(255,255,255,.12)!important;border-radius:12px;" id="editDescription"></textarea>
          </div>

          <div class="muted mt-2" style="font-size:12px;">
            This updates Django via: <code id="editEndpoint"></code>
          </div>
        </div>

        <div class="muted mt-3" id="editError" style="display:none;color:#ffb3b3;"></div>
      </div>

      <div class="modal-footer border-0">
        <button class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-glass" id="btnEditSave">
          <span class="spinner-border spinner-border-sm d-none" id="spinEditSave" aria-hidden="true"></span>
          Save changes
        </button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* =========================================================
   BFRN PROXY ROUTES (same-origin) — CONFIRMED by your route:list
========================================================= */
const SHIPMENTS = { list: "/bfrn/api/shipments" };

const API = {
  loading:   { loadings: "/bfrn/api/loading/loadings" },

  movement:  {
    movements:     "/bfrn/api/movement/movements",
    movementItems: "/bfrn/api/movement/movement-items"
  },

  offloading:{
    offloadings:     "/bfrn/api/offloading/offloadings",
    offloadingItems: "/bfrn/api/offloading/offloading-items"
  },

  storage:   {
    storage:      "/bfrn/api/storage/storage",
    storageItems: "/bfrn/api/storage/storage-items"
  }
};

document.getElementById('ep-loading').textContent    = API.loading.loadings;
document.getElementById('ep-movement').textContent   = API.movement.movements;
document.getElementById('ep-offloading').textContent = API.offloading.offloadings;
document.getElementById('ep-storage').textContent    = API.storage.storage;

/* =========================================================
   CSRF + fetch helper
========================================================= */
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function fetchJSON(url, options = {}) {
  const method = (options.method || 'GET').toUpperCase();

  const headers = Object.assign(
    { 'Accept': 'application/json' },
    options.headers || {}
  );

  // Add JSON content type if body is plain object
  if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(options.body);
  }

  // Add CSRF for non-GET
  if (method !== 'GET') headers['X-CSRF-TOKEN'] = CSRF;

  const res = await fetch(url, Object.assign({}, options, { headers }));
  const text = await res.text();

  let payload;
  try { payload = JSON.parse(text); } catch { payload = { raw: text }; }

  return { res, payload, text };
}

/* =========================================================
   FILTERING
========================================================= */
let selectedShipment = null;

function buildFilteredUrl(baseUrl) {
  if (!selectedShipment || !selectedShipment.id) return baseUrl;
  const u = new URL(baseUrl, location.origin);
  u.searchParams.set("shipment_id", selectedShipment.id);
  return u.toString();
}

/* =========================================================
   JSON pretty (colored)
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

    if (match.startsWith('"')) {
      const unquoted = match.slice(1, -1);
      const looksUrl = /^https?:\/\/[^\s"]+$/i.test(unquoted);
      if (looksUrl) {
        // keep quotes visually, but link the URL
        return `"${`<a class="json-url" href="${escapeHtml(unquoted)}" target="_blank" rel="noopener noreferrer">${escapeHtml(unquoted)}</a>`}"`;
      }
      return `<span class="json-str">${match}</span>`;
    }

    if (match === 'true' || match === 'false') return `<span class="json-bool">${match}</span>`;
    if (match === 'null') return `<span class="json-null">${match}</span>`;
    return `<span class="json-num">${match}</span>`;
  });
}

function setRawPretty(panelKey, payload) {
  const raw = document.getElementById(`raw-${panelKey}`);
  const pretty = JSON.stringify(payload, null, 2);
  raw.innerHTML = syntaxHighlightJson(pretty);
}

/* =========================================================
   OPS tables
========================================================= */
let activeAbort = null;
const activeDataset = { movement: "movements", offloading: "offloadings", storage: "storage" };

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
    const candidates = ['data','results','items','movements','offloadings','loadings','storage','storage_items'];
    for (const k of candidates) if (Array.isArray(payload[k])) return payload[k];
  }
  return [];
}

function renderTable(panelKey, items) {
  const thead = document.getElementById(`thead-${panelKey}`);
  const tbody = document.getElementById(`tbody-${panelKey}`);
  const count = document.getElementById(`count-${panelKey}`);
  const hint  = document.getElementById(`hint-${panelKey}`);

  thead.innerHTML = "";
  tbody.innerHTML = "";

  if (!items.length) {
    count.textContent = "0";
    hint.textContent = "No rows found for this shipment.";
    return;
  }

  const first = items[0];
  const cols = isObject(first) ? Object.keys(first) : ['value'];

  cols.forEach(c => {
    const th = document.createElement('th');
    th.textContent = c;
    thead.appendChild(th);
  });

  items.forEach(row => {
    const tr = document.createElement('tr');
    cols.forEach(c => {
      const td = document.createElement('td');
      let val = isObject(row) ? row[c] : row;
      if (isObject(val) || Array.isArray(val)) val = JSON.stringify(val);
      td.textContent = (val === null || val === undefined) ? "" : String(val);
      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });

  count.textContent = String(items.length);
  hint.textContent = "";
}

function resolveEndpoint(panelKey) {
  if (panelKey === "loading") return API.loading.loadings;

  if (panelKey === "movement") {
    return (activeDataset.movement === "movementItems") ? API.movement.movementItems : API.movement.movements;
  }
  if (panelKey === "offloading") {
    return (activeDataset.offloading === "offloadingItems") ? API.offloading.offloadingItems : API.offloading.offloadings;
  }
  if (panelKey === "storage") {
    return (activeDataset.storage === "storageItems") ? API.storage.storageItems : API.storage.storage;
  }
  return "";
}

async function loadPanel(panelKey) {
  const base = resolveEndpoint(panelKey);
  const endpoint = buildFilteredUrl(base);
  document.getElementById(`endpoint-${panelKey}`).textContent = endpoint;

  if (activeAbort) activeAbort.abort();
  activeAbort = new AbortController();

  try {
    setSpinner(panelKey, true);
    setStatus(panelKey, "Loading…", true);

    const { res, payload } = await fetchJSON(endpoint, { signal: activeAbort.signal });

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
    renderTable(panelKey, []);
  } finally {
    setSpinner(panelKey, false);
  }
}

function showOpsPanel(panelKey) {
  document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`panel-${panelKey}`).classList.add('active');

  document.querySelectorAll('#opsTabs .nav-link').forEach(btn => btn.classList.remove('active'));
  document.querySelector(`#opsTabs .nav-link[data-key="${panelKey}"]`).classList.add('active');

  loadPanel(panelKey);
}

document.querySelectorAll('#opsTabs .nav-link').forEach(btn => {
  btn.addEventListener('click', () => showOpsPanel(btn.dataset.key));
});

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

document.getElementById('btnReload-loading').addEventListener('click', () => loadPanel("loading"));
document.getElementById('btnReload-movement').addEventListener('click', () => loadPanel("movement"));
document.getElementById('btnReload-offloading').addEventListener('click', () => loadPanel("offloading"));
document.getElementById('btnReload-storage').addEventListener('click', () => loadPanel("storage"));

/* =========================================================
   SHIPMENTS LIST (name + description + view/edit/delete buttons)
========================================================= */
const shipmentsTbody = document.getElementById('shipmentsTbody');
const shipmentsHint = document.getElementById('shipmentsHint');
const spinShipments = document.getElementById('spinShipments');

function setShipmentsLoading(on, msg="") {
  spinShipments.classList.toggle('d-none', !on);
  shipmentsHint.textContent = msg;
}

function normalizeShipmentRow(s) {
  return {
    id: s.id ?? "",
    name: s.name ?? "",
    description: s.description ?? "",
    bu: s.bu ?? "",
    created_at: s.created_at ?? "",
    _raw: s
  };
}

async function loadShipments() {
  try {
    setShipmentsLoading(true, "Loading shipments…");
    shipmentsTbody.innerHTML = `<tr><td colspan="6" class="muted">Loading…</td></tr>`;

    const { res, payload } = await fetchJSON(SHIPMENTS.list);

    if (!res.ok) {
      shipmentsTbody.innerHTML = `<tr><td colspan="6" class="muted">Failed to load shipments (HTTP ${res.status}).</td></tr>`;
      setShipmentsLoading(false, "");
      return;
    }

    const rows = pickArray(payload);
    if (!rows.length) {
      shipmentsTbody.innerHTML = `<tr><td colspan="6" class="muted">No shipments found.</td></tr>`;
      setShipmentsLoading(false, "");
      return;
    }

    shipmentsTbody.innerHTML = "";
    rows.forEach((s) => {
      const row = normalizeShipmentRow(s);

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${row.id}</td>
        <td>${row.name || '-'}</td>
        <td><div class="truncate" title="${escapeHtml(row.description || '')}">${escapeHtml(row.description || '-')}</div></td>
        <td>${row.bu ?? '-'}</td>
        <td>${row.created_at ?? '-'}</td>
        <td class="text-end">
          <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-sm btn-glass" data-act="view">View</button>
            <button class="btn btn-sm btn-glass" data-act="edit">Edit</button>
            <button class="btn btn-sm btn-outline-danger" data-act="del">Delete</button>
          </div>
        </td>
      `;

      tr.querySelector('[data-act="view"]').addEventListener('click', () => openShipmentModal(row, row._raw));
      tr.querySelector('[data-act="edit"]').addEventListener('click', () => openEditShipment(row));
      tr.querySelector('[data-act="del"]').addEventListener('click', () => deleteShipment(row));

      shipmentsTbody.appendChild(tr);
    });

    setShipmentsLoading(false, "");

  } catch (e) {
    shipmentsTbody.innerHTML = `<tr><td colspan="6" class="muted">Error: ${escapeHtml(String(e))}</td></tr>`;
    setShipmentsLoading(false, "");
  }
}

document.getElementById('btnShipmentsReload').addEventListener('click', loadShipments);

/* =========================================================
   OPEN MODAL
========================================================= */
const shipmentModalEl = document.getElementById('shipmentModal');
const shipmentModal = new bootstrap.Modal(shipmentModalEl);

function openShipmentModal(simpleRow, original) {
  selectedShipment = { ...original, ...simpleRow };

  document.getElementById('modalTitle').textContent =
    `${simpleRow.name || 'Shipment'}  #${simpleRow.id}`;

  document.getElementById('modalSub').textContent =
    `Filtered by shipment_id=${simpleRow.id}`;

  // reset datasets
  activeDataset.movement = "movements";
  activeDataset.offloading = "offloadings";
  activeDataset.storage = "storage";

  // reset active switch buttons
  document.querySelectorAll('#panel-movement .ds-switch .btn').forEach(b => b.classList.remove('active'));
  document.querySelector('#panel-movement .ds-switch .btn[data-ds="movements"]').classList.add('active');

  document.querySelectorAll('#panel-offloading .ds-switch .btn').forEach(b => b.classList.remove('active'));
  document.querySelector('#panel-offloading .ds-switch .btn[data-ds="offloadings"]').classList.add('active');

  document.querySelectorAll('#panel-storage .ds-switch .btn').forEach(b => b.classList.remove('active'));
  document.querySelector('#panel-storage .ds-switch .btn[data-ds="storage"]').classList.add('active');

  // default open
  showOpsPanel("movement");
  shipmentModal.show();
}

/* =========================================================
   EDIT (real modal + PUT)
========================================================= */
const editShipmentModalEl = document.getElementById('editShipmentModal');
const editShipmentModal = new bootstrap.Modal(editShipmentModalEl);

let editingShipment = null;

function openEditShipment(row){
  editingShipment = row;

  document.getElementById('editShipmentSub').textContent = `${row.name || 'Shipment'}  #${row.id}`;
  document.getElementById('editName').value = row.name || '';
  document.getElementById('editDescription').value = row.description || '';

  const endpoint = `/bfrn/api/shipments/${row.id}`;
  document.getElementById('editEndpoint').textContent = endpoint;

  document.getElementById('editError').style.display = 'none';
  document.getElementById('editError').textContent = '';

  editShipmentModal.show();
}

async function saveEditShipment(){
  if (!editingShipment) return;

  const btn = document.getElementById('btnEditSave');
  const spin = document.getElementById('spinEditSave');
  const err = document.getElementById('editError');

  const id = editingShipment.id;
  const endpoint = `/bfrn/api/shipments/${id}`;

  const body = {
    name: document.getElementById('editName').value.trim(),
    description: document.getElementById('editDescription').value.trim(),
  };

  // basic validation
  if (!body.name) {
    err.style.display = 'block';
    err.textContent = "Name is required.";
    return;
  }

  try {
    spin.classList.remove('d-none');
    btn.disabled = true;
    err.style.display = 'none';
    err.textContent = '';

    const { res, payload } = await fetchJSON(endpoint, {
      method: "PUT",
      body
    });

    if (!res.ok) {
      console.log("Update failed payload:", payload);
      err.style.display = 'block';
      err.textContent = `Update failed (HTTP ${res.status}). Check console.`;
      return;
    }

    editShipmentModal.hide();
    await loadShipments(); // refresh table

  } catch (e) {
    err.style.display = 'block';
    err.textContent = String(e);
  } finally {
    spin.classList.add('d-none');
    btn.disabled = false;
  }
}

document.getElementById('btnEditSave').addEventListener('click', saveEditShipment);



/* =========================================================
   DELETE (real)
========================================================= */
async function deleteShipment(row) {
  if (!confirm(`Delete shipment #${row.id} (${row.name || ''})?`)) return;

  const { res, payload } = await fetchJSON(`/bfrn/api/shipments/${row.id}`, {
    method: "DELETE",
    headers: { 'Accept': 'application/json' }
  });

  if (!res.ok) {
    console.log("Delete failed payload:", payload);
    alert(`Delete failed (HTTP ${res.status}). See console.`);
    return;
  }

  loadShipments();
}

/* =========================================================
   INIT
========================================================= */
loadShipments();
</script>

</body>
</html>
