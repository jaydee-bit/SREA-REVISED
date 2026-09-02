<div class="mb-3">
    <a href="{{ url()->current() }}" class="btn btn-sm {{ !request('role') ? 'btn-success' : 'btn-outline-secondary' }}">All</a>
    <a href="{{ url()->current() }}?role=admin" class="btn btn-sm {{ request('role') === 'admin' ? 'btn-success' : 'btn-outline-secondary' }}">Admins</a>
    <a href="{{ url()->current() }}?role=responder" class="btn btn-sm {{ request('role') === 'responder' ? 'btn-success' : 'btn-outline-secondary' }}">Responders</a>
</div>