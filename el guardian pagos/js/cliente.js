let servicioSeleccionado = null;

function formatoMoneda(valor) {
    return 'Bs ' + Number(valor).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function abrirModal() {
    const modal = document.getElementById('modal-registro');
    if (!modal) return;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    const modal = document.getElementById('modal-registro');
    const form = document.getElementById('form-registro');

    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';

    if (form) form.reset();
    servicioSeleccionado = null;
}

function seleccionarServicio(id, nombre, precio) {
    servicioSeleccionado = {
        id: id,
        nombre: nombre,
        precio: Number(precio)
    };

    const servicioId = document.getElementById('servicio_id');
    const servicioNombre = document.getElementById('servicio-nombre');
    const servicioPrecio = document.getElementById('servicio-precio');

    if (servicioId) servicioId.value = id;
    if (servicioNombre) servicioNombre.textContent = nombre;
    if (servicioPrecio) servicioPrecio.textContent = formatoMoneda(precio);

    abrirModal();
}

function abrirModalExito() {
    const modal = document.getElementById('modal-exito');
    if (!modal) return;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalExito() {
    const modal = document.getElementById('modal-exito');

    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';

    window.location.href = 'cliente_invitado.php';
}

document.addEventListener('DOMContentLoaded', function() {
    const modalRegistro = document.getElementById('modal-registro');
    const modalExito = document.getElementById('modal-exito');
    const formRegistro = document.getElementById('form-registro');

    if (modalRegistro) {
        modalRegistro.addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
    }

    if (modalExito) {
        modalExito.addEventListener('click', function(e) {
            if (e.target === this) cerrarModalExito();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
        }
    });

    if (formRegistro) {
        formRegistro.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!servicioSeleccionado) {
                alert('Por favor selecciona un servicio');
                return;
            }

            const btnSubmit = this.querySelector('.btn-primary');
            const textoOriginal = btnSubmit ? btnSubmit.innerHTML : '';

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            }

            const formData = new FormData(this);
            formData.set('servicio_id', servicioSeleccionado.id);

            fetch('php/api.php?action=cliente_con_pago', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('API response:', data);

                    if (data.success) {
                        const nombreServicio = servicioSeleccionado.nombre;
                        cerrarModal();

                        const solicitudNumero = document.getElementById('solicitud-numero');
                        const servicioContratado = document.getElementById('servicio-contratado-nombre');

                        if (solicitudNumero) {
                            solicitudNumero.textContent = '#' + String(data.cliente_id || 0).padStart(5, '0');
                        }

                        if (servicioContratado) {
                            servicioContratado.textContent = nombreServicio;
                        }

                        abrirModalExito();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo enviar la solicitud'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión. Intenta de nuevo.');
                })
                .finally(() => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = textoOriginal;
                    }
                });
        });
    }

    const cards = document.querySelectorAll('.servicio-card');

    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            const target = document.querySelector(this.getAttribute('href'));

            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});