<?php
/**
 * DriveClub - User Account
 */
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];
$user = get_user_data();

// If there is no user, redirect
if (!$user) {
  logout_user();
  redirect('login.php');
}

// Get active subscription
$subscription = get_user_subscription($user_id);

// Process plan change
if (isset($_GET['cambiar_plan']) && is_numeric($_GET['cambiar_plan'])) {
  $new_plan_id = (int) $_GET['cambiar_plan'];

  // Check if the user already has this plan
  if ($subscription && $subscription['plan_id'] == $new_plan_id) {
    set_flash_message('info', 'Ya tienes este plan activo.');
  } else {
    // Change plan
    if (subscribe_user_to_plan($user_id, $new_plan_id)) {
      set_flash_message('success', 'Has cambiado tu plan correctamente.');
    } else {
      set_flash_message('danger', 'Error al cambiar de plan. Por favor, inténtalo de nuevo.');
    }
  }
  redirect('mi-cuenta.php');
}
// Get reservations
$active_reservations = get_user_reservations($user_id, 'active');
$past_reservations = get_user_reservations($user_id, 'past');

// Process credit card form
$card_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_card'])) {
  $card_holder = $_POST['card_holder'] ?? '';
  $card_number = $_POST['card_number'] ?? '';
  $expiry_month = $_POST['expiry_month'] ?? '';
  $expiry_year = $_POST['expiry_year'] ?? '';
  $cvv = $_POST['cvv'] ?? '';
  $make_default = isset($_POST['make_default']) ? 1 : 0;

  // Validar datos de la tarjeta
  if (empty($card_holder) || empty($card_number) || empty($expiry_month) || empty($expiry_year) || empty($cvv)) {
    $card_errors[] = 'Todos los campos de la tarjeta son obligatorios.';
  } else {
    if (!is_valid_name($card_holder)) {
      $card_errors[] = 'El titular de la tarjeta no es válido (solo letras, espacios, guiones, 2-40 caracteres).';
    }
    if (!is_valid_card_format($card_number, $expiry_month, $expiry_year, $cvv)) {
      $card_errors[] = 'El formato de la tarjeta es inválido (número, fecha o CVV incorrectos o caducados).';
    }
  }

  // Si no hay errores, guardar tarjeta
  if (empty($card_errors)) {
    $card_number_clean = str_replace(' ', '', $card_number);
    $first_digit = substr($card_number_clean, 0, 1);
    $card_type = '';
    if ($first_digit == '4') {
      $card_type = 'Visa';
    } elseif ($first_digit == '5') {
      $card_type = 'Mastercard';
    } elseif ($first_digit == '3') {
      $card_type = 'Amex';
    } else {
      $card_type = 'Otra';
    }
    $last_four = substr($card_number_clean, -4);
    $card_data = [
      'user_id' => $user_id,
      'card_holder' => $card_holder,
      'last_four' => $last_four,
      'card_type' => $card_type,
      'expiry_month' => $expiry_month,
      'expiry_year' => $expiry_year,
      'is_default' => $make_default
    ];
    if (add_credit_card($card_data)) {
      set_flash_message('success', 'Tarjeta añadida correctamente');
      redirect('mi-cuenta.php?tab=subscription');
    } else {
      $card_errors[] = 'Error al añadir la tarjeta. Por favor, inténtelo de nuevo.';
    }
  }
}

// Process setting default card
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default_card'])) {
  $card_id = $_POST['card_id'] ?? 0;

  if ($card_id) {
    if (set_default_credit_card($card_id, $user_id)) {
      set_flash_message('success', 'Tarjeta establecida como predeterminada');
    } else {
      set_flash_message('danger', 'Error al establecer la tarjeta como predeterminada');
    }
    redirect('mi-cuenta.php?tab=subscription');
  }
}

