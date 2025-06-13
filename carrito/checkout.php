<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit;
}

$conn = getDBConnection();
$usuario_id = $_SESSION['user_id'];

// Obtener datos del usuario
$user_query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Obtener items del carrito
$carrito_query = "SELECT c.*, p.nombre, p.destino, p.duracion_dias, p.imagen_principal,
                         cat.nombre as categoria_nombre, cat.icono as categoria_icono,
                         (c.cantidad * c.precio_unitario) as subtotal
                  FROM carrito c
                  JOIN productos p ON c.producto_id = p.id
                  LEFT JOIN categorias cat ON p.categoria_id = cat.id
                  WHERE c.usuario_id = ?";

$stmt = $conn->prepare($carrito_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$items_carrito = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items_carrito[] = $row;
    $total += $row['subtotal'];
}

// Si el carrito está vacío, redirigir
if (empty($items_carrito)) {
    header('Location: ver.php');
    exit;
}

// Obtener métodos de pago
$metodos_query = "SELECT * FROM metodos_pago WHERE activo = 1 ORDER BY nombre";
$metodos_result = $conn->query($metodos_query);
$metodos_pago = [];
if ($metodos_result) {
    while ($row = $metodos_result->fetch_assoc()) {
        $metodos_pago[] = $row;
    }
}

$mensaje_exito = '';
$mensaje_error = '';

// Procesar formulario de checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_reserva'])) {
    try {
        $conn->begin_transaction();
        
        // Validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $metodo_pago_id = intval($_POST['metodo_pago'] ?? 0);
        $notas = trim($_POST['notas'] ?? '');
        
        // Validar que todos los campos requeridos estén completos
        if (empty($nombre) || empty($apellido) || empty($email) || empty($direccion) || $metodo_pago_id <= 0) {
            throw new Exception('Por favor complete todos los campos obligatorios');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email ingresado no es válido');
        }
        
        // Verificar que el método de pago existe y obtener su nombre
        $metodo_stmt = $conn->prepare("SELECT id, nombre FROM metodos_pago WHERE id = ? AND activo = 1");
        $metodo_stmt->bind_param("i", $metodo_pago_id);
        $metodo_stmt->execute();
        $metodo_result = $metodo_stmt->get_result();
        if ($metodo_result->num_rows === 0) {
            throw new Exception('Método de pago no válido');
        }
        $metodo_pago = $metodo_result->fetch_assoc();
        $metodo_pago_nombre = $metodo_pago['nombre'];
        
        // Generar número de reserva único
        $numero_reserva = 'RES-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Verificar que el número de reserva no exista
        $check_reserva = $conn->prepare("SELECT id FROM reservas WHERE numero_reserva = ?");
        $check_reserva->bind_param("s", $numero_reserva);
        $check_reserva->execute();
        while ($check_reserva->get_result()->num_rows > 0) {
            $numero_reserva = 'RES-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $check_reserva->bind_param("s", $numero_reserva);
            $check_reserva->execute();
        }
        
        // Crear la reserva principal
        // Primero, obtener el ID del estado 'pendiente'
        $estado_id = 1; // Valor por defecto para 'pendiente'
        $estado_query = $conn->query("SELECT id FROM estados_reserva WHERE nombre = 'pendiente' LIMIT 1");
        if ($estado_query && $estado_row = $estado_query->fetch_assoc()) {
            $estado_id = $estado_row['id'];
        }

        // Insertar la reserva con los campos correctos según la estructura de la tabla
        $insert_reserva = "INSERT INTO reservas (
            usuario_id,
            numero_reserva,
            fecha_reserva,
            total,
            estado_id,
            nombre_cliente,
            apellido_cliente,
            email_cliente,
            direccion_facturacion,
            numero_personas,
            observaciones,
            created_at,
            updated_at
        ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($insert_reserva);
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        
        $stmt->bind_param(
            "ississsss", 
            $usuario_id,
            $numero_reserva,
            $total,
            $estado_id,
            $nombre,
            $apellido,
            $email,
            $direccion,
            $notas
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error al crear la reserva: ' . $stmt->error);
        }
        
        $reserva_id = $conn->insert_id;
        
        // Crear los detalles de la reserva
        $insert_detalle = "INSERT INTO detalle_reservas (reserva_id, producto_id, cantidad, precio_unitario, subtotal) 
                          VALUES (?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conn->prepare($insert_detalle);
        
        foreach ($items_carrito as $item) {
            $stmt_detalle->bind_param("iiidd", $reserva_id, $item['producto_id'], $item['cantidad'], 
                                    $item['precio_unitario'], $item['subtotal']);
            
            if (!$stmt_detalle->execute()) {
                throw new Exception('Error al guardar los detalles de la reserva');
            }
        }
        
        // Limpiar el carrito
        $clear_carrito = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $clear_carrito->bind_param("i", $usuario_id);
        
        if (!$clear_carrito->execute()) {
            throw new Exception('Error al limpiar el carrito');
        }
        
        $conn->commit();
        
        // Preparar el contenido del correo
        $asunto = "Confirmación de Reserva #$numero_reserva";
        
        $cuerpo = "
        <h2>¡Gracias por tu reserva, $nombre $apellido!</h2>
        <p>Tu reserva ha sido registrada con el número: <strong>$numero_reserva</strong></p>
        
        <h3>Detalles de la Reserva:</h3>
        <p><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</p>
        <p><strong>Total:</strong> $" . number_format($total, 2) . "</p>
        
        <h3>Productos Reservados:</h3>
        <ul>";;
        
        foreach ($items_carrito as $item) {
            $cuerpo .= "<li>{$item['nombre']} x{$item['cantidad']} - $" . number_format($item['subtotal'], 2) . "</li>";
        }
        
        $cuerpo .= "
        </ul>
        
        <p><strong>Método de Pago:</strong> " . htmlspecialchars($metodo_pago_nombre) . "</p>
        <p><strong>Estado:</strong> Pendiente de confirmación</p>
        
        <p>Te notificaremos por correo electrónico una vez que tu reserva sea confirmada.</p>
        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>";
        
        // Enviar correo de confirmación
        $nombre_completo = trim("$nombre $apellido");
        if (true) {
            $_SESSION['mensaje_exito'] = "¡Reserva realizada con éxito! Se ha enviado un correo de confirmación a $email";
        } else {
            $_SESSION['mensaje_advertencia'] = "La reserva se realizó correctamente, pero hubo un error al enviar el correo de confirmación.";
        }
        
        // Redirigir a página de confirmación
        header("Location: confirmacion.php?reserva=" . urlencode($numero_reserva));
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $mensaje_error = $e->getMessage();
        error_log("Error en checkout: " . $e->getMessage());
    }
}

