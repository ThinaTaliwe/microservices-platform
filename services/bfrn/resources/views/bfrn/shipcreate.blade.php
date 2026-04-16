<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BFRN — Create Shipment</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    :root{
      --bg:#0b0b0f; --panel: rgba(255,255,255,.06); --border: rgba(255,255,255,.12);
      --text:#eaeaf2; --muted: rgba(234,234,242,.75); --hover: rgba(255,255,255,.16);
      --btn: rgba(255,255,255,.10); --ok:#43f08a; --bad:#ff6b6b;
      --chip: rgba(255,255,255,.08);
    }
    body { background:var(--bg); color:var(--text); }

    .glass { background: var(--panel); border: 1px solid var(--border); border-radius: 14px; }
    .btn-glass { border: 1px solid rgba(255,255,255,.18); background: var(--btn); color: #fff; border-radius: 10px; }
    .btn-glass:hover { background: var(--hover); color:#fff; }
    .muted { color: var(--muted); }

    .form-control, .form-select{
      background: rgba(255,255,255,.06) !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      color: var(--text) !important;
    }

    /* Dark selects */
    .form-select{
      background-color: rgba(255,255,255,.06) !important;
      color: var(--text) !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background-image: var(--bs-form-select-bg-img);
      background-repeat: no-repeat;
      background-position: right .75rem center;
      background-size: 16px 12px;
    }
    .form-select option,
    .form-select optgroup{
      background-color: #0b0b0f !important;
      color: #eaeaf2 !important;
    }
    .form-select option[value=""]{
      color: rgba(234,234,242,.65) !important;
    }

    .form-control::placeholder{ color: rgba(234,234,242,.45); }

    .alert-glass{
      background: rgba(255,107,107,.08);
      border: 1px solid rgba(255,107,107,.25);
      color: var(--text);
      border-radius: 12px;
    }
    .ok-glass{
      background: rgba(67,240,138,.08);
      border: 1px solid rgba(67,240,138,.25);
      color: var(--text);
      border-radius: 12px;
    }

    pre{
      background: rgba(0,0,0,.35);
      border: 1px solid rgba(255,255,255,.10);
      border-radius: 12px;
      padding: 12px;
      max-height: 320px;
      overflow: auto;
      color:#eaeaf2;
      font-size: 13px;
    }

    .small-help{ font-size:12px; }

    .chip{
      display:inline-flex; gap:.5rem; align-items:center;
      padding:.35rem .55rem; border-radius: 999px;
      background: var(--chip); border:1px solid rgba(255,255,255,.12);
      font-size:12px; color: rgba(234,234,242,.86);
    }
    .chip strong{ color:#fff; font-weight:900; }

    .step-title{
      display:flex; align-items:center; justify-content:space-between; gap:1rem;
    }
    .step-badge{
      width:34px; height:34px; border-radius:10px;
      display:flex; align-items:center; justify-content:center;
      background: rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.12);
      font-weight:800;
    }
    .disabled-panel{ opacity:.55; pointer-events:none; }
    .kpi{ display:flex; gap:.75rem; flex-wrap:wrap; }

    .typeahead-list{
      position:absolute; z-index: 9999;
      width:100%;
      background: rgba(20,20,30,.98);
      border:1px solid rgba(255,255,255,.15);
      border-radius: 12px;
      overflow:hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,.35);
      margin-top:.35rem;
    }
    .typeahead-item{
      padding:.55rem .75rem;
      cursor:pointer;
      border-bottom:1px solid rgba(255,255,255,.08);
      color: rgba(234,234,242,.92);
    }
    .typeahead-item:hover{ background: rgba(255,255,255,.08); }
    .typeahead-item small{ color: rgba(234,234,242,.65); }

    .divider{ height:1px; background: rgba(255,255,255,.10); width:100%; margin: 1rem 0; }

    .required-dot{
      display:inline-block; width:6px; height:6px; border-radius:999px;
      background: red; margin-left:.35rem;
      vertical-align: middle;
    }

    /* ✅ Compact inputs (non-invasive) */
    .compact .form-label{ margin-bottom:.25rem; font-size:.85rem; }
    .compact .form-control,
    .compact .form-select{
      padding-top:.35rem;
      padding-bottom:.35rem;
      font-size:.92rem;
      border-radius: .6rem;
    }
    .compact textarea.form-control{ min-height: 80px; }
    .compact .row.g-3{ --bs-gutter-y: .75rem; }
  </style>
</head>

<body>
<div class="container py-4" style="max-width: 980px;">
  <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h3 class="mb-0">Create Shipment Instructions</h3>
      <div class="muted">Step 1: Create instruction • Step 2: Create shipment</div>
    </div>
    <a href="/bfrn/" class="btn btn-glass">← Back</a>
  </div>

  <div id="msgBox" class="d-none mb-3"></div>

  <!-- STEP 1 -->
  <div class="glass p-4 mb-3 compact">
    <div class="step-title mb-2">
      <div class="d-flex align-items-center gap-2">
        <div class="step-badge">1</div>
        <div>
          <h5 class="mb-0">Shipment Instruction</h5>
          <div class="muted small-help"></div>
        </div>
      </div>

      <button class="btn btn-glass" id="btnLoadMeta">
        <span class="spinner-border spinner-border-sm d-none" id="spinMeta"></span>
        Load dropdown options
      </button>
    </div>

    <div class="kpi mb-3">
      <span class="chip">Instruction ID: <strong id="kpiInstrId">—</strong></span>
      <span class="chip">From Address ID: <strong id="kpiFromId">—</strong></span>
      <span class="chip">To Address ID: <strong id="kpiToId">—</strong></span>
    </div>

    <form id="formInstruction" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">BU <span class="required-dot"></span></label>
        <select class="form-select bg-black" name="bu" id="selBu" required>
          <option value="">Select BU…</option>
        </select>
      </div>

      <div class="col-md-8">
        <label class="form-label">Instruction Type <span class="required-dot"></span></label>
        <select class="form-select" name="instruction_type" id="selInstructionType" required>
          <option value="">Select Instruction Type…</option>
        </select>
        <div class="muted small-help mt-1"></div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Reference <span class="required-dot"></span></label>
        <input class="form-control" name="instruction_reference" placeholder="e.g. DUMMY-INST-20260209-204902" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Details</label>
        <input class="form-control" name="instruction_detail" placeholder="Short instruction details…">
      </div>

      <div class="divider"></div>

      <div class="col-md-6 position-relative">
        <label class="form-label">From Address <span class="required-dot"></span></label>
        <div class="d-flex gap-2">
          <div class="flex-grow-1 position-relative">
            <input class="form-control" id="fromAddressSearch" placeholder="Search addresses…" autocomplete="off">
            <input type="hidden" name="from_address" id="from_address_id" required>
            <div class="typeahead-list d-none" id="fromAddressList"></div>
          </div>
          <button type="button" class="btn btn-glass" data-bs-toggle="modal" data-bs-target="#addressModal" data-address-target="from">
            + New
          </button>
        </div>
        <div class="muted small-help mt-1">Type 2+ characters. Select an address OR create a new one.</div>
      </div>

      <div class="col-md-6 position-relative">
        <label class="form-label">To Address <span class="required-dot"></span></label>
        <div class="d-flex gap-2">
          <div class="flex-grow-1 position-relative">
            <input class="form-control" id="toAddressSearch" placeholder="Search addresses…" autocomplete="off">
            <input type="hidden" name="to_address" id="to_address_id" required>
            <div class="typeahead-list d-none" id="toAddressList"></div>
          </div>
          <button type="button" class="btn btn-glass" data-bs-toggle="modal" data-bs-target="#addressModal" data-address-target="to">
            + New
          </button>
        </div>
        <div class="muted small-help mt-1">Type 2+ characters. Select an address OR create a new one.</div>
      </div>

      <div class="col-12 d-flex gap-2 align-items-center">
        <button class="btn btn-glass bg-success mx-auto" type="submit" id="btnSaveInstruction">
          <span class="spinner-border spinner-border-sm d-none" id="spinInstrSave"></span>
          Save Instruction
        </button>
        <span class="muted" id="instrHint"></span>
      </div>
    </form>
  </div>

  <!-- STEP 2 -->
  <div class="glass p-4 compact" id="shipmentPanel">
    <div class="step-title mb-2">
      <div class="d-flex align-items-center gap-2">
        <div class="step-badge">2</div>
        <div>
          <h5 class="mb-0">Shipment</h5>
          <div class="muted small-help">Enabled after Step 1 is saved successfully.</div>
        </div>
      </div>
      <span class="chip">Status: <strong id="shipStatus">Waiting for instruction…</strong></span>
    </div>

    <form id="formCreate" class="row g-3">
      <div class="col-12">
        <label class="form-label">Name <span class="required-dot"></span></label>
        <input class="form-control" name="name" placeholder="e.g. TEST-SHIPMENT-NEW" required>
      </div>

      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Short description..."></textarea>
      </div>

      <div class="col-md-2">
        <label class="form-label">BU</label>
        <input class="form-control" name="bu" id="ship_bu" readonly required>
      </div>

      <div class="col-md-5">
        <label class="form-label">Shipment Type <span class="required-dot"></span></label>
        <select class="form-select" name="shipment_type" id="selShipmentType" required>
          <option value="">Select Shipment Type…</option>
        </select>
      </div>

      <div class="col-md-5">
        <label class="form-label">Mode of Transport <span class="required-dot"></span></label>
        <select class="form-select" name="mode_of_transport" id="selMode" required>
          <option value="">Select Mode…</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Shipment Instruction (id)</label>
        <input class="form-control" name="shipment_instruction" id="shipment_instruction" readonly required>
      </div>

      <!-- ✅ Hidden but functional hard-coded links -->
      <input type="hidden" name="loading" id="hid_loading" value="2">
      <input type="hidden" name="movement" id="hid_movement" value="2">
      <input type="hidden" name="offloading" id="hid_offloading" value="2">
      <input type="hidden" name="storage" id="hid_storage" value="2">

      <div class="col-12 d-flex gap-2 align-items-center">
        <button class="btn btn-glass bg-success mx-auto" type="submit" id="btnSubmit">
          <span class="spinner-border spinner-border-sm d-none" id="spinSubmit"></span>
          Create Shipment
        </button>
        <span class="muted" id="submitHint"></span>
      </div>
    </form>

    <div class="divider"></div>
    <div class="muted mb-2">Last API Response (debug)</div>
    <pre id="debugBox">{}</pre>
  </div>
</div>

<!-- ADDRESS MODAL -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background: rgba(20,20,30,.98); color: var(--text); border:1px solid rgba(255,255,255,.12);">
      <div class="modal-header" style="border-color: rgba(255,255,255,.12);">
        <h5 class="modal-title">Create Address</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <form id="formAddress" class="row g-3 compact">
          <input type="hidden" id="addressTarget" value="from">

          <!-- Address Type (required) -->
          <div class="col-md-6">
            <label class="form-label">Address Type <span class="required-dot"></span></label>
            <select class="form-select" name="adress_type" id="selAddressType" readonly>
              <option value="1" selected>Auto (id: 1)</option>
            </select>
            <div class="muted small-help mt-1">Fixed to Address Type ID 1.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Line 1 <span class="required-dot"></span></label>
            <input class="form-control" name="line1" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Line 2</label>
            <input class="form-control" name="line2">
          </div>

          <div class="col-md-4">
            <label class="form-label">PO Box</label>
            <input class="form-control" name="p_o_box">
          </div>

          <div class="col-md-4">
            <label class="form-label">Suburb</label>
            <input class="form-control" name="suburb">
          </div>

          <!-- Hidden/ignored (never submitted) -->
          <div class="col-12 d-none">
            <select id="selCity" disabled></select>
            <select id="selProvince" disabled></select>
            <select id="selCountry" disabled></select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Zip</label>
            <input class="form-control" name="zip">
          </div>

          <div class="col-12 d-flex gap-2 align-items-center">
            <button class="btn btn-glass" type="submit" id="btnSaveAddress">
              <span class="spinner-border spinner-border-sm d-none" id="spinAddrSave"></span>
              Save Address
            </button>
            <span class="muted" id="addrHint"></span>
          </div>
        </form>

      </div>
      <div class="modal-footer" style="border-color: rgba(255,255,255,.12);">
        <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function fetchJSON(url, options = {}) {
  const method = (options.method || 'GET').toUpperCase();
  const headers = Object.assign({ 'Accept': 'application/json' }, options.headers || {});
  if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(options.body);
  }
  if (method !== 'GET') headers['X-CSRF-TOKEN'] = CSRF;

  const res = await fetch(url, Object.assign({}, options, { headers }));
  const text = await res.text();

  let payload;
  try { payload = JSON.parse(text); } catch { payload = { raw: text }; }
  return { res, payload };
}

