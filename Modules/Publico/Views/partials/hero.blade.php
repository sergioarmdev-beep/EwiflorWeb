{{--
    Hero de portada: fotografía real de equipamiento gastronómico a pantalla
    casi completa, debajo de la cabecera (que ya no se superpone). Entra una vez
    al cargar con una animación escalonada suave.
--}}
<section id="inicio" class="hero-home" data-enter>
    <img
        class="hero-home-media"
        src="https://tierrasbajas.cl/wp-content/uploads/2026/04/maquinaria-equipamiento-para-panaderia-industrial.webp"
        alt="Cocina industrial equipada con maquinaria gastronómica profesional"
        width="1800"
        height="1100"
        fetchpriority="high">
    <div class="hero-home-overlay" aria-hidden="true"></div>

    <div class="container-main hero-home-inner">
        <span class="section-label enter" style="--i:0">
            <span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span>
            Equipamiento gastronómico desde 2001
        </span>

        <h1 class="enter" style="--i:1">
            Equipamos tu negocio con
            <span class="gradient-text">maquinaria profesional</span>
        </h1>

        <p class="hero-home-text enter" style="--i:2">
            Maquinaria, repuestos y servicio técnico para panaderías, restaurantes,
            carnicerías y reposterías de toda Bolivia.
        </p>

        <div class="hero-home-actions enter" style="--i:3">
            <a href="#destacados" class="button-primary">
                Ver equipos destacados
                <span class="material-symbols-rounded" aria-hidden="true">arrow_downward</span>
            </a>
            <a href="https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text={{ urlencode('Hola Ewiflor, quiero cotizar un equipo.') }}"
                class="button-secondary" target="_blank" rel="noopener">
                <img src="{{ asset('images/whatsapp-logo.svg') }}" alt="" width="20" height="20" aria-hidden="true">
                Cotizar por WhatsApp
            </a>
        </div>
    </div>

    <div class="hero-home-foot enter" style="--i:4">
        <span>La Paz · Santa Cruz · Envíos a todo el país</span>
        <span class="hero-home-scroll">
            Desliza para descubrir
            <span class="material-symbols-rounded" aria-hidden="true">south</span>
        </span>
    </div>
</section>
