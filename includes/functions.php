<?php
/**
 * Helper Functions
 *
 * Contains various helper functions for the application
 */

/**
 * Display error message with Bootstrap styling
 *
 * @param string $message The error message
 * @return string HTML for error message
 */
function display_error($message)
{
    return '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display success message with Bootstrap styling
 *
 * @param string $message The success message
 * @return string HTML for success message
 */
function display_success($message)
{
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display info message with Bootstrap styling
 *
 * @param string $message The info message
 * @return string HTML for info message
 */
function display_info($message)
{
    return '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>';
}

/**
 * Redirect to another page
 *
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Require login to access page
 *
 * @return void Redirects to login page if not logged in
 */
function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['error'] = "You must be logged in to access this page";
        redirect('login.php');
    }
}

/**
 * Get current user data
 *
 * @return array|null User data or null if not logged in
 */
function get_user_data()
{
    if (!is_logged_in()) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE id = ?";
    return db_fetch_assoc($sql, [$_SESSION['user_id']]);
}

/**
 * Get user's active subscription
 *
 * @param int $user_id User ID
 * @return array|null Subscription data or null if no active subscription
 */
function get_user_subscription($user_id)
{
    $sql = "SELECT us.*, sp.name as plan_name, sp.price_monthly, sp.description 
            FROM user_subscriptions us 
            JOIN subscription_plans sp ON us.plan_id = sp.id 
            WHERE us.user_id = ? AND us.is_active = 1 
            ORDER BY us.start_date DESC LIMIT 1";
    
    return db_fetch_assoc($sql, [$user_id]);
}

/**
 * Get all subscription plans
 *
 * @param bool $active_only Whether to get only active plans
 * @return array All subscription plans
 */
function get_all_plans($active_only = true)
{
    $sql = "SELECT * FROM subscription_plans";
    
    if ($active_only) {
        $sql .= " WHERE is_active = 1";
    }
    
    $sql .= " ORDER BY price_monthly";
    $plans = db_fetch_all($sql);
    
    // Si no hay planes, devolver array vacío
    if (empty($plans)) {
        return [];
    }
    
    // Procesar features en formato JSON
    foreach ($plans as &$plan) {
        $plan['features'] = [];
        if (!empty($plan['special_features'])) {
            $decoded = json_decode($plan['special_features'], true);
            $plan['features'] = is_array($decoded) ? $decoded : [];
        }
    }
    unset($plan); // Liberar la referencia
    
    return $plans;
}


/**
 * Subscribe user to a plan
 *
 * @param int $user_id User ID
 * @param int $plan_id Plan ID
 * @return bool Success or failure
 */
function subscribe_user_to_plan($user_id, $plan_id)
{
    // Comprobar que el plan existe
    $plan = get_plan_by_id($plan_id);
    if (!$plan || !$plan['is_active']) {
        return false;
    }
    
    // Desactivar suscripciones actuales
    $update_sql = "UPDATE user_subscriptions SET is_active = 0 WHERE user_id = ?";
    db_query($update_sql, [$user_id]);
    
    // Establecer fechas
    $start_date = date('Y-m-d');
    $next_payment_date = date('Y-m-d', strtotime('+1 month'));
    
    // Crear nueva suscripción
    $subscription_data = [
        'user_id' => $user_id,
        'plan_id' => $plan_id,
        'start_date' => $start_date,
        'end_date' => null,
        'payment_status' => 'pending',
        'auto_renewal' => 1,
        'is_active' => 1,
        'payment_method' => 'credit_card',
        'last_payment_date' => date('Y-m-d H:i:s'),
        'next_payment_date' => $next_payment_date
    ];
    
    return db_insert('user_subscriptions', $subscription_data) ? true : false;
}

/**
 * Get plan by ID
 *
 * @param int $plan_id Plan ID
 * @return array|null Plan data or null if not found
 */
function get_plan_by_id($plan_id)
{
    $sql = "SELECT * FROM subscription_plans WHERE id = ?";
    return db_fetch_assoc($sql, [$plan_id]);
}

/**
 * Get all vehicle types
 *
 * @return array All vehicle types
 */
function get_all_vehicle_types()
{
    $sql = "SELECT * FROM vehicle_types ORDER BY name";
    return db_fetch_all($sql);
}

/**
 * Get all vehicle brands
 *
 * @return array All vehicle brands
 */
function get_all_vehicle_brands()
{
    $sql = "SELECT * FROM brands ORDER BY name";
    return db_fetch_all($sql);
}

/**
 * Get all vehicles
 *
 * @param array $filters Optional array of filters
 * @return array All vehicles matching filters
 */
