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
    
    if (!$input || !isset($input['item_id']) || !isset($input['cantidad'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $item_id = (int)$input['item_id'];
    $cantidad = (int)$input['cantidad'];
    $usuario_id = $_SESSION['user_id'];
    
    if ($item_id <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Verificar que el ítem pertenece al usuario
    $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("iii", $cantidad, $item_id, $usuario_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el ítem en el carrito']);
        }
    } else {
        throw new Exception('Error al actualizar la cantidad');
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error en carrito/actualizar.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
