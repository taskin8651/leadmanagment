@php($unread = auth()->user()->unreadNotifications()->latest()->limit(8)->get())
<div class="dropdown">
    <button class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications, {{ $unread->count() }} unread">
        <i class="bi bi-bell"></i>
        @if($unread->count() > 0)<span class="notif-dot"></span>@endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0" style="border-radius:14px;width:340px;max-height:420px;overflow-y:auto">
        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom:1px solid var(--border)">
            <span class="fw-bold" style="font-size:13.5px">Notifications</span>
            @if($unread->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-ghost btn-sm p-0" style="font-size:11.5px">Mark all read</button></form>
            @endif
        </div>
        @forelse($unread as $n)
            <a href="{{ route('notifications.read', $n->id) }}" class="d-flex gap-2 px-3 py-2 text-decoration-none search-result-row" style="border-bottom:1px solid var(--border)">
                <span class="icon-wrap" style="width:32px;height:32px;margin:0;background:var(--primary-soft);color:var(--primary)"><i class="bi {{ $n->data['icon'] ?? 'bi-bell' }}"></i></span>
                <span>
                    <span class="d-block fw-semibold" style="font-size:12.8px;color:var(--text)">{{ $n->data['title'] ?? 'Notification' }}</span>
                    <span class="d-block muted" style="font-size:11.8px">{{ $n->data['body'] ?? '' }}</span>
                    <span class="d-block muted" style="font-size:10.8px">{{ $n->created_at->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <div class="text-center py-4 muted small">You're all caught up.</div>
        @endforelse
    </div>
</div>