function get_all_vehicles($filters = [])
{
    $sql = "SELECT DISTINCT v.*, b.name as brand_name, vt.name as type_name, sp.name as plan_name, sp.price_monthly 
            FROM vehicles v
            JOIN brands b ON v.brand_id = b.id
            JOIN vehicle_types vt ON v.type_id = vt.id
            JOIN subscription_plans sp ON v.minimum_plan_id = sp.id
            WHERE v.is_active = 1";
    
    $params = [];
    
    if (!empty($filters['brand_id'])) {
        $sql .= " AND v.brand_id = ?";
        $params[] = $filters['brand_id'];
    }
    
    if (!empty($filters['type_id'])) {
        $sql .= " AND v.type_id = ?";
        $params[] = $filters['type_id'];
    }
    
    if (!empty($filters['plan_id'])) {
        $sql .= " AND v.minimum_plan_id <= ?";
        $params[] = $filters['plan_id'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (v.name LIKE ? OR b.name LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }
    
    $sql .= " ORDER BY ";
    if (!empty($filters['random'])) {
        $sql .= "RAND()";
    } else {
        $sql .= "b.name, v.name";
    }
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];
    }
    
    $vehicles = db_fetch_all($sql, $params);
    
    // Eliminar duplicados basados en el ID usando comparación estricta
    $filtered_vehicles = [];
    $seen_vehicle_ids = [];
    
    foreach ($vehicles as $vehicle) {
        if (!in_array($vehicle['id'], $seen_vehicle_ids, true)) {
            $seen_vehicle_ids[] = $vehicle['id'];
            $filtered_vehicles[] = $vehicle;
        }
    }
    
    return $filtered_vehicles;
}


/**
 * Get vehicle by ID
 *
 * @param int $vehicle_id Vehicle ID
 * @return array|null Vehicle data or null if not found
 */
function get_vehicle_by_id($vehicle_id)
{
    $sql = "SELECT v.*, b.name as brand_name, b.id as brand_id, 
                   vt.name as type_name, vt.id as type_id, 
                   sp.name as plan_name, sp.id as plan_id, sp.price_monthly
            FROM vehicles v
            JOIN brands b ON v.brand_id = b.id
            JOIN vehicle_types vt ON v.type_id = vt.id
            JOIN subscription_plans sp ON v.minimum_plan_id = sp.id
            WHERE v.id = ? AND v.is_active = 1";
    
    return db_fetch_assoc($sql, [$vehicle_id]);
}

/**
 * Get vehicle images
 *
 * @param int $vehicle_id Vehicle ID
 * @return array Vehicle images
 */
function get_vehicle_images($vehicle_id)
{
    $sql = "SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, display_order";
    return db_fetch_all($sql, [$vehicle_id]);
}

/**
 * Get primary image for a vehicle
 *
 * @param int $vehicle_id Vehicle ID
 * @return string|null Image URL or null if not found
 */
function get_vehicle_primary_image($vehicle_id)
{
    $sql = "SELECT image_url FROM vehicle_images WHERE vehicle_id = ? AND is_primary = 1 LIMIT 1";
    $result = db_fetch_assoc($sql, [$vehicle_id]);
    
    if ($result) {
        return $result['image_url'];
    }
    
    // If no primary image, get the first one
    $sql = "SELECT image_url FROM vehicle_images WHERE vehicle_id = ? ORDER BY display_order LIMIT 1";
    $result = db_fetch_assoc($sql, [$vehicle_id]);
    
    return $result ? $result['image_url'] : 'https://via.placeholder.com/500x300?text=No+Image';
}

/**
 * Get user reservations
 *
 * @param int $user_id User ID
 * @param string $status Status filter (all, active, past)
 * @return array User reservations
 */
function get_user_reservations($user_id, $status = 'all')
{
    $sql = "SELECT r.*, v.name as vehicle_name, b.name as brand_name, 
                   vt.name as vehicle_type, rs.name as status_name
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            JOIN brands b ON v.brand_id = b.id
            JOIN vehicle_types vt ON v.type_id = vt.id
            JOIN reservation_statuses rs ON r.status_id = rs.id
            WHERE r.user_id = ?";
    
    $params = [$user_id];
    
    if ($status == 'active') {
        $sql .= " AND (rs.name = 'Pending' OR rs.name = 'Active')";
    } elseif ($status == 'past') {
        $sql .= " AND (rs.name = 'Completed' OR rs.name = 'Cancelled')";
    }
    
    $sql .= " ORDER BY r.start_date DESC";
    
    return db_fetch_all($sql, $params);
}
// functions.php

// Conexión a la BD, por ejemplo, ya está configurada en $conn en db.php

