@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Live Map' => false];
@endphp

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #liveMap { height: 560px; border-radius: 10px; z-index: 1; }
    .brgy-badge { background:#4C6FFF; color:#fff; border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.3); }
    .brgy-badge.zero { background:#8A8F98; }
    .incident-pin { width:32px; height:32px; border-radius:50%; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; font-size:16px; cursor:pointer; background:#E0554F; color:#fff; position:relative; }
    .responder-pin { width:32px; height:32px; border-radius:50%; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; font-size:16px; cursor:pointer; }
    .report-count-badge { position:absolute; top:-6px; right:-6px; background:#D63939; color:#fff; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; border:2px solid #fff; font-weight:bold; }
    .legend-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:6px; }
    .map-layer-toggle .btn.active { background:#1CA97B; color:#fff; border-color:#1CA97B; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:460px; max-width:90vw; padding:0; max-height:88vh; overflow-y:auto; }
    .modal-box-inner { padding:20px; }
    .media-banner { width:100%; height:200px; object-fit:cover; border-radius:10px 10px 0 0; background:#EEF0F4; }
    .media-banner-wrap { position:relative; }
    .media-count-badge { position:absolute; top:10px; right:10px; background:#D63939; color:#fff; border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.4); }
    .media-placeholder { width:100%; height:140px; background:#EEF0F4; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:center; color:#8A8F98; font-size:13px; }
    .stage-track { display:flex; align-items:center; margin:10px 0; }
    .stage-dot { width:10px; height:10px; border-radius:50%; background:#D9DCE3; flex-shrink:0; }
    .stage-dot.done { background:#2FB344; }
    .stage-dot.current { background:#F59F00; }
    .stage-line { flex:1; height:2px; background:#D9DCE3; }
    .stage-line.done { background:#2FB344; }
    .procedure-item { border-left:2px solid #D9DCE3; padding-left:10px; margin-bottom:8px; font-size:13px; }
    .also-reported-item { border-bottom:1px solid #EEF0F4; padding:6px 0; font-size:13px; }
    #criticalAlertBanner { background:#FDE8E8; border:1px solid #D63939; color:#7A1F1F; border-radius:8px; padding:12px 16px; animation: alertPulse 1.5s ease-in-out infinite; }
    @keyframes alertPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(214,57,57,.4); } 50% { box-shadow: 0 0 0 8px rgba(214,57,57,0); } }
</style>

<div class="d-flex justify-content-between align-items-start mb-1">
    <div>
        <h2 class="mb-1">Live Map</h2>
        <div class="text-muted">Real-time incident & responder locations</div>
    </div>
    <div class="text-success small fw-bold">● LIVE TRACKING</div>
</div>

<div id="criticalAlertBanner" style="display:none;" class="d-flex justify-content-between align-items-center mb-3" role="alert">
    <div class="d-flex align-items-center gap-2">
        <span style="font-size:20px;">🚨</span>
        <div>
            <strong id="alertBannerTitle">Critical Incident Reported</strong>
            <div id="alertBannerSubtitle" class="small"></div>
        </div>
    </div>
    <button class="btn btn-sm btn-light" onclick="dismissAlertBanner()">Dismiss</button>
</div>

<button class="btn btn-sm btn-outline-danger mb-2" onclick="simulateCriticalAlert()">🔔 Simulate Critical Alert (Demo)</button>

<div class="row mt-3">
    <div class="col-lg-8">
        <div class="btn-group btn-group-sm map-layer-toggle mb-2" id="layerToggle">
            <button type="button" class="btn btn-outline-secondary active" data-layer="all">All</button>
            <button type="button" class="btn btn-outline-secondary" data-layer="incidents">Incidents</button>
            <button type="button" class="btn btn-outline-secondary" data-layer="responders">Responders</button>
            <button type="button" class="btn btn-outline-secondary" data-layer="barangays">Barangays</button>
        </div>
        <div style="position:relative;">
            <div id="liveMap"></div>
            <button id="clearRouteBtn" class="btn btn-sm btn-danger" style="display:none; position:absolute; top:12px; right:60px; z-index:1000;" onclick="clearRoute()">✕ Clear Route</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Legend</h5>
                <div class="mb-2">🔴 Incident report (with report count)</div>
                <div>🚑 Ambulance &nbsp; 🚒 Fire Truck &nbsp; 🚐 Rescue Van</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Active on Map</h5>
                @foreach ($incidents as $incident)
                    @if (!$incident['is_duplicate'])
                        <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2">
                            <span class="small">{{ $incident['id'] }} — {{ $incident['type'] }} · {{ $incident['barangay'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Incident Modal --}}
<div class="modal-backdrop-custom" id="incidentModal">
    <div class="modal-box">
        <div id="incMediaBanner"></div>
        <div class="modal-box-inner">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h4 id="incModalTitle">Incident Details</h4>
                <button class="btn-close" onclick="closeModal('incidentModal')"></button>
            </div>
            <div id="incModalBody"></div>
            <button class="btn btn-light mt-2" onclick="closeModal('incidentModal'); clearRoute();">Close</button>
        </div>
    </div>
</div>

{{-- Responder Modal --}}
<div class="modal-backdrop-custom" id="responderModal">
    <div class="modal-box">
        <div class="modal-box-inner">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h4>Responder Details</h4>
                <button class="btn-close" onclick="closeModal('responderModal')"></button>
            </div>
            <div id="respModalBody"></div>
            <button class="btn btn-light mt-2" onclick="closeModal('responderModal')">Close</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const barangays = @json($barangays);
    const incidents = @json($incidents);
    const responders = @json($responders);

    const map = L.map('liveMap').setView([14.998, 120.953], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 18
    }).addTo(map);

    const brgyLookup = {};
    barangays.forEach(b => brgyLookup[b.name] = b);
    const townCenter = { lat: 14.9985, lng: 120.9532 };

    function locationToCoords(locationLabel) {
        if (!locationLabel) return townCenter;
        if (locationLabel.toLowerCase().includes('mun. hall') || locationLabel.toLowerCase().includes('municipal')) return townCenter;
        const match = brgyLookup[locationLabel];
        return match ? { lat: match.lat, lng: match.lng } : townCenter;
    }

    const vehicleIcons = { ambulance:'🚑', 'fire truck':'🚒', 'rescue van':'🚐' };
    function vehicleEmoji(teamLabel) {
        if (!teamLabel) return '🚓';
        const key = Object.keys(vehicleIcons).find(k => teamLabel.toLowerCase().includes(k));
        return key ? vehicleIcons[key] : '🚓';
    }

    // --- Barangay layer ---
    const barangayLayer = L.layerGroup();
    barangays.forEach(b => {
        const icon = L.divIcon({ className:'', html:`<div class="brgy-badge ${b.incident_count === 0 ? 'zero' : ''}">${b.incident_count}</div>`, iconSize:[34,34], iconAnchor:[17,17] });
        L.marker([b.lat, b.lng], { icon }).bindTooltip(`${b.name} — ${b.incident_count} incident(s)`).addTo(barangayLayer);
    });

    // --- Incident layer: ONE pin per primary incident, count badge = 1 + duplicates ---
    const incidentLayer = L.layerGroup();
    const primaryIncidents = incidents.filter(i => !i.is_duplicate);

    primaryIncidents.forEach((inc, i) => {
        const brgy = brgyLookup[inc.barangay];
        if (!brgy) return;
        const lat = brgy.lat + 0.004 * Math.cos(i);
        const lng = brgy.lng + 0.004 * Math.sin(i);
        const dupCount = incidents.filter(x => x.is_duplicate && x.duplicate_of === inc.id).length;
        const totalReports = 1 + dupCount;

        const badgeHtml = totalReports > 1 ? `<div class="report-count-badge">${totalReports}</div>` : '';
        const icon = L.divIcon({ className:'', html:`<div class="incident-pin">🔴${badgeHtml}</div>`, iconSize:[32,32], iconAnchor:[16,16] });
        const marker = L.marker([lat, lng], { icon }).bindTooltip(`${inc.id} — ${inc.type} (${totalReports} report${totalReports > 1 ? 's' : ''})`).addTo(incidentLayer);
        marker.on('click', () => openIncident(inc, { lat, lng }));
    });

    // --- Responder layer (vehicle icons only) ---
    const responderLayer = L.layerGroup();
    primaryIncidents.forEach((inc, i) => {
        if (!inc.assigned) return;
        const brgy = brgyLookup[inc.barangay];
        if (!brgy) return;
        const lat = brgy.lat - 0.003 * Math.sin(i);
        const lng = brgy.lng - 0.003 * Math.cos(i);
        const emoji = vehicleEmoji(inc.assigned.team);
        const icon = L.divIcon({ className:'', html:`<div class="responder-pin" style="background:#4C6FFF;">${emoji}</div>`, iconSize:[32,32], iconAnchor:[16,16] });
        const marker = L.marker([lat, lng], { icon }).bindTooltip(inc.assigned.name).addTo(responderLayer);
        marker.on('click', () => openResponder(inc.assigned, inc));
    });

    barangayLayer.addTo(map); incidentLayer.addTo(map); responderLayer.addTo(map);

    document.querySelectorAll('#layerToggle button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#layerToggle button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            map.removeLayer(barangayLayer); map.removeLayer(incidentLayer); map.removeLayer(responderLayer);
            const layer = btn.dataset.layer;
            if (layer === 'all' || layer === 'barangays') barangayLayer.addTo(map);
            if (layer === 'all' || layer === 'incidents') incidentLayer.addTo(map);
            if (layer === 'all' || layer === 'responders') responderLayer.addTo(map);
        });
    });

    // --- Route drawing (OSRM) ---
    let routeLayer = null;
    async function drawRoute(fromLatLng, toLatLng) {
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
        const url = `https://router.project-osrm.org/route/v1/driving/${fromLatLng.lng},${fromLatLng.lat};${toLatLng.lng},${toLatLng.lat}?overview=full&geometries=geojson`;
        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.routes && data.routes[0]) {
                routeLayer = L.geoJSON(data.routes[0].geometry, { style: { color:'#1CA97B', weight:4, dashArray:'6,6' } }).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [40, 40] });
                document.getElementById('clearRouteBtn').style.display = 'block';
            }
        } catch (e) { console.warn('Route service unavailable', e); }
    }
    function clearRoute() {
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
        document.getElementById('clearRouteBtn').style.display = 'none';
    }

    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    function openIncident(inc, incidentLatLng) {
        document.getElementById('incModalTitle').textContent = `${inc.id} — ${inc.type}`;

        // Media banner + report count badge
        const dupCountForBadge = incidents.filter(x => x.is_duplicate && x.duplicate_of === inc.id).length;
        const totalReportsForBadge = 1 + dupCountForBadge;
        const countBadgeHtml = totalReportsForBadge > 1 ? `<div class="media-count-badge">${totalReportsForBadge}</div>` : '';

        let mediaInner = '';
        if (inc.media_type === 'photo') {
            mediaInner = `<img src="${inc.media_url}" class="media-banner" alt="Incident photo">`;
        } else if (inc.media_type === 'video') {
            mediaInner = `<video src="${inc.media_url}" class="media-banner" controls></video>`;
        } else {
            mediaInner = `<div class="media-placeholder">📷 No photo or video attached</div>`;
        }
        document.getElementById('incMediaBanner').innerHTML = `<div class="media-banner-wrap">${mediaInner}${countBadgeHtml}</div>`;

        let stageHtml = '';
        if (inc.assigned && inc.stage !== null) {
            const stages = ['Assigned', 'En Route', 'On Scene', 'Resolved'];
            stageHtml = '<div class="stage-track">' + stages.map((s, i) => {
                const dot = `<div class="stage-dot ${i < inc.stage ? 'done' : (i == inc.stage ? 'current' : '')}"></div>`;
                return i < stages.length - 1 ? dot + `<div class="stage-line ${i < inc.stage ? 'done' : ''}"></div>` : dot;
            }).join('') + '</div>';
        }

        let procedureHtml = (inc.procedure_log || []).map(p => `<div class="procedure-item"><strong>${p.time}</strong> — ${p.step}</div>`).join('');

        const dupReports = incidents.filter(x => x.is_duplicate && x.duplicate_of === inc.id);
        let alsoReportedHtml = dupReports.length
            ? dupReports.map(d => `<div class="also-reported-item">👤 ${d.reporter} — ${d.time}</div>`).join('')
            : '<div class="small text-muted">No other reports for this incident.</div>';

        document.getElementById('incModalBody').innerHTML = `
            <div class="bg-light rounded p-2 mb-2">
                <strong>${inc.barangay}</strong><br>
                <small class="text-muted">Reporter: ${inc.reporter} · Contact: ${inc.contact || '-'}<br>Coordinates: ${inc.coordinates || '-'}</small>
            </div>
            ${inc.assigned ? `<div class="small mb-2">Assigned: <strong>${inc.assigned.name}</strong> — ${inc.assigned.team}</div>${stageHtml}` : '<div class="small text-muted mb-2">No responder assigned yet.</div>'}
            <h6 class="mt-3">Also Reported By (${dupReports.length})</h6>
            ${alsoReportedHtml}
            <h6 class="mt-3">Procedure Log</h6>
            ${procedureHtml || '<div class="small text-muted">No log entries yet.</div>'}
        `;

        openModal('incidentModal');

        if (inc.assigned) {
            const startCoords = locationToCoords(inc.assigned.location || inc.barangay);
            drawRoute(startCoords, incidentLatLng);
        }
    }

    function openResponder(assigned, inc) {
        document.getElementById('respModalBody').innerHTML = `
            <div class="mb-1"><strong>${assigned.name}</strong></div>
            <div class="small text-muted mb-2">${assigned.team}</div>
            <div class="small mb-1">Status: <strong>${assigned.current_status}</strong></div>
            <div class="small mb-1">Location: ${assigned.location}</div>
            <div class="small">Assigned Incident: <strong>${inc.id}</strong> — ${inc.type} @ ${inc.barangay}</div>
        `;
        openModal('responderModal');
    }

    // --- Critical alert banner + siren ---
    let audioCtx;
    function playSiren() {
        audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
        const now = audioCtx.currentTime;
        for (let i = 0; i < 3; i++) {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now + i * 0.6);
            osc.frequency.linearRampToValueAtTime(660, now + i * 0.6 + 0.3);
            gain.gain.setValueAtTime(0.15, now + i * 0.6);
            gain.gain.linearRampToValueAtTime(0, now + i * 0.6 + 0.5);
            osc.connect(gain).connect(audioCtx.destination);
            osc.start(now + i * 0.6);
            osc.stop(now + i * 0.6 + 0.5);
        }
    }
    function showAlertBanner(title, subtitle) {
        document.getElementById('alertBannerTitle').textContent = title;
        document.getElementById('alertBannerSubtitle').textContent = subtitle;
        document.getElementById('criticalAlertBanner').style.display = 'flex';
        playSiren();
    }
    function dismissAlertBanner() { document.getElementById('criticalAlertBanner').style.display = 'none'; }
    function simulateCriticalAlert() {
        showAlertBanner('Critical Incident Reported', 'New critical flood incident in Banca-Banca — immediate response required.');
    }

    const existingCritical = primaryIncidents.find(i => i.status !== 'resolved' && i.id === 'INC-001');
    if (existingCritical) {
        showAlertBanner('Active Incident', `${existingCritical.id} — ${existingCritical.type} in ${existingCritical.barangay} is still unresolved.`);
    }
</script>
@endsection