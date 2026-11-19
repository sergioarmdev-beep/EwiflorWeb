@php
$latInicial = old('latitud', $sucursal->latitud);
$lngInicial = old('longitud', $sucursal->longitud);
$tieneUbicacion = $latInicial !== null && $lngInicial !== null;

// Escalares listos para @json — sin ternarios ni comas dentro del @json
$latJson = $latInicial !== null ? (float) $latInicial : null;
$lngJson = $lngInicial !== null ? (float) $lngInicial : null;
@endphp

@include('sucursales::partials.leaflet-assets')

<div class="space-y-4">

    <div>
        <h3 class="text-sm font-semibold text-ink">Ubicación geográfica</h3>
        <p class="text-xs text-muted mt-1">
            Haz clic en el mapa para colocar el marcador, o usa tu ubicación actual.
            Coordenadas opcionales: sirven para mostrar la sucursal en el mapa general.
        </p>
    </div>

    {{-- Coordenadas visibles --}}
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <span id="coordenadas-lectura" class="text-xs font-mono text-muted">
            {{ $tieneUbicacion
                ? 'Lat: '.number_format($latJson, 6).' · Lng: '.number_format($lngJson, 6)
                : 'Sin ubicación asignada' }}
        </span>

        <div class="flex items-center gap-2">
            <button type="button" id="btn-mi-ubicacion"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                          border border-line bg-surface-2 text-ink hover:bg-surface-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Mi ubicación
            </button>

            <button type="button" id="btn-quitar-ubicacion"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                          border border-line bg-surface-2 text-red-500 hover:bg-red-500/10 transition-colors">
                Quitar ubicación
            </button>
        </div>
    </div>

    {{-- Inputs ocultos sincronizados con el mapa --}}
    <input type="hidden" id="latitud" name="latitud" value="{{ $latJson }}">
    <input type="hidden" id="longitud" name="longitud" value="{{ $lngJson }}">

    {{-- Mapa --}}
    <div id="mapa-picker"
        class="w-full h-80 rounded-lg overflow-hidden border border-line z-0"></div>

    @error('latitud')
    <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror
    @error('longitud')
    <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
    (function() {
        if (typeof L === 'undefined') {
            console.error('Leaflet no está cargado en mapa-sucursales.');
            return;
        }

        var inicial = {
            lat: @json($latJson),
            lng: @json($lngJson),
        };

        var CENTRO_BOLIVIA = [-17.0, -64.7];
        var map = L.map('mapa-picker', {
            scrollWheelZoom: true
        }).setView(
            inicial.lat !== null ? [inicial.lat, inicial.lng] : CENTRO_BOLIVIA,
            inicial.lat !== null ? 15 : 6
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker = null;

        function fmt(n) {
            return Number(n).toFixed(6);
        }

        function actualizarLectura() {
            var lectura = document.getElementById('coordenadas-lectura');
            if (inicial.lat === null) {
                lectura.textContent = 'Sin ubicación asignada';
            } else {
                lectura.textContent = 'Lat: ' + fmt(inicial.lat) + ' · Lng: ' + fmt(inicial.lng);
            }
        }

        function ponerMarker(lat, lng, centrar) {
            inicial.lat = lat;
            inicial.lng = lng;
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;

            if (marker === null) {
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);
                marker.on('dragend', function() {
                    var p = marker.getLatLng();
                    ponerMarker(p.lat, p.lng, false);
                });
            } else {
                marker.setLatLng([lat, lng]);
            }

            if (centrar) {
                map.setView([lat, lng], 15);
            }
            actualizarLectura();
        }

        function quitarMarker() {
            inicial.lat = null;
            inicial.lng = null;
            document.getElementById('latitud').value = '';
            document.getElementById('longitud').value = '';
            if (marker !== null) {
                map.removeLayer(marker);
                marker = null;
            }
            map.setView(CENTRO_BOLIVIA, 6);
            actualizarLectura();
        }

        map.on('click', function(e) {
            ponerMarker(e.latlng.lat, e.latlng.lng, false);
        });

        document.getElementById('btn-quitar-ubicacion').addEventListener('click', quitarMarker);

        document.getElementById('btn-mi-ubicacion').addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    ponerMarker(pos.coords.latitude, pos.coords.longitude, true);
                },
                function() {
                    alert('No se pudo obtener tu ubicación. Revisa los permisos del navegador.');
                }
            );
        });

        // Estado inicial: marker si la sucursal ya tiene coordenadas.
        if (inicial.lat !== null) {
            ponerMarker(inicial.lat, inicial.lng, false);
        }
    })();
</script>
