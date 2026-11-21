@php
    // Sucursales provistas por el view composer de PublicoServiceProvider.
    $sucursalesFooter = $sucursalesFooter ?? collect();

    $puntosMapa = $sucursalesFooter
        ->filter(fn ($s) => $s->latitud !== null && $s->longitud !== null)
        ->map(fn ($s) => [
            'nombre'    => $s->nombre,
            'direccion' => $s->direccion,
            'lat'       => (float) $s->latitud,
            'lng'       => (float) $s->longitud,
        ])
        ->values();
@endphp

<footer class="footer">
    <div class="container-main">

        <div class="footer-grid">

            <div class="footer-brand-column">
                <a href="{{ route('catalogo.publico') }}" class="brand">
                    <div class="brand-icon">
                        <img src="{{ asset('images/ewiflor-logo.webp') }}" alt="Logo Ewiflor">
                    </div>
                    <div>
                        <span class="brand-name">EWIFLOR</span>
                        <span class="brand-subtitle">Importaciones</span>
                    </div>
                </a>

                <p class="footer-description">
                    Maquinaria y equipamiento gastronómico con respaldo técnico
                    y repuestos originales en toda Bolivia.
                </p>

                <a class="footer-whatsapp"
                    href="https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text={{ urlencode('Hola Ewiflor, quiero más información.') }}"
                    target="_blank"
                    rel="noopener">
                    <img src="{{ asset('images/whatsapp-logo.svg') }}" alt="" width="20" height="20" aria-hidden="true">
                    Escribir por WhatsApp
                </a>
            </div>

            <div class="footer-column footer-branches">
                <h4>Sucursales</h4>

                @forelse($sucursalesFooter as $sucursal)
                <div class="footer-branch">
                    <strong>{{ $sucursal->nombre }}</strong>
                    @if($sucursal->departamento)
                    <span class="footer-branch-dept">{{ ucwords(strtolower(str_replace('_', ' ', $sucursal->departamento))) }}</span>
                    @endif
                    @if($sucursal->direccion)
                    <span>{{ $sucursal->direccion }}</span>
                    <a href="https://www.openstreetmap.org/search?query={{ urlencode($sucursal->direccion . ' ' . $sucursal->nombre) }}"
                        target="_blank"
                        rel="noopener">Ver en el mapa</a>
                    @endif
                </div>
                @empty
                <p class="footer-empty">Próximamente publicaremos nuestras sucursales.</p>
                @endforelse
            </div>

            <div class="footer-column footer-map-column">
                <h4>Ubicación</h4>
                <div id="footer-map" class="footer-map" role="img" aria-label="Mapa de sucursales de Ewiflor"></div>
            </div>

        </div>

        <div class="footer-bottom">
            <span>
                © {{ date('Y') }} Ewiflor Importaciones. Todos los derechos reservados.
            </span>

            <span>
                Mapa: © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors
            </span>
        </div>

    </div>
</footer>

@include('sucursales::partials.leaflet-assets')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var contenedor = document.getElementById('footer-map');
        if (!contenedor || typeof L === 'undefined') {
            if (contenedor) contenedor.style.display = 'none';
            return;
        }

        var puntos = @json($puntosMapa);

        // Centro de referencia: Bolivia. Si hay sucursales con coordenadas,
        // el mapa se ajusta a sus puntos.
        var mapa = L.map(contenedor, {
            scrollWheelZoom: false,
            zoomControl: true,
            attributionControl: true
        }).setView([-16.5, -64.5], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapa);

        var icono = L.divIcon({
            className: '',
            html: '<span style="display:inline-block;width:14px;height:14px;border-radius:9999px;background:#2A9832;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);"></span>',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        var limites = [];

        puntos.forEach(function (p) {
            L.marker([p.lat, p.lng], { icon: icono })
                .addTo(mapa)
                .bindPopup('<strong>' + p.nombre + '</strong>' + (p.direccion ? '<br>' + p.direccion : ''));
            limites.push([p.lat, p.lng]);
        });

        if (limites.length === 1) {
            mapa.setView(limites[0], 13);
        } else if (limites.length > 1) {
            mapa.fitBounds(limites, { padding: [30, 30], maxZoom: 13 });
        }
    });
</script>
