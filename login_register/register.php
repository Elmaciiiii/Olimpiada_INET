<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        
        // Verificar si la conexión se estableció correctamente
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Depuración: Verificar datos recibidos
        error_log("Datos del formulario: " . print_r($_POST, true));
        
        // Validaciones básicas
        if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
            throw new Exception('Todos los campos son obligatorios');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del email no es válido');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres');
        }
        
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('El email ya está registrado');
        }
        
        // Hash de la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            throw new Exception('Error al generar el hash de la contraseña');
        }
        
        // Insertar nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta de inserción: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $nombre, $apellido, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            // Limpiar los campos del formulario
            $_POST = array();
        } else {
            throw new Exception('Error al registrar el usuario: ' . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Error en registro: " . $error);
        // Cerrar conexión si está abierta
        if (isset($conn) && $conn) {
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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
                <a href="login.php" class="toggle-btn">Iniciar Sesión</a>
                <button class="toggle-btn active">Registrarse</button>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                    <button class="alert-close" onclick="this.parentElement.classList.add('hide')">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <button class="alert-close" onclick="this.parentElement.classList.add('hide')">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Formulario de Registro -->
            <div id="registerForm" class="form active">
                <h2 class="form-title">🚀 Crear Cuenta</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-input" placeholder="👤 Nombre" 
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="apellido" class="form-input" placeholder="👥 Apellido" 
                               value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-input" placeholder="📧 Email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-input" placeholder="🔒 Contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        Crear Cuenta
                    </button>
                </form>
                <div class="form-footer">
                    ¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>
