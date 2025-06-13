<?php
// Iniciar la sesión
session_start();

// Redirigir a login si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit();
}

// Incluir el archivo de conexión a la base de datos
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();
$usuario_id = $_SESSION['user_id'];

// Obtener items del carrito
$query = "SELECT c.*, p.nombre, p.destino, p.duracion_dias, p.imagen_principal,
                 cat.nombre as categoria_nombre, cat.icono as categoria_icono,
                 (c.cantidad * c.precio_unitario) as subtotal
          FROM carrito c
          JOIN productos p ON c.producto_id = p.id
          LEFT JOIN categorias cat ON p.categoria_id = cat.id
          WHERE c.usuario_id = ?
          ORDER BY c.fecha_agregado DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$items_carrito = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items_carrito[] = $row;
    $total += $row['subtotal'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Turismo INET</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #7db899 0%, #6ba085 50%, #5a8871 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
            position: relative;
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="30" cy="30" r="2"/></g></g></svg>');
            pointer-events: none;
            z-index: -1;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
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
            gap: 20px;
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
        
        .carrito-header {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .carrito-header h1 {
            color: #2c2c2c;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .carrito-empty {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .carrito-empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .carrito-item {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 20px;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .carrito-item:hover {
            transform: translateY(-2px);
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-nombre {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 8px;
        }
        
        .item-detalles {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .item-detalles span {
            font-size: 0.9rem;
            color: #666;
        }
        
        .item-precio {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4CAF50;
        }
        
        .item-cantidad {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cantidad-btn {
            background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
            color: #2c2c2c;
            padding: 5px 10px;
            width: 30px;
            height: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .cantidad-btn:hover {
            background: linear-gradient(135deg, #e0e0e0, #d0d0d0);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .cantidad-input {
            width: 50px;
            height: 30px;
            text-align: center;
            margin: 0 5px;
            padding: 5px;
            border: 2px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            color: #2c2c2c;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .cantidad-input:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        
        /* Botones */
        .btn-eliminar,
        .cantidad-btn,
        .btn-checkout,
        .btn-seguir-comprando {
            font-family: 'Poppins', sans-serif;
            border: none;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-eliminar {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            padding: 10px 18px;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
        }

        .btn-eliminar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
            background: linear-gradient(135deg, #e53935, #c62828);
        }
        
        .carrito-resumen {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .resumen-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .resumen-row:last-child {
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c2c2c;
            margin-top: 10px;
        }
        
        .btn-checkout {
            width: 100%;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 12px 24px;
            font-size: 1rem;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.5);
            background: linear-gradient(135deg, #45a049, #3d8b40);
        }
        
        .btn-seguir-comprando {
            width: 100%;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            padding: 12px 24px;
            font-size: 1rem;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
        }
        
        .btn-seguir-comprando:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
            background: linear-gradient(135deg, #1976D2, #1565C0);
        }
        
        @media (max-width: 768px) {
            .carrito-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-detalles {
                justify-content: center;
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="../index.php" class="nav-btn">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                    <a href="../product/productos.php" class="nav-btn">
                        <i class="fas fa-box"></i> Productos
                    </a>
                    <a href="../perfil/" class="nav-btn">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                    <a href="../login_register/logout.php" class="nav-btn">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="../login_register/login.php" class="nav-btn">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <a href="../login_register/register.php" class="nav-btn">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="carrito-header">
            <h1>🛒 Mi Carrito de Compras</h1>
            <p>Revisa tus productos seleccionados antes de continuar</p>
        </div>

        <?php if (empty($items_carrito)): ?>
            <div class="carrito-empty">
                <div class="carrito-empty-icon">🛒</div>
                <h3>Tu carrito está vacío</h3>
                <p>¡Explora nuestros increíbles productos y experiencias!</p>
                <a href="../product/productos.php" class="btn-seguir-comprando">
                    🔍 Ver Productos
                </a>
            </div>
        <?php else: ?>
            <div class="carrito-items">
                <?php foreach ($items_carrito as $item): ?>
                    <div class="carrito-item" data-item-id="<?php echo $item['id']; ?>">
                        <div class="item-info">
                            <div class="categoria-badge">
                                <?php echo $item['categoria_icono'] . ' ' . htmlspecialchars($item['categoria_nombre']); ?>
                            </div>
                            <div class="item-nombre"><?php echo htmlspecialchars($item['nombre']); ?></div>
                            <div class="item-detalles">
                                <span>📍 <?php echo htmlspecialchars($item['destino']); ?></span>
                                <?php if ($item['duracion_dias']): ?>
                                    <span>⏰ <?php echo $item['duracion_dias']; ?> día<?php echo $item['duracion_dias'] > 1 ? 's' : ''; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['descripcion_corta'])): ?>
                                <p style="color: #666; font-size: 0.9rem; margin: 10px 0;">
                                    <?php echo htmlspecialchars($item['descripcion_corta']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="item-precio">$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?> por persona</div>
                        </div>
                        
                        <div class="item-cantidad" style="display: flex; align-items: center; gap: 10px;">
                            <button class="cantidad-btn" onclick="cambiarCantidad(<?php echo $item['id']; ?>, -1)">-</button>
                            <input type="number" class="cantidad-input" value="<?php echo $item['cantidad']; ?>" 
                                   min="1" onchange="actualizarCantidad(<?php echo $item['id']; ?>, this.value)">
                            <button class="cantidad-btn" onclick="cambiarCantidad(<?php echo $item['id']; ?>, 1)">+</button>
                            
                            <button class="btn-eliminar" onclick="eliminarItem(<?php echo $item['id']; ?>)" style="margin-left: 10px;">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </div>
                        
                        <div class="item-precio" style="font-weight: 700; font-size: 1.2rem; color: #2c2c2c;">
                            $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="carrito-resumen">
                <h3 style="margin-bottom: 20px; color: #2c2c2c;">📋 Resumen del Pedido</h3>
                
                <div class="resumen-row">
                    <span>Subtotal (<?php echo count($items_carrito); ?> producto<?php echo count($items_carrito) > 1 ? 's' : ''; ?>):</span>
                    <span id="subtotal">$<?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                
                <div class="resumen-row">
                    <span><strong>Total:</strong></span>
                    <span id="total"><strong>$<?php echo number_format($total, 0, ',', '.'); ?></strong></span>
                </div>
                
                <a href="checkout.php" class="btn-checkout">
                    💳 Proceder al Checkout
                </a>
                
                <a href="../product/productos.php" class="btn-seguir-comprando">
                    🔍 Seguir Comprando
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function cambiarCantidad(itemId, cambio) {
            const input = document.querySelector(`[data-item-id="${itemId}"] .cantidad-input`);
            const nuevaCantidad = Math.max(1, parseInt(input.value) + cambio);
            input.value = nuevaCantidad;
            actualizarCantidad(itemId, nuevaCantidad);
        }

        function actualizarCantidad(itemId, cantidad) {
            cantidad = Math.max(1, parseInt(cantidad));
            
            fetch('actualizar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_id: itemId,
                    cantidad: cantidad
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Recargar para actualizar totales
                } else {
                    mostrarNotificacion('❌ Error al actualizar: ' + data.message, 'error');
                }
            })
            .catch(error => {
                mostrarNotificacion('❌ Error de conexión', 'error');
            });
        }

        function eliminarItem(itemId) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                fetch('eliminar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        item_id: itemId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        mostrarNotificacion('❌ Error al eliminar: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    mostrarNotificacion('❌ Error de conexión', 'error');
                });
            }
        }

        function mostrarNotificacion(mensaje, tipo) {
            const notification = document.createElement('div');
            notification.className = `notification ${tipo}`;
            notification.textContent = mensaje;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>