@props(['title', 'subtitle' => null, 'eyebrow' => null])
<div class="page-header fade-up">
    <div>
        @if($eyebrow)<div class="page-eyebrow mb-1">{{ $eyebrow }}</div>@endif
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="d-flex gap-2 flex-wrap">{{ $actions }}</div>
    @endisset
</div>
