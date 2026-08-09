@if (isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs))
    <div class="mb-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb text-uppercase small text-muted mb-0" style="letter-spacing: 0.5px;">
                @foreach ($breadcrumbs as $label => $link)
                    @if ($link)
                        <li class="breadcrumb-item"><a href="{{ $link }}" class="text-muted">{{ $label }}</a></li>
                    @else
                        <li class="breadcrumb-item active text-muted" aria-current="page">{{ $label }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
@endif