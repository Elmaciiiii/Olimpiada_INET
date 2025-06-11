<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turismo Córdoba</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        /* Estilos para la barra de navegación */
        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .nav-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .nav-btn span {
            font-size: 1.1em;
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
            <div class="logo">INICIO</div>
            <div class="nav-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Botones cuando el usuario ha iniciado sesión -->
                    <a href="#" class="nav-btn">
                        <span>📦</span> Productos
                    </a>
                    <a href="#" class="nav-btn">
                        <span>🛒</span> Carrito
                    </a>
                    <a href="login_register/logout.php" class="nav-btn">
                        <span>👤</span> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <!-- Botones cuando el usuario no ha iniciado sesión -->
                    <a href="login_register/login.php" class="nav-btn">
                        <span>🔑</span> Iniciar Sesión
                    </a>
                    <a href="login_register/register.php" class="nav-btn" style="background: rgba(255, 255, 255, 0.3);">
                        <span>📝</span> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="main-content">
            <div class="cordoba-section">
                <h1 class="cordoba-title">CÓRDOBA</h1>
                <div class="location-info">
                    <p>📍 Villa Carlos Paz</p>
                    <p>👨‍👩‍👧‍👦 PACK Familiar</p>
                </div>
                <div class="action-buttons">
                    <button class="action-btn description-btn" onclick="showDescription()">
                        📋 VER DETALLES
                    </button>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-content">
                    <h3 class="feature-title">🏖️ Destinos Costeros</h3>
                </div>
            </div>
        </div>

        <div class="grid-section">
            <div class="feature-card">
                <div class="feature-content">
                    <h3 class="feature-title">🏔️ Aventura Montaña</h3>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-content">
                    <h3 class="feature-title">🌆 Escapadas Urbanas</h3>
                </div>
            </div>
        </div>
    </div>

    <script src="js/index.js"></script>
</body>
</html>