const API = @json([
  'buList' => route('bfrn.api.bu.index'),
  'addressesListCreate' => route('bfrn.api.addresses.index'),
  'shipmentTypes' => route('bfrn.api.shipment-types.index'),
  'instructionListCreate' => route('bfrn.api.shipment-instructions.store'),
  'createShipment' => route('bfrn.api.shipments.store'),
  'addressTypes' => route('bfrn.api.address-types.index'),
]);

const debugBox = document.getElementById('debugBox');
const msgBox = document.getElementById('msgBox');

function showMsg(type, title, bullets = []) {
  const html = `
    <div class="fw-bold mb-1">${title}</div>
    ${bullets.length ? `<ul class="mb-0">${bullets.map(b => `<li>${b}</li>`).join('')}</ul>` : ''}
  `;
  msgBox.className = "";
  msgBox.classList.add(type === "ok" ? "ok-glass" : "alert-glass", "p-3", "mb-3");
  msgBox.innerHTML = html;
  msgBox.classList.remove("d-none");
}
function clearMsg() {
  msgBox.classList.add("d-none");
  msgBox.innerHTML = "";
}

function drfErrorsToBullets(payload) {
  const bullets = [];
  if (!payload || typeof payload !== 'object') return bullets;
  if (payload.detail) bullets.push(String(payload.detail));
  for (const [field, val] of Object.entries(payload)) {
    if (field === 'detail') continue;
    if (Array.isArray(val)) bullets.push(`${field}: ${val.join(', ')}`);
    else if (val && typeof val === 'object') bullets.push(`${field}: ${JSON.stringify(val)}`);
    else bullets.push(`${field}: ${String(val)}`);
  }
  return bullets;
}