// Process card deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_card'])) {
  $card_id = $_POST['card_id'] ?? 0;

  if ($card_id) {
    // Check if there is an active subscription and auto-renewal
    $subscription = get_user_subscription($user_id);
    $credit_cards = get_user_credit_cards($user_id);
    if ($subscription && !empty($subscription['is_active']) && !empty($subscription['auto_renewal'])) {
      if (count($credit_cards) <= 1) {
        set_flash_message('danger', 'No puedes eliminar tu única tarjeta mientras tengas una suscripción activa con renovación automática.');
        redirect('mi-cuenta.php?tab=subscription');
      }
    }
    if (remove_credit_card($card_id, $user_id)) {
      set_flash_message('success', 'Tarjeta eliminada correctamente');
    } else {
      set_flash_message('danger', 'Error al eliminar la tarjeta');
    }
    redirect('mi-cuenta.php?tab=subscription');
  }
}

// Process profile update
$profile_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $first_name = $_POST['first_name'] ?? '';
  $last_name = $_POST['last_name'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $address = $_POST['address'] ?? '';
  $city = $_POST['city'] ?? '';
  $postal_code = $_POST['postal_code'] ?? '';
  $country = $_POST['country'] ?? '';

  // Validar todos los campos
  if (!is_valid_name($first_name)) {
    $profile_errors[] = 'El nombre no es válido (solo letras, espacios, guiones, 2-40 caracteres).';
  }
  if (!is_valid_name($last_name)) {
    $profile_errors[] = 'Los apellidos no son válidos (solo letras, espacios, guiones, 2-40 caracteres).';
  }
  if (!empty($phone) && !is_valid_phone($phone)) {
    $profile_errors[] = 'El teléfono no es válido (debe tener entre 9 y 15 dígitos, puede empezar por +).';
  }
  if (!empty($address)) {
    if (mb_strlen($address) < 2 || mb_strlen($address) > 80) {
      $profile_errors[] = 'La dirección debe tener entre 2 y 80 caracteres.';
    } elseif (!is_valid_address($address)) {
      $profile_errors[] = 'La dirección contiene un formato inválido. Usa solo letras, números y separadores comunes.';
    }
  }
  if (!empty($city) && !is_valid_city_or_country($city)) {
    $profile_errors[] = 'La ciudad no es válida (solo letras, espacios, guiones, 2-40 caracteres).';
  }
  if (!empty($postal_code) && !is_valid_postal_code($postal_code)) {
    $profile_errors[] = 'El código postal debe tener entre 4 y 10 dígitos.';
  }
  if (!empty($country) && !is_valid_city_or_country($country)) {
    $profile_errors[] = 'El país no es válido (solo letras, espacios, guiones, 2-40 caracteres).';
  }

  // Si no hay errores, actualizar perfil
  if (empty($profile_errors)) {
    $profile_data = [
      'first_name' => $first_name,
      'last_name' => $last_name,
      'phone' => $phone,
      'address' => $address,
      'city' => $city,
      'postal_code' => $postal_code,
      'country' => $country
    ];
    if (update_user_profile($user_id, $profile_data)) {
      set_flash_message('success', 'Cambios guardados correctamente');
      redirect('mi-cuenta.php');
    } else {
      $profile_errors[] = 'Error al guardar los cambios. Porfavor, intentelo de nuevo.';
    }
  }
}

// Process password change
$password_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Validate passwords
  if (empty($new_password) || empty($confirm_password)) {
    $password_errors[] = 'Todos los campos de contraseña son obligatorios';
  } elseif ($new_password !== $confirm_password) {
    $password_errors[] = 'Las contraseñas no coinciden';
  } elseif (strlen($new_password) < 8) {
    $password_errors[] = 'La contraseña debe tener al menos 8 caracteres';
  }

  // If there are no errors, change password
  if (empty($password_errors)) {
    if (update_user_password($user_id, $current_password, $new_password)) {
      set_flash_message('success', 'Contraseña cambiada correctamente');
      redirect('mi-cuenta.php');
    } else {
      $password_errors[] = 'La contraseña actual es incorrecta. Por favor, inténtalo de nuevo.';
    }
  }
}

