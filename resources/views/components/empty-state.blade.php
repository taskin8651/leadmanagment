@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'description' => null])
<div class="empty-state fade-in">
    <div class="ic"><i class="bi {{ $icon }}"></i></div>
    <h6>{{ $title }}</h6>
    @if($description)<p>{{ $description }}</p>@endif
    @isset($actions)<div class="d-flex gap-2 justify-content-center flex-wrap">{{ $actions }}</div>@endisset
</div>
