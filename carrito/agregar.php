<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $usuario_id = $_SESSION['user_id'];
    $producto_id = isset($input['producto_id']) ? (int)$input['producto_id'] : 0;
    $cantidad = isset($input['cantidad']) ? (int)$input['cantidad'] : 1;
    
    if ($producto_id <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Producto o cantidad inválidos']);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Verificar que el producto existe y está disponible
    $stmt = $conn->prepare("SELECT id, nombre, precio, disponible FROM productos WHERE id = ? AND disponible = 1");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado o no disponible']);
        exit;
    }
    
    $producto = $result->fetch_assoc();
    
    // Verificar si el producto ya está en el carrito
    $stmt = $conn->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
    $stmt->bind_param("ii", $usuario_id, $producto_id);
    $stmt->execute();
    $carrito_result = $stmt->get_result();
    
    if ($carrito_result->num_rows > 0) {
        // Actualizar cantidad existente
        $carrito_item = $carrito_result->fetch_assoc();
        $nueva_cantidad = $carrito_item['cantidad'] + $cantidad;
        
        $stmt = $conn->prepare("UPDATE carrito SET cantidad = ?, precio_unitario = ? WHERE id = ?");
        $stmt->bind_param("idi", $nueva_cantidad, $producto['precio'], $carrito_item['id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Cantidad actualizada en el carrito',
                'action' => 'updated'
            ]);
        } else {
            throw new Exception('Error al actualizar el carrito');
        }
    } else {
        // Agregar nuevo item al carrito
        $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $usuario_id, $producto_id, $cantidad, $producto['precio']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Producto agregado al carrito',
                'action' => 'added'
            ]);
        } else {
            throw new Exception('Error al agregar al carrito');
        }
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error en carrito/agregar.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>