function fillSelectWithObjects(sel, rows, firstLabel, labelFn) {
  sel.innerHTML = "";
  const first = document.createElement("option");
  first.value = "";
  first.textContent = firstLabel;
  sel.appendChild(first);

  rows.forEach(r => {
    const opt = document.createElement("option");
    opt.value = r.id;
    opt.textContent = labelFn ? labelFn(r) : `${r.name ?? ('ID ' + r.id)} (id: ${r.id})`;
    sel.appendChild(opt);
  });
}

function formToObject(form) {
  const fd = new FormData(form);
  const obj = {};
  for (const [k, v] of fd.entries()) {
    if (v === "") continue;

    if ([
      'bu','shipment_type','mode_of_transport','shipment_instruction',
      'loading','movement','offloading','storage',
      'instruction_type','from_address','to_address','country_id'
    ].includes(k)) {
      obj[k] = Number(v);
      if (Number.isNaN(obj[k])) obj[k] = v;
    } else {
      obj[k] = v;
    }
  }
  return obj;
}

// ---------- Load all dropdowns (BU + shipment types)
const btnLoadMeta = document.getElementById('btnLoadMeta');
const spinMeta = document.getElementById('spinMeta');

btnLoadMeta.addEventListener('click', async () => {
  clearMsg();
  spinMeta.classList.remove('d-none');

  const [buRes, typesRes] = await Promise.all([
    fetchJSON(API.buList),
    fetchJSON(API.shipmentTypes),
  ]);

  debugBox.textContent = JSON.stringify({
    bu: buRes.payload,
    shipmentTypes: typesRes.payload
  }, null, 2);

  spinMeta.classList.add('d-none');

  if (!buRes.res.ok) {
    showMsg('bad', 'Could not load BU list.', drfErrorsToBullets(buRes.payload));
    return;
  }
  if (!typesRes.res.ok) {
    showMsg('bad', 'Could not load Shipment Types.', drfErrorsToBullets(typesRes.payload));
    return;
  }

  const buRows = buRes.payload.results || buRes.payload || [];
  const typeRows = typesRes.payload.results || [];

  const selBu = document.getElementById('selBu');
  fillSelectWithObjects(selBu, buRows, "Select BU…", (r) => `${r.name ?? 'BU'} (id: ${r.id})`);

  const bu1 = buRows.find(x => Number(x.id) === 1);
  if (bu1) selBu.value = 1;

  fillSelectWithObjects(document.getElementById('selShipmentType'), typeRows, "Select Shipment Type…");
  fillSelectWithObjects(document.getElementById('selMode'), typeRows, "Select Mode…");
  fillSelectWithObjects(document.getElementById('selInstructionType'), typeRows, "Select Instruction Type…");

  showMsg('ok', 'Dropdowns loaded.', [
    'BU loaded from /bfrn/api/bu',
    'Shipment Type loaded from /bfrn/api/shipments/shipment-types',
    'Instruction Type is temporarily using shipment-types as fallback.'
  ]);
});

