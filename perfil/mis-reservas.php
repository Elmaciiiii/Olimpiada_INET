<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Obtener las reservas del usuario
$reservas_query = "
    SELECT r.*, e.nombre as estado_nombre, e.color as estado_color 
    FROM reservas r
    LEFT JOIN estados_reserva e ON r.estado_id = e.id
    WHERE r.usuario_id = ?
    ORDER BY r.fecha_reserva DESC
";

$stmt = $conn->prepare($reservas_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$reservas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - Turismo Córdoba</title>
    <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">)
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .reservas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .reserva-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .reserva-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .reserva-estado {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
        }
        
        .reserva-detalles {
            padding: 20px;
        }
        
        .reserva-productos {
            margin-top: 15px;
        }
        
        .producto-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .producto-item:last-child {
            border-bottom: none;
        }
        
        .producto-imagen {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .producto-info {
            flex: 1;
        }
        
        .producto-nombre {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .producto-meta {
            font-size: 0.85rem;
            color: #666;
        }
        
        .reserva-acciones {
            padding: 15px 20px;
            background: #f9f9f9;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-verde {
            background: #28a745;
            color: white;
        }
        
        .btn-rojo {
            background: #dc3545;
            color: white;
        }
        
        .btn-azul {
            background: #007bff;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .sin-reservas {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .sin-reservas i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .reserva-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .reserva-acciones {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Incluir el header -->
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="perfil-container">
            <h1>Mis Reservas</h1>
            
            <?php if (empty($reservas)): ?>
                <div class="sin-reservas">
                    <i class="far fa-calendar-times"></i>
                    <h2>No tienes reservas aún</h2>
                    <p>Explora nuestros paquetes y comienza a planear tu próximo viaje.</p>
                    <a href="../product/productos.php" class="btn btn-verde" style="margin-top: 20px;">
                        <i class="fas fa-umbrella-beach"></i> Ver Paquetes
                    </a>
                </div>
            <?php else: ?>
                <div class="reservas-lista">
                    <?php foreach ($reservas as $reserva): ?>
                        <div class="reserva-card">
                            <div class="reserva-header">
                                <div>
                                    <h3>Reserva #<?php echo htmlspecialchars($reserva['numero_reserva']); ?></h3>
                                    <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></p>
                                </div>
                                <span class="reserva-estado" style="background-color: <?php echo $reserva['estado_color']; ?>">
                                    <?php echo htmlspecialchars($reserva['estado_nombre']); ?>
                                </span>
                            </div>
                            
                            <div class="reserva-detalles">
                                <div class="resumen-total" style="text-align: right; font-size: 1.2rem; margin