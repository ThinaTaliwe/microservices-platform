@extends('bfrn.layouts.operations')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #bfrn-shipment-map {
        height: 420px;
        width: 100%;
        border-radius: 1rem;
        background: #f8fafc;
    }

    .map-placeholder {
        min-height: 420px;
        border-radius: 1rem;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, .14), transparent 34%),
            linear-gradient(135deg, #eef4ff, #f8fafc);
        border: 1px solid #dbeafe;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .map-placeholder::before {
        content: "";
        position: absolute;
        inset: 18px;
        border: 1px dashed rgba(37, 99, 235, .28);
        border-radius: .85rem;
        pointer-events: none;
    }

    .map-load-card {
        position: relative;
        max-width: 520px;
        background: rgba(255, 255, 255, .92);
        border: 1px solid #dbeafe;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .10);
    }

    .map-load-icon {
        width: 56px;
        height: 56px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 1.5rem;
        margin-bottom: .9rem;
    }

    .bfrn-map-fullscreen {
        position: fixed !important;
        inset: 16px !important;
        z-index: 3000 !important;
        background: #fff;
        border-radius: 1rem;
        padding: 12px;
        box-shadow: 0 24px 80px rgba(15, 23, 42, .35);
        overflow: auto;
    }

    .bfrn-map-fullscreen #bfrn-shipment-map {
        height: calc(100vh - 220px);
    }
</style>
@endpush

@section('content')

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">All Operations</h1>
    <p class="text-muted mb-0">Real-time operational overview.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="ops-card p-4">
            <div class="text-muted small">Total Shipments</div>
            <div class="display-6 fw-bold">{{ number_format($shipmentCount) }}</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="ops-card p-4">
            <div class="text-muted small">Total Documents</div>
            <div class="display-6 fw-bold">{{ number_format($documentCount) }}</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="ops-card p-4">
            <div class="text-muted small">Business Units</div>
            <div class="display-6 fw-bold">{{ number_format($businessUnitCount) }}</div>
        </div>
    </div>
</div>

<div class="ops-card mb-4" id="bfrn-map-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 border-bottom">
        <div>
            <h2 class="h5 fw-bold mb-1">Shipment Map</h2>
            {{-- <div class="text-muted small">Fast on-demand route view for the selected business unit.</div> --}}
        </div>

        <span class="badge bg-light text-dark border" id="bfrn-map-count">Map paused</span>
    </div>

    <div class="p-4">
        <div id="bfrn-map-placeholder" class="map-placeholder">
            <div>
                <div class="h5 fw-bold mb-2">Load Live Shipment Map</div>
                {{-- <div class="text-muted mb-3">Map data is loaded only when needed for better performance.</div> --}}
                <button type="button" class="btn btn-primary btn-lg" id="bfrn-load-map-btn">
                    <i class="bi bi-map me-1"></i>Load Map
                </button>
            </div>
        </div>

        <div id="bfrn-shipment-map" class="d-none"></div>

        <div id="bfrn-mapped-routes" class="mt-3 d-none border rounded-3 overflow-hidden">
            <div class="px-3 py-2 bg-light fw-semibold small">Mapped Routes</div>
            <div id="bfrn-mapped-routes-body"></div>
        </div>

        <div id="bfrn-missing-coordinates" class="alert alert-warning mt-3 mb-0 d-none">
            <div class="fw-semibold mb-1">Missing coordinates</div>
            <div class="small" id="bfrn-missing-coordinates-text"></div>
        </div>
    </div>
</div>

