@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        'SREA' => backpack_url('dashboard'),
        'Live Map' => false,
    ];
@endphp

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #liveMap { height: 560px; border-radius: 10px; z-index: 1; }
    .brgy-badge {
        background: #4C6FFF; color: #fff; border-radius: 50%;
        width: 26px; height: 26px; display: flex; align-items: center;
        justify-content: center; font-weight: bold; font-size: 13px;
        border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.3);
    }
    .brgy-badge.zero { background: #8A8F98; }
    .incident-pin {
        width: 16px; height: 16px; border-radius: 50%;
        border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4);
    }
    .responder-pin {
        width: 16px; height: 16px; border-radius: 50%;
        border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4);
    }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .map-layer-toggle .btn.active { background:#1CA97B; color:#fff; border-color:#1CA97B; }
</style>

<div class="d-flex justify-content-between align-items-start mb-1">
    <div>
        <h2 class="mb-1">Live Map</h2>
        <div class="text-muted">Real-time incident & responder locations</div>
    </div>
    <div class="text-success small fw-bold">● LIVE TRACKING</div>
</div>

<div class="row mt-3">
    <div class="col-lg-8">
        <div class="btn-group btn-group-sm map-layer-toggle mb-2" id="layerToggle">
            <button type="button" class="btn btn-outline-secondary active" data-layer="all">All</button>
            <button type="button" class="btn btn-outline-secondary" data-layer="incidents">Incidents</button>
            <button type="button" class="btn btn-outline-secondary" data-layer="responders">Responders</button>
            <button type="button" class="btn btn-outline-secondary" data-layer="barangays">Barangays</button>
        </div>
        <div id="liveMap"></div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Legend</h5>
                <div class="mb-2"><span class="legend-dot" style="background:#D63939;"></span> Critical incident</div>
                <div class="mb-2"><span class="legend-dot" style="background:#F59F00;"></span> Medium incident</div>
                <div class="mb-2"><span class="legend-dot" style="background:#F76707;"></span> High incident</div>
                <div class="mb-2"><span class="legend-dot" style="background:#4C6FFF;"></span> Responder — En Route</div>
                <div><span class="legend-dot" style="background:#2FB344;"></span> Responder — Deployed</div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Active on Map</h5>
                @foreach ($incidents as $incident)
                    <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2">
                        <span class="legend-dot" style="background:{{ $incident['level'] === 'critical' ? '#D63939' : ($incident['level'] === 'high' ? '#F76707' : '#F59F00') }};"></span>
                        <span class="small">{{ $incident['id'] }} — {{ $incident['type'] }} · {{ $incident['barangay'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const barangays = @json($barangays);
    const incidents = @json($incidents);

    const map = L.map('liveMap').setView([14.998, 120.953], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    const brgyLookup = {};
    barangays.forEach(b => brgyLookup[b.name] = b);

    const levelColors = { critical: '#D63939', high: '#F76707', medium: '#F59F00', low: '#2FB344' };

    // --- Layer: barangay incident-count pins ---
    const barangayLayer = L.layerGroup();
    barangays.forEach(b => {
        const icon = L.divIcon({
            className: '',
            html: `<div class="brgy-badge ${b.incident_count === 0 ? 'zero' : ''}">${b.incident_count}</div>`,
            iconSize: [26, 26],
            iconAnchor: [13, 13]
        });
        L.marker([b.lat, b.lng], { icon: icon })
            .bindTooltip(`${b.name} — ${b.incident_count} incident(s)`)
            .addTo(barangayLayer);
    });

    // --- Layer: individual incident pins (small offset from barangay center) ---
    const incidentLayer = L.layerGroup();
    incidents.forEach((inc, i) => {
        const brgy = brgyLookup[inc.barangay];
        if (!brgy) return;
        const offsetLat = brgy.lat + 0.004 * Math.cos(i);
        const offsetLng = brgy.lng + 0.004 * Math.sin(i);
        const color = levelColors[inc.level] || '#4C6FFF';
        const icon = L.divIcon({
            className: '',
            html: `<div class="incident-pin" style="background:${color};"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        L.marker([offsetLat, offsetLng], { icon: icon })
            .bindTooltip(`${inc.id} — ${inc.type} (${inc.level})`)
            .addTo(incidentLayer);
    });

    // --- Layer: responder pins (from incidents with an assigned responder) ---
    const responderLayer = L.layerGroup();
    incidents.forEach((inc, i) => {
        if (!inc.assigned) return;
        const brgy = brgyLookup[inc.barangay];
        if (!brgy) return;
        const offsetLat = brgy.lat - 0.003 * Math.sin(i);
        const offsetLng = brgy.lng - 0.003 * Math.cos(i);
        const status = (inc.assigned.current_status || '').toLowerCase();
        const color = status.includes('scene') || status.includes('deployed') ? '#2FB344' : '#4C6FFF';
        const icon = L.divIcon({
            className: '',
            html: `<div class="responder-pin" style="background:${color};"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        L.marker([offsetLat, offsetLng], { icon: icon })
            .bindTooltip(`${inc.assigned.name} — ${inc.assigned.current_status}`)
            .addTo(responderLayer);
    });

    // Default: show all
    barangayLayer.addTo(map);
    incidentLayer.addTo(map);
    responderLayer.addTo(map);

    document.querySelectorAll('#layerToggle button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#layerToggle button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            map.removeLayer(barangayLayer);
            map.removeLayer(incidentLayer);
            map.removeLayer(responderLayer);

            const layer = btn.dataset.layer;
            if (layer === 'all' || layer === 'barangays') barangayLayer.addTo(map);
            if (layer === 'all' || layer === 'incidents') incidentLayer.addTo(map);
            if (layer === 'all' || layer === 'responders') responderLayer.addTo(map);
        });
    });
</script>
@endsection