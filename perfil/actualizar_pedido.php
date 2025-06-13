<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$pedido_id = $_POST['pedido_id'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;

if (!$pedido_id || !$cantidad || $cantidad < 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDBConnection();
    
    // Verificar que el pedido pertenece al usuario
    $verificar_query = "SELECT p.* FROM pedidos p 
                      WHERE p.id = ? AND p.usuario_id = ? AND p.estado = 'pendiente'";
    $stmt = $conn->prepare($verificar_query);
    $stmt->bind_param("ii", $pedido_id, $_SESSION['user_id']);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado o no se puede modificar');
    }
    
    // Actualizar la cantidad
    $update_query = "UPDATE pedidos SET cantidad = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $cantidad, $pedido_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al actualizar el pedido');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
