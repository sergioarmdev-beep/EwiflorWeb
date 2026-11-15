@php
$puntos = $sucursales
    ->filter(fn ($s) => $s->latitud !== null && $s->longitud !== null)
    ->values();
$puntosJson = $puntos->map(fn ($s) => [
    'id' => $s->id_sucursal,
    'nombre' => $s->nombre,
    'estado' => $s->estado,
    'direccion' => $s->direccion,
    'lat' => (float) $s->latitud,
    'lng' => (float) $s->longitud,
    'url' => route('sucursales.show', $s),
])->values();
$mapaId = 'mapa-sucursales-'.uniqid();
$altura = $altura ?? 'h-96';
@endphp

@include('sucursales::partials.leaflet-assets')

<section class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-xl shadow-sm overflow-hidden">

    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800
                flex items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                Mapa de sucursales
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $puntos->count() }} sucursal(es) con ubicación registrada.
            </p>
        </div>
        <span class="text-xs text-slate-400">
            OpenStreetMap
        </span>
    </div>

    @if ($puntos->isEmpty())
    <div class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
        Ninguna sucursal tiene coordenadas todavía. Asigna una ubicación
        desde el formulario de edición de cada sucursal.
    </div>
    @else
    <div id="{{ $mapaId }}" class="w-full {{ $altura }} z-0"></div>
    @endif
</section>

@if ($puntos->isNotEmpty())
<script>
(function () {
    var puntos = @json($puntosJson);

    var map = L.map('{{ $mapaId }}', { scrollWheelZoom: true });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str === null || str === undefined ? '' : String(str);
        return div.innerHTML;
    }

    var bounds = [];

    puntos.forEach(function (p) {
        var color = p.estado === 'ACTIVO' ? '#059669' : '#dc2626';
        var icon = L.divIcon({
            className: '',
            html: '<span style="display:inline-block;width:14px;height:14px;border-radius:9999px;' +
                  'background:' + color + ';border:2px solid #ffffff;' +
                  'box-shadow:0 1px 4px rgba(0,0,0,.4);"></span>',
            iconSize: [14, 14],
            iconAnchor: [7, 7],
        });

        var html =
            '<div style="min-width:180px">' +
            '<strong>' + escapeHtml(p.nombre) + '</strong><br>' +
            '<span style="color:' + color + ';font-weight:600;font-size:11px">' +
            escapeHtml(p.estado) + '</span>' +
            (p.direccion ? '<br><span style="font-size:12px">' + escapeHtml(p.direccion) + '</span>' : '') +
            '<br><a href="' + p.url + '" style="font-size:12px;font-weight:600">Ver detalle</a>' +
            '</div>';

        L.marker([p.lat, p.lng], { icon: icon })
            .addTo(map)
            .bindPopup(html);

        bounds.push([p.lat, p.lng]);
    });

    if (bounds.length === 1) {
        map.setView(bounds[0], 15);
    } else {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
    }
})();
</script>
@endif
