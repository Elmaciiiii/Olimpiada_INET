<?php
session_start();

// Redirigir a login si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Obtener categorías para el filtro
$categorias_query = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
$categorias_result = $conn->query($categorias_query);
$categorias = [];
if ($categorias_result) {
    while ($row = $categorias_result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Obtener tipos de paquete para el filtro
$tipos_query = "SELECT * FROM tipos_paquete ORDER BY nombre";
$tipos_result = $conn->query($tipos_query);
$tipos_paquete = [];
if ($tipos_result) {
    while ($row = $tipos_result->fetch_assoc()) {
        $tipos_paquete[] = $row;
    }
}

// Construir consulta de productos con filtros
$where_conditions = ["p.disponible = 1"];
$params = [];
$types = "";

// Filtro por categoría
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $where_conditions[] = "p.categoria_id = ?";
    $params[] = $_GET['categoria'];
    $types .= "i";
}

// Filtro por tipo de paquete
if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $where_conditions[] = "p.tipo_paquete_id = ?";
    $params[] = $_GET['tipo'];
    $types .= "i";
}

// Filtro por destino
if (isset($_GET['destino']) && !empty($_GET['destino'])) {
    $where_conditions[] = "p.destino LIKE ?";
    $params[] = "%" . $_GET['destino'] . "%";
    $types .= "s";
}

// Filtro por precio
if (isset($_GET['precio_max']) && !empty($_GET['precio_max'])) {
    $where_conditions[] = "p.precio <= ?";
    $params[] = $_GET['precio_max'];
    $types .= "d";
}

// Filtro por búsqueda de texto
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $where_conditions[] = "(p.nombre LIKE ? OR p.descripcion LIKE ? OR p.destino LIKE ?)";
    $busqueda = "%" . $_GET['busqueda'] . "%";
    $params[] = $busqueda;
    $params[] = $busqueda;
    $params[] = $busqueda;
    $types .= "sss";
}

// Orden
$order_by = "p.destacado DESC, p.created_at DESC";
if (isset($_GET['orden'])) {
    switch ($_GET['orden']) {
        case 'precio_asc':
            $order_by = "p.precio ASC";
            break;
        case 'precio_desc':
            $order_by = "p.precio DESC";
            break;
        case 'nombre':
            $order_by = "p.nombre ASC";
            break;
        case 'destacados':
            $order_by = "p.destacado DESC, p.precio ASC";
            break;
    }
}

$where_clause = implode(" AND ", $where_conditions);

$productos_query = "SELECT p.*, c.nombre as categoria_nombre, c.icono as categoria_icono, 
                           tp.nombre as tipo_paquete_nombre
                    FROM productos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    LEFT JOIN tipos_paquete tp ON p.tipo_paquete_id = tp.id 
                    WHERE $where_clause 
                    ORDER BY $order_by";

