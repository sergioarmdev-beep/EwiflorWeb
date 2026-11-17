{{-- Alta rápida de cliente.
     Permite registrar un cliente sin salir del formulario (cotización o venta
     directa) y lo deja seleccionado en el <select id="id_cliente"> de la página.
     Si el teléfono ya existe, ofrece reutilizar ese cliente.

     OJO: este partial se incluye DENTRO del formulario de la cotización o de la
     venta, así que NO define un formulario propio ni atributos name: los
     formularios anidados son inválidos en HTML y el navegador descarta el
     interno. Los datos se recolectan con data-campo para no enviarse junto con
     el documento padre. --}}
<div class="mt-1 flex flex-wrap items-center gap-2">
    <button type="button" data-cliente-rapido-open
        class="text-sm text-blue-600 hover:underline">
        + Registrar cliente nuevo
    </button>

    <noscript>
        <a href="{{ route('clientes.create') }}" class="text-sm text-blue-600 hover:underline">
            (abrir el formulario de clientes)
        </a>
    </noscript>
</div>

<dialog id="dlg-cliente-rapido" class="rounded-lg shadow-xl p-6 w-full max-w-lg">
    <div id="qc-campos">
        <h2 class="text-lg font-semibold mb-1">Registrar cliente</h2>
        <p class="text-sm text-gray-600 mb-4">
            Se guarda en el panel de clientes y queda seleccionado en este formulario.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium" for="qc-nombre">Nombre *</label>
                <input type="text" id="qc-nombre" data-campo="nombre" maxlength="120"
                    placeholder="Nombre completo o razón social"
                    class="w-full border rounded px-3 py-2">
                <p class="text-red-600 text-xs mt-1 hidden" data-error="nombre"></p>
            </div>

            <div>
                <label class="block text-sm font-medium" for="qc-telefono">Teléfono *</label>
                <input type="text" id="qc-telefono" data-campo="telefono" maxlength="20"
                    placeholder="Número de teléfono"
                    class="w-full border rounded px-3 py-2">
                <p class="text-red-600 text-xs mt-1 hidden" data-error="telefono"></p>
            </div>

            <div>
                <label class="block text-sm font-medium" for="qc-correo">Correo</label>
                <input type="email" id="qc-correo" data-campo="correo" maxlength="100"
                    placeholder="ejemplo@correo.com"
                    class="w-full border rounded px-3 py-2">
                <p class="text-red-600 text-xs mt-1 hidden" data-error="correo"></p>
            </div>

            <div>
                <label class="block text-sm font-medium" for="qc-tipo">Tipo de cliente</label>
                <select id="qc-tipo" data-campo="tipo_cliente" class="w-full border rounded px-3 py-2">
                    <option value="">Seleccionar tipo</option>
                    @foreach (\Modules\Clientes\Models\Cliente::TIPOS as $tipo)
                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-xs mt-1 hidden" data-error="tipo_cliente"></p>
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm" for="qc-mayorista">
                    <input type="checkbox" id="qc-mayorista" data-campo="es_mayorista"
                        class="w-4 h-4 rounded border-gray-300">
                    Cliente mayorista
                </label>
                <p class="text-red-600 text-xs mt-1 hidden" data-error="es_mayorista"></p>
            </div>
        </div>

        <div id="qc-existente" class="hidden mt-4 p-3 rounded border border-amber-300 bg-amber-50 text-sm">
            <p>
                Ya existe un cliente con ese teléfono:
                <span class="font-semibold" data-existente-nombre></span>
            </p>
            <div class="flex flex-wrap gap-2 mt-3">
                <button type="button" data-usar-existente
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Usar este cliente
                </button>
                <button type="button" data-crear-igual class="border px-4 py-2 rounded">
                    Registrar de todas formas
                </button>
            </div>
        </div>

        <p class="text-red-600 text-sm mt-3 hidden" data-error-general></p>

        <div class="flex justify-end gap-2 mt-5">
            <button type="button" data-cerrar class="border px-4 py-2 rounded">Cancelar</button>
            <button type="button" data-submit
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Guardar cliente
            </button>
        </div>
    </div>
</dialog>

