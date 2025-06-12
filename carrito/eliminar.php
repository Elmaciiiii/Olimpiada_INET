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
    
    if (!$input || !isset($input['item_id'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $item_id = (int)$input['item_id'];
    $usuario_id = $_SESSION['user_id'];
    
    if ($item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de ítem inválido']);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Verificar que el ítem pertenece al usuario
    $stmt = $conn->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $item_id, $usuario_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el ítem en el carrito']);
        }
    } else {
        throw new Exception('Error al eliminar el producto del carrito');
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error en carrito/eliminar.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
