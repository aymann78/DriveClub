<?php
/**
 * DriveClub - Vehicles Listing
 */
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Page title
$page_title = 'Vehículos | DriveClub - Alquiler de Coches por Suscripción';

// Obtener filtros
$brand_id = $_GET['brand'] ?? null;
$type_id = $_GET['type'] ?? null;
$search = $_GET['search'] ?? null;
$plan_id = null;

// Si el usuario está logueado, obtener su plan de suscripción
if (is_logged_in()) {
    $user_subscription = get_user_subscription($_SESSION['user_id']);
    if ($user_subscription) {
        $plan_id = $user_subscription['plan_id'];
    }
}

// Preparar filtros
$filters = [];
if ($brand_id) $filters['brand_id'] = $brand_id;
if ($type_id) $filters['type_id'] = $type_id;
if ($search) $filters['search'] = $search;

// Obtener vehículos
$vehicles = get_all_vehicles($filters);

// Obtener marcas y tipos para filtros
$brands = get_all_vehicle_brands();
$types = get_all_vehicle_types();

// Include header
include 'includes/header.php';

// Asegurar que los vehículos tengan todos los datos necesarios
// Especialmente la imagen primaria, año y tracción
if ($vehicles) {
    foreach ($vehicles as &$vehicle) {
        // Obtener imagen primaria
        $vehicle['primary_image'] = get_vehicle_primary_image($vehicle['id']);
        
        // Asegurar que el año existe o asignar el año actual
        if (!isset($vehicle['year']) || empty($vehicle['year'])) {
            $vehicle['year'] = date('Y');
        }
        
        // Asegurar que la tracción existe o asignar un valor por defecto
        if (!isset($vehicle['traction']) || empty($vehicle['traction'])) {
            $vehicle['traction'] = isset($vehicle['type_name']) && 
                                   (strpos(strtolower($vehicle['type_name']), 'suv') !== false || 
                                    strpos(strtolower($vehicle['type_name']), '4x4') !== false) ? 'AWD' : 'RWD';
        }
        
        // Asegurar que el precio mensual existe
        if (!isset($vehicle['price_monthly']) || $vehicle['price_monthly'] === null) {
            // Intentar obtener el precio del plan asociado
            if (isset($vehicle['minimum_plan_id'])) {
                $sql = "SELECT price_monthly FROM subscription_plans WHERE id = ?";
                $plan_data = db_fetch_assoc($sql, [$vehicle['minimum_plan_id']]);
                if ($plan_data && isset($plan_data['price_monthly'])) {
                    $vehicle['price_monthly'] = $plan_data['price_monthly'];
                }
            }
        }
    }
    unset($vehicle); // Liberar la referencia para evitar duplicaciones
}
?>

<!-- Vehicles Banner -->
<section class="vehicles-banner">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <h1>Nuestra Flota de Vehículos</h1>
        <p class="lead">Descubre y elige entre nuestra selección de vehículos premium para tu próxima experiencia de conducción.</p>
      </div>
    </div>
  </div>
</section>

<!-- Flash Messages -->
<div class="container mt-3">
<?php if (isset($_SESSION['toast'])): ?>
    <div class="alert alert-<?php echo $_SESSION['toast']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['toast']['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>
</div>

<!-- Filter Section -->
<section class="filter-section py-4">
  <div class="container">
    <div class="filter-container">
      <form action="vehiculos.php" method="get">
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">
            <div class="filter-group">
              <label for="type" class="form-label">Categoría</label>
              <select class="form-select" id="type" name="type">
                <option value="">Todas las categorías</option>
                <?php foreach ($types as $type): ?>
                  <option value="<?php echo $type['id']; ?>" <?php echo ($type_id == $type['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($type['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="filter-group">
              <label for="brand" class="form-label">Marca</label>
              <select class="form-select" id="brand" name="brand">
                <option value="">Todas las marcas</option>
                <?php foreach ($brands as $brand): ?>
                  <option value="<?php echo $brand['id']; ?>" <?php echo ($brand_id == $brand['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($brand['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="filter-group">
              <label for="search" class="form-label">Buscar</label>
              <input type="text" class="form-control" id="search" name="search" placeholder="Nombre o modelo..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
          </div>
          <div class="col-lg-3 col-md-6 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- Filter Error Message -->
<?php if (empty($vehicles)): ?>
<div class="container mb-4" id="filterErrorContainer">
  <div class="alert alert-warning" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <span id="filterErrorMessage">No se encontraron vehículos que coincidan con los criterios seleccionados.</span>
  </div>
</div>
<?php endif; ?>

<!-- Vehicles Grid -->
<section class="vehicles-grid py-5">
  <div class="container">
    <div class="row g-4" id="vehiclesContainer">
      <?php foreach ($vehicles as $vehicle): ?>
        <div class="col-xxl-3 col-xl-4 col-md-6 mb-4">
          <div class="vehicle-card">
            <img src="<?php echo htmlspecialchars($vehicle['primary_image']); ?>" alt="<?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?>" class="vehicle-img">
            <div class="vehicle-content">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h3 class="vehicle-name"><?php echo htmlspecialchars($vehicle['brand_name'] . ' ' . $vehicle['name']); ?></h3>
                <span class="vehicle-year"><?php echo htmlspecialchars($vehicle['year']); ?></span>
              </div>
              <!-- Badge de categoría justo encima de specs -->
              <div class="specs mb-4">
                <span class="vehicle-type mb-3"><?php echo htmlspecialchars($vehicle['type_name']); ?></span>
                <div class="specs-row">
                <div class="spec-item">
                  <span class="spec-name">Potencia</span>
                  <span class="spec-value"><?php echo htmlspecialchars($vehicle['power']); ?></span>
                </div>
                <div class="spec-item">
                  <span class="spec-name">0-100</span>
                  <span class="spec-value"><?php echo htmlspecialchars($vehicle['acceleration']); ?></span>
                </div>
                <div class="spec-item">
                  <span class="spec-name">Tracción</span>
                  <span class="spec-value"><?php echo htmlspecialchars($vehicle['traction'] ?? 'RWD'); ?></span>
                  </div>
                </div>
              </div>
              <div class="plan-badge mb-3">Plan <?php echo ucfirst(htmlspecialchars($vehicle['plan_name'])); ?></div>
              <a href="detalle-vehiculo.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary w-100">Ver Detalles</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Subscription CTA -->
<section class="cta-section py-5">
  <div class="container">
    <div class="cta-container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h2 class="cta-title">¿No encuentras el vehículo ideal?</h2>
          <p class="cta-text">Mejora tu plan de suscripción para acceder a más vehículos exclusivos.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="planes.php" class="btn btn-light btn-lg">Ver Planes</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
