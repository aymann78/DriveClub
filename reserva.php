<?php
/**
 * DriveClub - Formulario de Reserva
 */
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// If there is no vehicle ID or user is not logged in, redirect
if (!isset($_GET['vehicle_id']) || !is_logged_in()) {
    redirect('vehiculos.php');
}

$vehicle_id = $_GET['vehicle_id'];
$user_id = $_SESSION['user_id'];

// Get vehicle details
$vehicle = get_vehicle_by_id($vehicle_id);

// If the vehicle does not exist, redirect
if (!$vehicle) {
    set_flash_message('error', 'Vehicle not found');
    redirect('vehiculos.php');
}

// Get previous form dates (if they exist)
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Check if the user can reserve this vehicle
if (!has_valid_subscription_for_vehicle($user_id, $vehicle['plan_id'])) {
    set_flash_message('error', 'Your subscription does not allow you to reserve this vehicle');
    redirect("detalle-vehiculo.php?id=$vehicle_id");
}

// If an existing reservation is being shown
$reservation = null;
if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
    $sql = "SELECT r.*, v.id as vehicle_id, v.name as vehicle_name, b.name as brand_name
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            JOIN brands b ON v.brand_id = b.id
            WHERE r.id = ? AND r.user_id = ?";
    
    $reservation = db_fetch_assoc($sql, [$reservation_id, $user_id]);
    
    if (!$reservation) {
        set_flash_message('error', 'Reserva no encontrada');
        redirect('mi-cuenta.php');
    }
    
    // Use vehicle data from the reservation
    $vehicle = [
        'id' => $reservation['vehicle_id'],
        'name' => $reservation['vehicle_name'],
        'brand_name' => $reservation['brand_name']
    ];
}

// Get reserved dates for this vehicle
$reserved_dates = get_reserved_dates($vehicle_id);
$reserved_dates_json = json_encode($reserved_dates);

// Process reservation form
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $pickup_location = $_POST['pickup_location'] ?? '';
    $comments = $_POST['comments'] ?? '';
    
    // Check if the pickup and return dates are required
    if (empty($start_date) || empty($end_date)) {
        $errors[] = 'Las fechas de recogida y devolución son obligatorias';
    } elseif (strtotime($start_date) <= strtotime(date('Y-m-d'))) {
        $errors[] = 'La fecha de recogida debe ser a partir de mañana';
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = 'La fecha de devolución debe ser posterior a la fecha de recogida';
    } elseif ((strtotime($end_date) - strtotime($start_date)) > 7 * 24 * 60 * 60) {
        $errors[] = 'La reserva no puede ser superior a 7 días';
    }
    
    // Check if the pickup location is required
    if (empty($pickup_location)) {
        $errors[] = 'La ubicación de recogida es obligatoria';
    }
    
    // Check if the dates do not overlap with existing reservations
    if (empty($errors)) {
        foreach ($reserved_dates as $reservation) {
            $reserved_start = new DateTime($reservation['start_date']);
            $reserved_end = new DateTime($reservation['end_date']);
            $new_start = new DateTime($start_date);
            $new_end = new DateTime($end_date);
            
            if ($new_start <= $reserved_end && $new_end >= $reserved_start) {
                $errors[] = 'Las fechas seleccionadas se solapan con una reserva existente.';
                break;
            }
        }
    }
    
    // If there are no errors, create reservation
    if (empty($errors)) {
        $reservation_data = [
            'user_id' => $user_id,
            'vehicle_id' => $vehicle_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pickup_location' => $pickup_location,
            'comments' => $comments
        ];
        
        $reservation_id = create_reservation($reservation_data);
        
        if ($reservation_id) {
            // Send confirmation email
            if (sendReservationConfirmation($reservation_id)) {
                set_flash_message('success', '¡Reserva creada con éxito! Revisa tu correo para la confirmación.');
            } else {
                set_flash_message('success', '¡Reserva creada con éxito! (No se pudo enviar el correo de confirmación)');
            }
            redirect('mi-cuenta.php');
        } else {
            $errors[] = 'Error al crear la reserva. Por favor, inténtalo de nuevo.';
        }
    }
}

