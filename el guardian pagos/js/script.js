// Efectos dinámicos y 3D
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.login-card');
    const inputs = document.querySelectorAll('.input-group input');

    // Efecto parallax 3D en la card
    document.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;

        card.style.transform = `
            perspective(1000px) 
            rotateX(${rotateX}deg) 
            rotateY(${rotateY}deg) 
            scale(1.02)
        `;
    });

    // Resetear al salir del mouse
    document.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
    });

    // Animaciones de inputs
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.parentElement.querySelector('i').style.color = '#667eea';
        });

        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
                this.parentElement.querySelector('i').style.color = 'rgba(255,255,255,0.5)';
            }
        });
    });

    // Submit con loading
    const form = document.querySelector('.login-form');
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.btn-login');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando...';
        btn.disabled = true;
    });
});