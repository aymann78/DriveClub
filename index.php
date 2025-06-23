<?php
/**
 * DriveClub - Homepage
 */
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Page title
$page_title = 'Home - DriveClub';

// Get featured vehicles
$featured_vehicles = get_popular_vehicles(6);

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-banner">
  <div class="container h-100">
    <div class="row h-100 align-items-center">
      <div class="col-lg-6">
        <h1 class="display-4 fw-bold">Experimenta la libertad de conducir sin compromisos</h1>
        <p class="lead mb-4">Accede a vehículos exclusivos con nuestros planes de suscripción flexibles.</p>
        <div class="d-flex flex-wrap">
          <a href="vehiculos.php" class="btn btn-primary me-3 mb-3">Ver Vehículos</a>
          <a href="planes.php" class="btn btn-outline-light mb-3">Ver Planes</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About Section -->
<section class="cards-section py-5">
  <div class="container">
    <h1 class="section-title text-center mb-5">¿Por qué elegir DriveClub?</h1>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="feature-card"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="icon-container">
            <i class="bi bi-car-front"></i>
          </div>
          <h3>Vehículos Premium</h3>
          <p>Accede a una flota de vehículos exclusivos y de alta gama.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="icon-container">
            <i class="bi bi-wallet2"></i>
          </div>
          <h3>Sin Compromisos</h3>
          <p>Olvídate de las preocupaciones de mantenimiento y depreciación.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="icon-container">
            <i class="bi bi-arrow-repeat"></i>
          </div>
          <h3>Flexibilidad Total</h3>
          <p>Cambia de vehículo según tus necesidades o preferencias.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="icon-container">
            <i class="bi bi-qr-code"></i>
          </div>
          <h3>Acceso Digital</h3>
          <p>Reserva y accede a tu vehículo con nuestro sistema de códigos QR.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="plans-section py-5">
  <div class="container">
    <h2 class="section-title text-center mb-5">Nuestros Planes de Suscripción</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="plan-card"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="plan-header">
            <h3>Básico</h3>
            <p class="price">€299<span>/mes</span></p>
          </div>
          <div class="plan-body">
            <ul class="plan-features">
              <li><i class="bi bi-check-circle"></i> Acceso a vehículos compactos</li>
              <li><i class="bi bi-check-circle"></i> Cambio de vehículo mensual</li>
              <li><i class="bi bi-check-circle"></i> 1500 km mensuales</li>
              <li><i class="bi bi-check-circle"></i> Mantenimiento básico incluido</li>
              <li><i class="bi bi-check-circle"></i> Seguro a terceros</li>
            </ul>
            <a href="planes.php" class="btn btn-outline-primary w-100 mt-auto">Elegir Plan</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="plan-card featured"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="plan-badge">Más Popular</div>
          <div class="plan-header">
            <h3>Premium</h3>
            <p class="price">€599<span>/mes</span></p>
          </div>
          <div class="plan-body">
            <ul class="plan-features">
              <li><i class="bi bi-check-circle"></i> Acceso a vehículos premium</li>
              <li><i class="bi bi-check-circle"></i> Cambio de vehículo cada 2 semanas</li>
              <li><i class="bi bi-check-circle"></i> 2500 km mensuales</li>
              <li><i class="bi bi-check-circle"></i> Mantenimiento completo incluido</li>
              <li><i class="bi bi-check-circle"></i> Seguro a todo riesgo</li>
              <li><i class="bi bi-check-circle"></i> Asistencia en carretera 24/7</li>
            </ul>
            <a href="planes.php" class="btn btn-primary w-100 mt-auto">Elegir Plan</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="plan-card"
          style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
          <div class="plan-header">
            <h3>Elite</h3>
            <p class="price">€899<span>/mes</span></p>
          </div>
          <div class="plan-body">
            <ul class="plan-features">
              <li><i class="bi bi-check-circle"></i> Acceso a todos los vehículos</li>
              <li><i class="bi bi-check-circle"></i> Cambio de vehículo ilimitado</li>
              <li><i class="bi bi-check-circle"></i> Kilometraje ilimitado</li>
              <li><i class="bi bi-check-circle"></i> Mantenimiento premium</li>
              <li><i class="bi bi-check-circle"></i> Seguro a todo riesgo con ampliación</li>
              <li><i class="bi bi-check-circle"></i> Asistencia premium personalizada</li>
              <li><i class="bi bi-check-circle"></i> Entrega y recogida a domicilio</li>
            </ul>
            <a href="planes.php" class="btn btn-outline-primary w-100 mt-auto">Elegir Plan</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Featured Cars Section -->
<section class="todays-selection py-5">
  <div class="container">
    <h2 class="section-title text-center mb-5">Selección Destacada</h2>
    <div class="featured-vehicle-container">
      <?php foreach ($featured_vehicles as $index => $vehicle): ?>
        <div class="featured-vehicle<?php echo $index === 0 ? ' active' : ''; ?>" data-index="<?php echo $index; ?>">
        <div class="row align-items-center">
          <div class="col-lg-6">
              <img src="<?php echo get_vehicle_primary_image($vehicle['id']); ?>"
                   alt="<?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?>"
                   class="img-fluid featured-img">
          </div>
          <div class="col-lg-6">
            <div class="featured-content">
                <span class="vehicle-type"><?php echo htmlspecialchars($vehicle['type_name']); ?></span>
                <h3 class="vehicle-name"><?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?></h3>
                <p class="vehicle-year"><?php echo htmlspecialchars($vehicle['year'] ?? ''); ?></p>
              <div class="specs">
                <div class="spec-item">
                  <span class="spec-name"><i class="fas fa-tachometer-alt"></i> Potencia</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['power'] ?? '-'); ?> CV</span>
                </div>
                <div class="spec-item">
                  <span class="spec-name"><i class="fas fa-bolt"></i> 0-100 km/h</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['acceleration'] ?? '-'); ?>s</span>
                </div>
                <div class="spec-item">
                  <span class="spec-name"><i class="fas fa-road"></i> Tracción</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['traction'] ?? '-'); ?></span>
                  </div>
                </div>
              <div class="car-price">
                  <span>Disponible con</span>
                  <strong><?php echo htmlspecialchars($vehicle['plan_name']); ?></strong>
                  <span class="price">€<?php echo number_format($vehicle['price_monthly'], 0, ',', '.'); ?>/mes</span>
                </div>
                <a href="detalle-vehiculo.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary mt-4">Ver Detalles</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="featured-indicators">
      <?php foreach ($featured_vehicles as $index => $vehicle): ?>
        <button class="indicator<?php echo $index === 0 ? ' active' : ''; ?>" data-index="<?php echo $index; ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="vehiculos.php" class="btn btn-outline-primary">Ver Colección</a>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
        <div class="container">
          <div class="cta-container text-center">
            <h2 class="mb-4">¿Listo para disfrutar de la libertad al volante?</h2>
            <p class="lead mb-4">Únete a DriveClub hoy y descubre una nueva forma de disfrutar de la conducción sin ataduras.</p>
            <a href="/login.html?registro=true" class="btn btn-primary btn-lg">Comienza Ahora</a>
          </div>
        </div>
      </section>

<?php
// Include footer
include 'includes/footer.php';
?>