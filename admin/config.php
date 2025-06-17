<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'olimpiada_inet';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para obtener estadísticas del dashboard
function obtenerEstadisticas($pdo) {
    try {
        $stats = [];
        
        // Productos activos
        $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE disponible = 1");
        $stats['productos_activos'] = $stmt->fetchColumn();
        
        // Pedidos pendientes
        $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado_id = 1");
        $stats['pedidos_pendientes'] = $stmt->fetchColumn();
        
        // Categorías
        $stmt = $pdo->query("SELECT COUNT(*) FROM categorias WHERE activo = 1");
        $stats['categorias'] = $stmt->fetchColumn();
        
        return $stats;
    } catch(PDOException $e) {
        return ['productos_activos' => 0, 'pedidos_pendientes' => 0, 'categorias' => 0];
    }
}

// Función para obtener todos los productos
function obtenerProductos($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function cambiarDisponibilidadProducto($pdo, $id, $disponible) {
    try {
        $stmt = $pdo->prepare("UPDATE productos SET disponible = :disponible WHERE id = :id");
        return $stmt->execute([':disponible' => $disponible, ':id' => $id]);
    } catch(PDOException $e) {
        error_log("Error al cambiar disponibilidad: " . $e->getMessage());
        return false;
    }
}

// Función para obtener pedidos pendientes
function obtenerPedidosPendientes($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT r.*, e.nombre as estado_nombre,
                   GROUP_CONCAT(DISTINCT dr.nombre_producto SEPARATOR ', ') as productos
            FROM reservas r
            LEFT JOIN estados_reserva e ON r.estado_id = e.id
            LEFT JOIN detalle_reservas dr ON r.id = dr.reserva_id
            WHERE r.estado_id = 1
            GROUP BY r.id
            ORDER BY r.fecha_reserva DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Función para obtener categorías
function obtenerCategorias($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Función para obtener tipos de paquete
function obtenerTiposPaquete($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM tipos_paquete ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Función para guardar producto
function guardarProducto($pdo, $datos) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO productos (
                nombre, descripcion, precio, categoria_id, tipo_paquete_id, 
                destino, duracion_dias, servicios_incluidos, servicios_no_incluidos,
                disponible, destacado, fecha_inicio, fecha_fin
            ) VALUES (
                :nombre, :descripcion, :precio, :categoria_id, :tipo_paquete_id,
                :destino, :duracion_dias, :servicios_incluidos, :servicios_no_incluidos,
                :disponible, :destacado, :fecha_inicio, :fecha_fin
            )
        ");
        
        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio' => $datos['precio'],
            ':categoria_id' => $datos['categoria_id'],
            ':tipo_paquete_id' => $datos['tipo_paquete_id'] ?: null,
            ':destino' => $datos['destino'],
            ':duracion_dias' => $datos['duracion_dias'] ?: null,
            ':servicios_incluidos' => $datos['servicios_incluidos'] ?: null,
            ':servicios_no_incluidos' => $datos['servicios_no_incluidos'] ?: null,
            ':disponible' => $datos['disponible'] ? 1 : 0,
            ':destacado' => $datos['destacado'] ? 1 : 0,
            ':fecha_inicio' => $datos['fecha_inicio'] ?: null,
            ':fecha_fin' => $datos['fecha_fin'] ?: null
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

// Función para eliminar producto
function eliminarProducto($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch(PDOException $e) {
        return false;
    }
}

// Función para actualizar estado de reserva
function actualizarEstadoReserva($pdo, $id, $estado) {
    try {
        $stmt = $pdo->prepare("UPDATE reservas SET estado_id = :estado WHERE id = :id");
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    } catch(PDOException $e) {
        return false;
    }
}
// Obtener un producto por id
function obtenerProductoPorId($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Actualizar producto
function actualizarProducto($pdo, $datos) {
    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, categoria_id = :categoria_id, tipo_paquete_id = :tipo_paquete_id, destino = :destino, duracion_dias = :duracion_dias, servicios_incluidos = :servicios_incluidos, servicios_no_incluidos = :servicios_no_incluidos, disponible = :disponible, destacado = :destacado, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin WHERE id = :id");
        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio' => $datos['precio'],
            ':categoria_id' => $datos['categoria_id'],
            ':tipo_paquete_id' => $datos['tipo_paquete_id'],
            ':destino' => $datos['destino'],
            ':duracion_dias' => $datos['duracion_dias'] ?: null,
            ':servicios_incluidos' => $datos['servicios_incluidos'] ?: null,
            ':servicios_no_incluidos' => $datos['servicios_no_incluidos'] ?: null,
            ':disponible' => $datos['disponible'] ? 1 : 0,
            ':destacado' => $datos['destacado'] ? 1 : 0,
            ':fecha_inicio' => $datos['fecha_inicio'] ?: null,
            ':fecha_fin' => $datos['fecha_fin'] ?: null,
            ':id' => $datos['id']
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

?>

