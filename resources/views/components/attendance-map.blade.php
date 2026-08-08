@props(['points' => [], 'height' => '220px'])
<div class="js-attendance-map border rounded-3" style="height: {{ $height }};" data-points='@json($points)'></div>
@once
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>.js-attendance-map{background:var(--background,#f4f5f7)}</style>
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-attendance-map').forEach(function (el) {
        var points = [];
        try { points = JSON.parse(el.dataset.points || '[]'); } catch (e) { points = []; }
        if (!points.length) {
            el.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 muted" style="font-size:12.5px">No location recorded</div>';
            return;
        }
        var map = L.map(el, { scrollWheelZoom: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        var bounds = [];
        points.forEach(function (p) {
            var marker = L.circleMarker([p.lat, p.lng], {
                radius: 9, color: '#fff', weight: 2, fillColor: p.color || '#4f46e5', fillOpacity: 1,
            }).addTo(map);
            if (p.label) marker.bindPopup(p.label);
            bounds.push([p.lat, p.lng]);
        });
        if (bounds.length === 1) {
            map.setView(bounds[0], 16);
        } else {
            map.fitBounds(bounds, { padding: [24, 24] });
        }
    });
});
</script>
@endpush
@endonce
