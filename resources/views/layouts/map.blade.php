<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jesse Kregel Trail — Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-textpath@1.2.3/leaflet.textpath.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; display: flex; flex-direction: column; }
        #map-root { display: flex; flex: 1; min-height: 0; width: 100vw; }
        #sidebar { width: 360px; min-width: 280px; max-width: 360px; height: 100%; overflow-y: auto; background: oklch(var(--b1)); border-right: 1px solid oklch(var(--b3)); display: flex; flex-direction: column; }
        #map-container { flex: 1; height: 100%; }
        .stop-item { cursor: pointer; padding: 0.75rem 1rem; border-bottom: 1px solid oklch(var(--b3)); transition: background 0.15s; }
        .stop-item:hover, .stop-item.active { background: oklch(var(--b2)); }
        .badge-info { background: oklch(var(--in)); color: oklch(var(--inc)); }
        .badge-success { background: oklch(var(--su)); color: oklch(var(--suc)); }
        .badge-warning { background: oklch(var(--wa)); color: oklch(var(--wac)); }
        .badge-danger { background: oklch(var(--er)); color: oklch(var(--erc)); }
        .type-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .media-icons { display: flex; gap: 0.4rem; margin-top: 0.3rem; font-size: 0.75rem; color: oklch(var(--bc) / 0.5); }
        .detail-panel { padding: 1rem; }
        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.5rem; margin: 0.75rem 0; }
        .photo-grid img { width: 100%; height: 90px; object-fit: cover; border-radius: 0.375rem; }
        audio, video { width: 100%; margin-bottom: 0.5rem; border-radius: 0.375rem; }
        .media-section-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: oklch(var(--bc) / 0.5); margin: 1rem 0 0.4rem; }
    </style>
</head>
<body>
<x-mary-nav class="bg-base-200 border-b border-base-300">
    <x-slot:brand>
        <span class="font-bold text-lg">Jesse Kregel Trail</span>
    </x-slot:brand>
    <x-slot:actions>
        <x-mary-button label="Resources" link="{{ route('resources.index') }}" class="btn-ghost btn-sm" />
        <x-mary-button label="Map" link="{{ route('map') }}" class="btn-ghost btn-sm" />
        @auth
            <x-mary-button label="Admin" link="/admin" class="btn-ghost btn-sm" />
        @endauth
        @guest
            <x-mary-button label="Sign In" link="/admin/login" class="btn-primary btn-sm" />
        @endguest
    </x-slot:actions>
</x-mary-nav>

{{ $slot }}

<script>
const typeColors = {
    scenic: '#3b82f6',
    ecological: '#22c55e',
    historical: '#f59e0b',
    artistic: '#ef4444',
};

window._leafletMap = null;
window._leafletMarkers = {};

