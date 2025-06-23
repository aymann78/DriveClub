<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar datos personales
$p = $input['personal'] ?? [];
$campos = ['nombre','apellidos','telefono','direccion','ciudad','postal','pais'];
foreach ($campos as $campo) {
    if (empty($p[$campo])) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos personales: ' . $campo]);
        exit;
    }
}

$card_id = $input['card_id'] ?? null;
$card_data = $input['card'] ?? null;
$only_add_card = !empty($input['only_add_card']);
if (!$card_id && !$card_data) {
    echo json_encode(['success' => false, 'error' => 'Debes seleccionar o añadir una tarjeta de crédito']);
    exit;
}
if ($card_id) {
    // Validar que la tarjeta pertenece al usuario
    $user_cards = get_user_credit_cards($user_id);
    $found = false;
    foreach ($user_cards as $c) {
        if ($c['id'] == $card_id) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo json_encode(['success' => false, 'error' => 'Tarjeta no válida para este usuario']);
        exit;
    }
    // Establecer como predeterminada
    set_default_credit_card($card_id, $user_id);
}
if ($card_data) {
    if (empty($card_data['holder']) || empty($card_data['number']) || empty($card_data['expiry_month']) || empty($card_data['expiry_year']) || empty($card_data['cvv'])) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos de la tarjeta']);
        exit;
    }
    if (!is_valid_card_format($card_data['number'], $card_data['expiry_month'], $card_data['expiry_year'], $card_data['cvv'])) {
        echo json_encode(['success' => false, 'error' => 'Formato de tarjeta inválido.']);
        exit;
    }
    $last_four = substr(preg_replace('/\s+/', '', $card_data['number']), -4);
    $card_type = $card_data['type'] ?? 'Otra';
    $res_card = add_credit_card([
        'user_id' => $user_id,
        'card_holder' => $card_data['holder'],
        'last_four' => $last_four,
        'card_type' => $card_type,
        'expiry_month' => $card_data['expiry_month'],
        'expiry_year' => $card_data['expiry_year'],
        'is_default' => 1
    ]);
    if (!$res_card) {
        echo json_encode(['success' => false, 'error' => 'Error al guardar la tarjeta']);
        exit;
    }
    if ($only_add_card) {
        $cards = array_map(function($c) {
            return [
                'id' => $c['id'],
                'holder' => $c['card_holder'],
                'last_four' => $c['last_four'],
                'expiry_month' => $c['expiry_month'],
                'expiry_year' => $c['expiry_year'],
                'type' => $c['card_type'],
                'is_default' => $c['is_default'],
            ];
        }, get_user_credit_cards($user_id));
        echo json_encode(['success' => true, 'cards' => $cards]);
        exit;
    }
}

// Guardar datos personales
$profile_data = [
    'first_name' => $p['nombre'],
    'last_name' => $p['apellidos'],
    'phone' => $p['telefono'],
    'address' => $p['direccion'],
    'city' => $p['ciudad'],
    'postal_code' => $p['postal'],
    'country' => $p['pais']
];
$res_profile = update_user_profile($user_id, $profile_data);
if ($res_profile !== true && $res_profile !== 0) {
    $err = ($res_profile === false || $res_profile === '') ? 'Error desconocido (sin mensaje SQL)' : $res_profile;
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar datos personales: ' . $err,
        'debug' => [
            'profile_data' => $profile_data,
            'update_result' => $res_profile
        ]
    ]);
    exit;
}

// Suscribir/cambiar plan
$plan_id = intval($input['plan_id'] ?? 0);
if (!$plan_id) {
    echo json_encode(['success' => false, 'error' => 'Plan no válido']);
    exit;
}
$res_sub = subscribe_user_to_plan($user_id, $plan_id);
if (!$res_sub) {
    echo json_encode(['success' => false, 'error' => 'No se pudo activar la suscripción. ¿El plan está activo?']);
    exit;
}

echo json_encode(['success' => true]); 