/**
 * Genera el código QR en base64 a partir de los datos de la reserva.
 *
 * @param array $reservation Array con los datos de la reserva.
 *                           Se esperan keys: id, user_id, vehicle_id, start_date, end_date.
 * @return string Cadena en base64 que representa la imagen QR.
 */
function generateQrCode($reservation) {
    // Incluir la librería de PHP QR Code
    require_once __DIR__ . '/../lib/phpqrcode/phpqrcode.php';

    
    // Construir el contenido del QR (personaliza según lo que necesites)
    $data = "ID Reserva: " . $reservation['id'] . "\n" .
            "ID Usuario: " . $reservation['user_id'] . "\n" .
            "ID Vehículo: " . $reservation['vehicle_id'] . "\n" .
            "Inicio: " . $reservation['start_date'] . "\n" .
            "Fin: " . $reservation['end_date'];
    
    // Capturar la imagen generada en memoria
    ob_start();
    QRcode::png($data, null, 'H', 10, 1);
    $imageData = ob_get_clean();
    
    // Retornar el contenido en base64
    return base64_encode($imageData);
}

/**
 * Create a new reservation.
 *
 * @param array $data Reservation data.
 * @return int|bool New reservation ID or false on failure.
 */
function create_reservation($data)
{
    global $conn;
    
    // Comprobar datos obligatorios
    if (empty($data['user_id']) || empty($data['vehicle_id']) || 
        empty($data['start_date']) || empty($data['end_date']) || 
        empty($data['pickup_location'])) {
        return false;
    }
    
    // Verificar si existen registros en reservation_statuses
    $check_sql = "SELECT COUNT(*) as count FROM reservation_statuses";
    $check_result = db_fetch_assoc($check_sql);
    
    // Si no hay registros, insertar los estados básicos
    if (!$check_result || $check_result['count'] == 0) {
        $insert_sql = "INSERT INTO reservation_statuses (id, name, description) VALUES 
                      (1, 'Pending', 'Reserva pendiente de confirmación'),
                      (2, 'Active', 'Reserva confirmada y activa'),
                      (3, 'Completed', 'Reserva completada'),
                      (4, 'Cancelled', 'Reserva cancelada')";
        mysqli_query($conn, $insert_sql);
    }
    
    // Crear la reserva con status_id=1 (Pending)
    $reservation_data = [
        'user_id'         => $data['user_id'],
        'vehicle_id'      => $data['vehicle_id'],
        'start_date'      => $data['start_date'],
        'end_date'        => $data['end_date'],
        'status_id'       => 1, // Pending
        'pickup_location' => $data['pickup_location'],
        'return_location' => $data['return_location'] ?? $data['pickup_location'],
        'comments'        => $data['comments'] ?? '',
        'created_at'      => date('Y-m-d H:i:s')
    ];
    
    // Insertar la reserva en la BD
    $reservation_id = db_insert('reservations', $reservation_data);
    
    if ($reservation_id) {
        // Construir el array de reserva con el ID para generar el QR
        $reservation_data['id'] = $reservation_id;
        
        // Generar el QR en base64
        $qr_code = generateQrCode($reservation_data);
        
        // Actualizar la reserva para guardar el QR
        $update_sql = "UPDATE reservations SET qr_code = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $qr_code, $reservation_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        return $reservation_id;
    }
    
    return false;
}


/**
 * Format date for display
 *
 * @param string $date Date string in Y-m-d format
 * @return string Formatted date
 */
function format_date($date)
{
    if ($date === null || $date === '') {
        return 'Pendiente';
    }
    return date('d/m/Y', strtotime($date));
}

/**
 * Format price for display
 *
 * @param float $price Price
 * @return string Formatted price
 */
function format_price($price)
{
    if ($price === null || $price === '') {
        return '€0,00';
    }
    return '€' . number_format((float)$price, 2, ',', '.');
}

/**
 * Register a new user
 *
 * @param array $data User data
 * @return int|bool New user ID or false on failure
 */
