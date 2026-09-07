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
    .incident-pin-photo { width:32px; height:32px; border-radius:50%; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.4); cursor:pointer; background-size:cover; background-position:center; position:relative; }
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
    .also-reported-item { border-bottom:1px solid #EEF0F4; padding:6px 0; font-size:13px; }
    .escalation-note { background:#FFF3CD; border:1px solid #F0C36D; border-radius:8px; padding:10px; font-size:13px; margin-bottom:10px; }
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
                <div class="mb-2">🖼️ Photo attached &nbsp; 🎥 Video attached &nbsp; 🔴 No media</div>
                <div>🚑 Ambulance &nbsp; 🚒 Fire Truck &nbsp; 🚐 Rescue Van</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Active on Map</h5>
                @foreach ($incidents as $incident)
                    <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2">
                        <span class="small">#{{ $incident->id }} — {{ $incident->type }} · {{ $incident->barangay }}</span>
                    </div>
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

@vite('resources/js/app.js')
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

    const vehicleIcons = { ambulance:'🚑', 'fire truck':'🚒', 'rescue van':'🚐' };
    function vehicleEmoji(vehicleLabel) {
        if (!vehicleLabel) return '🚓';
        const key = Object.keys(vehicleIcons).find(k => vehicleLabel.toLowerCase().includes(k));
        return key ? vehicleIcons[key] : '🚓';
    }

    const barangayLayer = L.layerGroup();
    barangays.forEach(b => {
        const icon = L.divIcon({ className:'', html:`<div class="brgy-badge ${b.incident_count === 0 ? 'zero' : ''}">${b.incident_count}</div>`, iconSize:[34,34], iconAnchor:[17,17] });
        L.marker([b.lat, b.lng], { icon }).bindTooltip(`${b.name} — ${b.incident_count} incident(s)`).addTo(barangayLayer);
    });

    const incidentLayer = L.layerGroup();
    incidents.forEach((inc, i) => {
        let lat, lng;
        if (inc.latitude && inc.longitude) {
            lat = inc.latitude;
            lng = inc.longitude;
        } else {
            const brgy = brgyLookup[inc.barangay];
            if (!brgy) return;
            lat = brgy.lat + 0.004 * Math.cos(i);
            lng = brgy.lng + 0.004 * Math.sin(i);
        }
        const badgeHtml = inc.nearby_count > 0 ? `<div class="report-count-badge">${inc.nearby_count + 1}</div>` : '';

        let pinHtml;
        if (inc.photo_path) {
            pinHtml = `<div class="incident-pin-photo" style="background-image:url('${inc.photo_path}');">${badgeHtml}</div>`;
        } else if (inc.video_path) {
            pinHtml = `<div class="incident-pin">🎥${badgeHtml}</div>`;
        } else {
            pinHtml = `<div class="incident-pin">🔴${badgeHtml}</div>`;
        }

        const icon = L.divIcon({ className:'', html: pinHtml, iconSize:[32,32], iconAnchor:[16,16] });
        const tooltipText = inc.nearby_count > 0 ? `#${inc.id} — ${inc.type} (⚠️ ${inc.nearby_count} nearby)` : `#${inc.id} — ${inc.type}`;
        const marker = L.marker([lat, lng], { icon: icon }).bindTooltip(tooltipText).addTo(incidentLayer);
        marker.on('click', () => openIncident(inc, { lat, lng }));
    });

    const responderLayer = L.layerGroup();
    incidents.forEach((inc, i) => {
        if (!inc.assigned_to) return;
         const profile = inc.assigned_to.responder_profile;
        if (!profile || !profile.current_latitude || !profile.current_longitude) return;
        const lat = profile.current_latitude;
        const lng = profile.current_longitude;
        const vehicle = inc.assigned_to.responder_profile?.vehicle ?? '';
        const status = inc.assigned_to.responder_profile?.current_status ?? '';
        const color = status === 'Deployed' ? '#2FB344' : '#4C6FFF';
        const emoji = vehicleEmoji(vehicle);
        const icon = L.divIcon({ className:'', html:`<div class="responder-pin" style="background:${color};">${emoji}</div>`, iconSize:[32,32], iconAnchor:[16,16] });
        const marker = L.marker([lat, lng], { icon }).bindTooltip(inc.assigned_to.name).addTo(responderLayer);
        marker.on('click', () => openResponder(inc.assigned_to, inc));
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
        document.getElementById('incModalTitle').textContent = `#${inc.id} — ${inc.type}`;

        const countBadgeHtml = inc.nearby_count > 0 ? `<div class="media-count-badge">${inc.nearby_count + 1}</div>` : '';

        let mediaInner = '';
        if (inc.photo_path) {
            mediaInner = `<img src="${inc.photo_path}" class="media-banner" alt="Incident photo">`;
        } else if (inc.video_path) {
            mediaInner = `<video src="${inc.video_path}" class="media-banner" controls></video>`;
        } else {
            mediaInner = `<div class="media-placeholder">📷 No photo or video attached</div>`;
        }
        document.getElementById('incMediaBanner').innerHTML = `<div class="media-banner-wrap">${mediaInner}${countBadgeHtml}</div>`;

        let escalationHtml = '';
        if (inc.status === 'Escalated' && inc.escalation_reason) {
            escalationHtml = `<div class="escalation-note">⚠ <strong>Escalated</strong> by ${inc.escalated_by?.name ?? 'a responder'}<br>${inc.escalation_reason}</div>`;
        }

        document.getElementById('incModalBody').innerHTML = `
            <div class="bg-light rounded p-2 mb-2">
                <strong>${inc.barangay}</strong><br>
                <small class="text-muted">Reporter: ${inc.reporter_name ?? 'Anonymous'}<br>Address: ${inc.address ?? '-'}<br>Status: ${inc.status}</small>
            </div>
            <div class="small mb-2"><strong>Description</strong><br>${inc.description ?? '-'}</div>
            ${escalationHtml}
            ${inc.assigned_to ? `<div class="small mb-2">Assigned: <strong>${inc.assigned_to.name}</strong></div>` : '<div class="small text-muted mb-2">No responder assigned yet.</div>'}
            ${inc.responder_notes ? `<div class="small mb-2"><strong>Notes</strong><br>${inc.responder_notes}</div>` : ''}
            <div class="small text-muted">⚠️ ${inc.nearby_count} nearby report(s) of the same type</div>
        `;

        openModal('incidentModal');

        if (inc.assigned_to) {
            drawRoute(townCenter, incidentLatLng);
        }
    }

    function openResponder(responder, inc) {
        document.getElementById('respModalBody').innerHTML = `
            <div class="mb-1"><strong>${responder.name}</strong></div>
            <div class="small text-muted mb-2">${responder.responder_profile?.team ?? '-'} · ${responder.responder_profile?.vehicle ?? '-'}</div>
            <div class="small mb-1">Status: <strong>${responder.responder_profile?.current_status ?? '-'}</strong></div>
            <div class="small">Assigned Incident: <strong>#${inc.id}</strong> — ${inc.type} @ ${inc.barangay}</div>
        `;
        openModal('responderModal');
    }

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

    // --- Real-time: listen for new incidents via Reverb/Echo ---
    if (window.Echo) {
        window.Echo.channel('live-map')
            .listen('.incident.reported', (e) => {
                const inc = e.incident;
                showAlertBanner('New Incident Reported', `#${inc.id} — ${inc.type} in ${inc.barangay} just came in.`);

                if (inc.latitude && inc.longitude) {
                    const icon = L.divIcon({ className:'', html:`<div class="incident-pin">🔴</div>`, iconSize:[32,32], iconAnchor:[16,16] });
                    const marker = L.marker([inc.latitude, inc.longitude], { icon }).bindTooltip(`#${inc.id} — ${inc.type} (new)`).addTo(incidentLayer);
                    marker.on('click', () => openIncident(inc, { lat: inc.latitude, lng: inc.longitude }));
                }
            });
    }
</script>
@endsection