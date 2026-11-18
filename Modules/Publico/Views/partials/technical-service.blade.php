        <section id="contacto" class="section contacto-section">
            <div class="container-main section-content">

                <div class="section-heading reveal">
                    <span class="section-label">Servicio técnico</span>

                    <h2 class="section-title">
                        ¿Necesitas soporte? <span class="gradient-text">Contáctanos</span>
                    </h2>

                    <p class="section-description">
                        Si eres cliente de Ewiflor Importaciones y necesitas asistencia técnica,
                        completa el formulario con tus datos. Un técnico se comunicará contigo.
                    </p>
                </div>

                <div class="contacto-grid reveal">

                    <div class="contacto-info">
                        <h3>Atención al cliente</h3>
                        <p>
                            Si tienes una compra registrada con nosotros, puedes solicitar servicio técnico
                            para tu equipo. Completa el formulario con tu nombre y número de celular tal como
                            aparecen en tu factura de compra.
                        </p>

                        <div class="contacto-dato">
                            <span class="material-symbols-rounded" aria-hidden="true">call</span>
                            <div>
                                <strong>Llamada:</strong>
                                <span>Línea de servicio técnico</span>
                            </div>
                        </div>

                        <div class="contacto-dato">
                            <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                            <div>
                                <strong>Correo:</strong>
                                <span>soporte@ewiflor.com</span>
                            </div>
                        </div>

                        <div class="contacto-dato">
                            <span class="material-symbols-rounded" aria-hidden="true">schedule</span>
                            <div>
                                <strong>Horario:</strong>
                                <span>Lunes a viernes, 8:00 a 17:00</span>
                            </div>
                        </div>
                    </div>

                    <div class="contacto-form">
                        <h3>Solicitar asistencia técnica</h3>
                        @if (session('error'))
                        <div class="mb-4 p-3 rounded border border-red-500/30 bg-red-500/10 text-red-300">
                            {{ session('error') }}
                        </div>
                        @endif
                        @if (session('success'))
                        <div class="mb-4 p-3 rounded border border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                            {{ session('success') }}
                        </div>
                        @endif
                        <form method="POST" action="{{ route('publico.contactar.servicio.tecnico') }}">
                            @csrf

                            <div class="form-group">
                                <label for="nombre">Nombre completo</label>
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    value="{{ old('nombre') }}"
                                    placeholder="Juan Pérez"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="telefono">Número de celular</label>
                                <input
                                    type="tel"
                                    id="telefono"
                                    name="telefono"
                                    value="{{ old('telefono') }}"
                                    placeholder="71234567"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="mensaje">Describe el problema o solicitud</label>
                                <textarea
                                    id="mensaje"
                                    name="mensaje"
                                    rows="4"
                                    placeholder="Ej: El horno no enciende, ¿pueden revisarlo?"
                                    required></textarea>
                                <p class="form-hint">Debes estar registrado como cliente con compras previas.</p>
                            </div>

                            <button type="submit">Enviar solicitud</button>
                        </form>
                    </div>

                </div>
            </div>
        </section>
