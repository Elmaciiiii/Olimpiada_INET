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

if (!$pedido_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado']);
    exit();
}

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDBConnection();
    
    // Verificar que el pedido pertenece al usuario
    $verificar_query = "SELECT id FROM pedidos WHERE id = ? AND usuario_id = ? AND estado = 'pendiente'";
    $stmt = $conn->prepare($verificar_query);
    $stmt->bind_param("ii", $pedido_id, $_SESSION['user_id']);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado o no se puede eliminar');
    }
    
    // Eliminar el pedido
    $delete_query = "DELETE FROM pedidos WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $pedido_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);
    } else {
        throw new Exception('Error al eliminar el pedido');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
