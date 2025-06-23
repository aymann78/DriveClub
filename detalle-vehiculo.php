<?php
/**
 * DriveClub - Vehicle Detail
 */
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$vehicle_id = $_GET['id'] ?? 0;

// If there is no ID, redirect
if (!$vehicle_id) {
    redirect('vehiculos.php');
}

// Get vehicle details
$vehicle = get_vehicle_by_id($vehicle_id);

// If the vehicle does not exist, redirect
if (!$vehicle) {
    set_flash_message('error', 'Vehículo no encontrado');
    redirect('vehiculos.php');
}

// Get vehicle images
$vehicle_images = get_vehicle_images($vehicle_id);

$reserved_dates = get_reserved_dates($vehicle_id);
$reserved_dates_json = json_encode($reserved_dates);

// If there are no images, use a placeholder image
if (empty($vehicle_images)) {
    $vehicle_images[] = [
        'image_url' => 'https://via.placeholder.com/800x500?text=No+Image',
        'is_primary' => 1
    ];
}

// Get real reviews
$reviews = get_vehicle_reviews($vehicle_id);
$avg_rating = 0;
if (count($reviews) > 0) {
    $avg_rating = array_sum(array_column($reviews, 'rating')) / count($reviews);
}

// If logged in, get reviewable reservations
$reviewable_reservations = [];
if (is_logged_in()) {
    $reviewable_reservations = get_user_reviewable_reservations($_SESSION['user_id'], $vehicle_id);
}

// Check if the user can reserve this vehicle
$can_reserve = false;
$user_subscription = null;
if (is_logged_in()) {
    $can_reserve = has_valid_subscription_for_vehicle($_SESSION['user_id'], $vehicle['plan_id']);
    $user_subscription = get_user_subscription($_SESSION['user_id']);
}

$page_title = $vehicle['brand_name'] . ' ' . $vehicle['name'] . ' - DriveClub';
$current_page = 'vehiculos';
include 'includes/header.php';
?>

