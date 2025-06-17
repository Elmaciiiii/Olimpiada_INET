<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Obtener la conexión a la base de datos
$conn = getDBConnection();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turismo INET</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #7db899 0%, #6ba085 50%, #5a8871 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Formas flotantes mejoradas */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            justify-content: flex-start;
        }

        /* Header mejorado */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 40px;
            margin-bottom: 40px;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 1200px;
        }

        .logo {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo a {
            color: inherit;
            text-decoration: none;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-btn {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.95rem;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .nav-btn i {
            font-size: 1.1em;
        }

        /* Contenido principal */
        .main-content {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-grow: 1;
            padding: 0;
        }

        /* Sección de paquetes */
        .paquetes-destacados {
            max-width: 1000px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 20px 0;
        }

        .section-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 20px;
            color: white;
            font-weight: 800;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            letter-spacing: -1px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 50px;
            max-width: 600px;
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .paquetes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 900px;
            width: 100%;
            justify-items: center;
            align-items: stretch;
            padding: 0 20px;
        }

        /* Tarjetas de paquetes con tamaño uniforme */
        .paquete-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 30px 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-align: center;
            height: 420px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            max-width: 100%;
        }

        .paquete-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #7db899, #6ba085, #5a8871, #4a7059);
            background-size: 300% 300%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .paquete-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 1);
        }

        /* Icono decorativo para cada paquete */
        .paquete-icon {
            font-size: 3rem;
            color: #6ba085;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(107, 160, 133, 0.3);
        }

        .paquete-precio {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #7db899, #6ba085);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(125, 184, 153, 0.4);
        }

        .paquete-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
        }

        .paquete-nombre {
            font-size: 1.6rem;
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: 700;
            line-height: 1.3;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .paquete-descripcion {
            color: #666;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 20px;
            opacity: 0.9;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
        }

        .btn-ver-detalle {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #7db899, #6ba085);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 15px rgba(125, 184, 153, 0.4);
            margin-top: auto;
            width: fit-content;
            align-self: center;
        }

        .btn-ver-detalle:hover {
            background: linear-gradient(135deg, #6ba085, #5a8871);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(125, 184, 153, 0.6);
        }

        /* Responsive mejorado */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            header {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
                max-width: 100%;
            }

            .nav-buttons {
                justify-content: center;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .paquetes-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 15px;
                max-width: 400px;
            }

            .paquete-card {
                padding: 25px 20px;
                height: 380px;
                max-width: 100%;
            }

            .paquete-nombre {
                font-size: 1.4rem;
                min-height: 50px;
            }

            .paquete-descripcion {
                font-size: 0.95rem;
                min-height: 70px;
            }

            .paquete-icon {
                font-size: 2.5rem;
            }

            .main-content {
                padding: 10px 0;
            }
        }

        @media (max-width: 480px) {
            .section-title {
                font-size: 1.8rem;
            }

            .section-subtitle {
                font-size: 1rem;
            }

            .nav-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .paquete-card {
                padding: 20px 15px;
                height: 350px;
            }

            .paquete-nombre {
                font-size: 1.3rem;
                min-height: 45px;
            }

            .paquete-descripcion {
                font-size: 0.9rem;
                min-height: 65px;
            }
        }

        /* Animaciones adicionales */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .paquete-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .paquete-card:nth-child(1) { animation-delay: 0.1s; }
        .paquete-card:nth-child(2) { animation-delay: 0.2s; }
        .paquete-card:nth-child(3) { animation-delay: 0.3s; }
        .paquete-card:nth-child(4) { animation-delay: 0.4s; }
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
                <a href="index.php">Turismo INET</a>
            </div>
            <br>
            <div class="nav-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="product/productos.php" class="nav-btn">
                        <i class="fas fa-suitcase-rolling"></i> Productos
                    </a>
                    <a href="carrito/ver.php" class="nav-btn">
                        <i class="fas fa-shopping-cart"></i> Carrito
                    </a>
                    <a href="perfil/" class="nav-btn">
                        <i class="fas fa-user-circle"></i> Perfil
                    </a>
                     <a href="admin/admin.php" class="nav-btn">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                    <a href="login_register/logout.php" class="nav-btn">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="login_register/login.php" class="nav-btn">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <a href="login_register/register.php" class="nav-btn">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="main-content">
            <?php
            // Obtener paquetes destacados
            $query = "SELECT id, nombre, descripcion, precio, imagen_principal as imagen FROM productos WHERE destacado = 1 LIMIT 4";
            $result = $conn->query($query);
            $paquetes = [];
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $paquetes[] = $row;
                }
            }
            
            // Iconos para los paquetes (rotan automáticamente)
            $iconos = ['fas fa-plane', 'fas fa-mountain', 'fas fa-umbrella-beach', 'fas fa-city', 'fas fa-ship', 'fas fa-camera'];
            ?>

            <div class="paquetes-destacados">
                <h1 class="section-title">Experiencias Únicas te Esperan</h1>
                <p class="section-subtitle">Descubre destinos increíbles y vive aventuras inolvidables con nuestros paquetes exclusivos</p>
                
                <div class="paquetes-grid">
                    <?php foreach ($paquetes as $index => $paquete): ?>
                        <div class="paquete-card">
                            <div class="paquete-precio">$<?php echo number_format($paquete['precio'], 0, ',', '.'); ?></div>
                            <div class="paquete-content">
                                <div class="paquete-icon">
                                    <i class="<?php echo $iconos[$index % count($iconos)]; ?>"></i>
                                </div>
                                <h3 class="paquete-nombre"><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                                <p class="paquete-descripcion"><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
                            </div>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="product/productos.php#producto-<?php echo $paquete['id']; ?>" class="btn-ver-detalle">
                                    <i class="fas fa-arrow-right"></i> Ver Detalles
                                </a>
                            <?php else: ?>
                                <a href="login_register/login.php" class="btn-ver-detalle">
                                    <i class="fas fa-sign-in-alt"></i> Inicia Sesión
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="js/index.js"></script>
</body>
</html>