function register_user($data)
{
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $existing_user = db_fetch_assoc($sql, [$data['email']]);
    
    if ($existing_user) {
        return false;
    }
    
    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $user_data = [
        'email' => $data['email'],
        'password' => $hashed_password,
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'phone' => $data['phone'] ?? '',
        'role_id' => 2, // Regular user
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    return db_insert('users', $user_data);
}

/**
 * Login a user
 *
 * @param string $email User email
 * @param string $password User password
 * @return bool Success or failure
 */
function login_user($email, $password)
{
    $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
    $user = db_fetch_assoc($sql, [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['role_id'] = $user['role_id'];
        return true;
    }
    
    return false;
}

/**
 * Logout the current user
 *
 * @return void
 */
function logout_user()
{
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['role_id']);
    session_destroy();
}

/**
 * Check if a user is an admin
 *
 * @return bool True if the user is an admin
 */
function is_admin()
{
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

/**
 * Set a flash message to be displayed on the next page
 *
 * @param string $type Message type (success, error, info)
 * @param string $message Message text
 * @return void
 */
function set_flash_message($type, $message)
{
    $_SESSION['toast'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get user's credit cards
 *
 * @param int $user_id User ID
 * @return array User's credit cards
 */
function get_user_credit_cards($user_id)
{
    $sql = "SELECT * FROM credit_cards WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
    return db_fetch_all($sql, [$user_id]);
}

/**
 * Add a new credit card for a user
 *
 * @param array $data Credit card data
 * @return int|bool New credit card ID or false on failure
 */
function add_credit_card($data)
{
    // Si es la primera tarjeta, establecerla como predeterminada
    if (empty(get_user_credit_cards($data['user_id']))) {
        $data['is_default'] = 1;
    }
    
    // Si esta tarjeta se marca como predeterminada, desmarcar las demás
    if (!empty($data['is_default'])) {
        $update_sql = "UPDATE credit_cards SET is_default = 0 WHERE user_id = ?";
        db_query($update_sql, [$data['user_id']]);
    }
    
    // Establecer la fecha de creación
    $data['created_at'] = date('Y-m-d H:i:s');
    
    return db_insert('credit_cards', $data);
}

/**
 * Remove a credit card
 *
 * @param int $card_id Credit card ID
 * @param int $user_id User ID (for security check)
 * @return bool Success or failure
 */
function remove_credit_card($card_id, $user_id)
{
    // Verificar que la tarjeta pertenece al usuario
    $sql = "SELECT * FROM credit_cards WHERE id = ? AND user_id = ?";
    $card = db_fetch_assoc($sql, [$card_id, $user_id]);
    
    if (!$card) {
        return false;
    }
    
    // Si la tarjeta a eliminar es la predeterminada, establecer otra como predeterminada
    if ($card['is_default']) {
        // Buscar otra tarjeta para establecer como predeterminada
        $find_sql = "SELECT id FROM credit_cards WHERE user_id = ? AND id != ? ORDER BY created_at DESC LIMIT 1";
        $other_card = db_fetch_assoc($find_sql, [$user_id, $card_id]);
        
        if ($other_card) {
            $update_sql = "UPDATE credit_cards SET is_default = 1 WHERE id = ?";
            db_query($update_sql, [$other_card['id']]);
        }
    }
    
    // Eliminar la tarjeta
    $delete_sql = "DELETE FROM credit_cards WHERE id = ?";
    return db_query($delete_sql, [$card_id]) ? true : false;
}

/**
 * Set a credit card as default
 *
 * @param int $card_id Credit card ID
 * @param int $user_id User ID (for security check)
 * @return bool Success or failure
 */
function set_default_credit_card($card_id, $user_id)
{
    // Verificar que la tarjeta pertenece al usuario
    $sql = "SELECT * FROM credit_cards WHERE id = ? AND user_id = ?";
    $card = db_fetch_assoc($sql, [$card_id, $user_id]);
    
    if (!$card) {
        return false;
    }
    
    // Desmarcar todas las tarjetas del usuario
    $update_all_sql = "UPDATE credit_cards SET is_default = 0 WHERE user_id = ?";
    db_query($update_all_sql, [$user_id]);
    
    // Marcar la tarjeta como predeterminada
    $update_sql = "UPDATE credit_cards SET is_default = 1 WHERE id = ?";
    return db_query($update_sql, [$card_id]) ? true : false;
}

/**
 * Get upcoming reservations for a user
 *
 * @param int $user_id User ID
 * @param int $limit Number of reservations to return
 * @return array Upcoming reservations
 */
function get_upcoming_reservations($user_id, $limit = 3)
{
    $sql = "SELECT r.*, v.name as vehicle_name, b.name as brand_name,
                   vt.name as vehicle_type, rs.name as status_name
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            JOIN brands b ON v.brand_id = b.id
            JOIN vehicle_types vt ON v.type_id = vt.id
            JOIN reservation_statuses rs ON r.status_id = rs.id
            WHERE r.user_id = ? AND r.start_date >= CURDATE()
            AND (rs.name = 'Pending' OR rs.name = 'Active')
            ORDER BY r.start_date ASC
            LIMIT ?";
    
    return db_fetch_all($sql, [$user_id, $limit]);
}

/**
 * Verify if a card has valid format
 *
 * @param string $card_number Card number
 * @param string $exp_month Expiration month
 * @param string $exp_year Expiration year
 * @param string $cvv CVV code
 * @return bool True if the card has valid format
 */
function is_valid_card_format($card_number, $exp_month, $exp_year, $cvv)
{
    // Eliminar espacios y guiones
    $card_number = preg_replace('/[\s-]+/', '', $card_number);
    
    // Verificar longitud del número de tarjeta (13-19 dígitos)
    if (!preg_match('/^\d{13,19}$/', $card_number)) {
        return false;
    }
    
    // Verificar mes de expiración (01-12)
    if (!preg_match('/^(0[1-9]|1[0-2])$/', $exp_month)) {
        return false;
    }
    
    // Verificar año de expiración (formato YY, mayor o igual al año actual)
    $current_year = intval(date('y'));
    if (!preg_match('/^\d{2}$/', $exp_year) || intval($exp_year) < $current_year) {
        return false;
    }
    
    // Verificar CVV (3-4 dígitos)
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        return false;
    }
    
    // Verificar que la tarjeta no ha expirado
    if (intval($exp_year) == $current_year && intval($exp_month) < intval(date('m'))) {
        return false;
    }
    
    return true;
}

/**
 * Check if a user has a valid subscription for a vehicle
 *
 * @param int $user_id User ID
 * @param int $required_plan_id Plan ID required for the vehicle
 * @return bool True if the user has a valid subscription
 */
function has_valid_subscription_for_vehicle($user_id, $required_plan_id) 
{
    $subscription = get_user_subscription($user_id);
    
    // Si no tiene suscripción activa, no puede reservar
    if (!$subscription || !isset($subscription['is_active']) || !$subscription['is_active']) {
        return false;
    }
    
    // Si no tenemos plan_id en la suscripción, asumimos que puede reservar cualquier vehículo
    if (!isset($subscription['plan_id'])) {
        return true;
    }
    
    // Verificar si el plan del usuario es igual o superior al requerido
    return $subscription['plan_id'] >= $required_plan_id;
}

/**
 * Check if a vehicle is available for the specified dates
 *
 * @param int $vehicle_id Vehicle ID
 * @param string $start_date Start date (Y-m-d)
 * @param string $end_date End date (Y-m-d)
 * @param int|null $exclude_reservation_id Reservation ID to exclude (for updates)
 * @return bool True if the vehicle is available
 */
function is_vehicle_available($vehicle_id, $start_date, $end_date, $exclude_reservation_id = null) 
{
    // Intentamos verificar si hay conflictos con otras reservas
    try {
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE vehicle_id = ? 
                AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) 
                    OR (start_date <= ? AND end_date >= ?))";
        
        $params = [
            $vehicle_id, 
            $start_date, $end_date,
            $start_date, $end_date,
            $start_date, $end_date
        ];
        
        // Si estamos actualizando una reserva, excluir esa reserva de la verificación
        if ($exclude_reservation_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_reservation_id;
        }
        
        $result = db_fetch_assoc($sql, $params);
        
        // Si count = 0, el vehículo está disponible
        return ($result && $result['count'] == 0);
    } catch (Exception $e) {
        // Si hay un error, asumimos que está disponible
        return true;
    }
}

/**
 * Obtener opiniones reales de un vehículo
 * @param int $vehicle_id
 * @return array
 */
function get_vehicle_reviews($vehicle_id) 
{
    $sql = "SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.vehicle_id = ? AND r.is_approved = 1 ORDER BY r.created_at DESC";
    return db_fetch_all($sql, [$vehicle_id]);
}

/**
 * Devuelve las reservas de un usuario para un vehículo sobre las que puede dejar opinión
 * (Completed, no Cancelled, fecha fin pasada, sin review previa)
 * @param int $user_id
 * @param int $vehicle_id
 * @return array
 */
function get_user_reviewable_reservations($user_id, $vehicle_id)
{
    $sql = "SELECT r.* FROM reservations r
            JOIN reservation_statuses rs ON r.status_id = rs.id
            WHERE r.user_id = ? AND r.vehicle_id = ?
              AND rs.name = 'Completed'
              AND r.end_date < CURDATE()
              AND NOT EXISTS (SELECT 1 FROM reviews rev WHERE rev.reservation_id = r.id AND rev.user_id = r.user_id)
              AND r.status_id != (SELECT id FROM reservation_statuses WHERE name = 'Cancelled' LIMIT 1)
            ORDER BY r.end_date DESC";
    return db_fetch_all($sql, [$user_id, $vehicle_id]);
}

/**
 * Devuelve true si el usuario puede dejar opinión para una reserva concreta
 * @param int $user_id
 * @param int $reservation_id
 * @return bool
 */
function can_user_review_reservation($user_id, $reservation_id)
{
    $sql = "SELECT r.* FROM reservations r
            JOIN reservation_statuses rs ON r.status_id = rs.id
            WHERE r.id = ? AND r.user_id = ?
              AND rs.name = 'Completed'
              AND r.end_date < CURDATE()
              AND NOT EXISTS (SELECT 1 FROM reviews rev WHERE rev.reservation_id = r.id AND rev.user_id = r.user_id)
              AND r.status_id != (SELECT id FROM reservation_statuses WHERE name = 'Cancelled' LIMIT 1)
            LIMIT 1";
    $row = db_fetch_assoc($sql, [$reservation_id, $user_id]);
    return $row ? true : false;
}

/**
 * Display flash messages
 * 
 * @return void
 */
function display_flash_message()
{
    if (isset($_SESSION['toast'])) {
        $type = $_SESSION['toast']['type'] === 'error' ? 'danger' : $_SESSION['toast']['type'];
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['toast']['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['toast']);
    }
}

/**
 * Update user profile
 *
 * @param int $user_id User ID
 * @param array $data Profile data
 * @return bool Success or failure
 */
function update_user_profile($user_id, $data)
{
    $update_data = [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'phone' => $data['phone'] ?? '',
        'address' => $data['address'] ?? '',
        'city' => $data['city'] ?? '',
        'postal_code' => $data['postal_code'] ?? '',
        'country' => $data['country'] ?? ''
    ];
    
    return db_update('users', $update_data, 'id = ?', [$user_id]);
}
/**
 * Update user password
 *
 * @param int $user_id User ID
 * @param string|null $current_password Current password (null if not set)
 * @param string $new_password New password
 * @return bool Success or failure
 */
function update_user_password($user_id, $current_password, $new_password)
{
    $sql = "SELECT password FROM users WHERE id = ?";
    $user = db_fetch_assoc($sql, [$user_id]);
    
    if (!$user) {
        return false; // Usuario no encontrado
    }

    // Si el usuario ya tiene una contraseña, verificarla
    if ($user['password'] !== null) {
        if (!password_verify($current_password, $user['password'])) {
            return false; // Contraseña actual incorrecta
        }
    }
    // Si password es NULL, no se verifica la contraseña actual (caso de usuarios Google)

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    return db_update('users', ['password' => $hashed_password], 'id = ?', [$user_id]);
}
function get_reserved_dates($vehicle_id) {
    $sql = "SELECT start_date, end_date 
            FROM reservations 
            WHERE vehicle_id = ? 
            AND status_id = (SELECT id FROM reservation_statuses WHERE name = 'Pending')";
    return db_fetch_all($sql, [$vehicle_id]);
}

/**
 * Construye el cuerpo del correo electrónico para notificaciones de reserva.
 *
 * @param string $type Tipo de notificación ('confirmation' o 'cancellation')
 * @param array $reservation Datos de la reserva
 * @param array $user Datos del usuario
 * @param string|null $qrPath Ruta del archivo QR (opcional, solo para confirmación)
 * @return string Cuerpo del correo en formato HTML
 */
function buildReservationEmailBody($type, $reservation, $user, $qrPath = null) {
    $vehicleName = htmlspecialchars($reservation['brand_name'] . ' ' . $reservation['vehicle_name']);
    $startDate = format_date($reservation['start_date']);
    $endDate = format_date($reservation['end_date']);
    $pickupLocation = htmlspecialchars($reservation['pickup_location']);
    $userName = htmlspecialchars($user['first_name']);

    // Estilos comunes para ambos tipos de correo
    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . ($type === 'confirmation' ? 'Confirmación de Reserva' : 'Cancelación de Reserva') . '</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4; color: #333;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
                        <!-- Header -->
                        <tr>
                            <td bgcolor="#007bff" style="background-color: #007bff; padding: 20px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 28px;">DriveClub</h1>
                                <p style="color: #e6f0ff; margin: 5px 0 0; font-size: 14px;">Tu experiencia sobre ruedas</p>
                            </td>
                        </tr>
                        <!-- Contenido -->
                        <tr>
                            <td style="padding: 30px;">';

    if ($type === 'confirmation') {
        $html .= '
                                <h2 style="color: #007bff; margin: 0 0 20px; font-size: 24px;">¡Reserva Confirmada!</h2>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Hola ' . $userName . ',</p>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Tu reserva para el <strong>' . $vehicleName . '</strong> está lista. Aquí tienes los detalles:</p>
                                <table width="100%" cellpadding="10" cellspacing="0" border="0" style="background-color: #f9f9f9; border-radius: 5px; margin-bottom: 20px;">
                                    <tr>
                                        <td style="font-size: 14px; color: #555;"><strong>ID de Reserva:</strong></td>
                                        <td style="font-size: 14px;">' . $reservation['id'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 14px; color: #555;"><strong>Fecha de Inicio:</strong></td>
                                        <td style="font-size: 14px;">' . $startDate . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 14px; color: #555;"><strong>Fecha de Fin:</strong></td>
                                        <td style="font-size: 14px;">' . $endDate . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 14px; color: #555;"><strong>Ubicación de Recogida:</strong></td>
                                        <td style="font-size: 14px;">' . $pickupLocation . '</td>
                                    </tr>
                                </table>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Presenta este código QR al recoger tu vehículo:</p>
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <img src="cid:qr_code" alt="Código QR" style="width: 150px; height: 150px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0;">¡Gracias por elegir DriveClub! Nos vemos en la carretera.</p>';
    } elseif ($type === 'cancellation') {
        $html .= '
                                <h2 style="color: #e63946; margin: 0 0 20px; font-size: 24px;">Reserva Cancelada</h2>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Hola ' . $userName . ',</p>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Tu reserva para el <strong>' . $vehicleName . '</strong> (ID: ' . $reservation['id'] . ') ha sido cancelada.</p>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Si tienes alguna duda o necesitas ayuda, no dudes en contactarnos.</p>
                                <p style="font-size: 16px; line-height: 1.5; margin: 0;">Atentamente,<br>El equipo de DriveClub</p>';
    }

    // Footer
    $html .= '
                            </td>
                        </tr>
                        <!-- Footer -->
                        <tr>
                            <td bgcolor="#f8f9fa" style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #777;">
                                <p style="margin: 0 0 10px;">DriveClub &copy; ' . date('Y') . ' - Todos los derechos reservados</p>
                                <p style="margin: 0;">¿Necesitas ayuda? <a href="mailto:soporte@driveclub.com" style="color: #1d3557; text-decoration: none;">soporte@driveclub.com</a></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return $html;
}

/**
 * Envía un correo de confirmación de reserva con el QR.
 *
 * @param int $reservationId ID de la reserva
 * @return bool Verdadero si el correo se envió correctamente, falso en caso contrario
 */
function sendReservationConfirmation($reservationId) {
    require_once 'mailer.php';
    
    // Obtener datos de la reserva
    $sql = "SELECT r.*, v.name as vehicle_name, b.name as brand_name
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            JOIN brands b ON v.brand_id = b.id
            WHERE r.id = ?";
    $reservation = db_fetch_assoc($sql, [$reservationId]);
    
    if (!$reservation) {
        return false;
    }
    
    // Obtener datos del usuario
    $user = get_user_data();
    if (!$user) {
        return false;
    }
    
    // Generar QR y obtener la ruta temporal
    $qrData = generateQrCode($reservation);
    $qrPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    file_put_contents($qrPath, base64_decode($qrData));
    
    // Construir el cuerpo del correo
    $subject = 'Confirmación de Reserva - DriveClub';
    $body = buildReservationEmailBody('confirmation', $reservation, $user, $qrPath);
    
    // Enviar el correo
    $result = sendEmail($user['email'], $subject, $body, true, $qrPath, 'qr_code');
    
    // Eliminar el archivo temporal
    unlink($qrPath);
    
    return $result;
}

/**
 * Envía un correo de cancelación de reserva.
 *
 * @param int $reservationId ID de la reserva
 * @return bool Verdadero si el correo se envió correctamente, falso en caso contrario
 */
function sendReservationCancellation($reservationId) {
    require_once 'mailer.php';
    
    // Obtener datos de la reserva
    $sql = "SELECT r.*, v.name as vehicle_name, b.name as brand_name
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            JOIN brands b ON v.brand_id = b.id
            WHERE r.id = ?";
    $reservation = db_fetch_assoc($sql, [$reservationId]);
    
    if (!$reservation) {
        return false;
    }
    
    // Obtener datos del usuario
    $user = get_user_data();
    if (!$user) {
        return false;
    }
    
    // Construir el cuerpo del correo
    $subject = 'Cancelación de Reserva - DriveClub';
    $body = buildReservationEmailBody('cancellation', $reservation, $user);
    
    // Enviar el correo
    return sendEmail($user['email'], $subject, $body, true);
}

function cancel_user_subscription($user_id)
{
    // Buscar la suscripción activa
    $sql = "SELECT id FROM user_subscriptions WHERE user_id = ? AND is_active = 1 LIMIT 1";
    $subscription = db_fetch_assoc($sql, [$user_id]);
    if (!$subscription) {
        return false;
    }
    // Actualizar la suscripción: desactivar, poner fecha de fin y desactivar auto-renovación
    $update = db_update('user_subscriptions', [
        'is_active' => 0,
        'end_date' => date('Y-m-d'),
        'auto_renewal' => 0
    ], 'id = ?', [$subscription['id']]);
    return $update;
}

/**
 * Get the most popular vehicles (most reserved)
 *
 * @param int $limit Number of vehicles to return
 * @return array Popular vehicles
 */
function get_popular_vehicles($limit = 6)
{
    $sql = "SELECT v.*, b.name as brand_name, vt.name as type_name, sp.name as plan_name, sp.price_monthly,
                   COUNT(r.id) as reservations_count
            FROM vehicles v
            JOIN brands b ON v.brand_id = b.id
            JOIN vehicle_types vt ON v.type_id = vt.id
            JOIN subscription_plans sp ON v.minimum_plan_id = sp.id
            LEFT JOIN reservations r ON v.id = r.vehicle_id
            WHERE v.is_active = 1
            GROUP BY v.id
            ORDER BY reservations_count DESC, RAND()
            LIMIT ?";
    
    return db_fetch_all($sql, [$limit]);
}

/**
 * Obtiene vehículos similares a uno dado (por tipo y plan), excluyendo el propio vehículo.
 *
 * @param int $vehicle_id ID del vehículo actual
 * @param int $type_id ID del tipo de vehículo
 * @param int $plan_id ID del plan mínimo
 * @param int $limit Número máximo de vehículos a devolver
 * @return array Vehículos similares
 */
function get_similar_vehicles($vehicle_id, $type_id, $plan_id, $limit = 3)
{
    // Buscar vehículos del mismo tipo y plan igual o inferior, excluyendo el actual
    $filters = [
        'type_id' => $type_id,
        'plan_id' => $plan_id,
        'limit' => $limit + 1, // +1 por si el propio vehículo está incluido
    ];
    $vehicles = get_all_vehicles($filters);
    // Filtrar el propio vehículo
    $similar = [];
    foreach ($vehicles as $v) {
        if ($v['id'] != $vehicle_id) {
            $similar[] = $v;
        }
        if (count($similar) >= $limit) break;
    }
    return $similar;
}

/**
 * Validar nombre o apellidos: palabras separadas por espacio, guion o apóstrofe, sin símbolos seguidos, sin empezar/terminar por símbolo.
 * Ej: José Espronceda-Castillo de Ramón
 */
function is_valid_name($name) {
    return preg_match(
        "/^[A-Za-zÁÉÍÓÚáéíóúÑñ]+([\s'-][A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/u",
        $name
    );
}

/**
 * Validar titular de tarjeta: igual que nombre, pero permite coma.
 * Ej: Nazario, Ronaldo
 */
function is_valid_card_holder($name) {
    return preg_match(
        "/^[A-Za-zÁÉÍÓÚáéíóúÑñ]+([\s,'-][A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/u",
        $name
    );
}

/**
 * Validar dirección: palabras/números separados por espacio, guion, coma, punto, barra, º, ª, permitiendo combinaciones como ', ', '. ', '- ', '/ ', pero nunca símbolos seguidos ni empezar/terminar por símbolo. Longitud 2-80.
 * Ej: Calle de Rocafort, 14; Av. de Andalucía, 4A-C; C/ Mayor 12-B; Calle Godella, 40
 */
function is_valid_address($address) {
    return preg_match(
        "/^(?=.{2,80}$)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9ºª]+(([-,\.\/ºª] ?| )[A-Za-zÁÉÍÓÚáéíóúÑñ0-9ºª]+)*$/u",
        trim($address)
    );
}

/**
 * Validar ciudad o país: solo palabras separadas por espacio, sin símbolos, sin empezar/terminar por espacio, sin dobles espacios.
 * Ej: San Sebastián de los Reyes
 */
function is_valid_city_or_country($value) {
    return preg_match(
        "/^[A-Za-zÁÉÍÓÚáéíóúÑñ]+( [A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/u",
        $value
    );
}

/**
 * Validar código postal (4-10 dígitos)
 */
function is_valid_postal_code($postal) {
    return preg_match('/^\d{4,10}$/', $postal);
}

/**
 * Validar teléfono: permite +, dígitos, espacios, guiones, pero solo números y símbolos permitidos, sin letras.
 * Ej: +34 612 345 678, 612-345-678
 */
function is_valid_phone($phone) {
    return preg_match('/^\+?\d{1,4}([\s-]?\d{2,4}){2,5}$/', $phone);
}