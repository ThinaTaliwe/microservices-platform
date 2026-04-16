<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>BFRN — Data Hub</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>

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
    body { background: var(--bg); color: var(--text); }
    .glass { background: var(--panel); border: 1px solid var(--border); border-radius: 14px; }
    .btn-glass {
      border: 1px solid rgba(255, 255, 255, .18);
      background: rgba(255, 255, 255, .06);
      color: #fff;
      border-radius: 10px;
    }
    [data-theme="light"] .btn-glass { color: var(--btn-text); background: rgba(0,0,0,.05); border-color: var(--border); }
    .btn-glass:hover { background: rgba(255, 255, 255, .10); color: #fff; }
    [data-theme="light"] .btn-glass:hover { background: rgba(0,0,0,.10); }
    .form-control, .form-select {
      background: var(--input-bg) !important;
      border-color: var(--border) !important;
      color: var(--text) !important;
    }
    .muted { color: var(--muted); }
    pre.json-box {
      background: rgba(0,0,0,.35);
      border: 1px solid rgba(255,255,255,.10);
      border-radius: 12px;
      padding: 12px;
      max-height: 420px;
      overflow: auto;
      color: #eaeaf2;
      font-size: 13px;
      white-space: pre;
    }
    [data-theme="light"] pre.json-box { background: var(--json-bg); color: #212529; border-color: var(--border); }
    .json-key { color: rgba(234,234,242,.92); font-weight: 650; }
    .json-str { color: #9ad1ff; }
    .json-num { color: #ffd48a; }
    .json-bool { color: #b9a6ff; font-weight: 650; }
    .json-null { color: rgba(255,255,255,.55); font-style: italic; }

    .tab-card { cursor:pointer; transition: .15s; }
    .tab-card:hover { transform: translateY(-1px); background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); }

    .topbar {
      position: sticky; top: 0; z-index: 10;
      backdrop-filter: blur(10px);
      background: rgba(10,10,14,.65);
      border-bottom: 1px solid var(--border);
    }
    .badge-soft { border: 1px solid var(--border); background: rgba(255,255,255,.08); font-weight: 600; }
    .badge-ok { color: var(--ok); }
    .badge-bad { color: var(--bad); }

    /* Tables: keep consistent in dark/light */
    table { color: var(--text); }
    [data-theme="light"] table { color: var(--text); }
    thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
    code { color: rgba(234, 234, 242, .9); }
    [data-theme="light"] code { color: #212529; }
  </style>
</head>

<body>
  <div class="topbar bg-white">
    <div class="container-fluid px-3 px-lg-4 py-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <div class="fw-semibold" style="font-size: 18px;">BFRN — Data Hub</div>
          {{-- <div class="muted" style="font-size: 13px;">Ports • Shipping Lines • Clearing Agents • (CMA-CGM public T&T helper)</div> --}}
        </div>
        <div class="d-flex gap-2 align-items-center">
          <button class="btn btn-glass" id="btnTheme">Toggle Theme</button>
          <span class="badge badge-soft text-succes px-3 py-2" id="statusGlobal">Idle</span>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid px-3 px-lg-4 py-4">
    <div class="row g-3">
      <div class="col-12">
        <div class="glass p-3 p-lg-4">
          <ul class="nav nav-pills gap-2" id="mainTabs">
            <li class="nav-item"><button class="btn btn-glass active" data-tab="ports">Ports</button></li>
            <li class="nav-item"><button class="btn btn-glass" data-tab="lines">Shipping Lines </button></li>
            <li class="nav-item"><button class="btn btn-glass" data-tab="agents">Clearing Agents </button></li>
          </ul>
          {{-- <div class="muted mt-3" style="font-size: 12px;">
            Data is refreshed by cron using <code>curl</code> and stored locally as JSON (or served by your Laravel endpoints). Search runs in-browser.
          </div> --}}
        </div>
      </div>

      <!-- PORTS TAB -->
      <div class="col-12 tab-panel" id="tab-ports">
        <div class="glass p-3 p-lg-4 mb-3">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
              <h4 class="mb-1">Ports</h4>
              <div class="muted">
                From: <strong>UN/LOCODE JSON</strong>.
                Try: “Durban”, “CPT”.
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <button class="btn btn-glass" id="portsReload">Reload</button>
              <button class="btn btn-glass" id="portsDebugBtn">Debug JSON</button>
              <span class="badge badge-soft text-success px-3 py-2" id="portsStatus">Idle</span>
            </div>
          </div>
          <div class="row g-2 mt-3">
            <div class="col-12 col-lg-6">
              <input class="form-control" id="portsQ" placeholder="Search any field...">
            </div>
            {{-- <div class="col-12 col-lg-6 d-flex justify-content-lg-end align-items-center gap-2">
              <div class="muted">Source:</div>
              <code id="portsSrc">auto</code>
            </div> --}}
          </div>
        </div>

        <div class="glass p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div><strong>Results:</strong> <span id="portsCount">0</span></div>
            {{-- <div class="muted" style="font-size: 12px;">Tip: keep query short (2–6 chars) for speed.</div> --}}
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <!-- UPDATED for OurAirports-compatible view -->
                <tr>
                  <th>Country</th>
                  <th>Code</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>City</th>
                  <th>Region</th>
                </tr>
              </thead>
              <tbody id="portsBody"></tbody>
            </table>
          </div>

          <div class="mt-3" id="portsDebug" style="display:none;">
            <div class="fw-semibold mb-2">Raw JSON (first 30 rows)</div>
            <pre class="json-box mb-0" id="portsRaw">{}</pre>
          </div>
        </div>
      </div>

      <!-- LINES TAB -->
      <div class="col-12 tab-panel" id="tab-lines" style="display:none;">
        <div class="glass p-3 p-lg-4 mb-3">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
              <h4 class="mb-1">Shipping Lines</h4>
              <div class="muted">Search carrier name (e.g. “MAEU”).</div>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <button class="btn btn-glass" id="linesReload">Reload</button>
              <button class="btn btn-glass" id="linesDebugBtn">Debug JSON</button>
              <span class="badge badge-soft text-success px-3 py-2" id="linesStatus">Idle</span>
            </div>
          </div>
          <div class="row g-2 mt-3">
            <div class="col-12 col-lg-6">
              <input class="form-control" id="linesQ" placeholder="Search any field...">
            </div>
            <div class="col-12 col-lg-6 d-flex justify-content-lg-end align-items-center gap-2">
              {{-- <div class="muted">Source:</div>
              <code id="linesSrc">auto</code> --}}
            </div>
          </div>
        </div>

        <div class="glass p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div><strong>Results:</strong> <span id="linesCount">0</span></div>
            {{-- <div class="muted" style="font-size: 12px;">Merged from multiple free SCAC datasets.</div> --}}
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>SCAC</th>
                  <th>Name</th>
                  <th>Source</th>
                </tr>
              </thead>
              <tbody id="linesBody"></tbody>
            </table>
          </div>

          <div class="mt-3" id="linesDebug" style="display:none;">
            <div class="fw-semibold mb-2">Raw JSON (first 60 rows)</div>
            <pre class="json-box mb-0" id="linesRaw">{}</pre>
          </div>
        </div>
      </div>

      <!-- AGENTS TAB -->
      <div class="col-12 tab-panel" id="tab-agents" style="display:none;">
        <div class="glass p-3 p-lg-4 mb-3">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
              <h4 class="mb-1">Clearing Agents</h4>
              <div class="muted">Customs Brokers and other logistics entities.</div>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <button class="btn btn-glass" id="agentsReload">Reload</button>
              <button class="btn btn-glass" id="agentsDebugBtn">Debug JSON</button>
              <span class="badge badge-soft text-success px-3 py-2" id="agentsStatus">Idle</span>
            </div>
          </div>
          <div class="row g-2 mt-3">
            <div class="col-12 col-lg-6">
              <input class="form-control" id="agentsQ" placeholder="Search any field...">
            </div>
            <div class="col-12 col-lg-6 d-flex justify-content-lg-end align-items-center gap-2">
              {{-- <div class="muted">Source:</div>
              <code id="agentsSrc">auto</code> --}}
            </div>
          </div>
        </div>

        <div class="glass p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div><strong>Results:</strong> <span id="agentsCount">0</span></div>
            {{-- <div class="muted" style="font-size: 12px;">Use this as a base directory (you can refine later).</div> --}}
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Kind</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Source</th>
                </tr>
              </thead>
              <tbody id="agentsBody"></tbody>
            </table>
          </div>

          <div class="mt-3" id="agentsDebug" style="display:none;">
            <div class="fw-semibold mb-2">Raw JSON (first 60 rows)</div>
            <pre class="json-box mb-0" id="agentsRaw">{}</pre>
          </div>
        </div>
      </div>

      <!-- CMA-CGM PANEL (optional helper) -->
      <div class="col-12">
        <div class="glass p-3 p-lg-4">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
              <h5 class="mb-1">CMA-CGM (track&trace) </h5>
              {{-- <div class="muted">Uses your API Key (<code>keyId</code>) and calls <code>/operation/trackandtrace/v1/events</code>.</div> --}}
            </div>
            <div class="d-flex gap-2 align-items-center">
              <button class="btn btn-glass" id="cmaDebugBtn">Debug JSON</button>
              <span class="badge badge-soft text-success px-3 py-2" id="cmaStatus">Idle</span>
            </div>
          </div>

          <div class="row g-2 mt-3">
            <div class="col-12 col-lg-4">
              <label class="muted" style="font-size:12px;">CMA KeyId</label>
              <input class="form-control" value="ajlolJnAiakAECUZFMnQRRCaMONshiUx" id="cmaKey" placeholder="Paste your CMA keyId" readonly>
            </div>
            <div class="col-12 col-lg-3">
              <label class="muted" style="font-size:12px;">Event Type</label>
              <select class="form-select" id="cmaType">
                <option value="EQUIPMENT" selected>EQUIPMENT</option>
                <option value="TRANSPORT">TRANSPORT</option>
                <option value="SHIPMENT">SHIPMENT</option>
              </select>
            </div>
            <div class="col-12 col-lg-3">
              <label class="muted" style="font-size:12px;">Reference</label>
              <input class="form-control" id="cmaRef" placeholder="equipmentReference or carrierBookingReference">
            </div>
            <div class="col-12 col-lg-2 d-grid">
              <label class="muted" style="font-size:12px;">&nbsp;</label>
              <button class="btn btn-glass" id="cmaRun">Fetch Events</button>
            </div>
          </div>

          <div class="mt-3">
            {{-- <div class="muted" style="font-size:12px;">Endpoint:</div>
            <code>https://apis.cma-cgm.net/operation/trackandtrace/v1/events</code> --}}
          </div>

          <div class="mt-3" id="cmaDebug" style="display:none;">
            <div class="fw-semibold mb-2">Raw JSON</div>
            <pre class="json-box mb-0" id="cmaRaw">{}</pre>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    /* =========================================================
      PRETTY JSON
    ========================================================= */
    function escapeHtml(str) {
      return String(str)
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
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
    function setJson(el, obj) {
      el.innerHTML = syntaxHighlightJson(JSON.stringify(obj, null, 2));
    }

    function setBadgeStatus(elId, txt, ok = true) {
      const el = document.getElementById(elId);
      if (!el) return;
      el.textContent = txt;
      el.classList.remove('badge-ok', 'badge-bad');
      el.classList.add(ok ? 'badge-ok' : 'badge-bad');
    }
    function setGlobal(txt, ok = true) {
      setBadgeStatus('statusGlobal', txt, ok);
    }

    /* =========================================================
      TAB SWITCHING
    ========================================================= */
    document.querySelectorAll('#mainTabs button[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('#mainTabs button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
        document.getElementById('tab-' + tab).style.display = '';
      });
    });

    /* =========================================================
      THEME
    ========================================================= */
    const btnTheme = document.getElementById('btnTheme');
    btnTheme.addEventListener('click', () => {
      const html = document.documentElement;
      html.setAttribute('data-theme', html.getAttribute('data-theme') === 'light' ? 'dark' : 'light');
    });

    /* =========================================================
      FETCH + NORMALIZE
    ========================================================= */
    async function fetchJson(url) {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      const txt = await res.text();
      let payload;
      try { payload = JSON.parse(txt); } catch { payload = { raw: txt }; }
      if (!res.ok) {
        const err = new Error(`HTTP ${res.status}`);
        err.payload = payload;
        throw err;
      }
      return payload;
    }

    function asArray(payload) {
      if (Array.isArray(payload)) return payload;
      if (payload && typeof payload === 'object') {
        for (const k of ['data', 'results', 'items']) {
          if (Array.isArray(payload[k])) return payload[k];
        }
      }
      return [];
    }

    function rowTextBlob(row) {
      try { return JSON.stringify(row).toLowerCase(); } catch { return String(row).toLowerCase(); }
    }

    function filterRows(rows, q) {
      q = (q || '').trim().toLowerCase();
      if (!q) return rows;
      return rows.filter(r => rowTextBlob(r).includes(q));
    }

    function pick(obj, keys) {
      for (const k of keys) {
        if (!obj) continue;
        if (Object.prototype.hasOwnProperty.call(obj, k) && obj[k] !== null && obj[k] !== undefined && String(obj[k]).trim() !== '') {
          return obj[k];
        }
      }
      return null;
    }

    /* =========================================================
      SOURCES
      - Prefer Laravel endpoints (best).
      - Fallback to local JSON files.
    ========================================================= */
    const SOURCES = {
      ports: [
        '/bfrn/api/freehub/ports',
        '../datasets/json/ports_unlocode.json',
        '../datasets/json/ports.json'
      ],
      lines: [
        '/bfrn/api/freehub/lines',
        '../datasets/json/shipping_lines.json'
      ],
      agents: [
        '/bfrn/api/freehub/agents',
        '../datasets/json/clearing_agents.json'
      ]
    };

    async function loadFromAny(candidates, setSrcEl) {
      let lastErr = null;
      for (const url of candidates) {
        try {
          const payload = await fetchJson(url);
          if (setSrcEl) setSrcEl.textContent = url;
          return payload;
        } catch (e) {
          lastErr = e;
          // keep trying fallbacks
        }
      }
      throw lastErr || new Error('No sources available');
    }

    // cached datasets
    let PORTS = [], LINES = [], AGENTS = [];

    /* =========================================================
      PORTS
      - Supports both UN/LOCODE-like keys and OurAirports-like keys.
    ========================================================= */
    async function portsReload() {
      setBadgeStatus('portsStatus', 'Loading…', true);
      try {
        const payload = await loadFromAny(SOURCES.ports, document.getElementById('portsSrc'));
        PORTS = asArray(payload);

        // if the file endpoint returns an object with rows under another key
        if (!PORTS.length && Array.isArray(payload?.rows)) PORTS = payload.rows;

        setBadgeStatus('portsStatus', `OK (${PORTS.length})`, true);
        portsRender();
      } catch (e) {
        setBadgeStatus('portsStatus', e.message || 'Load failed', false);
        setJson(document.getElementById('portsRaw'), e.payload || { error: String(e) });
        document.getElementById('portsCount').textContent = '0';
        document.getElementById('portsBody').innerHTML = `<tr><td colspan="6" class="muted">Failed to load ports.</td></tr>`;
        PORTS = [];
      }
    }

    function normalizePortRow(r) {
      // UN/LOCODE style (your older renderer)
      const un_country = pick(r, ['Country', 'country', 'country_code']);
      const un_loc = pick(r, ['Location', 'location', 'locode', 'unlocode', 'code']);
      const un_name = pick(r, ['Name', 'NameWoDiacritics', 'name', 'location_name']);
      const un_func = pick(r, ['Function', 'function']);
      const un_status = pick(r, ['Status', 'status']);
      const un_iata = pick(r, ['IATA', 'iata']);

      // OurAirports style (ports.json you have now)
      const oa_country = pick(r, ['country', 'iso_country', 'country_code']);
      const oa_name = pick(r, ['name']);
      const oa_type = pick(r, ['type']);
      const oa_city = pick(r, ['municipality', 'city']);
      const oa_region = pick(r, ['region']);
      const oa_iata = pick(r, ['iata']);
      const oa_icao = pick(r, ['icao']);

      const country = un_country || oa_country || '—';
      const code = un_loc || oa_iata || oa_icao || '—';
      const name = un_name || oa_name || '—';
      const type = oa_type || un_func || '—';
      const city = oa_city || '—';
      const region = oa_region || '—';

      return { country, code, name, type, city, region, __raw: r };
    }

    function portsRender() {
      const q = document.getElementById('portsQ').value;
      const body = document.getElementById('portsBody');

      const normalized = PORTS.map(normalizePortRow);
      const filtered = filterRows(normalized.map(x => x.__raw), q);

      // keep stable by mapping filtered raws back into normalized rows
      const filteredSet = new Set(filtered.map(r => r));
      const finalRows = normalized.filter(n => filteredSet.has(n.__raw)).slice(0, 300);

      document.getElementById('portsCount').textContent = String(finalRows.length);

      body.innerHTML = finalRows.length ? finalRows.map(r => `
        <tr class="tab-card">
          <td>${escapeHtml(r.country)}</td>
          <td><code>${escapeHtml(r.code)}</code></td>
          <td>${escapeHtml(r.name)}</td>
          <td>${escapeHtml(r.type)}</td>
          <td>${escapeHtml(r.city)}</td>
          <td>${escapeHtml(r.region)}</td>
        </tr>
      `).join('') : `<tr><td colspan="6" class="muted">No matches.</td></tr>`;

      if (PORTS && PORTS.length) setJson(document.getElementById('portsRaw'), PORTS.slice(0, 30));
    }

    document.getElementById('portsReload').addEventListener('click', portsReload);
    document.getElementById('portsQ').addEventListener('input', portsRender);
    document.getElementById('portsDebugBtn').addEventListener('click', () => {
      const box = document.getElementById('portsDebug');
      box.style.display = (box.style.display === 'none') ? '' : 'none';
    });

    /* =========================================================
      LINES
    ========================================================= */
    async function linesReload() {
      setBadgeStatus('linesStatus', 'Loading…', true);
      try {
        const payload = await loadFromAny(SOURCES.lines, document.getElementById('linesSrc'));
        LINES = asArray(payload);
        setBadgeStatus('linesStatus', `OK (${LINES.length})`, true);
        linesRender();
      } catch (e) {
        setBadgeStatus('linesStatus', e.message || 'Load failed', false);
        setJson(document.getElementById('linesRaw'), e.payload || { error: String(e) });
        document.getElementById('linesCount').textContent = '0';
        document.getElementById('linesBody').innerHTML = `<tr><td colspan="3" class="muted">Failed to load lines.</td></tr>`;
        LINES = [];
      }
    }

    function linesRender() {
      const q = document.getElementById('linesQ').value;
      const body = document.getElementById('linesBody');

      const filtered = filterRows(LINES, q).slice(0, 300);
      document.getElementById('linesCount').textContent = String(filtered.length);

      body.innerHTML = filtered.length ? filtered.map(r => `
        <tr class="tab-card">
          <td><code>${escapeHtml(r.scac || '—')}</code></td>
          <td>${escapeHtml(r.name || '—')}</td>
          <td class="muted">${escapeHtml(r.source || '—')}</td>
        </tr>
      `).join('') : `<tr><td colspan="3" class="muted">No matches.</td></tr>`;

      if (LINES && LINES.length) setJson(document.getElementById('linesRaw'), LINES.slice(0, 60));
    }

    document.getElementById('linesReload').addEventListener('click', linesReload);
    document.getElementById('linesQ').addEventListener('input', linesRender);
    document.getElementById('linesDebugBtn').addEventListener('click', () => {
      const box = document.getElementById('linesDebug');
      box.style.display = (box.style.display === 'none') ? '' : 'none';
    });

    /* =========================================================
      AGENTS
    ========================================================= */
    async function agentsReload() {
      setBadgeStatus('agentsStatus', 'Loading…', true);
      try {
        const payload = await loadFromAny(SOURCES.agents, document.getElementById('agentsSrc'));
        AGENTS = asArray(payload);
        setBadgeStatus('agentsStatus', `OK (${AGENTS.length})`, true);
        agentsRender();
      } catch (e) {
        setBadgeStatus('agentsStatus', e.message || 'Load failed', false);
        setJson(document.getElementById('agentsRaw'), e.payload || { error: String(e) });
        document.getElementById('agentsCount').textContent = '0';
        document.getElementById('agentsBody').innerHTML = `<tr><td colspan="4" class="muted">Failed to load agents.</td></tr>`;
        AGENTS = [];
      }
    }

    function agentsRender() {
      const q = document.getElementById('agentsQ').value;
      const body = document.getElementById('agentsBody');

      const filtered = filterRows(AGENTS, q).slice(0, 300);
      document.getElementById('agentsCount').textContent = String(filtered.length);

      body.innerHTML = filtered.length ? filtered.map(r => `
        <tr class="tab-card">
          <td><span class="badge badge-soft text-success">${escapeHtml(r.kind || '—')}</span></td>
          <td>${escapeHtml(r.name || '—')}</td>
          <td><code>${escapeHtml(r.code || '—')}</code></td>
          <td class="muted">${escapeHtml(r.source || '—')}</td>
        </tr>
      `).join('') : `<tr><td colspan="4" class="muted">No matches.</td></tr>`;

      if (AGENTS && AGENTS.length) setJson(document.getElementById('agentsRaw'), AGENTS.slice(0, 60));
    }

    document.getElementById('agentsReload').addEventListener('click', agentsReload);
    document.getElementById('agentsQ').addEventListener('input', agentsRender);
    document.getElementById('agentsDebugBtn').addEventListener('click', () => {
      const box = document.getElementById('agentsDebug');
      box.style.display = (box.style.display === 'none') ? '' : 'none';
    });

    /* =========================================================
      CMA-CGM (Public)
      NOTE: Browser calls can be blocked by CORS.
      If you already have Laravel proxy working (recommended), we use it.
    ========================================================= */
    const cmaKey = document.getElementById('cmaKey');
    const savedKey = localStorage.getItem('CMA_KEYID') || '';
    if (savedKey) cmaKey.value = savedKey;

    async function cmaRun() {
      const keyId = cmaKey.value.trim();
      const eventType = document.getElementById('cmaType').value;
      const ref = document.getElementById('cmaRef').value.trim();
      if (!keyId) { alert("Paste CMA keyId first"); return; }
      if (!ref) { alert("Provide a reference"); return; }

      localStorage.setItem('CMA_KEYID', keyId);
      setBadgeStatus('cmaStatus', 'Loading…', true);

      // Prefer your backend proxy if available:
      //   /bfrn/api/freehub/cma/events?equipmentReference=...&eventType=EQUIPMENT&limit=10
      // (You showed this endpoint works)
      const proxy = new URL('/bfrn/api/freehub/cma/events', window.location.origin);
      proxy.searchParams.set('eventType', eventType);
      proxy.searchParams.set('limit', '50');

      if (eventType === 'EQUIPMENT') proxy.searchParams.set('equipmentReference', ref);
      else if (eventType === 'SHIPMENT') proxy.searchParams.set('carrierBookingReference', ref);
      else proxy.searchParams.set('transportDocumentReference', ref); // best-effort

      try {
        const payload = await fetchJson(proxy.toString());
        setJson(document.getElementById('cmaRaw'), payload);
        setBadgeStatus('cmaStatus', 'OK', true);
        return;
      } catch (e) {
        // fallback to direct CMA (may be blocked by CORS)
      }

      // Fallback direct (may fail due to CORS)
      const url = new URL("https://apis.cma-cgm.net/operation/trackandtrace/v1/events");
      url.searchParams.set("eventType", eventType);
      url.searchParams.set("limit", "50");
      if (eventType === "EQUIPMENT") url.searchParams.set("equipmentReference", ref);
      else url.searchParams.set("carrierBookingReference", ref);

      const res = await fetch(url.toString(), {
        headers: { "Accept": "application/json", "keyId": keyId }
      });

      const txt = await res.text();
      let payload;
      try { payload = JSON.parse(txt); } catch { payload = { raw: txt }; }

      setJson(document.getElementById('cmaRaw'), payload);
      setBadgeStatus('cmaStatus', res.ok ? 'OK (direct)' : `HTTP ${res.status}`, res.ok);
    }

    document.getElementById('cmaRun').addEventListener('click', cmaRun);
    document.getElementById('cmaDebugBtn').addEventListener('click', () => {
      const box = document.getElementById('cmaDebug');
      box.style.display = (box.style.display === 'none') ? '' : 'none';
    });

    /* =========================================================
      INIT
    ========================================================= */
    (async () => {
      try {
        setGlobal('Loading…', true);
        await portsReload();
        await linesReload();
        await agentsReload();
        setGlobal('Ready', true);
      } catch (e) {
        console.error(e);
        setGlobal('Load failed', false);
      }
    })();
  </script>
</body>
</html>