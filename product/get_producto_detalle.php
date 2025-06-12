<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar que se envió el ID del producto
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="error">❌ ID de producto no válido</div>';
    exit;
}

$producto_id = (int)$_GET['id'];
$conn = getDBConnection();

// Consulta para obtener los detalles completos del producto
$query = "SELECT p.*, c.nombre as categoria_nombre, c.icono as categoria_icono, 
                 tp.nombre as tipo_paquete_nombre, tp.descripcion as tipo_paquete_descripcion
          FROM productos p 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          LEFT JOIN tipos_paquete tp ON p.tipo_paquete_id = tp.id 
          WHERE p.id = ? AND p.disponible = 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="error">❌ Producto no encontrado o no disponible</div>';
    $conn->close();
    exit;
}

$producto = $result->fetch_assoc();
$conn->close();
?>

<div class="producto-detalle">
    <div class="detalle-header">
        <?php if ($producto['destacado']): ?>
            <div class="badge-destacado-detalle">⭐ Producto Destacado</div>
        <?php endif; ?>
        
        <div class="categorias-detalle">
            <span class="categoria-badge-detalle">
                <?php echo $producto['categoria_icono'] . ' ' . htmlspecialchars($producto['categoria_nombre']); ?>
            </span>
            <?php if ($producto['tipo_paquete_nombre']): ?>
                <span class="tipo-badge-detalle">
                    👥 <?php echo htmlspecialchars($producto['tipo_paquete_nombre']); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <h2 class="titulo-detalle"><?php echo htmlspecialchars($producto['nombre']); ?></h2>
        
        <div class="info-basica">
            <div class="info-item">
                <span class="icono">📍</span>
                <span class="label">Destino:</span>
                <span class="valor"><?php echo htmlspecialchars($producto['destino']); ?></span>
            </div>
            
            <?php if ($producto['duracion_dias']): ?>
                <div class="info-item">
                    <span class="icono">⏰</span>
                    <span class="label">Duración:</span>
                    <span class="valor"><?php echo $producto['duracion_dias']; ?> día<?php echo $producto['duracion_dias'] > 1 ? 's' : ''; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($producto['capacidad_min'] || $producto['capacidad_max']): ?>
                <div class="info-item">
                    <span class="icono">👥</span>
                    <span class="label">Capacidad:</span>
                    <span class="valor">
                        <?php echo $producto['capacidad_min']; ?>
                        <?php echo $producto['capacidad_max'] ? ' - ' . $producto['capacidad_max'] : '+'; ?> personas
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ($producto['fecha_inicio'] && $producto['fecha_fin']): ?>
                <div class="info-item">
                    <span class="icono">📅</span>
                    <span class="label">Fechas:</span>
                    <span class="valor">
                        <?php echo date('d/m/Y', strtotime($producto['fecha_inicio'])); ?> - 
                        <?php echo date('d/m/Y', strtotime($producto['fecha_fin'])); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="detalle-contenido">
        <div class="descripcion-completa">
            <h3>📝 Descripción</h3>
            <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
        </div>

        <?php if ($producto['incluye']): ?>
            <div class="incluye-detalle">
                <h3>✅ Qué incluye</h3>
                <div class="incluye-lista">
                    <?php
                    $incluye_items = explode("\n", $producto['incluye']);
                    foreach ($incluye_items as $item) {
                        $item = trim($item);
                        if (!empty($item)) {
                            echo '<div class="incluye-item">✓ ' . htmlspecialchars($item) . '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($producto['no_incluye']): ?>
            <div class="no-incluye-detalle">
                <h3>❌ Qué NO incluye</h3>
                <div class="no-incluye-lista">
                    <?php
                    $no_incluye_items = explode("\n", $producto['no_incluye']);
                    foreach ($no_incluye_items as $item) {
                        $item = trim($item);
                        if (!empty($item)) {
                            echo '<div class="no-incluye-item">✗ ' . htmlspecialchars($item) . '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($producto['tipo_paquete_descripcion']): ?>
            <div class="tipo-info">
                <h3>👥 Información del Paquete</h3>
                <p><?php echo htmlspecialchars($producto['tipo_paquete_descripcion']); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="detalle-footer">
        <div class="precio-detalle">
            <div class="precio-principal">
                <span class="precio-desde">Precio desde</span>
                <span class="precio-valor">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                <span class="precio-persona">por persona</span>
            </div>
        </div>
        
        <div class="acciones-detalle">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn-agregar-carrito-detalle" onclick="agregarCarrito(<?php echo $producto['id']; ?>); cerrarModal();">
                    🛒 Agregar al Carrito
                </button>
            <?php else: ?>
                <a href="login_register/login.php" class="btn-login-required-detalle">
                    🔑 Inicia sesión para comprar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.producto-detalle {
    padding: 25px;
    font-family: 'Poppins', sans-serif;
}

.detalle-header {
    border-bottom: 2px solid #eee;
    padding-bottom: 20px;
    margin-bottom: 25px;
    position: relative;
}

.badge-destacado-detalle {
    position: absolute;
    top: 0;
    right: 0;
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #2c2c2c;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
}

.categorias-detalle {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.categoria-badge-detalle,
.tipo-badge-detalle {
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    color: white;
}

.categoria-badge-detalle {
    background: linear-gradient(135deg, #2196F3, #1976D2);
}

.tipo-badge-detalle {
    background: linear-gradient(135deg, #9C27B0, #7B1FA2);
}

.titulo-detalle {
    font-size: 2rem;
    font-weight: 700;
    color: #2c2c2c;
    margin-bottom: 20px;
    line-height: 1.2;
}

.info-basica {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    background: rgba(33, 150, 243, 0.1);
    padding: 20px;
    border-radius: 15px;
    border-left: 4px solid #2196F3;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-item .icono {
    font-size: 1.2rem;
}

.info-item .label {
    font-weight: 600;
    color: #555;
    min-width: 80px;
}

.info-item .valor {
    color: #2c2c2c;
    font-weight: 500;
}

.detalle-contenido {
    margin: 25px 0;
}

.descripcion-completa,
.incluye-detalle,
.no-incluye-detalle,
.tipo-info {
    margin-bottom: 25px;
}

.descripcion-completa h3,
.incluye-detalle h3,
.no-incluye-detalle h3,
.tipo-info h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c2c2c;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.descripcion-completa p {
    line-height: 1.6;
    color: #555;
    font-size: 1rem;
}

.incluye-lista,
.no-incluye-lista {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 10px;
}

.incluye-item {
    background: rgba(76, 175, 80, 0.1);
    border-left: 4px solid #4CAF50;
    padding: 12px 15px;
    border-radius: 8px;
    color: #2e7d32;
    font-weight: 500;
}

.no-incluye-item {
    background: rgba(244, 67, 54, 0.1);
    border-left: 4px solid #f44336;
    padding: 12px 15px;
    border-radius: 8px;
    color: #c62828;
    font-weight: 500;
}

.tipo-info p {
    background: rgba(156, 39, 176, 0.1);
    border-left: 4px solid #9C27B0;
    padding: 15px;
    border-radius: 8px;
    color: #555;
    line-height: 1.5;
}

.detalle-footer {
    border-top: 2px solid #eee;
    padding-top: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.precio-detalle {
    flex: 1;
    min-width: 200px;
}

.precio-principal {
    text-align: left;
}

.precio-desde {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.precio-valor {
    display: block;
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c2c2c;
    margin-bottom: 5px;
}

.precio-persona {
    font-size: 0.9rem;
    color: #666;
}

.acciones-detalle {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-agregar-carrito-detalle,
.btn-reservar-ahora,
.btn-login-required-detalle {
    padding: 15px 25px;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    min-width: 180px;
}

.btn-agregar-carrito-detalle {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
}

.btn-agregar-carrito-detalle:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
}

.btn-reservar-ahora {
    background: linear-gradient(135deg, #FF5722, #E64A19);
    color: white;
}

.btn-reservar-ahora:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 87, 34, 0.4);
}

.btn-login-required-detalle {
    background: linear-gradient(135deg, #FF9800, #F57C00);
    color: white;
}

.btn-login-required-detalle:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
}

.error {
    text-align: center;
    padding: 40px 20px;
    color: #f44336;
    font-size: 1.2rem;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .titulo-detalle {
        font-size: 1.5rem;
    }
    
    .info-basica {
        grid-template-columns: 1fr;
    }
    
    .incluye-lista,
    .no-incluye-lista {
        grid-template-columns: 1fr;
    }
    
    .detalle-footer {
        flex-direction: column;
        text-align: center;
    }
    
    .precio-principal {
        text-align: center;
    }
    
    .acciones-detalle {
        justify-content: center;
        width: 100%;
    }
    
    .btn-agregar-carrito-detalle,
    .btn-reservar-ahora,
    .btn-login-required-detalle {
        min-width: 150px;
    }
}

@media (max-width: 480px) {
    .producto-detalle {
        padding: 15px;
    }
    
    .acciones-detalle {
        flex-direction: column;
    }
    
    .btn-agregar-carrito-detalle,
    .btn-reservar-ahora,
    .btn-login-required-detalle {
        width: 100%;
    }
}
</style>

<script>
function reservarAhora(productoId) {
    // Esta función puede dirigir a una página de reserva específica
    // o abrir otro modal con el formulario de reserva
    if (confirm('¿Deseas proceder con la reserva de este producto?')) {
        // Redirigir a página de reserva o checkout
        window.location.href = `checkout.php?producto_id=${productoId}&accion=reservar`;
    }
}
</script>