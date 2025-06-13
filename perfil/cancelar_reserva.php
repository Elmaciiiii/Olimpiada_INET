<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar si se proporcionó el ID de la reserva
if (!isset($_POST['reserva_id']) || empty($_POST['reserva_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de reserva no proporcionado']);
    exit();
}

$reserva_id = intval($_POST['reserva_id']);
$usuario_id = $_SESSION['user_id'];

$conn = getDBConnection();

// Verificar que la reserva pertenece al usuario y está en estado pendiente
$verificar_query = "SELECT id FROM reservas WHERE id = ? AND usuario_id = ? AND estado_id = 1";
$stmt = $conn->prepare($verificar_query);
$stmt->bind_param("ii", $reserva_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No se encontró la reserva o no pertenece al usuario o no está pendiente
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Reserva no encontrada o no se puede cancelar']);
    exit();
}

// Actualizar el estado de la reserva a cancelada (asumiendo que el estado 5 es Cancelada)
$actualizar_query = "UPDATE reservas SET estado_id = 5 WHERE id = ?";
$stmt = $conn->prepare($actualizar_query);
$stmt->bind_param("i", $reserva_id);

if ($stmt->execute()) {
    // Éxito
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Reserva cancelada correctamente']);
} else {
    // Error
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al cancelar la reserva']);
}
?>