$stmt = $conn->prepare($productos_query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$productos_result = $stmt->get_result();
$productos = [];
if ($productos_result) {
    while ($row = $productos_result->fetch_assoc()) {
        $productos[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Turismo INET</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/productos.css">
    <style>
        /* Estilos específicos para la barra de navegación */
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

        .cart-count {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            margin-left: 5px;
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
                    <a href="../carrito/ver.php" class="nav-btn">
                        <i class="fas fa-shopping-cart"></i> Carrito
                        <?php
                        // Contar items en el carrito
                        $carrito_count = 0;
                        if (isset($_SESSION['user_id'])) {
                            $conn = getDBConnection();
                            $count_query = "SELECT COUNT(*) as count FROM carrito WHERE usuario_id = ?";
                            $stmt = $conn->prepare($count_query);
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $carrito_count = $result->fetch_assoc()['count'];
                            $stmt->close();
                            $conn->close();
                        }
                        if ($carrito_count > 0): ?>
                            <span class="cart-count"><?php echo $carrito_count; ?></span>
                        <?php endif; ?>
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

        <!-- Filtros -->
        <div class="filters-section">
            <h2>🔍 Encuentra tu experiencia perfecta</h2>
            <form method="GET" class="filters-form">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>🏷️ Categoría</label>
                        <select name="categoria" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>" 
                                        <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $categoria['id']) ? 'selected' : ''; ?>>
                                    <?php echo $categoria['icono'] . ' ' . htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>👥 Tipo de Paquete</label>
                        <select name="tipo" onchange="this.form.submit()">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($tipos_paquete as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>" 
                                        <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == $tipo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>📍 Destino</label>
                        <input type="text" name="destino" placeholder="Ej: Villa Carlos Paz" 
                               value="<?php echo isset($_GET['destino']) ? htmlspecialchars($_GET['destino']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label>💰 Precio máximo</label>
                        <input type="number" name="precio_max" placeholder="Ej: 25000" 
                               value="<?php echo isset($_GET['precio_max']) ? htmlspecialchars($_GET['precio_max']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label>🔎 Búsqueda</label>
                        <input type="text" name="busqueda" placeholder="Buscar productos..." 
                               value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label>📊 Ordenar por</label>
                        <select name="orden" onchange="this.form.submit()">
                            <option value="destacados" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'destacados') ? 'selected' : ''; ?>>Destacados</option>
                            <option value="precio_asc" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'precio_asc') ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                            <option value="precio_desc" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'precio_desc') ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                            <option value="nombre" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'nombre') ? 'selected' : ''; ?>>Nombre A-Z</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
                    <a href="productos.php" class="btn-limpiar">🗑️ Limpiar filtros</a>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="results-section">
            <div class="results-header">
                <h3>📋 Resultados encontrados: <?php echo count($productos); ?></h3>
            </div>

            <?php if (empty($productos)): ?>
                <div class="no-results">
                    <div class="no-results-icon">😕</div>
                    <h3>No se encontraron productos</h3>
                    <p>Intenta ajustar tus filtros de búsqueda o <a href="productos.php">ver todos los productos</a></p>
                </div>
            <?php else: ?>
                <div class="productos-grid">
                    <?php foreach ($productos as $producto): ?>
                        <div class="producto-card <?php echo $producto['destacado'] ? 'destacado' : ''; ?>">
                            <?php if ($producto['destacado']): ?>
                                <div class="badge-destacado">⭐ Destacado</div>
                            <?php endif; ?>
                            
                            <div class="producto-header">
                                <div class="categoria-badge">
                                    <?php echo $producto['categoria_icono'] . ' ' . htmlspecialchars($producto['categoria_nombre']); ?>
                                </div>
                                <?php if ($producto['tipo_paquete_nombre']): ?>
                                    <div class="tipo-badge">
                                        👥 <?php echo htmlspecialchars($producto['tipo_paquete_nombre']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="producto-content">
                                <h3 class="producto-titulo"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                
                                <div class="producto-info">
                                    <p class="destino">📍 <?php echo htmlspecialchars($producto['destino']); ?></p>
                                    <?php if ($producto['duracion_dias']): ?>
                                        <p class="duracion">⏰ <?php echo $producto['duracion_dias']; ?> día<?php echo $producto['duracion_dias'] > 1 ? 's' : ''; ?></p>
                                    <?php endif; ?>
                                    <?php if (($producto['capacidad_min'] ?? '') || ($producto['capacidad_max'] ?? '')): ?>
                                        <p class="capacidad">
                                            👥 <?php echo $producto['capacidad_min'] ?? ''; ?>
                                            <?php echo ($producto['capacidad_max'] ?? '') ? ' - ' . $producto['capacidad_max'] : '+'; ?> personas
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <p class="producto-descripcion">
                                    <?php echo htmlspecialchars(($producto['descripcion_corta'] ?? '') ?: substr($producto['descripcion'], 0, 150) . '...'); ?>
                                </p>

                                <?php if (($producto['incluye'] ?? '') !== ''): ?>
                                    <div class="incluye-preview">
                                        <strong>✅ Incluye:</strong>
                                        <p><?php echo htmlspecialchars(substr($producto['incluye'], 0, 100) . '...'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="producto-footer">
                                <div class="precio">
<!-- ... -->
                                    <span class="precio-valor">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                                    <span class="precio-persona">por persona</span>
                                </div>
                                
                                <div class="producto-actions">
                                    <button class="btn-ver-detalle" onclick="verDetalle(<?php echo $producto['id']; ?>)">
                                        👁️ Ver Detalle
                                    </button>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button class="btn-agregar-carrito" onclick="agregarCarrito(<?php echo $producto['id']; ?>)">
                                            🛒 Agregar
                                        </button>
                                    <?php else: ?>
                                        <a href="login_register/login.php" class="btn-login-required">
                                            🔑 Inicia sesión para comprar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para detalles del producto -->
    <div id="modal-detalle" class="modal">
        <div class="modal-content">
            <div id="detalle-content">
                <!-- Contenido se carga dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        function verDetalle(productoId) {
            // Mostrar modal con loading
            document.getElementById('modal-detalle').style.display = 'block';
            document.getElementById('detalle-content').innerHTML = '<div class="loading">🔄 Cargando...</div>';
            
            // Cargar detalles del producto
            fetch(`get_producto_detalle.php?id=${productoId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detalle-content').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('detalle-content').innerHTML = '<div class="error">❌ Error al cargar el producto</div>';
                });
        }

        function cerrarModal() {
            document.getElementById('modal-detalle').style.display = 'none';
        }

        function agregarCarrito(productoId) {
            // Implementar lógica del carrito
            fetch('../carrito/agregar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    producto_id: productoId,
                    cantidad: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('✅ Producto agregado al carrito', 'success');
                } else {
                    mostrarNotificacion('❌ Error al agregar al carrito: ' + data.message, 'error');
                }
            })
            .catch(error => {
                mostrarNotificacion('❌ Error de conexión', 'error');
            });
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

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modal-detalle');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-submit del formulario al cambiar filtros (ya implementado en los selects)
        document.querySelectorAll('input[name="destino"], input[name="precio_max"], input[name="busqueda"]').forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.form.submit();
                }, 800); // Esperar 800ms después de que el usuario deje de escribir
            });
        });
    </script>
    <style>
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            animation: float 15s infinite ease-in-out;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: 50px;
            left: -50px;
            animation-delay: 5s;
            animation-duration: 20s;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            top: 40%;
            right: 10%;
            animation-delay: 10s;
            animation-duration: 25s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }
    </style>
</body>
</html>