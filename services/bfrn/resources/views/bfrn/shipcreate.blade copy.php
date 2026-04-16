<!-- 



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BFRN — Create Shipment</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --bg:#0b0b0f; --panel: rgba(255,255,255,.06); --border: rgba(255,255,255,.12);
      --text:#eaeaf2; --muted: rgba(234,234,242,.75); --hover: rgba(255,255,255,.16);
      --btn: rgba(255,255,255,.10); --ok:#43f08a; --bad:#ff6b6b;
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
      max-height: 300px;
      overflow: auto;
      color:#eaeaf2;
      font-size: 13px;
    }
  </style>
</head>
<body>

<div class="container py-4" style="max-width: 980px;">
  <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
    @if(session('success'))
      <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 font-bold">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700">
        {{ $errors->first() }}
      </div>
    @endif
    <div>
      <h3 class="mb-0">Create Shipment</h3>
      <div class="muted">This submits JSON to Django via <code>/bfrn/api/shipments</code>.</div>
    </div>
    <a href="/bfrn/shipments" class="btn btn-glass">← Back</a>
  </div>

  <div class="glass p-3 mb-3">
    <div class="d-flex gap-2 align-items-center">
      <button class="btn btn-glass" id="btnLoadMeta">
        <span class="spinner-border spinner-border-sm d-none" id="spinMeta"></span>
        Load dropdown options (from existing shipments)
      </button>
      <span class="muted" id="metaHint"></span>
    </div>
  </div>

  <div id="msgBox" class="d-none mb-3"></div>

  <div class="glass p-4">
    <form id="formCreate" class="row g-3">
      <div class="col-12">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" placeholder="e.g. DEMO-SHIPMENT-002" required>
      </div>

      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Short description..."></textarea>
      </div>
<!-- 
      <div class="col-md-4">
        <label class="form-label">BU</label>
        <select class="form-select" name="bu" id="selBu">
          <option value="">Select BU…</option>
        </select>
        <div class="muted mt-1" style="font-size:12px;">Loaded from existing shipments (distinct values).</div>
      </div> -->

      <div class="col-md-2">
        <label class="form-label">BU (id)</label>
        <input value="1" class="form-control" name="bu" placeholder="1" required readonly>
        <div class="muted mt-1" style="font-size:12px;">Business Unit ID</div>
      </div>


      <div class="col-md-5">
        <label class="form-label">Shipment Type</label>
        <select class="form-select" name="shipment_type" id="selShipmentType">
          <option value="">Select Shipment Type…</option>
        </select>
      </div>

      <div class="col-md-5">
        <label class="form-label">Mode of Transport</label>
        <select class="form-select" name="mode_of_transport" id="selMode">
          <option value="">Select Mode…</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Shipment Instruction (id)</label>
        <input class="form-control" name="shipment_instruction" placeholder="number id (if required by API)">
      </div>

      <div class="col-md-6">
        <label class="form-label">Links (ids)</label>
        <div class="row g-2">
          <div class="col-6"><input class="form-control" name="loading" placeholder="loading id"></div>
          <div class="col-6"><input class="form-control" name="movement" placeholder="movement id"></div>
          <div class="col-6"><input class="form-control" name="offloading" placeholder="offloading id"></div>
          <div class="col-6"><input class="form-control" name="storage" placeholder="storage id"></div>
        </div>
        <div class="muted mt-1" style="font-size:12px;">If Django auto-creates these, leave blank.</div>
      </div>

      <div class="col-12 d-flex gap-2 align-items-center">
        <button class="btn btn-glass" type="submit" id="btnSubmit">
          <span class="spinner-border spinner-border-sm d-none" id="spinSubmit"></span>
          Create Shipment
        </button>
        <span class="muted" id="submitHint"></span>
      </div>
    </form>

    <hr class="border-opacity-25">

    <div class="muted mb-2">Last API Response (debug)</div>
    <pre id="debugBox">{}</pre>
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

const API = {
  shipments: "http://192.168.1.9/siya/api/shipments/shipments",
  shipmentTypes: "http://192.168.1.9/siya/api/shipments/shipment-types/",
  createShipmentDjango: "http://192.168.1.9/siya/api/shipments/shipments/",
  shipment_items: "http://192.168.1.9/api/shipments/shipment_items/",
  shipment_types: "http://192.168.1.9/api/shipments/shipment-types/",
  shipment_has_shipment: "http://192.168.1.9/api/shipments/shipment-has-shipment/",
  shipment_has_previous_shipments: "http://192.168.1.9/api/shipments/shipment-has-previous-shipments/",
  shipment_instructions: "http://192.168.1.9/api/shipments/shipment-instructions/",
  shipment_instruction_items: "http://192.168.1.9/api/shipments/shipment-instruction-items/",
  shipment_instruction_links: "http://192.168.1.9/api/shipments/shipment-instruction-links/"
};

