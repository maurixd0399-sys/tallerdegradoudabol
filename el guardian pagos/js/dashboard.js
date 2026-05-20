// Variables globales
let clientes = [];
let servicios = [];
let pagos = [];
let recibos = [];

// Inicializar dashboard
document.addEventListener('DOMContentLoaded', async function() {
    initNavigation();
    initForms();
    await cargarDatos();
    cargarSelects();
});

// Navegación sidebar
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();

            const section = item.dataset.section;
            const activeItem = document.querySelector('.nav-item.active');

            if (activeItem) activeItem.classList.remove('active');
            item.classList.add('active');

            mostrarSeccion(section);

            const pageTitle = document.getElementById('page-title');
            if (pageTitle) {
                pageTitle.textContent = item.textContent.trim();
            }
        });
    });
}

// Mostrar sección
function mostrarSeccion(section) {
    document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
    });

    const sectionElement = document.getElementById(section);
    if (sectionElement) {
        sectionElement.classList.add('active');
    }
}

// Cargar todos los datos
async function cargarDatos() {
    await Promise.all([
        cargarClientes(),
        cargarServicios(),
        cargarPagos(),
        cargarRecibos()
    ]);
}

// 🎯 NUEVA FUNCIÓN: Recargar todo
function recargarTodo() {
    Promise.all([
        cargarClientes(),
        cargarServicios(),
        cargarPagos(),
        cargarRecibos()
    ]).then(() => {
        cargarSelects();
    });
}

// Cargar clientes
async function cargarClientes() {
    const res = await fetch('php/api.php?action=clientes');
    clientes = await res.json();
    renderClientes();
}

