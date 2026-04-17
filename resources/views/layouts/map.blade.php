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
        .media-icons { display: flex; gap: 0.4rem; margin-top: 0.3rem; font-size: 0.75rem; color: oklch(var(--bc) / 0.5); }
        .detail-panel { padding: 1rem; }
        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.5rem; margin: 0.75rem 0; }
        .photo-grid img { width: 100%; height: 90px; object-fit: cover; border-radius: 0.375rem; }
        audio, video { width: 100%; margin-bottom: 0.5rem; border-radius: 0.375rem; }
        .media-section-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: oklch(var(--bc) / 0.5); margin: 1rem 0 0.4rem; }
        #locate-btn {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 800;
            width: 2.75rem; height: 2.75rem;
            background: oklch(var(--b1)); border: 2px solid oklch(var(--b3));
            border-radius: 9999px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18); transition: background 0.15s, border-color 0.15s;
        }
        #locate-btn:hover { background: oklch(var(--b2)); }
        #locate-btn.active { background: #4A90D9; border-color: #4A90D9; color: white; }
        #locate-btn svg { width: 1.25rem; height: 1.25rem; }
        @keyframes user-location-pulse {
            0%   { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(3.5); opacity: 0; }
        }
        .leaflet-user-dot {
            width: 16px; height: 16px;
            background: #4A90D9; border: 2.5px solid white;
            border-radius: 50%; position: relative;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .leaflet-user-dot::after {
            content: ''; position: absolute;
            top: -4px; left: -4px; width: 16px; height: 16px;
            background: #4A90D9; border-radius: 50%; opacity: 0.6;
            animation: user-location-pulse 1.8s ease-out infinite;
        }
        #sidebar-toggle { display: none; }
        #sidebar-backdrop { display: none; }
        #sidebar-close { display: none; }
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100dvh;
                width: 85vw;
                max-width: 360px;
                z-index: 900;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 4px 0 24px rgba(0,0,0,0.25);
            }
            #sidebar.sidebar-open { transform: translateX(0); }
            #sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 899;
                background: rgba(0,0,0,0.4);
                backdrop-filter: blur(2px);
            }
            #sidebar-close { display: block; }
            #sidebar-toggle {
                display: flex;
                position: fixed;
                bottom: 1.5rem;
                left: 1.5rem;
                z-index: 800;
                align-items: center;
                gap: 0.5rem;
                padding: 0.6rem 1rem;
                background: oklch(var(--p));
                color: oklch(var(--pc));
                border: none;
                border-radius: 9999px;
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                cursor: pointer;
                box-shadow: 0 4px 16px rgba(0,0,0,0.25);
            }
        }
    </style>