// Process reservation cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
  $reservation_id = $_POST['reservation_id'] ?? 0;

  if ($reservation_id) {
    // Check that the reservation belongs to the user
    $reservation_sql = "SELECT * FROM reservations WHERE id = ? AND user_id = ?";
    $reservation = db_fetch_assoc($reservation_sql, [$reservation_id, $user_id]);

    if ($reservation) {
      // Get ID of 'Cancelled' status
      $status_sql = "SELECT id FROM reservation_statuses WHERE name = 'Cancelled' LIMIT 1";
      $status = db_fetch_assoc($status_sql);

      if ($status) {
        // Update reservation status
        if (db_update('reservations', ['status_id' => $status['id']], 'id = ?', [$reservation_id])) {
          // Send cancellation email
          if (sendReservationCancellation($reservation_id)) {
            set_flash_message('success', 'Reserva cancelada con éxito. Revisa tu correo.');
          } else {
            set_flash_message('success', 'Reserva cancelada con éxito. (No se pudo enviar el correo de cancelación)');
          }
        } else {
          set_flash_message('danger', 'Error al cancelar la reserva. Por favor, intenta de nuevo.');
        }
      } else {
        set_flash_message('danger', 'Estado de cancelación no encontrado. Contacta con soporte.');
      }
    } else {
      set_flash_message('danger', 'Reserva no encontrada o no te pertenece.');
    }

    redirect('mi-cuenta.php');
  }
}

// Process subscription cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_subscription'])) {
  if (cancel_user_subscription($user_id)) {
    set_flash_message('success', 'Suscripción cancelada correctamente.');
  } else {
    set_flash_message('danger', 'Error al cancelar la suscripción.');
  }
  redirect('mi-cuenta.php?tab=subscription');
}

// Process avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $file = $_FILES['avatar'];
    if (!in_array($file['type'], $allowed_types)) {
      $profile_errors[] = 'El archivo debe ser una imagen JPG, PNG, GIF o WEBP.';
    } elseif ($file['size'] > $max_size) {
      $profile_errors[] = 'La imagen no puede superar los 2MB.';
    } else {
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
      $avatar_name = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
      $avatar_path = 'uploads/avatars/' . $avatar_name;
      if (!is_dir('uploads/avatars')) { mkdir('uploads/avatars', 0777, true); }
      if (move_uploaded_file($file['tmp_name'], $avatar_path)) {
        // Update in DB
        db_update('users', ['avatar' => $avatar_path], 'id = ?', [$user_id]);
        $user['avatar'] = $avatar_path;
        set_flash_message('success', 'Foto de perfil actualizada correctamente.');
        redirect('mi-cuenta.php');
      } else {
        $profile_errors[] = 'Error al guardar la imagen. Inténtalo de nuevo.';
      }
    }
  } else {
    $profile_errors[] = 'Selecciona una imagen válida.';
  }
}

// Page title
$page_title = 'Mi Cuenta | DriveClub';

// Include header
include 'includes/header.php';

// Mostrar errores de perfil como toast
if (!empty($profile_errors)) {
  $msg = implode('<br>', array_map('htmlspecialchars', $profile_errors));
  set_flash_message('danger', $msg);
  // Redirigir para mostrar el toast
  redirect('mi-cuenta.php?tab=profile');
}

// Mostrar errores de tarjeta como toast
if (!empty($card_errors)) {
  $msg = implode('<br>', array_map('htmlspecialchars', $card_errors));
  set_flash_message('danger', $msg);
  // Redirigir para mostrar el toast
  redirect('mi-cuenta.php?tab=subscription');
}

// Mostrar errores de contraseña como toast
if (!empty($password_errors)) {
  $msg = implode('<br>', array_map('htmlspecialchars', $password_errors));
  set_flash_message('danger', $msg);
  // Redirigir para mostrar el toast
  redirect('mi-cuenta.php?tab=profile');
}
?>

<!-- Account Banner -->
<section class="account-banner">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <h1>Mi Cuenta</h1>
        <p class="lead">Gestiona tus reservas, suscripción y datos personales.</p>
      </div>
    </div>
  </div>
</section>

