<div class="text-muted small text-uppercase px-3 pt-3 pb-1">Overview</div>
<x-backpack::menu-item title="Dashboard" icon="la la-home" :link="backpack_url('dashboard')" />

<div class="text-muted small text-uppercase px-3 pt-3 pb-1">Incidents</div>
<x-backpack::menu-item title="Incidents" icon="la la-exclamation-triangle" :link="backpack_url('incidents')" />
<x-backpack::menu-item title="Response Monitor" icon="la la-bolt" :link="backpack_url('response-monitor')" />

<div class="text-muted small text-uppercase px-3 pt-3 pb-1">Field</div>
<x-backpack::menu-item title="Live Map" icon="la la-map-marker" :link="backpack_url('live-map')" />
<x-backpack::menu-item title="Responders" icon="la la-user-shield" :link="backpack_url('responders')" />
<x-backpack::menu-item title="Responders Reports" icon="la la-file-alt" :link="backpack_url('responders-reports')" />

<div class="text-muted small text-uppercase px-3 pt-3 pb-1">Control</div>
<x-backpack::menu-item title="Send Alert" icon="la la-bell" :link="backpack_url('send-alert')" />
<x-backpack::menu-item title="Analytics" icon="la la-chart-bar" :link="backpack_url('analytics')" />
<x-backpack::menu-item title="Users Management" icon="la la-users" :link="backpack_url('user')" />