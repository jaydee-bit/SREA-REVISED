@extends(backpack_view('layouts.' . (backpack_theme_config('layout') ?? 'vertical')))

@php
	// Merge widgets that were fluently declared with widgets declared without the fluent syntax:
	// - $data['widgets']['before_content']
	// - $data['widgets']['after_content']
	if (isset($widgets)) {
		foreach ($widgets as $section => $widgetSection) {
			foreach ($widgetSection as $key => $widget) {
				\Backpack\CRUD\app\Library\Widget::add($widget)->section($section);
			}
		}
	}
@endphp

@section('before_breadcrumbs_widgets')
	@include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('section', 'before_breadcrumbs')->toArray() ])
@endsection

 @section('after_content_widgets')
        @include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('section', 'after_content')->toArray() ])

        @vite('resources/js/app.js')

        {{--
            Siren + critical alert banner are super-admin-only. MDRRMO
            (super admin) is the one who decides whether to dispatch or
            request assistance, so they need the alert. A barangay admin
            only monitors their own barangay passively via the scoped
            Live Map — no siren, no popup — since acting on an incident
            (requesting assistance) is a separate, explicit workflow for
            them, not something this global alert should push on them.
        --}}
        @if (backpack_user()->is_super_admin)
            @php
                // Every admin page is a full server render, not a single-page
                // app — navigating away destroys the whole JS context, so the
                // in-memory pendingIncidentIds Set can't survive a page change
                // on its own. Re-querying here on every load means whichever
                // page the admin lands on picks the siren back up immediately
                // if anything is still genuinely unaccepted, instead of only
                // reacting to brand-new events fired after that page loaded.
                $globalPendingIncidentIds = \App\Models\Incident::where('status', 'Pending')->pluck('id');
            @endphp

            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <style>
                #criticalAlertBanner { position:fixed; top:16px; right:16px; z-index:1080; max-width:420px; background:#FDE8E8; border:1px solid #D63939; color:#7A1F1F; border-radius:8px; padding:12px 16px; animation: alertPulse 1.5s ease-in-out infinite; }
                @keyframes alertPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(214,57,57,.4); } 50% { box-shadow: 0 0 0 8px rgba(214,57,57,0); } }
            </style>

            <div id="criticalAlertBanner" class="d-none d-flex justify-content-between align-items-center" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:20px;">🚨</span>
                    <div>
                        <strong id="alertBannerTitle">New Incident Reported</strong>
                        <div id="alertBannerSubtitle" class="small"></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-light" onclick="dismissAlertBanner()">Dismiss</button>
            </div>

            <script>
                let audioCtx;

                // One "burst" is the original 3-beep descending tone,
                // ~1.7s long. This used to only ever play once per incident;
                // now it's the building block for a continuous loop instead.
                function playSirenBurst() {
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

                // Tracks every incident currently "unaccepted" — the siren
                // keeps looping as long as this set is non-empty, and only
                // fully stops once the last pending incident is cleared.
                const pendingIncidentIds = new Set();
                let sirenLoopId = null;

                function startSirenLoop() {
                    if (sirenLoopId) return; // already looping — don't stack intervals
                    playSirenBurst();
                    sirenLoopId = setInterval(playSirenBurst, 2000);
                }

                function stopSirenLoop() {
                    if (sirenLoopId) {
                        clearInterval(sirenLoopId);
                        sirenLoopId = null;
                    }
                }

                function updateBannerText() {
                    const count = pendingIncidentIds.size;
                    document.getElementById('alertBannerTitle').textContent =
                        count > 1 ? `${count} Incidents Need Response` : 'New Incident Reported';
                }

                function showAlertBanner(incidentId, subtitle) {
                    pendingIncidentIds.add(incidentId);
                    updateBannerText();
                    document.getElementById('alertBannerSubtitle').textContent = subtitle;
                    document.getElementById('criticalAlertBanner').classList.remove('d-none');
                    startSirenLoop();
                }

                // Dismiss is now a manual override: it hides the banner AND
                // stops the siren, clearing every currently-pending incident —
                // not just the most recent one. This is a deliberate choice to
                // let admin silence a false alarm/duplicate without needing a
                // formal accept/close event, at the cost of it being possible
                // to dismiss a real still-unaccepted incident. If that becomes
                // a problem in practice, this is the function to revisit.
                function dismissAlertBanner() {
                    document.getElementById('criticalAlertBanner').classList.add('d-none');
                    stopSirenLoop();
                    pendingIncidentIds.clear();
                }

                function clearPendingIncident(incidentId) {
                    pendingIncidentIds.delete(incidentId);
                    if (pendingIncidentIds.size === 0) {
                        stopSirenLoop();
                        dismissAlertBanner();
                    } else {
                        updateBannerText();
                    }
                }

                function registerGlobalIncidentAlert() {
                    window.Echo.channel('live-map')
                        .listen('.incident.reported', (e) => {
                            const inc = e.incident;
                            showAlertBanner(inc.id, `#${inc.id} — ${inc.type} in ${inc.barangay} just came in.`);
                        })
                        // Fires when a responder taps Accept in the responder
                        // app (IncidentController::respond), AND when admin
                        // dispatches from Response Monitor — both paths now
                        // broadcast the same event, so either one stops the
                        // siren for that incident.
                        .listen('.responder.assigned', (e) => {
                            const inc = e.incident;
                            if (inc && inc.id) clearPendingIncident(inc.id);
                        })
                        // Covers incidents closed without ever being accepted
                        // (admin rejects, or resolves directly) — these should
                        // stop the siren too, not just leave it looping forever
                        // for an incident that's already done.
                        .listen('.incident.closed', (e) => {
                            if (e.incidentId) clearPendingIncident(e.incidentId);
                        });
                }

                if (window.Echo) {
                    registerGlobalIncidentAlert();
                } else {
                    const waitForEcho = setInterval(() => {
                        if (window.Echo) {
                            clearInterval(waitForEcho);
                            registerGlobalIncidentAlert();
                        }
                    }, 100);
                }

                // Resume alerting immediately on this fresh page load if any
                // incident was already pending before we got here — otherwise
                // navigating between admin pages would silently "eat" an
                // unaccepted incident until the next brand-new event fires.
                const initialPendingIds = @json($globalPendingIncidentIds);
                if (initialPendingIds.length > 0) {
                    initialPendingIds.forEach(id => pendingIncidentIds.add(id));
                    updateBannerText();
                    document.getElementById('alertBannerSubtitle').textContent =
                        'Waiting for a responder to accept.';
                    document.getElementById('criticalAlertBanner').classList.remove('d-none');
                    startSirenLoop();
                }
            </script>
        @endif
@endsection

@section('before_content_widgets')
	@include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('section', 'before_content')->toArray() ])
@endsection

@section('content')
@endsection