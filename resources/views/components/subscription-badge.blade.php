@props(['subscription'])
@php
if (!$subscription) {
    $cls = 'badge-neutral'; $label = 'No Plan';
} else {
    $days = now()->startOfDay()->diffInDays($subscription->ends_at->copy()->startOfDay(), false);
    if ($subscription->status === 'cancelled') { $cls = 'badge-neutral'; $label = 'Cancelled'; }
    elseif ($days < 0) { $cls = 'badge-danger'; $label = 'Expired'; }
    elseif ($days <= 7) { $cls = 'badge-warning'; $label = 'Renewal Due Soon'; }
    elseif ($days <= 30) { $cls = 'badge-info'; $label = 'Expiring Soon'; }
    else { $cls = 'badge-success'; $label = 'Active'; }
}
@endphp
<span class="badge {{ $cls }}">{{ $label }}</span>