// ---------- Step locking
let savedInstructionId = null;
const shipmentPanel = document.getElementById('shipmentPanel');
const shipStatus = document.getElementById('shipStatus');
const shipmentInstructionInput = document.getElementById('shipment_instruction');
const shipBuInput = document.getElementById('ship_bu');

const kpiInstrId = document.getElementById('kpiInstrId');
const kpiFromId = document.getElementById('kpiFromId');
const kpiToId = document.getElementById('kpiToId');

function lockShipmentUntilInstruction() {
  shipmentPanel.classList.toggle('disabled-panel', !savedInstructionId);
  shipStatus.textContent = savedInstructionId ? 'Ready to save shipment' : 'Waiting for instruction…';
}
lockShipmentUntilInstruction();

// ---------- Address search (autocomplete)
let addrTimer = null;
const addrCache = { loaded: false, rows: [] };

async function queryAddresses(q) {
  const url = `${API.addressesListCreate}?search=${encodeURIComponent(q)}`;
  const r = await fetchJSON(url);
  if (r.res.ok) return (r.payload.results || r.payload || []);

  if (!addrCache.loaded) {
    const all = await fetchJSON(API.addressesListCreate);
    if (all.res.ok) {
      addrCache.rows = all.payload.results || all.payload || [];
      addrCache.loaded = true;
    } else {
      return [];
    }
  }
  const needle = q.toLowerCase();
  return addrCache.rows.filter(a => {
    const text = [
      a.line1, a.line2, a.suburb, a.city, a.province, a.zip, a.p_o_box
    ].filter(Boolean).join(' ').toLowerCase();
    return text.includes(needle);
  }).slice(0, 20);
}

