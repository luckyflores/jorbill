<x-filament-panels::page>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="flex items-center gap-4 mb-3 text-sm">
        <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-green-500"></span> Active</div>
        <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-yellow-500"></span> Prospect</div>
        <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-red-500"></span> Suspended</div>
        <div class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-gray-500"></span> Cancelled</div>
        <div class="flex items-center gap-1.5 ml-4"><span class="inline-block w-3 h-3 rounded-sm bg-indigo-600"></span> NAP</div>
        <div class="ml-auto text-gray-500">
            {{ count($customers) }} customers · {{ count($naps) }} NAPs
        </div>
    </div>

    <div id="jorbill-network-map" style="height: 70vh; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1);"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const customers = @json($customers);
            const naps = @json($naps);

            const allPoints = [...customers, ...naps];
            const center = allPoints.length
                ? [allPoints[0].lat, allPoints[0].lng]
                : [14.5995, 120.9842];  // Manila default

            const map = L.map('jorbill-network-map').setView(center, 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const statusColor = {
                active:    '#10b981',
                prospect:  '#f59e0b',
                suspended: '#ef4444',
                cancelled: '#6b7280',
            };

            // customers as circle markers
            customers.forEach(c => {
                L.circleMarker([c.lat, c.lng], {
                    radius: 7,
                    color: statusColor[c.status] || '#6b7280',
                    fillColor: statusColor[c.status] || '#6b7280',
                    fillOpacity: 0.7,
                    weight: 2,
                }).bindPopup(
                    `<div style="min-width:180px">
                        <div style="font-weight:600">${c.name}</div>
                        <div style="color:#666;font-size:11px">${c.code} · ${c.status}</div>
                        <div style="margin-top:4px">${c.phone || ''}</div>
                        <div style="color:#666">${c.city || ''}</div>
                        <a href="/admin/customers/${c.id}/edit" style="display:inline-block;margin-top:6px;color:#4f46e5">Open →</a>
                    </div>`
                ).addTo(map);
            });

            // NAPs as square icons
            const napIcon = L.divIcon({
                className: 'jorbill-nap-marker',
                html: '<div style="width:14px;height:14px;background:#4338ca;border:2px solid white;box-shadow:0 0 0 1px rgba(0,0,0,0.3)"></div>',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });
            naps.forEach(n => {
                const fillPct = n.capacity > 0 ? Math.round((n.ports_used / n.capacity) * 100) : 0;
                L.marker([n.lat, n.lng], { icon: napIcon })
                    .bindPopup(
                        `<div style="min-width:180px">
                            <div style="font-weight:600">${n.name}</div>
                            <div style="color:#666;font-size:11px">${n.code} · ${n.type}</div>
                            <div style="margin-top:4px">${n.ports_used} / ${n.capacity} ports (${fillPct}%)</div>
                            <a href="/admin/naps/${n.id}/edit" style="display:inline-block;margin-top:6px;color:#4f46e5">Open →</a>
                        </div>`
                    ).addTo(map);
            });

            // Fit bounds if we have any points
            if (allPoints.length > 0) {
                const bounds = L.latLngBounds(allPoints.map(p => [p.lat, p.lng]));
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        });
    </script>
</x-filament-panels::page>
