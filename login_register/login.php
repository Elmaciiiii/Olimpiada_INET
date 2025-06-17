<?php
session_start();
require_once '../config/database.php';

$error = '';

// Redirigir si ya está autenticado
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor ingresa tu email y contraseña';
    } else {
        // Buscar el usuario por email
        $stmt = $conn->prepare("SELECT id, nombre, email, password, rol_id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verificar la contraseña
            if (password_verify($password, $user['password'])) {
                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol_id'];
                
                // Redirigir según el rol del usuario
                if ($user['rol_id'] == 2) {
                    header('Location: ../admin/admin.php');
                } elseif ($user['rol_id'] == 1) {
                    header('Location: ../index.php');
                } else {
                    // Rol desconocido, redirigir a página por defecto
                    header('Location: ../index.php');
                }
                exit();
            } else {
                $error = 'Credenciales inválidas';
            }
        } else {
            $error = 'No existe una cuenta con este email';
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../css/login.css">
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
                <button class="toggle-btn active">Iniciar Sesión</button>
                <a href="register.php" class="toggle-btn">Registrarse</a>
            </div>

            <!-- Mostrar mensajes de error -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                    <button class="alert-close" onclick="this.parentElement.classList.add('hide')">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
                <div class="alert alert-success">
                    ¡Registro exitoso! Por favor inicia sesión.
                    <button class="alert-close" onclick="this.parentElement.classList.add('hide')">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Formulario de Login -->
            <div id="loginForm" class="form active">
                <h2 class="form-title">👋 Bienvenido</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="email" name="email" class="form-input" placeholder="📧 Email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-input" placeholder="🔒 Contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        Iniciar Sesión
                    </button>
                </form>
                
                <div class="form-footer">
                    ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>