function addrLabel(a) {
  return [a.line1, a.suburb, a.city, a.province, a.zip].filter(Boolean).join(', ') || ('Address #' + a.id);
}

function renderAddrList(listEl, items, onPick) {
  listEl.innerHTML = "";
  if (!items.length) { listEl.classList.add('d-none'); return; }

  items.slice(0, 8).forEach(a => {
    const div = document.createElement('div');
    div.className = 'typeahead-item';
    div.innerHTML = `<div class="fw-semibold">${addrLabel(a)}</div><small>ID: ${a.id}</small>`;
    div.addEventListener('click', () => onPick(a));
    listEl.appendChild(div);
  });
  listEl.classList.remove('d-none');
}

function setupAddressSearch(inputEl, listEl, hiddenIdEl, kpiEl) {
  inputEl.addEventListener('input', () => {
    const q = inputEl.value.trim();
    hiddenIdEl.value = "";
    kpiEl.textContent = "—";

    if (addrTimer) clearTimeout(addrTimer);
    if (q.length < 2) { listEl.classList.add('d-none'); return; }

    addrTimer = setTimeout(async () => {
      const items = await queryAddresses(q);
      renderAddrList(listEl, items, (a) => {
        inputEl.value = addrLabel(a);
        hiddenIdEl.value = a.id;
        kpiEl.textContent = a.id;
        listEl.classList.add('d-none');
      });
    }, 250);
  });

  document.addEventListener('click', (e) => {
    if (!listEl.contains(e.target) && e.target !== inputEl) listEl.classList.add('d-none');
  });
}

setupAddressSearch(
  document.getElementById('fromAddressSearch'),
  document.getElementById('fromAddressList'),
  document.getElementById('from_address_id'),
  kpiFromId
);

setupAddressSearch(
  document.getElementById('toAddressSearch'),
  document.getElementById('toAddressList'),
  document.getElementById('to_address_id'),
  kpiToId
);

// ---------- Address modal create
const addressModalEl = document.getElementById('addressModal');

async function loadAddressDropdowns() {
  // Address type fixed to 1
  const sel = document.getElementById('selAddressType');
  sel.value = "1";

  // Optional debug fetch (non-blocking)
  try {
    const r = await fetchJSON(API.addressTypes);
    if (r.res.ok) debugBox.textContent = JSON.stringify({ addressTypes: r.payload }, null, 2);
  } catch {}
}

addressModalEl.addEventListener('show.bs.modal', (event) => {
  const btn = event.relatedTarget;
  document.getElementById('addressTarget').value = btn?.getAttribute('data-address-target') || 'from';
  document.getElementById('formAddress').reset();
  document.getElementById('addrHint').textContent = "";
  loadAddressDropdowns();
});