<script>
    (function() {
        const abrir = document.querySelector('[data-cliente-rapido-open]');
        const dialogo = document.getElementById('dlg-cliente-rapido');
        const campos = document.getElementById('qc-campos');
        if (!abrir || !dialogo || !campos) return;

        const select = document.getElementById('id_cliente');
        const panelExistente = document.getElementById('qc-existente');
        const errorGeneral = campos.querySelector('[data-error-general]');
        const botonGuardar = campos.querySelector('[data-submit]');
        const urlStore = @json(route('clientes.store'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let clienteExistente = null;
        let forzarCreacion = false;

        const controles = () => campos.querySelectorAll('[data-campo]');

        const mostrarGeneral = (texto) => {
            errorGeneral.textContent = texto || '';
            errorGeneral.classList.toggle('hidden', !texto);
        };

        const limpiarErrores = () => {
            campos.querySelectorAll('[data-error]').forEach((p) => {
                p.textContent = '';
                p.classList.add('hidden');
            });
            mostrarGeneral('');
        };

        const pintarErrores = (errors) => {
            limpiarErrores();
            const entradas = Object.entries(errors || {});
            entradas.forEach(([campo, mensajes]) => {
                const p = campos.querySelector(`[data-error="${campo}"]`);
                if (p) {
                    p.textContent = mensajes[0];
                    p.classList.remove('hidden');
                }
            });
            if (entradas.length === 0) mostrarGeneral('No se pudo registrar el cliente.');
        };

        const reiniciar = () => {
            controles().forEach((el) => {
                if (el.type === 'checkbox') {
                    el.checked = false;
                } else {
                    el.value = '';
                }
            });
            limpiarErrores();
            clienteExistente = null;
            forzarCreacion = false;
            panelExistente.classList.add('hidden');
            botonGuardar.classList.remove('hidden');
            botonGuardar.disabled = false;
            botonGuardar.textContent = 'Guardar cliente';
        };

        const seleccionarCliente = (cliente) => {
            if (!select) return;

            let opcion = Array.from(select.options)
                .find((o) => String(o.value) === String(cliente.id_cliente));

            if (!opcion) {
                opcion = new Option(cliente.etiqueta, cliente.id_cliente);
                select.add(opcion);
            }

            select.value = String(cliente.id_cliente);
            select.dispatchEvent(new Event('change', { bubbles: true }));

            dialogo.close();
            reiniciar();
            select.focus();
        };

        const datosDelFormulario = () => {
            const datos = {};

            controles().forEach((el) => {
                datos[el.dataset.campo] = el.type === 'checkbox'
                    ? (el.checked ? 1 : 0)
                    : el.value;
            });

            if (forzarCreacion) datos.forzar_creacion = 1;

            return datos;
        };

        const enviar = async () => {
            botonGuardar.disabled = true;
            botonGuardar.textContent = 'Guardando…';

            try {
                const respuesta = await fetch(urlStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(datosDelFormulario()),
                });

                if (respuesta.status === 422) {
                    const datos = await respuesta.json();
                    pintarErrores(datos.errors);

                    return;
                }

                if (!respuesta.ok) {
                    mostrarGeneral('No se pudo registrar el cliente. Intenta nuevamente.');

                    return;
                }

                const datos = await respuesta.json();

                if (datos.existente) {
                    clienteExistente = datos;
                    limpiarErrores();
                    panelExistente.querySelector('[data-existente-nombre]').textContent = datos.etiqueta;
                    panelExistente.classList.remove('hidden');
                    botonGuardar.classList.add('hidden');

                    return;
                }

                seleccionarCliente(datos);
            } catch (e) {
                mostrarGeneral('No se pudo conectar con el servidor.');
            } finally {
                botonGuardar.disabled = false;
                botonGuardar.textContent = 'Guardar cliente';
            }
        };

        abrir.addEventListener('click', () => {
            reiniciar();
            dialogo.showModal();
        });

        botonGuardar.addEventListener('click', enviar);

        // Enter en cualquier campo dispara el alta (este partial no tiene
        // formulario propio, así tampoco se envía el formulario padre).
        campos.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter' || e.target.tagName === 'BUTTON') return;
            e.preventDefault();
            enviar();
        });

        campos.querySelectorAll('[data-cerrar]').forEach((boton) => {
            boton.addEventListener('click', () => dialogo.close());
        });

        panelExistente.querySelector('[data-usar-existente]').addEventListener('click', () => {
            if (clienteExistente) seleccionarCliente(clienteExistente);
        });

        panelExistente.querySelector('[data-crear-igual]').addEventListener('click', () => {
            forzarCreacion = true;
            clienteExistente = null;
            panelExistente.classList.add('hidden');
            botonGuardar.classList.remove('hidden');
            enviar();
        });
    })();
</script>
