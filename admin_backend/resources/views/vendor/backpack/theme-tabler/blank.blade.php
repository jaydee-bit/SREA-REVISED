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
            let bannerDismissTimer = null;
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
                document.getElementById('criticalAlertBanner').classList.remove('d-none');
                playSiren();
                if (bannerDismissTimer) clearTimeout(bannerDismissTimer);
                bannerDismissTimer = setTimeout(() => dismissAlertBanner(), 8000);
            }

            function dismissAlertBanner() {
                document.getElementById('criticalAlertBanner').classList.add('d-none');
                if (bannerDismissTimer) { clearTimeout(bannerDismissTimer); bannerDismissTimer = null; }
            }

            function registerGlobalIncidentAlert() {
                window.Echo.channel('live-map').listen('.incident.reported', (e) => {
                    const inc = e.incident;
                    showAlertBanner('New Incident Reported', `#${inc.id} — ${inc.type} in ${inc.barangay} just came in.`);
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
        </script>
@endsection

@section('before_content_widgets')
	@include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('section', 'before_content')->toArray() ])
@endsection

@section('content')
@endsection