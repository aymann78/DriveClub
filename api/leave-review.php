<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para dejar una opinión.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$reservation_id = isset($input['reservation_id']) ? (int)$input['reservation_id'] : 0;
$rating = isset($input['rating']) ? (int)$input['rating'] : 0;
$comment = trim($input['comment'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$reservation_id || $rating < 1 || $rating > 5 || $comment === '') {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos o inválidos.']);
    exit;
}

if (!can_user_review_reservation($user_id, $reservation_id)) {
    echo json_encode(['success' => false, 'error' => 'No puedes dejar opinión para esta reserva.']);
    exit;
}

// Obtener datos de la reserva para el vehicle_id
$sql = "SELECT vehicle_id FROM reservations WHERE id = ? AND user_id = ?";
$res = db_fetch_assoc($sql, [$reservation_id, $user_id]);
if (!$res) {
    echo json_encode(['success' => false, 'error' => 'Reserva no encontrada.']);
    exit;
}
$vehicle_id = $res['vehicle_id'];

// Insertar review
$data = [
    'user_id' => $user_id,
    'vehicle_id' => $vehicle_id,
    'reservation_id' => $reservation_id,
    'rating' => $rating,
    'comment' => $comment,
    'is_approved' => 1,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];
$review_id = db_insert('reviews', $data);
if ($review_id) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar la opinión.']);
} 