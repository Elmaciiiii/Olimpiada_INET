<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit;
}

$numero_reserva = $_GET['reserva'] ?? '';
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_advertencia = $_SESSION['mensaje_advertencia'] ?? '';

// Limpiar los mensajes de la sesión
unset($_SESSION['mensaje_exito']);
unset($_SESSION['mensaje_advertencia']);

// Si no hay número de reserva, redirigir al inicio
if (empty($numero_reserva)) {
    header('Location: ../index.php');
    exit;
}

// Obtener detalles de la reserva
$conn = getDBConnection();
$query = "SELECT r.*, 'Efectivo' as metodo_pago_nombre 
          FROM reservas r 
          WHERE r.numero_reserva = ? AND r.usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $numero_reserva, $_SESSION['user_id']);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

// Si no se encuentra la reserva, redirigir al inicio
if (!$reserva) {
    header('Location: ../index.php');
    exit;
}

// Obtener detalles de los productos de la reserva
$detalles_query = "SELECT rd.*, p.nombre 
                  FROM detalle_reservas rd 
                  JOIN productos p ON rd.producto_id = p.id 
                  WHERE rd.reserva_id = ?";
$stmt = $conn->prepare($detalles_query);
$stmt->bind_param("i", $reserva['id']);
$stmt->execute();
$detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva - Turismo INET</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Contenedor principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 40px 60px;
            position: relative;
            z-index: 1;
        }
        
        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0 0 20px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: #2c2c2c;
            background: linear-gradient(45deg, #2c2c2c, #4a4a4a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: #2c2c2c;
            text-decoration: none;
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
        
        /* Tarjeta de confirmación */
        .confirmation-container {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(145deg, #ffffff 0%, #f0f4f8 100%);
        }
        
        .confirmation-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
        }
        
        .reserva-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .reserva-header h1 {
            margin: 0 0 15px;
            color: #2c3e50;
            font-size: 2.5em;
            font-weight: 700;
        }
        
        .reserva-numero {
            font-size: 1.1em;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .reserva-estado {
            display: inline-block;
            padding: 8px 25px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1em;
            margin-top: 10px;
        }
        
        /* Secciones de resumen */
        .resumen-pedido, 
        .reserva-info {
            background: linear-gradient(to right, rgba(255, 255, 255, 0.7), rgba(240, 244, 248, 0.9));
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        h2, h3 {
            color: #2c3e50;
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        /* Detalles de productos */
        .detalle-producto {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .detalle-producto:hover {
            background: rgba(255, 255, 255, 0.5);
            padding-left: 10px;
            border-radius: 8px;
        }
        
        .detalle-producto:last-child {
            border-bottom: none;
        }
        
        .detalle-producto > div:first-child {
            flex: 2;
        }
        
        .detalle-producto > div:last-child {
            text-align: right;
        }
        
        .detalle-producto strong {
            display: block;
            font-size: 1.1em;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .detalle-producto p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 0.95em;
        }
        
        /* Total */
        .total-section {
            text-align: right;
            margin-top: 25px;
            font-size: 1.3em;
            font-weight: 700;
            color: #2c3e50;
        }
        
        /* Botones */
        .btn-volver {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.2);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-volver::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #3498db, #2c3e50);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .btn-volver:hover::before {
            left: 0;
        }
        
        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 62, 80, 0.3);
            background: linear-gradient(45deg, #3498db, #2c3e50);
        }
        
        .btn-volver i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        
        /* Mensajes */
        .mensaje-exito, 
        .mensaje-advertencia {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95em;
            line-height: 1.5;
        }
        
        .mensaje-exito {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        
        .mensaje-advertencia {
            background: #fff8e1;
            color: #ff8f00;
            border-left: 4px solid #ffc107;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .nav-buttons {
                gap: 10px;
            }
            
            .nav-btn {
                padding: 6px 12px;
                font-size: 0.9em;
            }
            
            .nav-btn.cta {
                padding: 8px 15px;
            }
            
            .reserva-header {
                padding: 25px 20px;
            }
            
            .reserva-header h1 {
                font-size: 2em;
            }
            
            .resumen-pedido,
            .resumen-cliente {
                padding: 20px 15px;
            }
            
            .detalle-producto {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .detalle-producto > div:last-child {
                text-align: left;
                width: 100%;
                padding-top: 10px;
                border-top: 1px dashed #eee;
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
    
    <header>
        <a href="../index.php" class="logo">Turismo INET</a>
        <div class="nav-buttons">
            <a href="../index.php" class="nav-btn"><i class="fas fa-home"></i> Inicio</a>
            <a href="#" class="nav-btn" id="ver-carrito"><i class="fas fa-shopping-cart"></i> Carrito</a>
            <a href="#" class="nav-btn"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Mi Cuenta'); ?></a>
        </div>
    </header>

    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 50px;">
            <h1 class="section-title">¡Reserva Confirmada!</h1>
            <p class="section-subtitle">Gracias por elegir Turismo Córdoba. Tu aventura está a punto de comenzar.</p>
        </div>
        
        <div class="confirmation-container">
        <?php if ($mensaje_exito): ?>
            <div class="mensaje-exito">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mensaje_advertencia): ?>
            <div class="mensaje-advertencia">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($mensaje_advertencia); ?>
            </div>
        <?php endif; ?>

        <div class="reserva-header">
            <h1>¡Reserva Confirmada!</h1>
            <div class="reserva-numero">Número de Reserva: <?php echo htmlspecialchars($reserva['numero_reserva']); ?></div>
            <div class="reserva-estado"><?php echo 'Pendiente'; // Estado por defecto ya que no hay columna estado ?></div>
        </div>

        <div class="resumen-pedido">
            <h2>Resumen del Pedido</h2>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></p>
            <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($reserva['metodo_pago_nombre']); ?></p>
            
            <h3>Productos:</h3>
            <?php foreach ($detalles as $detalle): ?>
                <div class="detalle-producto">
                    <div>
                        <strong><?php echo htmlspecialchars($detalle['nombre']); ?></strong>
                        <p><?php echo htmlspecialchars($detalle['descripcion_corta'] ?? ''); ?></p>
                    </div>
                    <div>
                        <?php echo $detalle['cantidad']; ?> x $<?php echo number_format($detalle['precio_unitario'], 2); ?>
                        <div><strong>Subtotal: $<?php echo number_format($detalle['subtotal'], 2); ?></strong></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="total-section">
                <p>Total: <strong>$<?php echo number_format($reserva['total'], 2); ?></strong></p>
            </div>
        </div>

        <div class="resumen-cliente">
            <h3>Datos del Cliente</h3>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($reserva['nombre_cliente'] . ' ' . $reserva['apellido_cliente']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($reserva['email_cliente'] ?? 'No especificado'); ?></p>
            <?php if (!empty($reserva['direccion'])): ?>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($reserva['direccion']); ?></p>
            <?php endif; ?>
            <?php if (!empty($reserva['notas'])): ?>
                <p><strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($reserva['notas'])); ?></p>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="../index.php" class="btn-volver">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
            <a href="../perfil/mis-reservas.php" class="btn-volver" style="background: #2196F3; margin-left: 15px;">
                <i class="fas fa-list"></i> Ver Mis Reservas
            </a>
        </div>
    </div>
    <script>
        // Efecto de movimiento suave para los elementos flotantes
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                const xPos = (x * 20 * speed) - (10 * speed);
                const yPos = (y * 20 * speed) - (10 * speed);
                
                shape.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        });
    </script>
</body>
</html>