<!-- Account Content -->
<section class="account-section py-5">
  <div class="container">
    <!-- Flash Messages -->
    <?php if (!empty($profile_errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($profile_errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-3 mb-4 mb-lg-0">
        <div class="account-sidebar">
          <div class="user-info mb-4">
            <div class="user-avatar">
              <?php if (!empty($user['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="User Profile" class="img-fluid rounded-circle">
              <?php else: ?>
                <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="img-fluid rounded-circle bg-light border">
                  <circle cx="40" cy="40" r="38" stroke="#adb5bd" stroke-width="4" fill="#f8f9fa" />
                  <circle cx="40" cy="32" r="14" fill="#dee2e6" />
                  <ellipse cx="40" cy="58" rx="22" ry="14" fill="#dee2e6" />
                  <circle cx="40" cy="32" r="12" fill="#ced4da" />
                  <ellipse cx="40" cy="58" rx="18" ry="10" fill="#ced4da" />
                </svg>
              <?php endif; ?>
            </div>
            <div class="user-details">
              <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
              <?php if ($subscription): ?>
                <p class="plan-badge"><?php echo htmlspecialchars($subscription['plan_name']); ?></p>
              <?php else: ?>
                <p class="plan-badge">Sin suscripción</p>
              <?php endif; ?>
            </div>
          </div>
          <ul class="nav flex-column account-nav" id="accountTabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="reservations-tab" data-bs-toggle="tab" href="#reservations" role="tab"
                aria-controls="reservations" aria-selected="true">
                <i class="bi bi-calendar-check me-2"></i>Mis Reservas
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="subscription-tab" data-bs-toggle="tab" href="#subscription" role="tab"
                aria-controls="subscription" aria-selected="false">
                <i class="bi bi-card-list me-2"></i>Mi Suscripción
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab"
                aria-controls="profile" aria-selected="false">
                <i class="bi bi-person me-2"></i>Mi Perfil
              </a>
            </li>
            <li class="nav-item mt-5">
              <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Content Area -->
      <div class="col-lg-9">
        <div class="tab-content account-content" id="accountTabsContent">
          <!-- Reservations Tab -->
          <div class="tab-pane fade show active" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
            <div class="content-card">
              <h2 class="section-title mb-4">Mis Reservas</h2>

              <ul class="nav nav-tabs mb-4 border-bottom border-2" id="reservationTabs" role="tablist">
                <li class="nav-item w-50 text-center" role="presentation">
                  <button class="nav-link active w-100" id="current-tab" data-bs-toggle="tab"
                    data-bs-target="#current-reservations" type="button" role="tab" aria-controls="current-reservations"
                    aria-selected="true">Actuales</button>
                </li>
                <li class="nav-item w-50 text-center" role="presentation">
                  <button class="nav-link w-100" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-reservations"
                    type="button" role="tab" aria-controls="past-reservations" aria-selected="false">Historial</button>
                </li>
              </ul>

              <div class="tab-content" id="reservationTabsContent">
                <div class="tab-pane fade show active" id="current-reservations" role="tabpanel"
                  aria-labelledby="current-tab">
                  <?php if (empty($active_reservations)): ?>
                    <div class="alert alert-info">No tienes reservas activas actualmente.</div>
                  <?php else: ?>
                    <?php foreach ($active_reservations as $reservation): ?>
                      <div class="reservation-card">
                        <div class="row align-items-center">
                          <div class="col-md-3">
                            <img src="<?php echo get_vehicle_primary_image($reservation['vehicle_id']); ?>"
                              alt="<?php echo htmlspecialchars($reservation['vehicle_name']); ?>" class="img-fluid rounded">
                          </div>
                          <div class="col-md-9">
                            <div class="reservation-details">
                              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                                <div>
                                  <h4 class="mb-2">
                                    <?php echo htmlspecialchars($reservation['brand_name'] . ' ' . $reservation['vehicle_name']); ?>
                                  </h4>
                                  <span
                                    class="vehicle-type"><?php echo htmlspecialchars($reservation['vehicle_type']); ?></span>
                                </div>
                                <div class="qr-code mt-2 mt-md-0"
                                  data-qr="<?php echo 'data:image/png;base64,' . $reservation['qr_code']; ?>"
                                  data-reservation-id="<?php echo $reservation['id']; ?>"
                                  data-vehicle="<?php echo htmlspecialchars($reservation['brand_name'] . ' ' . $reservation['vehicle_name']); ?>"
                                  data-dates="<?php echo format_date($reservation['start_date']) . ' - ' . format_date($reservation['end_date']); ?>">
                                  <i class="bi bi-qr-code"></i>
                                  <span>Ver QR</span>
                                </div>

                              </div>
                              <div class="reservation-dates mb-3">
                                <span class="date-badge"><i class="bi bi-calendar-event me-1"></i>
                                  <?php echo format_date($reservation['start_date']); ?> -
                                  <?php echo format_date($reservation['end_date']); ?></span>
                              </div>
                              <div class="reservation-status mb-3">
                                <span class="status-badge <?php echo strtolower($reservation['status_name']); ?>">
                                  <?php
                                  if ($reservation['status_name'] == 'Active')
                                    echo 'Activa';
                                  elseif ($reservation['status_name'] == 'Pending')
                                    echo 'Pendiente';
                                  elseif ($reservation['status_name'] == 'Completed')
                                    echo 'Completada';
                                  else
                                    echo 'Cancelada';
                                  ?>
                                </span>
                              </div>
                              <div class="reservation-actions">
                                <form method="post" action="" class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de que quieres cancelar esta reserva?')">
                                  <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                  <button type="submit" name="cancel_reservation"
                                    class="btn btn-sm btn-outline-danger">Cancelar Reserva</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>

                  <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="past-reservations" role="tabpanel" aria-labelledby="past-tab">
                  <?php if (empty($past_reservations)): ?>
                    <div class="alert alert-info">No tienes reservas pasadas.</div>
                  <?php else: ?>
                    <?php foreach ($past_reservations as $reservation): ?>
                      <div class="reservation-card">
                        <div class="row align-items-center">
                          <div class="col-md-3">
                            <img src="<?php echo get_vehicle_primary_image($reservation['vehicle_id']); ?>"
                              alt="<?php echo htmlspecialchars($reservation['vehicle_name']); ?>" class="img-fluid rounded">
                          </div>
                          <div class="col-md-9">
                            <div class="reservation-details">
                              <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                  <h4 class="mb-2">
                                    <?php echo htmlspecialchars($reservation['brand_name'] . ' ' . $reservation['vehicle_name']); ?>
                                  </h4>
                                  <span
                                    class="vehicle-type"><?php echo htmlspecialchars($reservation['vehicle_type']); ?></span>
                                </div>
                              </div>
                              <div class="reservation-dates mb-3">
                                <span class="date-badge"><i class="bi bi-calendar-event me-1"></i>
                                  <?php echo format_date($reservation['start_date']); ?> -
                                  <?php echo format_date($reservation['end_date']); ?></span>
                              </div>
                              <div class="reservation-status mb-3">
                                <span class="status-badge <?php echo strtolower($reservation['status_name']); ?>">
                                  <?php
                                  if ($reservation['status_name'] == 'Completed')
                                    echo 'Completada';
                                  else
                                    echo 'Cancelada';
                                  ?>
                                </span>
                              </div>
                              <div class="reservation-actions">
                                <a href="vehiculos.php" class="btn btn-sm btn-outline-primary">Reservar de Nuevo</a>
                                <?php if ($reservation['status_name'] == 'Completed' && can_user_review_reservation($user_id, $reservation['id'])): ?>
                                <button class="btn btn-sm btn-outline-secondary">Dejar Opinión</button>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Subscription Tab -->
          <div class="tab-pane fade" id="subscription" role="tabpanel" aria-labelledby="subscription-tab">
            <div class="content-card">
              <h2 class="section-title mb-4">Mi Suscripción</h2>

              <?php if ($subscription): ?>
                <div class="current-plan mb-5">
                  <div class="row align-items-center">
                    <div class="col-md-4">
                      <div class="plan-icon-container">
                        <i class="bi bi-award"></i>
                        <h3><?php echo htmlspecialchars($subscription['plan_name']); ?></h3>
                      </div>
                    </div>
                    <div class="col-md-8">
                      <div class="plan-details">
                        <div class="mb-3">
                          <span class="text-muted">Estado:</span>
                          <span class="status-badge active ms-2">Activo</span>
                        </div>
                        <div class="mb-3">
                          <span class="text-muted">Precio:</span>
                          <span
                            class="fw-bold ms-2"><?php echo format_price($subscription['price_monthly'] ?? $plan['price_monthly']); ?>/mes</span>
                        </div>
                        <div class="mb-3">
                          <span class="text-muted">Fecha renovación:</span>
                          <span class="fw-bold ms-2"><?php echo format_date($subscription['next_payment_date']); ?></span>
                        </div>
                        <div class="mb-3">
                          <span class="text-muted">Renovación automática:</span>
                          <span
                            class="fw-bold ms-2"><?php echo ($subscription['auto_renewal']) ? 'Activada' : 'Desactivada'; ?></span>
                        </div>
                        <div class="mt-4">
                          <a href="planes.php" class="btn btn-outline-primary">Cambiar Plan</a>
                          <form method="post" action="" class="d-inline" onsubmit="return confirm('¿Seguro que quieres cancelar tu suscripción?')">
                            <button type="submit" name="cancel_subscription" class="btn btn-outline-danger ms-2">Cancelar Suscripción</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <div class="alert alert-info">No tienes ninguna suscripción activa actualmente.</div>
                <p>Para disfrutar de todos nuestros vehículos, contrata uno de nuestros planes de suscripción.</p>
                <a href="planes.php" class="btn btn-primary mt-3">Ver Planes Disponibles</a>
              <?php endif; ?>

              <!-- Payment Methods Section -->
              <div class="payment-methods mt-5">
                <h3>Métodos de Pago</h3>

                <?php
                // Obtener tarjetas de crédito del usuario
                $credit_cards = get_user_credit_cards($user_id);
                ?>

                <div class="saved-cards mt-4">
                  <?php if (!empty($credit_cards)): ?>
                    <!-- Cards list -->
                    <div class="row">
                      <?php foreach ($credit_cards as $card): ?>
                        <div class="col-md-6 mb-3">
                          <div class="card-item">
                            <div class="card-icon">
                              <?php
                              $card_type = strtolower($card['card_type']);
                              if ($card_type == 'visa') {
                                echo '<i class="bi bi-credit-card-2-front-fill text-primary"></i>';
                              } elseif ($card_type == 'mastercard') {
                                echo '<i class="bi bi-credit-card-2-front-fill text-danger"></i>';
                              } elseif ($card_type == 'amex') {
                                echo '<i class="bi bi-credit-card-2-front-fill text-success"></i>';
                              } else {
                                echo '<i class="bi bi-credit-card-2-front-fill"></i>';
                              }
                              ?>
                            </div>
                            <div class="card-details">
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="card-name"><?php echo htmlspecialchars($card['card_type']); ?></span>
                                <?php if ($card['is_default']): ?>
                                  <span class="badge bg-primary">Predeterminada</span>
                                <?php endif; ?>
                              </div>
                              <p class="card-number mb-1">**** **** **** <?php echo htmlspecialchars($card['last_four']); ?>
                              </p>
                              <p class="card-expiry mb-1">Vence:
                                <?php echo htmlspecialchars($card['expiry_month'] . '/' . $card['expiry_year']); ?>
                              </p>
                              <div class="mt-2">
                                <?php if (!$card['is_default']): ?>
                                  <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                    <button type="submit" name="set_default_card"
                                      class="btn btn-sm btn-outline-primary">Establecer como predeterminada</button>
                                  </form>
                                <?php endif; ?>
                                <form method="post" action="" class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta tarjeta?')">
                                  <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                  <button type="submit" name="delete_card"
                                    class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-info">No tienes tarjetas de crédito guardadas.</div>
                  <?php endif; ?>

                  <!-- Button to open the modal to add a new card -->
                  <div class="mt-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCardModal">
                      <i class="bi bi-plus-circle me-2"></i>Añadir Nueva Tarjeta
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Tab -->
          <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <div class="content-card">
              <h2 class="section-title mb-4">Mi Perfil</h2>

              <div class="mb-4 text-center">
                <form action="" method="post" enctype="multipart/form-data" class="d-inline-block">
                  <div class="mb-2">
                    <?php if (!empty($user['avatar'])): ?>
                      <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="img-fluid rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
                    <?php else: ?>
                      <svg width="96" height="96" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="img-fluid rounded-circle bg-light border">
                        <circle cx="40" cy="40" r="38" stroke="#adb5bd" stroke-width="4" fill="#f8f9fa" />
                        <circle cx="40" cy="32" r="14" fill="#dee2e6" />
                        <ellipse cx="40" cy="58" rx="22" ry="14" fill="#dee2e6" />
                        <circle cx="40" cy="32" r="12" fill="#ced4da" />
                        <ellipse cx="40" cy="58" rx="18" ry="10" fill="#ced4da" />
                      </svg>
                    <?php endif; ?>
                  </div>
                  <input type="file" name="avatar" accept="image/*" class="form-control mb-2" required>
                  <button type="submit" name="upload_avatar" class="btn btn-outline-primary btn-sm">Cambiar Foto de Perfil</button>
                </form>
              </div>

              <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                  <div class="form-container">
                    <h3>Datos Personales</h3>

                    <form action="" method="post">
                      <div class="mb-3">
                        <label for="first_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                          value="<?php echo htmlspecialchars($user['first_name']); ?>" required pattern="(?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:[\s'-][A-Za-zÁÉÍÓÚáéíóúÑñ]+)*){2,40}" maxlength="40">
                      </div>
                      <div class="mb-3">
                        <label for="last_name" class="form-label">Apellidos</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                          value="<?php echo htmlspecialchars($user['last_name']); ?>" required pattern="(?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:[\s'-][A-Za-zÁÉÍÓÚáéíóúÑñ]+)*){2,40}" maxlength="40">
                      </div>
                      <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email"
                          value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                      </div>
                      <div class="mb-3">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                          value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" pattern="^\+?\d{1,4}([\s-]?\d{2,4}){2,5}$" maxlength="20">
                      </div>
                      <div class="mb-3">
                        <label for="address" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="address" name="address"
                          value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" pattern="^(?=.{2,80}$)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9ºª]+(([-,./ºª] ?| )[A-Za-zÁÉÍÓÚáéíóúÑñ0-9ºª]+)*$" maxlength="80">
                      </div>
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="city" class="form-label">Ciudad</label>
                          <input type="text" class="form-control" id="city" name="city"
                            value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" pattern="(?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?: [A-Za-zÁÉÍÓÚáéíóúÑñ]+)*){2,40}" maxlength="40">
                        </div>
                        <div class="col-md-6 mb-3">
                          <label for="postal_code" class="form-label">Código Postal</label>
                          <input type="text" class="form-control" id="postal_code" name="postal_code"
                            value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>" pattern="^\d{4,10}$" maxlength="10">
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="country" class="form-label">País</label>
                        <input type="text" class="form-control" id="country" name="country"
                          value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>" pattern="(?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?: [A-Za-zÁÉÍÓÚáéíóúÑñ]+)*){2,40}" maxlength="40">
                      </div>
                      <div class="d-grid">
                        <button type="submit" name="update_profile" class="btn btn-primary">Guardar Cambios</button>
                      </div>
                    </form>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-container">
                    <h3>Cambiar Contraseña</h3>

                    <form action="" method="post">
                      <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Si no tienes contraseña, deja en blanco">
                      </div>
                      <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                      </div>
                      <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                      </div>
                      <div class="d-grid">
                        <button type="submit" name="change_password" class="btn btn-primary">Cambiar Contraseña</button>
                      </div>
                    </form>

                    <div class="password-requirements mt-4">
                      <h4>Requisitos de Contraseña</h4>
                      <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle me-2 text-success"></i> Mínimo 8 caracteres</li>
                        <li><i class="bi bi-check-circle me-2 text-success"></i> Al menos una letra mayúscula</li>
                        <li><i class="bi bi-check-circle me-2 text-success"></i> Al menos un número</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal to add a new card -->
<div class="modal fade" id="addCardModal" tabindex="-1" aria-labelledby="addCardModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCardModalLabel">Añadir Nueva Tarjeta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($card_errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($card_errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form id="addCardForm" action="" method="post" autocomplete="off">
          <div class="mb-3">
            <label for="card_holder" class="form-label">Titular de la tarjeta</label>
            <input type="text" class="form-control" id="card_holder" name="card_holder" autocomplete="off" required pattern="(?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:[\s,'-][A-Za-zÁÉÍÓÚáéíóúÑñ]+)*){2,40}" maxlength="40">
          </div>
          <div class="mb-3">
            <label for="card_number" class="form-label">Número de tarjeta</label>
            <input type="text" class="form-control" id="card_number" name="card_number" autocomplete="off" maxlength="19" required pattern="^[0-9 ]{13,19}$">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="expiry_date" class="form-label">Fecha de expiración (MM/AA)</label>
              <input type="text" class="form-control" id="expiry_date" name="expiry_date" autocomplete="off" placeholder="MM/AA" maxlength="5" required pattern="^(0[1-9]|1[0-2])\/[0-9]{2}$">
              <input type="hidden" id="expiry_month" name="expiry_month">
              <input type="hidden" id="expiry_year" name="expiry_year">
            </div>
            <div class="col-md-6 mb-3">
              <label for="cvv" class="form-label">CVV</label>
              <input type="text" class="form-control" id="cvv" name="cvv" maxlength="4" autocomplete="off" required pattern="^[0-9]{3,4}$">
            </div>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="make_default" name="make_default" value="1">
            <label class="form-check-label" for="make_default">Establecer como tarjeta predeterminada</label>
          </div>
          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" name="add_card" class="btn btn-primary">Añadir Tarjeta</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal QR -->
<div id="qrModal" class="qr-modal">
  <div class="qr-modal-content">
    <span id="closeQrModal" class="qr-modal-close">&times;</span>
    <img id="qrModalImg" src="" alt="QR Code" class="img-fluid">
    <div class="qr-details">
      <h4>Detalles de la Reserva</h4>
      <p>Escanea este código QR con tu móvil para ver los detalles de tu reserva.</p>
      <p><strong>Reserva #:</strong> <span id="reservationId"></span></p>
      <p><strong>Vehículo:</strong> <span id="vehicleName"></span></p>
      <p><strong>Fechas:</strong> <span id="reservationDates"></span></p>
    </div>
  </div>
</div>

<!-- JavaScript to open the modal with animation and show details -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const qrButtons = document.querySelectorAll('.qr-code');
    const modal = document.getElementById('qrModal');
    const modalContent = document.querySelector('.qr-modal-content');
    const modalImg = document.getElementById('qrModalImg');
    const closeBtn = document.getElementById('closeQrModal');

    // Elements for additional data
    const reservationIdEl = document.getElementById('reservationId');
    const vehicleNameEl = document.getElementById('vehicleName');
    const reservationDatesEl = document.getElementById('reservationDates');

    // Function to open the modal
    function openModal(qrImage, reservationId, vehicleName, dates) {
      modalImg.setAttribute('src', qrImage);
      reservationIdEl.textContent = reservationId;
      vehicleNameEl.textContent = vehicleName;
      reservationDatesEl.textContent = dates;

      modal.style.display = 'flex';
      // Force reflow to start the animation
      void modalContent.offsetWidth;
      modal.classList.add('show');
    }

    // Function to close the modal
    function closeModal() {
      modal.classList.remove('show');
      setTimeout(() => {
        modal.style.display = 'none';
        modalImg.setAttribute('src', '');
      }, 400); // Must match the duration of the CSS transition
    }

    // Assign the click event to each QR button
    qrButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const qrImage = this.getAttribute('data-qr');
        const reservationId = this.getAttribute('data-reservation-id');
        const vehicleName = this.getAttribute('data-vehicle');
        const dates = this.getAttribute('data-dates');
        openModal(qrImage, reservationId, vehicleName, dates);
      });
    });

    closeBtn.addEventListener('click', closeModal);

    // Close the modal when clicking outside the content
    window.addEventListener('click', function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });
  });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>