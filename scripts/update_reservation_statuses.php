<?php
// Script para actualizar estados de reservas según la fecha y hora
// Ejecutar con cron a las 9:00 y 18:00 hora española
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Zona horaria España
date_default_timezone_set('Europe/Madrid');

$now = new DateTime();
$hour = (int)$now->format('H');
$today = $now->format('Y-m-d');

// Obtener IDs de estados
function get_status_id($name) {
    $sql = "SELECT id FROM reservation_statuses WHERE name = ? LIMIT 1";
    $row = db_fetch_assoc($sql, [$name]);
    return $row ? (int)$row['id'] : null;
}
$status_pending = get_status_id('Pending');
$status_active = get_status_id('Active');
$status_completed = get_status_id('Completed');
$status_cancelled = get_status_id('Cancelled');

// 1. Actualizar a 'Active' si hoy es start_date y hora >= 9, o si ya está entre start_date y end_date
if ($hour >= 9 && $hour < 18) {
    $sql = "UPDATE reservations SET status_id = ?
            WHERE status_id != ? AND status_id != ?
              AND start_date <= ? AND end_date >= ?";
    $count = db_query($sql, [$status_active, $status_cancelled, $status_completed, $today, $today]);
    echo "[" . date('Y-m-d H:i:s') . "] Reservas activadas: " . (is_numeric($count) ? $count : 'OK') . "\n";
}

// 2. Actualizar a 'Pending' si hoy es antes de start_date
$sql = "UPDATE reservations SET status_id = ?
        WHERE status_id != ? AND status_id != ?
          AND start_date > ?";
$count = db_query($sql, [$status_pending, $status_cancelled, $status_completed, $today]);
echo "[" . date('Y-m-d H:i:s') . "] Reservas pendientes: " . (is_numeric($count) ? $count : 'OK') . "\n";

// 3. Actualizar a 'Completed' si hoy es después de end_date, o si es el día de end_date y hora >= 18
if ($hour >= 18) {
    $sql = "UPDATE reservations SET status_id = ?
            WHERE status_id != ? AND status_id != ?
              AND ((end_date < ?) OR (end_date = ?))";
    $count = db_query($sql, [$status_completed, $status_cancelled, $status_pending, $today, $today]);
    echo "[" . date('Y-m-d H:i:s') . "] Reservas completadas: " . (is_numeric($count) ? $count : 'OK') . "\n";
}

// 4. No tocar reservas canceladas
// Fin del script 