<?php
session_start();

// Redirigir a login si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();
$usuario_id = $_SESSION['user_id'];

// Obtener información del usuario
$usuario_query = "SELECT id, nombre, apellido, email, rol_id, created_at FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($usuario_query);
$stmt->bind_param("i", $usuario_id); 
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Obtener reservas del usuario
$reservas_query = "SELECT 
                    r.id, 
                    r.numero_reserva, 
                    r.fecha_reserva, 
                    r.fecha_inicio, 
                    r.fecha_fin, 
                    r.total as monto_total, 
                    r.estado_id, 
                    r.numero_personas as cantidad_personas, 
                    r.observaciones,
                    er.nombre as estado_nombre, 
                    er.color as estado_color 
                FROM reservas r
                JOIN estados_reserva er ON r.estado_id = er.id
                WHERE r.usuario_id = ?
                ORDER BY r.fecha_reserva DESC";
$stmt = $conn->prepare($reservas_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$reservas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Turismo INET</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/perfil.css">
</head>
<body>
    <style>
 :root {
    --primary-color: #7db899;
    --primary-dark: #6ba085;
    --primary-darker: #5a8871;
    --primary-light: #8cc5a5;
    --secondary-color: #45a049;
   
    --warning-color: #f39c12;
    --success-color: #27ae60;
    --text-color: #2c3e50;
    --text-light: #34495e;
    --text-muted: #7f8c8d;
    --light-gray: #ecf0f1;
    --border-color: #bdc3c7;
    --white: #ffffff;
    --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.08);
    --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.12);
    --shadow-heavy: 0 8px 30px rgba(0, 0, 0, 0.15);
    --gradient-primary: linear-gradient(135deg, #7db899 0%, #6ba085 50%, #5a8871 100%);
    --gradient-card: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    --gradient-button: linear-gradient(135deg, #7db899, #6ba085);
    --gradient-button-hover: linear-gradient(135deg, #6ba085, #5a8871);
    --border-radius: 12px;
    --border-radius-lg: 20px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            color: var(--text-color);
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        /* Formas flotantes decorativas */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            background: var(--primary-light);
            top: 20%;
            right: 20%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            bottom: 30%;
            left: 15%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            background: var(--primary-dark);
            bottom: 20%;
            right: 10%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        .main-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header moderno */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            margin-bottom: 40px;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-button);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        .logo {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(145deg, #2c3e50, #34495e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .logo a {
            color: inherit;
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--text-color);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .nav-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .nav-btn:hover::before {
            left: 100%;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(125, 184, 153, 0.3);
            background: rgba(255, 255, 255, 0.25);
            border-color: var(--primary-light);
        }

        .nav-btn.active {
            background: var(--gradient-button);
            color: white;
            box-shadow: 0 4px 15px rgba(125, 184, 153, 0.4);
        }

        .cart-count {
            background: rgba(255, 255, 255, 0.3);
            color: var(--text-color);
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            min-width: 18px;
            text-align: center;
            font-weight: 700;
        }

        .nav-btn.active .cart-count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Contenedor principal */
        .perfil-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Información del perfil */
        .perfil-info {
            background: var(--gradient-card);
            border-radius: var(--border-radius-lg);
            padding: 35px;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            height: fit-content;
        }

        .perfil-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-button);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        .perfil-info h1 {
            color: var(--primary-darker);
            margin-bottom: 30px;
            font-size: 2.4rem;
            text-align: center;
            font-weight: 800;
            position: relative;
        }

        .perfil-info h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--gradient-button);
            border-radius: 3px;
        }

        .info-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(125, 184, 153, 0.1);
            transition: var(--transition);
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .info-card h2 {
            color: var(--primary-darker);
            font-size: 1.5rem;
            margin-bottom: 25px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            padding-bottom: 15px;
        }

        .info-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--gradient-button);
            border-radius: 2px;
        }

        .info-card p {
            color: var(--text-color);
            font-size: 1rem;
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(125, 184, 153, 0.1);
            transition: var(--transition);
            font-weight: 500;
        }

        .info-card p:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-card p:hover {
            padding-left: 8px;
            color: var(--primary-darker);
        }

        .info-card strong {
            color: var(--primary-darker);
            font-weight: 600;
        }

        /* Contenedor de reservas */
        .reservas-container {
            background: var(--gradient-card);
            border-radius: var(--border-radius-lg);
            padding: 35px;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
        }

        .reservas-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-button);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        .reservas-container h2 {
            color: var(--primary-darker);
            margin-bottom: 30px;
            font-size: 2rem;
            text-align: center;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }

        .reservas-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--gradient-button);
            border-radius: 3px;
        }

        .reservas-grid {
            display: grid;
            gap: 20px;
        }

        /* Tarjetas de reserva */
        .reserva-card {
            background: var(--gradient-card);
            border: 1px solid rgba(125, 184, 153, 0.2);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
            position: relative;
        }

        .reserva-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--gradient-button);
            opacity: 0;
            transition: var(--transition);
        }

        .reserva-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
            border-color: var(--primary-light);
        }

        .reserva-card:hover::before {
            opacity: 1;
        }

        .reserva-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: linear-gradient(135deg, rgba(125, 184, 153, 0.1), rgba(125, 184, 153, 0.05));
            border-bottom: 1px solid rgba(125, 184, 153, 0.1);
        }

        .reserva-header h3 {
            color: var(--text-color);
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
        }

        .estado {
            padding: 6px 16px;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .reserva-body {
            padding: 25px;
        }

        .reserva-body p {
            color: var(--text-color);
            margin-bottom: 12px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .reserva-body strong {
            color: var(--primary-darker);
            font-weight: 600;
        }

        .reserva-acciones {
            padding: 20px 25px;
            background: rgba(125, 184, 153, 0.05);
            border-top: 1px solid rgba(125, 184, 153, 0.1);
        }

        .reserva-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        /* Botones */
        .btn-cancelar {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-cancelar:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        .btn-historial {
            background: var(--gradient-button);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 25px;
            width: 100%;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(125, 184, 153, 0.3);
        }

        .btn-historial:hover {
            background: var(--gradient-button-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(125, 184, 153, 0.4);
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            animation: modalBackdrop 0.3s ease;
        }

        @keyframes modalBackdrop {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: var(--gradient-card);
            padding: 40px;
            border-radius: var(--border-radius-lg);
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
        }

        .modal-historial-content {
            max-width: 1000px;
            max-height: 80vh;
            overflow-y: auto;
        }

        @keyframes modalSlideIn {
            from { 
                opacity: 0; 
                transform: translateY(-30px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .modal h2 {
            margin-top: 0;
            color: var(--primary-darker);
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-muted);
            transition: var(--transition);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .close:hover {
            color: var(--danger-color);
            background: rgba(231, 76, 60, 0.1);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid rgba(125, 184, 153, 0.2);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.8);
            font-family: inherit;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(125, 184, 153, 0.2);
            background: white;
        }

        .btn-guardar {
            background: var(--gradient-button);
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(125, 184, 153, 0.3);
        }

        .btn-guardar:hover {
            background: var(--gradient-button-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(125, 184, 153, 0.4);
        }

        .modal-acciones {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(125, 184, 153, 0.2);
            gap: 12px;
        }

        .btn-eliminar-confirmar {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-eliminar-confirmar:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .perfil-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 15px;
            }
            
            header {
                flex-direction: column;
                gap: 20px;
                padding: 25px 20px;
            }
            
            .nav-buttons {
                justify-content: center;
                width: 100%;
                flex-wrap: wrap;
            }
            
            .nav-btn {
                font-size: 0.8rem;
                padding: 10px 16px;
            }
            
            .perfil-info, .reservas-container {
                padding: 25px 20px;
            }
            
            .info-card {
                padding: 25px 20px;
            }
            
            .reserva-header, .reserva-body, .reserva-acciones {
                padding: 20px;
            }
            
            .modal-content {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .modal-acciones {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .perfil-info h1 {
                font-size: 2rem;
            }
            
            .reservas-container h2 {
                font-size: 1.6rem;
            }
            
            .info-card h2 {
                font-size: 1.3rem;
            }
            
            .reserva-header h3 {
                font-size: 1.1rem;
            }
        }

        /* Animaciones adicionales */
        @keyframes fadeOut {
            from { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
            to { 
                opacity: 0; 
                transform: translateY(-20px) scale(0.95); 
            }
        }

        .fade-out {
            animation: fadeOut 0.3s ease forwards;
        }

        /* Estados de carga */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Efectos hover para botones */
        .btn-cancelar:active,
        .btn-historial:active,
        .btn-guardar:active,
        .btn-eliminar-confirmar:active {
            transform: translateY(1px) scale(0.98);
        }

        /* Focus states para accesibilidad */
        .nav-btn:focus,
        .btn-cancelar:focus,
        .btn-historial:focus,
        .btn-guardar:focus,
        .btn-eliminar-confirmar:focus,
        input:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
    </style>
    <!-- Header -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    
    <div class="main-container">
        <header>
            <div class="logo">
                <a href="../index.php" style="color: inherit; text-decoration: none;">Turismo INET</a>
            </div>
            <nav class="nav-buttons">
                <a href="../index.php" class="nav-btn">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="../product/productos.php" class="nav-btn">
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="../perfil/" class="nav-btn active">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a href="../carrito/ver.php" class="nav-btn cart-btn" id="cart-button">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <a href="../login_register/logout.php" class="nav-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </header>

        <div class="perfil-container">
            <!-- Sección de información del usuario -->
            <div class="perfil-info">
                <h1>Mi Perfil</h1>
                <div class="info-card">
                    <h2>Información Personal</h2>
                    <p><strong>ID:</strong> #<?php echo htmlspecialchars($usuario['id']); ?></p>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                    <p><strong>Rol:</strong> 
                        <?php 
                        $rol = 'Usuario';
                        if ($usuario['rol_id'] == 1) {
                            $rol = 'Cliente';
                        } elseif ($usuario['rol_id'] == 2) {
                            $rol = 'Administrador';
                        }
                        echo $rol;
                        ?>
                    </p>
                    <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></p>
                </div>
            </div>

            <!-- Sección de reservas -->
            <div class="reservas-container">
    <h2>Reservas Pendientes</h2>
    <div id="pendientes-grid" class="reservas-grid">
        <?php 
        $hayPendientes = false;
        foreach ($reservas as $reserva): 
            if (isset($reserva['estado_nombre']) && trim($reserva['estado_nombre']) === 'Pendiente'): 
                $hayPendientes = true;
        ?>
            <div class="reserva-card" id="reserva-<?php echo $reserva['id']; ?>">
                <div class="reserva-header">
                    <h3>Reserva #<?php echo $reserva['id']; ?></h3>
                    <span class="estado" style="background-color: <?php echo $reserva['estado_color']; ?>">
                        <?php echo htmlspecialchars($reserva['estado_nombre']); ?>
                    </span>
                </div>
                <div class="reserva-body">
                    <p><strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></p>
                    <p><strong>Fecha de viaje:</strong> 
                        <?php 
                        if (!empty($reserva['fecha_inicio'])) {
                            echo date('d/m/Y', strtotime($reserva['fecha_inicio']));
                            if (!empty($reserva['fecha_fin'])) {
                                echo ' al ' . date('d/m/Y', strtotime($reserva['fecha_fin']));
                            }
                        } else {
                            echo 'No especificada';
                        }
                        ?>
                    </p>
                    <p><strong>Total:</strong> $<?php echo number_format($reserva['monto_total'], 2, ',', '.'); ?></p>
                    <p><strong>Personas:</strong> <?php echo $reserva['cantidad_personas'] ?? 1; ?></p>
                    <?php if (!empty($reserva['observaciones'])): ?>
                        <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($reserva['observaciones'])); ?></p>
                    <?php endif; ?>
                </div>
                <div class="reserva-acciones">
                    <div class="reserva-actions">
                        <button class="btn-cancelar" onclick="confirmarCancelar(<?php echo $reserva['id']; ?>)">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
        <?php 
            endif; 
        endforeach; 
        if (!$hayPendientes): ?>
            <p style="grid-column: 1/-1;">No tienes reservas pendientes.</p>
        <?php endif; ?>
    </div>

    <button class="btn-historial" onclick="abrirHistorialModal()">Ver historial de reservas</button>
    <!-- Modal de historial de reservas -->
    <div id="modal-historial" class="modal" style="display:none;">
      <div class="modal-content modal-historial-content">
        <span class="close" onclick="cerrarHistorialModal()">&times;</span>
        <h2>Historial de Reservas</h2>
        <div id="historial-grid" class="reservas-grid">
          <?php 
          $hayHistorial = false;
           foreach ($reservas as $reserva): 
            if ($reserva['estado_nombre'] !== 'Pendiente'):
              $hayHistorial = true;
          ?>
            <div class="reserva-card" id="reserva-<?php echo $reserva['id'] ?? '-'; ?>">
              <div class="reserva-header">
                <h3>Reserva #<?php echo $reserva['id'] ?? '-'; ?></h3>
                <span class="estado" style="background-color: <?php echo $reserva['estado_color'] ?? '#ccc'; ?>">
                  <?php echo htmlspecialchars($reserva['estado_nombre'] ?? ''); ?>
                </span>
              </div>
              <div class="reserva-body">
                <p><strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'] ?? '')); ?></p>
                <p><strong>Fecha de viaje:</strong> 
                  <?php 
                  if (!empty($reserva['fecha_inicio'])) {
                    echo date('d/m/Y', strtotime($reserva['fecha_inicio']));
                    if (!empty($reserva['fecha_fin'])) {
                      echo ' al ' . date('d/m/Y', strtotime($reserva['fecha_fin']));
                    }
                  } else {
                    echo 'No especificada';
                  }
                  ?>
                </p>
                <p><strong>Total:</strong> $<?php echo number_format($reserva['monto_total'] ?? 0, 2, ',', '.'); ?></p>
                <p><strong>Personas:</strong> <?php echo $reserva['cantidad_personas'] ?? 1; ?></p>
                <?php if (isset($reserva['observaciones']) && !empty($reserva['observaciones'])): ?>
                    <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($reserva['observaciones'])); ?></p>
                <?php endif; ?>
            </div>
          <?php 
            endif;
          endforeach;
          if (!$hayHistorial): ?>
            <p style="grid-column: 1/-1;">No tienes historial de reservas.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <script>
      function abrirHistorialModal() {
        document.getElementById('modal-historial').style.display = 'block';
      }
      function cerrarHistorialModal() {
        document.getElementById('modal-historial').style.display = 'none';
      }
      // Cerrar modal al hacer click fuera del contenido
      window.onclick = function(event) {
        var modal = document.getElementById('modal-historial');
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      }
    </script>
                                
            </div>
        </div>
    </div>

    <!-- Modal para editar cantidad -->
    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Modificar Cantidad</h2>
            <form id="form-editar-cantidad" method="POST" action="actualizar_pedido.php">
                <input type="hidden" name="pedido_id" id="pedido-id">
                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" required>
                </div>
                <button type="submit" class="btn-guardar">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="modal-eliminar" class="modal">
        <div class="modal-content">
            <h2>¿Estás seguro?</h2>
            <p>¿Deseas eliminar esta reserva de tu lista?</p>
            <div class="modal-acciones">
                <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <form id="form-eliminar-reserva" method="POST" action="eliminar_reserva.php">
                    <input type="hidden" name="reserva_id" id="reserva-eliminar-id">
                    <button type="submit" class="btn-eliminar-confirmar">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para ver los detalles de una reserva
        function verDetalles(reservaId) {
            // Aquí podrías redirigir a una página de detalles o mostrar un modal
            alert('Detalles de la reserva #' + reservaId + ' (función en desarrollo)');
        }

        // Función para confirmar la cancelación de una reserva
        function confirmarCancelar(reservaId) {
            if (confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
                cancelarReserva(reservaId);
            }
        }

        // Función para cancelar una reserva
        function cancelarReserva(reservaId) {
            fetch('cancelar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reserva_id=${reservaId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reserva cancelada correctamente');
                    // Eliminar la tarjeta de pendientes al cancelar
                    const card = document.getElementById(`reserva-${reservaId}`);
                    if (card) {
                        card.remove();
                    }
                    // Opcional: podrías mostrar un mensaje o refrescar el historial vía AJAX si lo deseas.
                } else {
                    alert('Error al cancelar la reserva: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor');
            });
        }

        // Función para mostrar el formulario de edición de perfil
        function mostrarFormularioEdicion() {
            alert('Función de edición de perfil en desarrollo.');
        }

        // Funciones para los modales
        function cerrarModal() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }

        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                cerrarModal();
            }
        };

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });

        // Manejar envío del formulario de eliminación con AJAX
        document.getElementById('form-eliminar-reserva').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('eliminar_reserva.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Eliminar el elemento del DOM con animación
                    const reservaElement = document.getElementById(`reserva-${data.reserva_id}`);
                    if (reservaElement) {
                        reservaElement.style.animation = 'fadeOut 0.3s forwards';
                        
                        // Esperar a que termine la animación antes de eliminar
                        setTimeout(() => {
                            reservaElement.remove();
                            
                            // Verificar si no quedan más reservas
                            if (document.querySelectorAll('.reserva-card').length === 0) {
                                location.reload(); // Recargar para mostrar el mensaje de "No hay reservas"
                            }
                        }, 300);
                    }
                } else {
                    alert('Error al eliminar la reserva: ' + data.message);
                }
                cerrarModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
    </script>
</body>
</html>