</head>
<body>
<x-mary-nav class="bg-base-200 border-b border-base-300">
    <x-slot:brand>
        <span class="font-bold text-lg">Jesse Kregel Trail</span>
    </x-slot:brand>
    <x-slot:actions>
        <x-mary-button label="Resources" link="{{ route('resources.index') }}" class="btn-ghost btn-sm" />
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

    map.createPane('watershedPane');
    map.getPane('watershedPane').style.zIndex = 210;

    map.createPane('landusePane');
    map.getPane('landusePane').style.zIndex = 220;

    map.createPane('lakesPane');
    map.getPane('lakesPane').style.zIndex = 290;
    map.getPane('lakesPane').style.mixBlendMode = 'multiply';

    map.createPane('creekPane');
    map.getPane('creekPane').style.zIndex = 300;
    map.getPane('creekPane').style.mixBlendMode = 'multiply';

    const baseMaps = {
        'Street': L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> © <a href="https://carto.com/">CARTO</a>',
            maxZoom: 19,
        }),
        'Satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri — Source: Esri, DigitalGlobe, USDA FSA, USGS, GeoEye, Aerogrid, IGN, IGP, and the GIS User Community',
            maxZoom: 19,
        }),
    };

    baseMaps['Street'].addTo(map);
    L.control.layers(baseMaps, {}, { position: 'topright', collapsed: false }).addTo(map);


    const firstCoord = geom => {
        let c = geom.coordinates;
        while (Array.isArray(c[0])) { c = c[0]; }
        return c;
    };

    fetch('/watershed.geojson')
        .then(r => r.json())
        .then(watershedData => {
            const watershedLayer = L.geoJSON(watershedData, {
                pane: 'watershedPane',
                style: {
                    color: '#7ab8cc',
                    weight: 1,
                    opacity: 0.6,
                    fillColor: '#a8d5a2',
                    fillOpacity: 0.12,
                },
            }).addTo(map);

            // Derive bounding box from watershed extents
            const wb = watershedLayer.getBounds();
            const inWatershedBounds = coords => {
                const [lng, lat] = coords;
                return lat >= wb.getSouth() && lat <= wb.getNorth()
                    && lng >= wb.getWest()  && lng <= wb.getEast();
            };

            const programStyles = {
                'Drinking Water Contaminant':        { fillColor: '#ef4444', color: '#b91c1c', fillOpacity: 0.45 },
                'Environmental Restoration Program': { fillColor: '#4ade80', color: '#15803d', fillOpacity: 0.45 },
                'Petroleum Remediation Program':     { fillColor: '#fb923c', color: '#c2410c', fillOpacity: 0.45 },
                'Resource Conservation and Recovery':{ fillColor: '#a78bfa', color: '#6d28d9', fillOpacity: 0.45 },
                'State Superfund Program':           { fillColor: '#f87171', color: '#991b1b', fillOpacity: 0.55 },
                'Voluntary Cleanup Program':         { fillColor: '#fbbf24', color: '#b45309', fillOpacity: 0.45 },
                'Brownfield Cleanup Program':        { fillColor: '#c4a882', color: '#9e8060', fillOpacity: 0.5  },
            };

            fetch('/remediation_parcels.geojson')
                .then(r => r.json())
                .then(data => {
                    const local = data.features.filter(f => inWatershedBounds(firstCoord(f.geometry)));
                    console.log(`remediation_parcels: ${data.features.length} total, ${local.length} in watershed bounds`);

                    L.geoJSON(data, {
                        pane: 'landusePane',
                        filter: feature => inWatershedBounds(firstCoord(feature.geometry)),
                        style: feature => {
                            const s = programStyles[feature.properties.PROGRAM] ?? { fillColor: '#cccccc', color: '#999', fillOpacity: 0.3 };
                            return { ...s, weight: 1, opacity: 0.6 };
                        },
                        onEachFeature(feature, layer) {
                            const { PROGRAM, SITENAME } = feature.properties;
                            const lines = [
                                SITENAME    ? `<strong>${SITENAME}</strong>` : null,
                                PROGRAM ? `<em>${PROGRAM}</em>`      : null,
                            ].filter(Boolean).join('<br>');
                            if (lines) {
                                layer.bindTooltip(lines, { sticky: true, direction: 'top', opacity: 0.9 });
                            }
                        },
                    }).addTo(map);
                })
                .catch(err => console.error('remediation_parcels.geojson error:', err));
        })
        .catch(err => console.error('watershed.geojson error:', err));

    const creekStyle = (feature, satellite) => {
        const tunnel = feature.properties.tunnel;
        const underground = tunnel === 'yes' || tunnel === 'culvert';
        const widthMap = { 1: 1, 2: 3, 3: 5 };
        const weight = widthMap[feature.properties.width] ?? 6;
        if (underground) {
            return satellite
                ? { color: '#ffffff', weight: 2, opacity: 0.8, dashArray: '1 6', lineCap: 'round', lineJoin: 'round' }
                : { color: '#000000', weight: 1.5, opacity: 0.5, dashArray: '1 4', lineCap: 'round', lineJoin: 'round' };
        }
        return satellite
            ? { weight: 0, opacity: 0 }
            : { color: '#AAD3DF', weight, opacity: 0.85, lineCap: 'round', lineJoin: 'round' };
    };

    fetch('/scajacuada_creek.geojson')
        .then(r => r.json())
        .then(data => {
            const creekLayer = L.geoJSON(data, {
                pane: 'creekPane',
                style: feature => creekStyle(feature, false),
            }).addTo(map);

            map.on('baselayerchange', e => {
                const satellite = e.name === 'Satellite';
                map.getPane('creekPane').style.mixBlendMode = satellite ? 'normal' : 'multiply';
                creekLayer.eachLayer(l => l.setStyle(creekStyle(l.feature, satellite)));
            });
        })
        .catch(err => console.error('scajacuada_creek.geojson error:', err));

    fetch('/lakes.geojson')
        .then(r => r.json())
        .then(data => {
            L.geoJSON(data, {
                pane: 'lakesPane',
                style: {
                    color: '#AAD3DF',
                    weight: 0,
                    fillColor: '#AAD3DF',
                    fillOpacity: 1,
                },
            }).addTo(map);
        })
        .catch(err => console.error('lakes.geojson error:', err));




    fetch('/Jesse_Kregal_Pathway.geojson')
        .then(r => r.json())
        .then(data => {
            L.geoJSON(data, {
                style: { color: '#1e3a5f', weight: 7, opacity: 1, lineCap: 'round', lineJoin: 'round' },
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

                const popupContent = stop.icon_url
                    ? `<div style="text-align:center; line-height:1.4;">
                           <img src="${stop.icon_url}" style="max-width:280px; max-height:280px; width:auto; height:auto; display:block; margin:0 auto 0.5rem;">
                           <strong style="font-size:0.9rem;">${stop.title}</strong>
                       </div>`
                    : `<strong style="font-size:0.9rem;">${stop.title}</strong>`;

                marker.bindPopup(popupContent, { maxWidth: 320, autoPan: false });
                marker.on('mouseover', () => marker.openPopup());
                marker.on('mouseout',  () => marker.closePopup());

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

    // User location
    let locationMarker = null;
    let accuracyCircle = null;
    let watching = false;
    let firstFix = true;

    const dotIcon = L.divIcon({
        className: '',
        html: '<div class="leaflet-user-dot"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });

    map.on('locationfound', e => {
        if (accuracyCircle) { map.removeLayer(accuracyCircle); }
        if (locationMarker) { map.removeLayer(locationMarker); }

        accuracyCircle = L.circle(e.latlng, {
            radius: e.accuracy,
            color: '#4A90D9', fillColor: '#4A90D9',
            fillOpacity: 0.08, weight: 1, opacity: 0.4,
        }).addTo(map);

        locationMarker = L.marker(e.latlng, { icon: dotIcon, zIndexOffset: 1000 }).addTo(map);

        if (firstFix) {
            map.setView(e.latlng, 16, { animate: true });
            firstFix = false;
        }
    });

    map.on('locationerror', e => {
        console.warn('Location error:', e.message);
        watching = false;
        firstFix = true;
        document.getElementById('locate-btn')?.classList.remove('active');
    });

    document.getElementById('locate-btn')?.addEventListener('click', () => {
        if (!watching) {
            watching = true;
            firstFix = true;
            map.locate({ watch: true, enableHighAccuracy: true });
            document.getElementById('locate-btn').classList.add('active');
        } else {
            watching = false;
            firstFix = true;
            map.stopLocate();
            document.getElementById('locate-btn').classList.remove('active');
            if (locationMarker)  { map.removeLayer(locationMarker);  locationMarker = null; }
            if (accuracyCircle)  { map.removeLayer(accuracyCircle);  accuracyCircle = null; }
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
        lightbox: null,
        sidebarOpen: false,

        init() {
            window.addEventListener('stop-selected', (e) => {
                this.selectStop(e.detail);
            });
        },

        panToStop(lat, lng, stopId) {
            if (window._leafletMap) {
                window._leafletMap.setView([lat, lng], 16, { animate: true });
            }
            if (window._leafletMarkers && window._leafletMarkers[stopId]) {
                window._leafletMarkers[stopId].openPopup();
            }
        },

        selectStop(stop) {
            const wrapper = document.getElementById(`stop-wrapper-${stop.id}`);
            if (wrapper) {
                const checkbox = wrapper.querySelector('input[type="checkbox"]');
                if (checkbox && !checkbox.checked) {
                    document.querySelectorAll('#sidebar input[type="checkbox"]').forEach(cb => {
                        if (cb !== checkbox) { cb.checked = false; }
                    });
                    checkbox.checked = true;
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
            this.panToStop(stop.latitude, stop.longitude, stop.id);
        },
    };
}
</script>
@livewireScripts
</body>
</html>