// Title of the page
$page_title = 'Reservar ' . $vehicle['brand_name'] . ' ' . $vehicle['name'] . ' - DriveClub';
$current_page = 'reserva';

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="page-header">
    <div class="container">
        <h1>Reservar un Vehículo</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="vehiculos.php">Vehículos</a></li>
                <li class="breadcrumb-item"><a href="detalle-vehiculo.php?id=<?php echo $vehicle['id']; ?>"><?php echo htmlspecialchars($vehicle['name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Reservar</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Reservation section -->
<section class="reservation-section">
    <div class="container">
        <!-- Reservation form -->
        <div class="reservation-form-container">
            <div class="row">
                <div class="col-md-7">
                    <div class="reservation-form">
                        <h2>Detalles de la Reserva</h2>
                        <form method="post" action="">
                            <div class="form-group mb-3">
                                <label for="vehicle" class="form-label">Vehículo</label>
                                <input type="text" class="form-control" id="vehicle" value="<?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?>" readonly>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="start_date" class="form-label">Fecha de Recogida</label>
                                        <input class="form-control" id="start_date" name="start_date" type="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo htmlspecialchars($start_date ?: date('Y-m-d', strtotime('+1 day'))); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="end_date" class="form-label">Fecha de Devolución</label>
                                        <input class="form-control" id="end_date" name="end_date" type="date" min="<?php echo date('Y-m-d', strtotime('+2 day')); ?>" value="<?php echo htmlspecialchars($end_date ?: date('Y-m-d', strtotime('+2 day'))); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="pickup_location" class="form-label">Ubicación de Recogida</label>
                                <select class="form-select" id="pickup_location" name="pickup_location" required>
                                    <option value="">Selecciona una ubicación</option>
                                    <option value="Madrid">Madrid</option>
                                    <option value="Barcelona">Barcelona</option>
                                    <option value="Valencia">Valencia</option>
                                    <option value="Sevilla">Sevilla</option>
                                    <option value="Bilbao">Bilbao</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="comments" class="form-label">Comentarios Adicionales</label>
                                <textarea class="form-control" id="comments" name="comments" rows="3"><?php echo isset($reservation) && isset($reservation['comments']) ? $reservation['comments'] : ''; ?></textarea>
                                <div class="form-text">Cualquier información relevante para tu reserva</div>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Confirmar Reserva</button>
                                <a href="detalle-vehiculo.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="reservation-sidebar">
                        <div class="vehicle-summary">
                            <h3>Resumen del Vehículo</h3>
                            <div class="vehicle-image">
                                <img src="<?php echo get_vehicle_primary_image($vehicle['id']); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>" class="img-fluid rounded">
                            </div>
                            <div class="vehicle-details mt-3">
                                <h4><?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?></h4>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($vehicle['type_name'] ?? ''); ?></p>
                                <p><strong>Potencia:</strong> <?php echo htmlspecialchars($vehicle['power'] ?? ''); ?></p>
                            </div>
                        </div>
                        
                        <div class="reservation-rules mt-4">
                            <h3>Normas de Reserva</h3>
                            <ul>
                                <li>Período mínimo de reserva: 2 días.</li>
                                <li>Período máximo de reserva según tu plan de suscripción.</li>
                                <li>Las cancelaciones deben realizarse con al menos 24 horas de antelación.</li>
                                <li>Las devoluciones tardías incurrirán en cargos adicionales.</li>
                                <li>El vehículo debe devolverse con el mismo nivel de combustible que al recogerlo.</li>
                                <li>Todos los daños deben ser reportados inmediatamente.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Script for Flatpickr -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Reserved dates from PHP
        var reservedDates = <?php echo $reserved_dates_json; ?>;
        
        // Convert reserved dates to disabled ranges
        var disableRanges = reservedDates.map(function(reservation) {
            return {
                from: reservation.start_date,
                to: reservation.end_date
            };
        });
        
        // Configure Flatpickr for start_date
        var startPicker = flatpickr("#start_date", {
            minDate: new Date().fp_incr(1), // mañana
            disable: disableRanges,
            defaultDate: document.getElementById('start_date').value,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    var minEndDate = new Date(selectedDates[0]);
                    minEndDate.setDate(minEndDate.getDate() + 1);
                    endPicker.set('minDate', minEndDate);
                    
                    // If the current end date is before minEndDate, adjust it
                    var currentEndDate = endPicker.selectedDates[0];
                    if (currentEndDate && currentEndDate < minEndDate) {
                        endPicker.setDate(minEndDate);
                    }
                }
            }
        });
        
        // Configure Flatpickr for end_date
        var endPicker = flatpickr("#end_date", {
            minDate: new Date().fp_incr(2), // pasado mañana
            disable: disableRanges,
            defaultDate: document.getElementById('end_date').value,
            onChange: function(selectedDates, dateStr, instance) {
                // We don't set maxDate in startPicker to allow full freedom in start_date
            }
        });
    });
</script>

<?php
    // Include footer
include 'includes/footer.php';
?>
<!-- Toast Notifications Container (same as mi-cuenta and checkout) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script src="assets/js/reserva.js"></script>
<script>
<?php if (!empty($errors)): ?>
  window.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($errors as $error): ?>
      showFlash('danger', <?php echo json_encode($error); ?>);
    <?php endforeach; ?>
  });
<?php endif; ?>
</script>