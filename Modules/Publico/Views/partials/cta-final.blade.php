{{-- Cierre: invitación directa a cotizar por WhatsApp. --}}
<section class="cta-final">
    <div class="container-main">
        <div class="cta-final-box reveal">
            <span class="section-label">Cotización sin compromiso</span>

            <h2>¿Listo para equipar su negocio?</h2>

            <p>
                Cuéntenos qué necesita y le enviamos una propuesta con los equipos
                adecuados para su producción y su espacio.
            </p>

            <a href="https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text={{ urlencode('Hola Ewiflor, quiero cotizar un equipo para mi negocio.') }}"
                class="button-whatsapp cta-final-btn" target="_blank" rel="noopener">
                <img src="{{ asset('images/whatsapp-logo.svg') }}" alt="" width="22" height="22" aria-hidden="true">
                Cotizar por WhatsApp
            </a>
        </div>
    </div>
</section>