console.log("API Endpoints:", API);

const spinMeta = document.getElementById('spinMeta');
const metaHint = document.getElementById('metaHint');
const debugBox = document.getElementById('debugBox');
const msgBox = document.getElementById('msgBox');

function showMsg(type, html) {
  msgBox.className = "";
  msgBox.classList.add(type === "ok" ? "ok-glass" : "alert-glass", "p-3", "mb-3");
  msgBox.innerHTML = html;
  msgBox.classList.remove("d-none");
}
function clearMsg() {
  msgBox.classList.add("d-none");
  msgBox.innerHTML = "";
}

function uniqNums(rows, key) {
  const set = new Set();
  rows.forEach(r => { if (r && r[key] != null && r[key] !== "") set.add(r[key]); });
  return Array.from(set).sort((a,b) => Number(a) - Number(b));
}
function setOptions(sel, values) {
  const keepFirst = sel.querySelector('option');
  sel.innerHTML = "";
  sel.appendChild(keepFirst);
  values.forEach(v => {
    const opt = document.createElement('option');
    opt.value = v;
    opt.textContent = String(v);
    sel.appendChild(opt);
  });
}

function fillSelectWithObjects(sel, rows, firstLabel) {
  sel.innerHTML = "";
  const first = document.createElement("option");
  first.value = "";
  first.textContent = firstLabel;
  sel.appendChild(first);

  rows.forEach(r => {
    const opt = document.createElement("option");
    opt.value = r.id;
    opt.textContent = `${r.name} (id: ${r.id})`;
    sel.appendChild(opt);
  });
}


async function loadMeta() {
  clearMsg();
  spinMeta.classList.remove("d-none");
  metaHint.textContent = "Loading shipment types…";

  const { res, payload } = await fetchJSON(API.shipmentTypes);

  debugBox.textContent = JSON.stringify(payload, null, 2);
  spinMeta.classList.add("d-none");

  if (!res.ok) {
    metaHint.textContent = "Failed to load shipment types.";
    showMsg("bad", `<strong>Failed:</strong> HTTP ${res.status}<br><small>See debug box.</small>`);
    return;
  }

  const rows = payload?.results || [];
  if (!rows.length) {
    metaHint.textContent = "No shipment types found.";
    return;
  }

  // Populate Shipment Type and Mode of Transport using same endpoint for now
  fillSelectWithObjects(document.getElementById('selShipmentType'), rows, "Select Shipment Type…");
  fillSelectWithObjects(document.getElementById('selMode'), rows, "Select Mode…");

  // BU: you don’t have a BU endpoint yet. Keep BU empty for now (or we’ll fetch once you give BU endpoint)
  // Alternatively: let BU be manual input (number) if required.
  metaHint.textContent = "Dropdowns ready (Shipment Type + Mode).";
  showMsg("ok", `Loaded Shipment Type + Mode of Transport from Django.`);
}


document.getElementById('btnLoadMeta').addEventListener('click', loadMeta);

function formToObject(form) {
  const fd = new FormData(form);
  const obj = {};
  for (const [k, v] of fd.entries()) {
    if (v === "") continue;
    // convert number-like fields to numbers
    if (['bu','shipment_type','mode_of_transport','shipment_instruction','loading','movement','offloading','storage'].includes(k)) {
      obj[k] = Number(v);
      if (Number.isNaN(obj[k])) obj[k] = v;
    } else {
      obj[k] = v;
    }
  }
  return obj;
}

const spinSubmit = document.getElementById('spinSubmit');
const submitHint = document.getElementById('submitHint');

document.getElementById('formCreate').addEventListener('submit', async (e) => {
  e.preventDefault();
  clearMsg();

  spinSubmit.classList.remove("d-none");
  submitHint.textContent = "Posting…";

  const body = formToObject(e.target);

  const { res, payload } = await fetchJSON(API.createShipmentDjango, {
    method: "POST",
    body
  });

  debugBox.textContent = JSON.stringify(payload, null, 2);
  console.log("Response:", res, payload);

  spinSubmit.classList.add("d-none");

  if (!res.ok) {
    submitHint.textContent = "";
    // Django DRF often returns field errors as object
    showMsg("bad", `<strong>Create failed:</strong> HTTP ${res.status}<br><small>Check required fields in the response below.</small>`);
    return;
  }

  submitHint.textContent = "";
  showMsg("ok", `<strong>Created!</strong> Shipment saved. You can go back to Shipments list.`);
});
</script>

</body>
</html> 
