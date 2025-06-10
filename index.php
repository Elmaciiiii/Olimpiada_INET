<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turismo Córdoba</title>
    <link rel="stylesheet" href="index.css">
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
                <button class="nav-btn" onclick="showFilter()">
                    <span>🔍</span> Filtro
                </button>
                <button class="nav-btn" onclick="showCart()">
                    <span>🛒</span> Carrito
                </button>
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
                        📋 DESCRIPCIÓN
                    </button>
                    <button class="action-btn carrito-btn" onclick="addToCart()">
                        🛒 CARRITO
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

    <script src="index.js"></script>
</body>
</html>