<div class="ops-card">
    <div class="p-4 border-bottom">
        <h2 class="h5 fw-bold mb-0">Latest Shipments</h2>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>BU</th>
                    <th>Mode</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latestShipments as $shipment)
                <tr>
                    <td>{{ $shipment->id ?? '' }}</td>
                    <td>{{ $shipment->name ?? '' }}</td>
                    <td>{{ $shipment->bu_name ?? '' }}</td>
                    <td>{{ $shipment->mode_name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No shipments found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let mapLoaded = false;
    let map = null;
    let bounds = [];

    const button = document.getElementById('bfrn-load-map-btn');
    const mapElement = document.getElementById('bfrn-shipment-map');
    const placeholder = document.getElementById('bfrn-map-placeholder');
    const countBadge = document.getElementById('bfrn-map-count');
    const routesBox = document.getElementById('bfrn-mapped-routes');
    const routesBody = document.getElementById('bfrn-mapped-routes-body');
    const missingBox = document.getElementById('bfrn-missing-coordinates');
    const missingText = document.getElementById('bfrn-missing-coordinates-text');

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
        });
    }

    async function loadMap() {
        if (mapLoaded) return;

        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Loading...';
        countBadge.textContent = 'Loading map...';

        try {
            const response = await fetch('/bfrn/operations/dashboard/map-data', {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to load map data.');
            }

            mapLoaded = true;
            placeholder.classList.add('d-none');
            mapElement.classList.remove('d-none');
            countBadge.textContent = `${data.mapped_count || 0} mapped shipment(s)`;

            map = L.map(mapElement);
            bounds = [];

            const FullscreenControl = L.Control.extend({
                options: { position: 'topleft' },
                onAdd: function () {
                    const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                    const control = L.DomUtil.create('a', '', container);

                    control.href = '#';
                    control.title = 'Full screen';
                    control.innerHTML = '⛶';
                    control.style.fontSize = '18px';
                    control.style.fontWeight = '700';
                    control.style.textAlign = 'center';

                    L.DomEvent.disableClickPropagation(container);
                    L.DomEvent.on(control, 'click', function (event) {
                        L.DomEvent.preventDefault(event);

                        const card = document.getElementById('bfrn-map-card');
                        card.classList.toggle('bfrn-map-fullscreen');

                        control.innerHTML = card.classList.contains('bfrn-map-fullscreen') ? '×' : '⛶';

                        setTimeout(() => {
                            map.invalidateSize();
                            if (bounds.length) map.fitBounds(bounds, { padding: [30, 30] });
                        }, 250);
                    });

                    return container;
                }
            });

            map.addControl(new FullscreenControl());

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            (data.mapped_shipments || []).forEach((shipment) => {
                const points = [];

                if (shipment.from) {
                    const p = [shipment.from.latitude, shipment.from.longitude];
                    points.push(p);
                    bounds.push(p);

                    L.marker(p).addTo(map).bindPopup(
                        `<strong>${escapeHtml(shipment.name)}</strong><br>Origin: ${escapeHtml(shipment.from.name)}<br>Mode: ${escapeHtml(shipment.mode || '-')}`
                    );
                }

                if (shipment.to) {
                    const p = [shipment.to.latitude, shipment.to.longitude];
                    points.push(p);
                    bounds.push(p);

                    L.marker(p).addTo(map).bindPopup(
                        `<strong>${escapeHtml(shipment.name)}</strong><br>Destination: ${escapeHtml(shipment.to.name)}<br>Mode: ${escapeHtml(shipment.mode || '-')}`
                    );
                }

                if (points.length === 2) {
                    L.polyline(points, { weight: 3 }).addTo(map);
                }
            });

            if (bounds.length) {
                map.fitBounds(bounds, { padding: [30, 30] });
            } else {
                map.setView([-26.2041, 28.0473], 5);
            }

            routesBody.innerHTML = (data.mapped_shipments || []).map(shipment => `
                <div class="px-3 py-2 border-top small d-flex justify-content-between gap-3">
                    <div>
                        <span class="fw-semibold">${escapeHtml(shipment.name)}</span>
                        <span class="text-muted">#${escapeHtml(shipment.id)}</span>
                    </div>
                    <div class="text-muted text-end">
                        ${escapeHtml(shipment.from?.name || 'No origin')} → ${escapeHtml(shipment.to?.name || 'No destination')}
                    </div>
                </div>
            `).join('');

            if ((data.mapped_shipments || []).length) routesBox.classList.remove('d-none');

            if ((data.missing_count || 0) > 0) {
                missingText.textContent = `${data.missing_count} shipment(s) cannot be mapped because their origin/destination addresses do not have coordinates.`;
                missingBox.classList.remove('d-none');
            }

            setTimeout(() => map.invalidateSize(), 200);
        } catch (error) {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-map me-1"></i>Retry Map';
            countBadge.textContent = 'Map failed';
            alert(error.message || 'Unable to load map.');
        }
    }

    button.addEventListener('click', loadMap);
});
</script>
@endpush

@endsection