function initLeafletMap() {
    const stopsData = window._stopsData || [];

    const map = L.map('leaflet-map');
    window._leafletMap = map;

    map.createPane('creekPane');
    map.getPane('creekPane').style.zIndex = 300;
    map.getPane('creekPane').style.mixBlendMode = 'multiply';

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    fetch('/scajaquada_historic.geojson')
        .then(r => r.json())
        .then(data => {
            console.log('Features loaded:', data.features.length);
            L.geoJSON(data, {
                style: {
                    color: '#C4956A',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '2 6',
                    lineCap: 'round',
                    lineJoin: 'round'
                }
            }).addTo(map);
            // Invisible simplified layer used only for text placement
            const labelLayer = L.geoJSON(data, {
                style: { opacity: 0, fillOpacity: 0, weight: 0 }
            }).addTo(map);
            labelLayer.eachLayer(l => {
                const lls = l.getLatLngs();
                // Reverse direction, then decimate — keep every Nth point for a smoother label path
                const N = 10;
                const decimate = arr => [...arr].reverse().filter((_, i) => i % N === 0);
                l.setLatLngs(Array.isArray(lls[0]) ? lls.map(decimate) : decimate(lls));
            });
            labelLayer.setText('Historic Scajaquada Creek', {
                repeat: false,
                center: true,
                offset: 20,
                attributes: {
                    'font-size': '16',
                    'font-family': 'sans-serif',
                    'font-style': 'italic',
                    'fill': '#C4956A',
                    'fill-opacity': '0.85',
                    'offset': '6',
                },
            });
        })
        .catch(err => console.error('GeoJSON error:', err));


    fetch('/scajaquada.geojson')
        .then(r => r.json())
        .then(data => {
            L.geoJSON(data, {
                pane: 'creekPane',
                style: feature => {
                    const fcode = feature.properties.fcode;
                    switch(fcode) {
                        case 46006: // Perennial stream
                            return { color: '#AAD3DF', weight: 8, opacity: 0.9, lineCap: 'round', lineJoin: 'round'};
                        case 46003: // Intermittent stream
                            return { color: '#AAD3DF', weight: 5, opacity: 0.7, dashArray: '4 4', lineCap: 'round', lineJoin: 'round' };
                        case 33400: // Underground conduit
                            return { color: '#000000', weight: 5, opacity: 0.5, dashArray: '2 6', lineCap: 'round', lineJoin: 'round' };
                        case 55800: // Artificial path (Hoyt Lake etc)
                            return { color: '#AAD3DF', weight: 10, opacity: 1, lineCap: 'round', lineJoin: 'round' };
                        default:
                            return { color: '#AAD3DF', weight: 1.5, opacity: 1, lineCap: 'round', lineJoin: 'round' };
                    }
                }
            }).addTo(map);
        });




    fetch('/pathway.geojson')
        .then(r => r.json())
        .then(data => {
            L.geoJSON(data, {
                style: { color: '#1e3a5f', weight: 10, opacity: 1, lineCap: 'round', lineJoin: 'round' },
            }).addTo(map);
            const trail = L.geoJSON(data, {
                style: { color: '#ddfa6d', weight: 5, opacity: 1, lineCap: 'round', lineJoin: 'round' },
            }).addTo(map);
            L.geoJSON(data, {
                style: { color: '#1e3a5f', weight: 1, opacity: 1, lineCap: 'round', lineJoin: 'round', dashArray: '6 6' },
            }).addTo(map);
            map.fitBounds(trail.getBounds(), { padding: [40, 40] });

            stopsData.forEach(stop => {
                const color = typeColors[stop.type] || '#6b7280';
                const marker = L.circleMarker([stop.latitude, stop.longitude], {
                    radius: 10,
                    fillColor: color,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85,
                }).addTo(map);

                marker.bindTooltip(stop.title, { permanent: false, direction: 'top' });

                marker.on('click', () => {
                    window.dispatchEvent(new CustomEvent('stop-selected', { detail: stop }));
                });

                window._leafletMarkers[stop.id] = marker;
            });

            if (window._focusStopId) {
                const target = stopsData.find(s => s.id === window._focusStopId);
                if (target) {
                    window.dispatchEvent(new CustomEvent('stop-selected', { detail: target }));
                }
            }
        });
}

document.addEventListener('livewire:navigated', initLeafletMap);

document.addEventListener('livewire:navigating', () => {
    if (window._leafletMap) {
        window._leafletMap.remove();
        window._leafletMap = null;
        window._leafletMarkers = {};
    }
});

function mapApp(stopsData, focusStopId) {
    window._stopsData = stopsData;
    window._focusStopId = focusStopId;

    return {
        stops: stopsData,
        openStopId: null,
        lightbox: null,

        init() {
            window.addEventListener('stop-selected', (e) => {
                this.selectStop(e.detail);
            });
        },

        selectStop(stop) {
            this.openStopId = this.openStopId === stop.id ? null : stop.id;

            if (this.openStopId && window._leafletMap && window._leafletMarkers[stop.id]) {
                window._leafletMap.setView([stop.latitude, stop.longitude], 16, { animate: true });
                window._leafletMarkers[stop.id].openTooltip();
            }
        },
    };
}
</script>
@livewireScripts
</body>
</html>