<!-- Vehicle detail section -->
<section class="vehicle-detail-section py-5">
    <div class="container">
        <div class="row vehicle-detail-container">
            <div class="col-lg-8 vehicle-content-section">
                <!-- Vehicle images -->
                <div class="vehicle-images mb-4">
                    <div id="vehicleCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php foreach ($vehicle_images as $index => $image): ?>
                                <button type="button" data-bs-target="#vehicleCarousel"
                                    data-bs-slide-to="<?php echo $index; ?>"
                                    class="<?php echo ($index === 0) ? 'active' : ''; ?>" <?php echo ($index === 0) ? 'aria-current="true"' : ''; ?>></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner" id="vehicleImagesContainer">
                            <?php foreach ($vehicle_images as $index => $image): ?>
                                <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" class="d-block w-100"
                                        alt="<?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#vehicleCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#vehicleCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                <!-- Vehicle info -->
                <div class="vehicle-info mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="vehicle-type"
                            id="vehicleType"><?php echo htmlspecialchars($vehicle['type_name']); ?></span>
                        <h1 class="vehicle-name ms-3 mb-0" id="vehicleName">
                            <?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?>
                        </h1>
                    </div>
                    <p class="vehicle-year mb-3" id="vehicleYear"><?php echo htmlspecialchars($vehicle['year']); ?></p>
                    <div class="plan-badge mb-4" id="vehiclePlan">Plan
                        <?php echo htmlspecialchars($vehicle['plan_name']); ?>
                    </div>
                </div>

                <!-- Technical specifications -->
                <div class="technical-specs mb-5">
                    <h2 class="section-title mb-4">Especificaciones Técnicas</h2>
                    <div class="row g-4" id="technicalSpecsContainer">
                        <div class="col-md-4 col-6">
                            <div class="spec-card">
                                <div class="spec-icon">
                                    <i class="bi bi-lightning-charge"></i>
                                </div>
                                <div class="spec-title">Potencia</div>
                                <div class="spec-value"><?php echo htmlspecialchars($vehicle['power'] ?: 'N/D'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="spec-card">
                                <div class="spec-icon">
                                    <i class="bi bi-speedometer"></i>
                                </div>
                                <div class="spec-title">0-100 km/h</div>
                                <div class="spec-value">
                                    <?php echo htmlspecialchars($vehicle['acceleration'] ?: 'N/D'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="spec-card">
                                <div class="spec-icon">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div class="spec-title">Velocidad máxima</div>
                                <div class="spec-value"><?php echo htmlspecialchars($vehicle['top_speed'] ?: 'N/D'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="spec-card">
                                <div class="spec-icon">
                                    <i class="bi bi-gear"></i>
                                </div>
                                <div class="spec-title">Tracción</div>
                                <div class="spec-value"><?php echo htmlspecialchars($vehicle['traction'] ?: 'N/D'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="spec-card">
                                <div class="spec-icon">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </div>
                                <div class="spec-title">Transmisión</div>
                                <div class="spec-value">
                                    <?php echo htmlspecialchars($vehicle['transmission'] ?: 'N/D'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="spec-card">
                                <div class="spec-icon">
                                    <i class="bi bi-fuel-pump"></i>
                                </div>
                                <div class="spec-title">Consumo</div>
                                <div class="spec-value">
                                    <?php echo htmlspecialchars($vehicle['fuel_consumption'] ?: 'N/D'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description section -->
                <div class="vehicle-description mb-5">
                    <h2 class="section-title mb-4">Descripción</h2>
                    <?php if (!empty($vehicle['description'])): ?>
                        <p id="vehicleDescription"><?php echo nl2br(htmlspecialchars($vehicle['description'])); ?></p>
                    <?php else: ?>
                        <p id="vehicleDescription">El
                            <?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?>,
                            <?php echo strtolower(htmlspecialchars($vehicle['type_name'])); ?> del
                            <?php echo htmlspecialchars($vehicle['year']); ?>, destaca por su agilidad y la sensación
                            inigualable de conducirlo.
                        </p>
                        <p>
                            Con su tracción <?php echo strtolower(htmlspecialchars($vehicle['traction'] ?: 'trasera')); ?> y
                            excepcional distribución de peso, el <?php echo htmlspecialchars($vehicle['name']); ?> ofrece
                            una agilidad y precisión extraordinarias en curvas. Su tecnología de vanguardia incluye
                            suspensión activa, dirección asistida y un sistema de infoentretenimiento de última generación.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Reviews section -->
                <div class="vehicle-reviews">
                    <h2 class="section-title mb-4">Opiniones de Usuarios</h2>
                    <div class="reviews-container" id="reviewsContainer">
                        <?php if (empty($reviews)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Este vehículo aún no tiene opiniones.
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($reviews, 0, 2) as $index => $review): ?>
                                <div class="review-card">
                                    <div class="reviewer-info">
                                        <img src="https://i.pravatar.cc/150?img=<?php echo 20 + $index; ?>"
                                            alt="<?php echo htmlspecialchars($review['first_name']); ?>">
                                        <div>
                                            <h5><?php echo htmlspecialchars($review['first_name']); ?></h5>
                                            <div class="rating">
                                                <?php
                                                $rating_text = str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']);
                                                echo $rating_text;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($reviewable_reservations)): ?>
                        <div class="card mt-4" id="leaveReviewCard">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Deja tu opinión</h5>
                                <form id="leaveReviewForm">
                                    <?php if (count($reviewable_reservations) > 1): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Reserva</label>
                                            <select class="form-select" name="reservation_id" required>
                                                <?php foreach ($reviewable_reservations as $r): ?>
                                                    <option value="<?php echo $r['id']; ?>">
                                                        <?php echo htmlspecialchars(format_date($r['start_date']) . ' - ' . format_date($r['end_date'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php else: ?>
                                        <input type="hidden" name="reservation_id" value="<?php echo $reviewable_reservations[0]['id']; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">Puntuación</label>
                                        <div id="ratingStars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star" data-value="<?php echo $i; ?>" style="font-size:1.5rem;cursor:pointer;color:#ffc107;"></i>
                                            <?php endfor; ?>
                                            <input type="hidden" name="rating" id="ratingInput" value="0" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Comentario</label>
                                        <textarea class="form-control" name="comment" rows="3" maxlength="500" required></textarea>
                                    </div>
                                    <div id="reviewFormMsg" class="mb-2"></div>
                                    <button type="submit" class="btn btn-primary">Enviar Opinión</button>
                                </form>
                            </div>
                        </div>
                        <script>
                        // Interactive stars
                        document.querySelectorAll('#ratingStars .bi-star').forEach(star => {
                            star.addEventListener('click', function() {
                                const val = this.getAttribute('data-value');
                                document.getElementById('ratingInput').value = val;
                                document.querySelectorAll('#ratingStars .bi-star').forEach((s, idx) => {
                                    s.classList.toggle('bi-star-fill', idx < val);
                                    s.classList.toggle('bi-star', idx >= val);
                                });
                            });
                        });
                        // AJAX submission
                        document.getElementById('leaveReviewForm').onsubmit = async function(e) {
                            e.preventDefault();
                            const form = e.target;
                            const data = Object.fromEntries(new FormData(form).entries());
                            data.rating = parseInt(data.rating);
                            if (!data.rating || data.rating < 1 || data.rating > 5) {
                                document.getElementById('reviewFormMsg').innerHTML = '<div class="alert alert-danger">Selecciona una puntuación válida.</div>';
                                return;
                            }
                            document.getElementById('reviewFormMsg').innerHTML = '';
                            const resp = await fetch('api/leave-review.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify(data)
                            });
                            const res = await resp.json();
                            if (res.success) {
                                document.getElementById('reviewFormMsg').innerHTML = '<div class="alert alert-success">¡Opinión enviada correctamente!</div>';
                                document.getElementById('leaveReviewForm').reset();
                                setTimeout(() => {
                                    document.getElementById('leaveReviewCard').style.display = 'none';
                                    // Optional: reload reviews without reloading the whole page
                                    location.reload();
                                }, 1200);
                            } else {
                                document.getElementById('reviewFormMsg').innerHTML = '<div class="alert alert-danger">' + (res.error || 'Error al enviar la opinión.') + '</div>';
                            }
                        };
                        </script>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reservation sidebar -->
            <div class="col-lg-4 reservation-sidebar-section">
                <div class="reservation-sidebar">
                    <div class="reservation-card">
                        <h3 class="mb-4">Reserva este vehículo</h3>

                        <form action="reserva.php" method="get" id="reservationForm">
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">

                            <div class="date-picker mb-4">
                                <label for="startDate" class="form-label">Fecha de recogida</label>
                                <input class="form-control" id="startDate" name="start_date" type="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>

                            <div class="date-picker mb-4">
                                <label for="endDate" class="form-label">Fecha de devolución</label>
                                <input class="form-control" id="endDate" name="end_date" type="date" min="<?php echo date('Y-m-d', strtotime('+2 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+2 day')); ?>">
                            </div>

                            <div class="subscription-status mb-4">
                                <?php if (is_logged_in()): ?>
                                    <?php if ($user_subscription): ?>
                                        <?php if ($can_reserve): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <span>Incluido en tu suscripción
                                                    <?php echo htmlspecialchars($user_subscription['plan_name']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                <span>Necesitas un plan <?php echo htmlspecialchars($vehicle['plan_name']); ?> o
                                                    superior</span>
                                            </div>
                                            <a href="planes.php" class="btn btn-outline-primary btn-sm mt-2">Ver planes</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>
                                            <span>No tienes una suscripción activa</span>
                                        </div>
                                        <a href="planes.php" class="btn btn-outline-primary btn-sm mt-2">Ver planes</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-info-circle-fill text-info me-2"></i>
                                        <span>Inicia sesión para reservar</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (is_logged_in()): ?>
                                <?php if ($can_reserve): ?>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">Reservar Ahora</button>
                                <?php else: ?>
                                    <a href="planes.php" class="btn btn-primary w-100 mb-3">Actualizar Plan</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary w-100 mb-3">Iniciar Sesión</a>
                            <?php endif; ?>
                        </form>

                        <div class="reservation-notes mt-3">
                            <p class="small">Al reservar este vehículo, recibirás un código QR que te permitirá acceder
                                a él en la fecha seleccionada.</p>
                        </div>
                    </div>

                    <div class="need-help-card mt-4">
                        <h4>¿Necesitas ayuda?</h4>
                        <p>Nuestro equipo está disponible para resolver cualquier duda.</p>
                        <a href="#" class="btn btn-outline-secondary w-100">Contactar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var reservedDates = <?php echo $reserved_dates_json; ?>;


    document.addEventListener('DOMContentLoaded', function () {
        // Initialize the carousel
        var vehicleCarousel = document.getElementById('vehicleCarousel');
        if (vehicleCarousel) {
            new bootstrap.Carousel(vehicleCarousel);
        }

        // Configure minimum dates for the reservation form
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        if (startDateInput && endDateInput) {
            // Set minimum date to today
            const today = new Date();
            const isoDate = today.toISOString().split('T')[0];
            startDateInput.min = isoDate;

            // Set default values if not already set
            if (!startDateInput.value) {
                const defaultStartDate = new Date();
                defaultStartDate.setDate(defaultStartDate.getDate() + 3);
                startDateInput.value = defaultStartDate.toISOString().split('T')[0];
            }

            if (!endDateInput.value) {
                const defaultEndDate = new Date();
                defaultEndDate.setDate(defaultEndDate.getDate() + 10);
                endDateInput.value = defaultEndDate.toISOString().split('T')[0];
            }

            // Update end date based on start date
            startDateInput.addEventListener('change', function () {
                if (this.value) {
                    const minEndDate = new Date(this.value);
                    minEndDate.setDate(minEndDate.getDate() + 1);
                    endDateInput.min = minEndDate.toISOString().split('T')[0];

                    if (!endDateInput.value || new Date(endDateInput.value) <= new Date(this.value)) {
                        const defaultEndDate = new Date(this.value);
                        defaultEndDate.setDate(defaultEndDate.getDate() + 7);
                        endDateInput.value = defaultEndDate.toISOString().split('T')[0];
                    }
                }
            });
        }

        // Al enviar el formulario, siempre pasar los valores actuales de los inputs como parámetros GET
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            var start = document.getElementById('startDate').value;
            var end = document.getElementById('endDate').value;
            this.action = 'reserva.php?vehicle_id=<?php echo $vehicle_id; ?>&start_date=' + encodeURIComponent(start) + '&end_date=' + encodeURIComponent(end);
        });
    });
</script>

<!-- Similar Vehicles Section -->
<section class="similar-vehicles-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Vehículos Similares</h2>
        <div class="row g-4" id="similarVehiclesContainer">
            <?php
            // Obtener vehículos similares
            $similar_vehicles = get_similar_vehicles(
                $vehicle_id,
                $vehicle['type_id'],
                $vehicle['plan_id'],
                3
            );

            // If there are not enough similar vehicles, get some random ones that are not already or the own
            if (count($similar_vehicles) < 3) {
                $ids_to_exclude = array_column($similar_vehicles, 'id');
                $ids_to_exclude[] = $vehicle_id;
                $all_vehicles = get_all_vehicles(['limit' => 10, 'random' => true]);
                foreach ($all_vehicles as $v) {
                    if (!in_array($v['id'], $ids_to_exclude, true)) {
                        $similar_vehicles[] = $v;
                        $ids_to_exclude[] = $v['id'];
                    }
                    if (count($similar_vehicles) >= 3) break;
                }
            }

            // Show similar vehicles
            foreach ($similar_vehicles as $similar_vehicle):
                // Avoid showing the same vehicle
                if ($similar_vehicle['id'] == $vehicle_id)
                    continue;

                // Get the main image of the vehicle
                $similar_image = get_vehicle_primary_image($similar_vehicle['id']);
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="vehicle-card">
                        <img src="<?php echo htmlspecialchars($similar_image); ?>"
                            alt="<?php echo htmlspecialchars($similar_vehicle['brand_name'] . ' ' . $similar_vehicle['name']); ?>"
                            class="vehicle-img">
                        <div class="vehicle-content">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="vehicle-name">
                                    <?php echo htmlspecialchars($similar_vehicle['brand_name'] . ' ' . $similar_vehicle['name']); ?>
                                </h3>
                                <span class="vehicle-year"><?php echo htmlspecialchars($similar_vehicle['year']); ?></span>
                            </div>
                            <span
                                class="vehicle-type mb-3"><?php echo htmlspecialchars($similar_vehicle['type_name']); ?></span>
                            <div class="specs mb-4">
                                <div class="spec-item">
                                    <span class="spec-name">Potencia</span>
                                    <span
                                        class="spec-value"><?php echo htmlspecialchars($similar_vehicle['power'] ?: 'N/D'); ?></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-name">0-100 km/h</span>
                                    <span
                                        class="spec-value"><?php echo htmlspecialchars($similar_vehicle['acceleration'] ?: 'N/D'); ?></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-name">Tracción</span>
                                    <span
                                        class="spec-value"><?php echo htmlspecialchars($similar_vehicle['traction'] ?: 'N/D'); ?></span>
                                </div>
                            </div>
                            <div class="plan-badge mb-3">Plan <?php echo htmlspecialchars($similar_vehicle['plan_name']); ?>
                            </div>
                            <a href="detalle-vehiculo.php?id=<?php echo $similar_vehicle['id']; ?>"
                                class="btn btn-primary w-100">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize the carousel
        var vehicleCarousel = document.getElementById('vehicleCarousel');
        if (vehicleCarousel) {
            new bootstrap.Carousel(vehicleCarousel);
        }

        // Reserved dates from PHP
        var reservedDates = <?php echo $reserved_dates_json; ?>;

        // Convert reserved dates to disabled ranges
        var disableRanges = reservedDates.map(function(reservation) {
            return {
                from: reservation.start_date,
                to: reservation.end_date
            };
        });

        // Configure Flatpickr for startDate
        var startPicker = flatpickr("#startDate", {
            minDate: new Date().fp_incr(1), // mañana
            disable: disableRanges,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    var minEndDate = new Date(selectedDates[0]);
                    minEndDate.setDate(minEndDate.getDate() + 1);
                    endPicker.set('minDate', minEndDate);
                }
            }
        });

        // Configure Flatpickr for endDate
        var endPicker = flatpickr("#endDate", {
            minDate: new Date().fp_incr(2), // pasado mañana
            disable: disableRanges,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    var maxStartDate = new Date(selectedDates[0]);
                    maxStartDate.setDate(maxStartDate.getDate() - 1);
                    startPicker.set('maxDate', maxStartDate);
                }
            }
        });
    });
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<?php include 'includes/footer.php'; ?>