// Render clientes
function renderClientes() {
    const tbody = document.getElementById('clientes-table');
    if (!tbody) return;

    tbody.innerHTML = '';

    clientes.forEach(cliente => {
        tbody.innerHTML += `
            <tr>
                <td>#${cliente.id}</td>
                <td>${cliente.nombre}</td>
                <td>${cliente.email || '<em>Sin email</em>'}</td>
                <td><a href="tel:${cliente.telefono}">${cliente.telefono}</a></td>
                <td>${cliente.direccion}</td>
                <td>
                    <button class="btn-action" onclick="editarCliente(${cliente.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-action btn-danger" onclick="eliminarCliente(${cliente.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
}

function editarCliente(id) {
    fetch(`php/api.php?action=cliente_get&id=${encodeURIComponent(id)}`)
        .then(res => res.json())
        .then(cliente => {
            if (!cliente || !cliente.id) {
                alert('No se encontró el cliente');
                return;
            }

            mostrarModalEditar(cliente);
        })
        .catch(error => {
            console.error(error);
            alert('Error al cargar datos del cliente');
        });
}

function mostrarModalEditar(cliente) {
    const modal = document.getElementById('modal-cliente-edit');
    const form = document.getElementById('form-cliente-edit');

    if (!modal) {
        alert('No existe el modal con id="modal-cliente-edit"');
        return;
    }

    if (!form) {
        alert('No existe el formulario con id="form-cliente-edit"');
        return;
    }

    form.dataset.id = cliente.id;

    if (form.nombre) form.nombre.value = cliente.nombre || '';
    if (form.email) form.email.value = cliente.email || '';
    if (form.telefono) form.telefono.value = cliente.telefono || '';
    if (form.direccion) form.direccion.value = cliente.direccion || '';

    modal.classList.add('active');
}

// Eliminar cliente
function eliminarCliente(id) {
    if (!confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.')) return;

    fetch('php/api.php?action=cliente_delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(id)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                recargarTodo(); // ✅ CAMBIO: recarga todo
                alert('Cliente eliminado');
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar'));
            }
        });
}

// Modales
function mostrarModal(tipo) {
    const modal = document.getElementById('modal-' + tipo);
    if (modal) modal.classList.add('active');
}

function cerrarModal(tipo) {
    const modal = document.getElementById('modal-' + tipo);
    const form = document.getElementById('form-' + tipo);

    if (modal) modal.classList.remove('active');
    if (form) form.reset();
}

// Cargar servicios
async function cargarServicios() {
    const res = await fetch('php/api.php?action=servicios');
    servicios = await res.json();
    renderServicios();
}

// Render servicios
function renderServicios() {
    const tbody = document.getElementById('servicios-table');
    if (!tbody) return;

    tbody.innerHTML = '';

    servicios.forEach(servicio => {
        tbody.innerHTML += `
            <tr>
                <td>${servicio.nombre}</td>
                <td>${servicio.descripcion}</td>
                <td>Bs ${Number(servicio.precio).toLocaleString('es-CO')}</td>
                <td>${servicio.duracion_meses} meses</td>
                <td>
                    <span class="status activo">${servicio.estado}</span>
                </td>
            </tr>
        `;
    });
}

// Cargar pagos pendientes
async function cargarPagos() {
    const res = await fetch('php/api.php?action=pagos');
    pagos = await res.json();
    renderPagos();
}

// Render pagos
function renderPagos() {
    const tbody = document.getElementById('pagos-table');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!pagos || pagos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:3rem;color:#999;">No hay pagos pendientes</td></tr>';
        return;
    }

    pagos.forEach(pago => {
        tbody.innerHTML += `
            <tr>
                <td>${pago.cliente_nombre}</td>
                <td>${pago.servicio_nombre}</td>
                <td>Bs ${Number(pago.monto).toLocaleString('es-CO')}</td>
                <td>${pago.fecha_pago}</td>
                <td><span class="status ${pago.estado}">${pago.estado.toUpperCase()}</span></td>
                <td>
                    <button class="btn-action" onclick="confirmarPago(${pago.id})" title="Confirmar Pago">
                        <i class="fas fa-check-circle"></i>
                    </button>
                </td>
            </tr>
        `;
    });
}

// Confirmar pago y generar recibo
function confirmarPago(pagoId) {
    if (!confirm('¿Confirmar pago y generar recibo?')) return;

    fetch('php/api.php?action=pago_confirmar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pago_id: pagoId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Pago confirmado. Recibo generado: ' + data.recibo_numero);
                recargarTodo(); // ✅ CAMBIO: recarga todo
            } else {
                alert('Error: ' + (data.message || 'No se pudo confirmar el pago'));
            }
        });
}

// Cargar recibos
async function cargarRecibos() {
    const res = await fetch('php/api.php?action=recibos');
    recibos = await res.json();
    renderRecibos();
}

// Render recibos
function renderRecibos() {
    const tbody = document.getElementById('recibos-table');
    if (!tbody) return;

    tbody.innerHTML = '';

    recibos.forEach(recibo => {
        tbody.innerHTML += `
            <tr>
                <td>${recibo.numero_recibo}</td>
                <td>${recibo.cliente_nombre}</td>
                <td>Bs ${Number(recibo.total_pagado).toLocaleString('es-CO')}</td>
                <td>${new Date(recibo.fecha_emision).toLocaleDateString('es-CO')}</td>
                <td>
                    <a href="php/generar_pdf.php?id=${recibo.id}" target="_blank" class="btn-action" title="Descargar PDF">
                        <i class="fas fa-download"></i>
                    </a>
                </td>
            </tr>
        `;
    });
}

// Cargar datos para selects
function cargarSelects() {
    const clienteSelect = document.querySelector('[name="cliente_id"]');
    const servicioSelectCliente = document.querySelector('#form-cliente [name="servicio_id"]'); // ✅ Para modal cliente
    const servicioSelectPago = document.querySelector('[name="servicio_id"]:not(#form-cliente [name="servicio_id"])'); // Para otros

    if (clienteSelect) {
        clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';
        clientes.forEach(cliente => {
            const option = new Option(cliente.nombre, cliente.id);
            clienteSelect.appendChild(option);
        });
    }

    // ✅ Cargar servicios en TODOS los selects
    const allServicioSelects = document.querySelectorAll('[name="servicio_id"]');
    allServicioSelects.forEach(select => {
        select.innerHTML = '<option value="">Seleccione un servicio</option>';
        servicios.forEach(servicio => {
            const precio = Number(servicio.precio).toLocaleString('es-CO');
            const option = new Option(`${servicio.nombre} (BS ${precio})`, servicio.id);
            select.appendChild(option);
        });
    });
}

// 🎯 FUNCIÓN NUEVA: Inicializar formularios MEJORADO
function initForms() {
    // ✅ FORM CLIENTE - CREAR CLIENTE + PAGO AUTOMÁTICO
    const formCliente = document.getElementById('form-cliente');
    if (formCliente) {
        formCliente.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const servicioId = formData.get('servicio_id');

            // ✅ VALIDAR SERVICIO OBLIGATORIO
            if (!servicioId) {
                alert('❌ Debes seleccionar un SERVICIO');
                return;
            }

            // ✅ NUEVA ACCIÓN: cliente_con_pago (crea cliente + pago pendiente)
            fetch('php/api.php?action=cliente_con_pago', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ Cliente "${data.cliente_nombre}" creado!\n💳 Pago pendiente: Bs ${Number(data.monto).toLocaleString('es-CO')}`);
                        cerrarModal('cliente');
                        recargarTodo(); // ✅ RECARGA PAGOS AUTOMÁTICO
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear'));
                    }
                });
        });
    }

    // Form editar cliente (sin cambios)
    const formClienteEdit = document.getElementById('form-cliente-edit');
    if (formClienteEdit) {
        formClienteEdit.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('id', this.dataset.id);

            fetch('php/api.php?action=cliente_update', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        recargarTodo();
                        cerrarModal('cliente-edit');
                        alert('Cliente actualizado');
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    }
                });
        });
    }

    // Form nuevo pago manual (sin cambios)
    const formNuevoPago = document.getElementById('form-nuevo-pago');
    if (formNuevoPago) {
        formNuevoPago.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('php/api.php?action=pago_nuevo', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(`Pago creado. Monto: Bs ${Number(data.monto).toLocaleString('es-CO')}`);
                        cerrarModal('nuevo-pago');
                        recargarTodo();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear el pago'));
                    }
                });
        });
    }
}