// ---------- Address form submission (adress_type=1, ignore city/province/country)
document.getElementById('formAddress').addEventListener('submit', async (e) => {
  e.preventDefault();
  clearMsg();

  const spin = document.getElementById('spinAddrSave');
  const hint = document.getElementById('addrHint');
  spin.classList.remove('d-none');
  hint.textContent = "Saving…";

  const formData = new FormData(e.target);
  const body = {};

  for (const [key, value] of formData.entries()) {
    if (value === "") continue;

    if (key === 'adress_type') { body[key] = 1; continue; }
    if (['city','province','country'].includes(key)) continue;

    body[key] = value;
  }

  body.adress_type = 1;

  const { res, payload } = await fetchJSON(API.addressesListCreate, { method: 'POST', body });

  debugBox.textContent = JSON.stringify(payload, null, 2);

  spin.classList.add('d-none');
  hint.textContent = "";

  if (!res.ok) {
    showMsg('bad', 'Address could not be saved.', drfErrorsToBullets(payload));
    return;
  }

  addrCache.loaded = false;

  const target = document.getElementById('addressTarget').value;
  const id = payload.id;
  const label = addrLabel(payload);

  if (target === 'from') {
    document.getElementById('fromAddressSearch').value = label;
    document.getElementById('from_address_id').value = id;
    kpiFromId.textContent = id;
  } else {
    document.getElementById('toAddressSearch').value = label;
    document.getElementById('to_address_id').value = id;
    kpiToId.textContent = id;
  }

  showMsg('ok', 'Address saved and selected.', [`New Address ID: ${id}`]);

  const modal = bootstrap.Modal.getInstance(addressModalEl);
  modal.hide();
});

// ---------- Step 1: Save instruction
const spinInstrSave = document.getElementById('spinInstrSave');
const instrHint = document.getElementById('instrHint');

document.getElementById('formInstruction').addEventListener('submit', async (e) => {
  e.preventDefault();
  clearMsg();

  const fromId = document.getElementById('from_address_id').value;
  const toId = document.getElementById('to_address_id').value;

  if (!fromId || !toId) {
    showMsg('bad', 'Please select both addresses before saving instruction.', [
      !fromId ? 'From Address is required.' : '',
      !toId ? 'To Address is required.' : '',
    ].filter(Boolean));
    return;
  }

  spinInstrSave.classList.remove('d-none');
  instrHint.textContent = "Saving instruction…";

  const body = formToObject(e.target);

  const { res, payload } = await fetchJSON(API.instructionListCreate, { method: "POST", body });

  debugBox.textContent = JSON.stringify(payload, null, 2);

  spinInstrSave.classList.add('d-none');
  instrHint.textContent = "";

  if (!res.ok) {
    showMsg('bad', 'Instruction could not be saved.', drfErrorsToBullets(payload));
    return;
  }

  savedInstructionId = payload.id;
  shipmentInstructionInput.value = payload.id;

  shipBuInput.value = payload.bu ?? body.bu ?? '';

  kpiInstrId.textContent = payload.id ?? '—';
  kpiFromId.textContent = payload.from_address ?? body.from_address ?? '—';
  kpiToId.textContent = payload.to_address ?? body.to_address ?? '—';

  lockShipmentUntilInstruction();

  showMsg('ok', 'Instruction saved successfully.', [
    `Instruction ID: ${payload.id}`,
    `From Address ID: ${payload.from_address}`,
    `To Address ID: ${payload.to_address}`,
  ]);
});

// ---------- Step 2: Save shipment
const spinSubmit = document.getElementById('spinSubmit');
const submitHint = document.getElementById('submitHint');

document.getElementById('formCreate').addEventListener('submit', async (e) => {
  e.preventDefault();
  clearMsg();

  if (!savedInstructionId) {
    showMsg('bad', 'Please save the Shipment Instruction first.');
    return;
  }

  spinSubmit.classList.remove("d-none");
  submitHint.textContent = "Posting…";

  const body = formToObject(e.target);

  // Enforce instruction id from Step 1
  body.shipment_instruction = savedInstructionId;

  // Enforce BU from instruction
  body.bu = Number(shipBuInput.value);

  // ✅ Enforce hardcoded link ids (functional but hidden)
  body.loading = 2;
  body.movement = 2;
  body.offloading = 2;
  body.storage = 2;

  const { res, payload } = await fetchJSON(API.createShipment, { method: "POST", body });

  debugBox.textContent = JSON.stringify(payload, null, 2);

  spinSubmit.classList.add("d-none");
  submitHint.textContent = "";

  if (!res.ok) {
    showMsg("bad", "Shipment could not be created.", drfErrorsToBullets(payload));
    return;
  }

  showMsg("ok", "Shipment created successfully!", [
    `Shipment ID: ${payload.id}`,
    `Name: ${payload.name || body.name}`
  ]);
});
</script>

</body>
</html>
