{{--
    Marcas: por ahora las imágenes están referenciadas de forma fija.
    Deje los archivos en public/images/marcas/ con estos nombres y se mostrarán
    automáticamente; mientras no existan, se muestra el nombre de la marca.
--}}
@php
    $marcas = [
        ['nombre' => 'Ewiflor',                 'archivo' => 'images/marcas/ewiflor.webp'],
        ['nombre' => 'TechHogar',               'archivo' => 'images/marcas/techhogar.webp'],
        ['nombre' => 'MegaImport',              'archivo' => 'images/marcas/megaimport.webp'],
        ['nombre' => 'Refacciones del Norte',   'archivo' => 'images/marcas/refacciones-del-norte.webp'],
        ['nombre' => 'Repuestos originales',    'archivo' => 'images/marcas/repuestos-originales.webp'],
    ];
@endphp

<section id="marcas" class="section brands-section">
    <div class="container-main section-content">

        <div class="section-heading reveal">
            <span class="section-label">Respaldo de fábrica</span>

            <h2 class="section-title">
                Marcas que <span class="gradient-text">trabajamos</span>
            </h2>

            <p class="section-description">
                Equipos y repuestos de marcas con respaldo técnico y disponibilidad
                de piezas.
            </p>
        </div>

        <div class="brand-grid">
            @foreach($marcas as $marca)
            @php $existe = is_file(public_path($marca['archivo'])); @endphp
            <div class="brand-tile reveal" title="{{ $marca['nombre'] }}">
                @if($existe)
                <img src="{{ asset($marca['archivo']) }}" alt="Marca {{ $marca['nombre'] }}" loading="lazy" decoding="async">
                @else
                <span class="brand-tile-name">{{ $marca['nombre'] }}</span>
                @endif
            </div>
            @endforeach
        </div>

    </div>
</section>
