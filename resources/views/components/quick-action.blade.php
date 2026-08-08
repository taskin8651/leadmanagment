@props(['icon' => 'bi-lightning', 'label' => '', 'href' => '#'])
<a href="{{ $href }}" class="quick-action">
    <span class="qi"><i class="bi {{ $icon }}"></i></span>
    <span class="fw-semibold" style="font-size:13.4px">{{ $label }}</span>
    <i class="bi bi-chevron-right ms-auto muted" style="font-size:12px"></i>
</a>
