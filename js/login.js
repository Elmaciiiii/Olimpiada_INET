function showLogin() {
    document.getElementById('loginForm').classList.add('active');
    document.getElementById('registerForm').classList.remove('active');
    
    document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function showRegister() {
    document.getElementById('registerForm').classList.add('active');
    document.getElementById('loginForm').classList.remove('active');
    
    document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function handleLogin(event) {
    event.preventDefault();
    const email = event.target.querySelector('input[type="email"]').value;
    const password = event.target.querySelector('input[type="password"]').value;
    
    // Simulación de login
    showNotification('🎉 ¡Inicio de sesión exitoso! Bienvenido de vuelta.', 'success');
    
    // Aquí irían las validaciones reales y el envío al servidor
    setTimeout(() => {
        window.location.href = '#dashboard'; // Redirigir al dashboard
    }, 2000);
}

function handleRegister(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const nombre = event.target.querySelector('input[placeholder*="Nombre"]').value;
    const apellido = event.target.querySelector('input[placeholder*="Apellido"]').value;
    const email = event.target.querySelector('input[type="email"]').value;
    const password = event.target.querySelector('input[type="password"]').value;
    
    // Validaciones básicas
    if (password.length < 6) {
        showNotification('❌ La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    // Simulación de registro
    showNotification("🎉 ¡Cuenta creada exitosamente! Bienvenido " + nombre + "!", 'success');
    
    // Cambiar automáticamente al login después del registro
    setTimeout(() => {
        showLogin();
    }, 2000);
}

function socialLogin(platform) {
    showNotification("🔗 Conectando con " + platform + "...", 'success');
    // Aquí iría la integración real con las APIs de redes sociales
}

function forgotPassword() {
    const email = prompt('Ingresa tu email para recuperar la contraseña:');
    if (email) {
        showNotification('📧 Te hemos enviado un email con las instrucciones', 'success');
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = "notification " + type;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 400);
    }, 4000);
}

// Efectos de entrada para los inputs
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach((input, index) => {
        input.style.animationDelay = index * 0.1 + "s";
        input.style.animation = 'fadeIn 0.6s ease-out forwards';
    });
});

// Efectos de partículas al hacer clic
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('submit-btn')) {
        createParticles(e.target);
    }
});

function createParticles(element) {
    const rect = element.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    for (let i = 0; i < 6; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: fixed;
            width: 6px;
            height: 6px;
            background: #7db899;
            border-radius: 50%;
            left: ${centerX}px;
            top: ${centerY}px;
            pointer-events: none;
            z-index: 1000;
            animation: particleExplode 0.8s ease-out forwards;
        `;
        
        particle.style.setProperty('--angle', i * 60 + "deg");
        document.body.appendChild(particle);
        
        setTimeout(() => particle.remove(), 800);
    }
}

// Añadir estilos de animación para las partículas
const style = document.createElement('style');
style.textContent = `
    @keyframes particleExplode {
        to {
            transform: 
                rotate(var(--angle)) 
                translateX(50px) 
                scale(0);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);