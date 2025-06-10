<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro e Inicio de Sesión</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container">
        <div class="auth-container">
            <div class="toggle-buttons">
                <button class="toggle-btn active" onclick="showLogin()">Iniciar Sesión</button>
                <button class="toggle-btn" onclick="showRegister()">Registrarse</button>
            </div>

            <!-- Formulario de Login -->
            <div id="loginForm" class="form active">
                <h2 class="form-title">👋 Bienvenido</h2>
                <form onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <input type="email" class="form-input" placeholder="📧 Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-input" placeholder="🔒 Contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        Iniciar Sesión
                    </button>
                </form>
                
                <div class="social-divider">
                    <span>o continúa con</span>
                </div>
                
                <div class="social-login">
                    <button class="social-btn google" onclick="socialLogin('Google')">G</button>
                    <button class="social-btn facebook" onclick="socialLogin('Facebook')">f</button>
                    <button class="social-btn twitter" onclick="socialLogin('Twitter')">🐦</button>
                </div>

                <div class="form-footer">
                    ¿Olvidaste tu contraseña? <a href="#" onclick="forgotPassword()">Recuperar</a>
                </div>
            </div>

            <!-- Formulario de Registro -->
            <div id="registerForm" class="form">
                <h2 class="form-title">🚀 Crear Cuenta</h2>
                <form onsubmit="handleRegister(event)">
                    <div class="form-group">
                        <input type="text" class="form-input" placeholder="👤 Nombre" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-input" placeholder="👥 Apellido" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-input" placeholder="📧 Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-input" placeholder="🔒 Contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        Crear Cuenta
                    </button>
                </form>

                <div class="social-divider">
                    <span>o regístrate con</span>
                </div>
                
                <div class="social-login">
                    <button class="social-btn google" onclick="socialLogin('Google')">G</button>
                    <button class="social-btn facebook" onclick="socialLogin('Facebook')">f</button>
                    <button class="social-btn twitter" onclick="socialLogin('Twitter')">🐦</button>
                </div>

                <div class="form-footer">
                    ¿Ya tienes una cuenta? <a href="#" onclick="showLogin()">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>