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
    <title>Turismo Córdoba</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
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

        /* Estilos para paquetes destacados */
        .paquetes-destacados {
            padding: 80px 0;
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
            margin-top: 0;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 2px;
        }

        .paquetes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 0 30px;
            box-sizing: border-box;
        }
        
        @media (max-width: 1440px) {
            .paquetes-grid {
                max-width: 1300px;
                gap: 40px;
            }
        }
        
        @media (max-width: 1200px) {
            .paquetes-grid {
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                padding: 0 25px;
            }
            
            .paquete-imagen {
                height: 260px;
            }
            
            .paquete-info {
                padding: 25px;
            }
        }
        
        @media (max-width: 1024px) {
            .paquetes-grid {
                padding: 0 30px;
                max-width: 900px;
            }
        }
        
        @media (max-width: 768px) {
            .paquetes-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
                max-width: 600px;
                gap: 30px;
            }
            
            .paquete-card {
                max-width: 100%;
                margin-bottom: 0;
            }
            
            .paquete-imagen {
                height: 250px;
            }
        }
        
        @media (max-width: 768px) {
            .paquetes-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
                gap: 25px;
            }
            
            .paquete-imagen {
                height: 250px;
            }
            
            .paquete-info {
                padding: 30px;
            }
            
            .paquete-nombre {
                font-size: 1.6rem;
            }
            
            .paquete-descripcion {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 480px) {
            .paquetes-grid {
                padding: 0 15px;
                gap: 30px;
            }
            
            .paquete-info {
                padding: 25px;
            }
            
            .paquete-nombre {
                font-size: 1.4rem;
                margin-bottom: 15px;
            }
            
            .paquete-descripcion {
                font-size: 1rem;
                line-height: 1.6;
                margin-bottom: 20px;
            }
            
            .btn-ver-detalle {
                width: 100%;
                padding: 12px 20px;
                font-size: 1rem;
            }
        }
        
        .paquete-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            position: relative;
        }



        .paquete-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }


        .paquete-imagen {
            height: 320px;
            background-size: cover;
            background-position: center;
            position: relative;
            background-color: #f5f5f5;
            flex-shrink: 0;
        }

        .paquete-precio {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .paquete-info {
            padding: 35px 40px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            text-align: center;
            align-items: center;
        }

        .paquete-nombre {
            font-size: 1.7rem;
            margin: 0 auto 20px;
            color: #2c3e50;
            font-weight: 700;
            line-height: 1.3;
            max-width: 90%;
        }

        .paquete-descripcion {
            color: #666;
            font-size: 1.15rem;
            line-height: 1.7;
            margin: 0 auto 25px;
            max-width: 90%;
        }

        .btn-ver-detalle {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(90deg, #4ecdc4, #45b7d1);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin: 20px auto 0;
            text-align: center;
            min-width: 180px;
            border: none;
            cursor: pointer;
        }

        .btn-ver-detalle:hover {
            background: linear-gradient(90deg, #45b7af, #3ba199);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .paquetes-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
                <a href="index.php" style="color: inherit; text-decoration: none;">INICIO</a>
            </div>
            <div class="nav-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="product/productos.php" class="nav-btn">
                        <i class="fas fa-box"></i> Productos
                    </a>
                    <a href="carrito/ver.php" class="nav-btn">
                        <i class="fas fa-shopping-cart"></i> Carrito
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
            
            ?>

            <div class="paquetes-destacados">
                <h1 class="section-title">Nuestros Paquetes Destacados</h1>
                <p class="section-subtitle">Descubre las mejores experiencias de viaje que tenemos para ti</p>
                
                <div class="paquetes-grid">
                    <?php foreach ($paquetes as $paquete): ?>
                        <div class="paquete-card">
                            <div class="paquete-imagen" style="background-image: url('<?php echo !empty($paquete['imagen']) ? 'img/productos/' . htmlspecialchars($paquete['imagen']) : 'img/placeholder.jpg'; ?>');">
                                <div class="paquete-precio">$<?php echo number_format($paquete['precio'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="paquete-info">
                                <h3 class="paquete-nombre"><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                                <p class="paquete-descripcion"><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <a href="product/productos.php#producto-<?php echo $paquete['id']; ?>" class="btn-ver-detalle">Ver Detalles</a>
                                <?php else: ?>
                                    <a href="login_register/login.php" class="btn-ver-detalle">Inicia sesión para ver detalles</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="js/index.js"></script>
</body>
</html>