// No cerramos la conexión aquí para que esté disponible en todo el script
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Turismo Córdoba</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/productos.css">
    <style>
        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: #2c2c2c;
            background: linear-gradient(45deg, #2c2c2c, #4a4a4a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .nav-btn {
            color: #2c2c2c;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 18px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .nav-btn i {
            font-size: 1.1em;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .checkout-form {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .checkout-summary {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #2c2c2c;
            margin-bottom: 20px;
            font-size: 1.3rem;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c2c2c;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2196F3;
        }
        
        .form-group.required label::after {
            content: " *";
            color: #f44336;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .metodo-pago {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .metodo-pago:hover {
            border-color: #2196F3;
            background: rgba(33, 150, 243, 0.05);
        }
        
        .metodo-pago input[type="radio"] {
            margin-right: 12px;
            width: auto;
        }
        
        .metodo-pago.selected {
            border-color: #2196F3;
            background: rgba(33, 150, 243, 0.1);
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .item-details {
            flex: 1;
            margin-left: 15px;
        }
        
        .item-name {
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 5px;
        }
        
        .item-info {
            font-size: 0.9rem;
            color: #666;
        }
        
        .item-price {
            font-weight: 600;
            color: #4CAF50;
        }
        
        .total-section {
            border-top: 2px solid #e0e0e0;
            margin-top: 20px;
            padding-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-final {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c2c2c;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .btn-procesar {
            width: 100%;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-procesar:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }
        
        .btn-procesar:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-volver {
            width: 100%;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid #4CAF50;
            color: #2e7d32;
        }
        
        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid #f44336;
            color: #c62828;
        }
        
        @media (max-width: 968px) {
            .checkout-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .checkout-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container">
        <header>
            <div class="logo">
                <a href="../index.php" style="color: inherit; text-decoration: none;">Turismo INET</a>
            </div>
            <div class="nav-buttons">
                <a href="../product/productos.php" class="nav-btn">
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="ver.php" class="nav-btn">
                    <i class="fas fa-shopping-cart"></i> Carrito
                </a>
                <a href="../perfil/" class="nav-btn">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a href="../login_register/logout.php" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </header>
    </div>

    <div class="checkout-container">
        <!-- Formulario de Checkout -->
        <div class="checkout-form">
            <h2 style="color: #2c2c2c; margin-bottom: 30px;"> Finalizar Reserva</h2>
            
            <?php if ($mensaje_error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($mensaje_exito); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkout-form">
                <!-- Información Personal -->
                <div class="form-section">
                    <h3>👤 Información Personal</h3>
                    
                    <?php
                    // Obtener datos del usuario si está autenticado
                    $usuario_nombre = '';
                    $usuario_apellido = '';
                    $usuario_email = '';
                    
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $user_query = $conn->prepare("SELECT nombre, apellido, email FROM usuarios WHERE id = ?");
                        $user_query->bind_param("i", $user_id);
                        $user_query->execute();
                        $user_result = $user_query->get_result();
                        if ($user_data = $user_result->fetch_assoc()) {
                            $usuario_nombre = htmlspecialchars($user_data['nombre']);
                            $usuario_apellido = htmlspecialchars($user_data['apellido']);
                            $usuario_email = htmlspecialchars($user_data['email']);
                        }
                    }
                    ?>
                    
                    <div class="form-row">
                        <div class="form-group required">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" 
                                   value="<?php echo $usuario_nombre; ?>" required>
                        </div>
                        <div class="form-group required">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" 
                                   value="<?php echo $usuario_apellido; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo $usuario_email; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" 
                               value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="form-section">
                    <h3>💳 Método de Pago</h3>
                    
                    <?php if (empty($metodos_pago)): ?>
                        <div class="alert alert-error">
                            ❌ No hay métodos de pago disponibles. Contacte al administrador.
                        </div>
                    <?php else: ?>
                        <?php foreach ($metodos_pago as $metodo): ?>
                            <div class="metodo-pago" onclick="seleccionarMetodo(<?php echo $metodo['id']; ?>)">
                                <input type="radio" name="metodo_pago" value="<?php echo $metodo['id']; ?>" 
                                       id="metodo_<?php echo $metodo['id']; ?>" required>
                                <label for="metodo_<?php echo $metodo['id']; ?>" style="margin: 0; cursor: pointer;">
                                    <strong><?php echo htmlspecialchars($metodo['nombre']); ?></strong>
                                    <?php if ($metodo['descripcion']): ?>
                                        <br><small style="color: #666;"><?php echo htmlspecialchars($metodo['descripcion']); ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Notas Adicionales -->
                <div class="form-section">
                    <h3>📝 Notas Adicionales</h3>
                    
                    <div class="form-group">
                        <label for="notas">Comentarios o solicitudes especiales</label>
                        <textarea id="notas" name="notas" rows="4" 
                                  placeholder="Ej: Preferencias dietéticas, necesidades especiales, etc."></textarea>
                    </div>
                </div>

                <button type="submit" name="procesar_reserva" class="btn-procesar" 
                        <?php echo empty($metodos_pago) ? 'disabled' : ''; ?>>
                    🔒 Confirmar Reserva
                </button>
            </form>
        </div>

        <!-- Resumen de la Compra -->
        <div class="checkout-summary">
            <h3 style="color: #2c2c2c; margin-bottom: 20px;">📋 Resumen del Pedido</h3>
            
            <?php foreach ($items_carrito as $item): ?>
                <div class="summary-item">
                    <div class="categoria-badge" style="font-size: 0.8rem;">
                        <?php echo $item['categoria_icono']; ?>
                    </div>
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['nombre']); ?></div>
                        <div class="item-info">
                            📍 <?php echo htmlspecialchars($item['destino']); ?>
                            <?php if ($item['duracion_dias']): ?>
                                <br>⏰ <?php echo $item['duracion_dias']; ?> día<?php echo $item['duracion_dias'] > 1 ? 's' : ''; ?>
                            <?php endif; ?>
                            <br>👥 <?php echo $item['cantidad']; ?> persona<?php echo $item['cantidad'] > 1 ? 's' : ''; ?>
                        </div>
                    </div>
                    <div class="item-price">
                        $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                
                <div class="total-row total-final">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <a href="ver.php" class="btn-volver">
                ← Volver al Carrito
            </a>
        </div>
    </div>

    <script>
        function seleccionarMetodo(metodoId) {
            // Remover selección previa
            document.querySelectorAll('.metodo-pago').forEach(metodo => {
                metodo.classList.remove('selected');
            });
            
            // Seleccionar el método actual
            const metodoSeleccionado = document.querySelector(`input[value="${metodoId}"]`).closest('.metodo-pago');
            metodoSeleccionado.classList.add('selected');
            
            // Marcar el radio button
            document.querySelector(`input[value="${metodoId}"]`).checked = true;
        }

        // Validación del formulario
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let allValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    allValid = false;
                    field.style.borderColor = '#f44336';
                } else {
                    field.style.borderColor = '#e0e0e0';
                }
            });
            
            // Validar email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                allValid = false;
                email.style.borderColor = '#f44336';
            }
            
            if (!allValid) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios correctamente.');
            }
        });

        // Limpiar estilos de error al escribir
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.style.borderColor = '#e0e0e0';
            });
        });
    </script>
</body>
</html>

<?php
// Cerrar la conexión al final del script
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>