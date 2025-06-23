<?php
/**
 * Plans Page
 */
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Set page title and current page
$page_title = 'Planes de Suscripción';
$current_page = 'planes';
$page_description = 'Descubre nuestros planes de suscripción para alquiler de vehículos';

// Verificar si hay planes y crearlos si es necesario
$check_plans = "SELECT COUNT(*) as count FROM subscription_plans";
$plans_count = db_fetch_assoc($check_plans);

if (!$plans_count || $plans_count['count'] == 0) {
    // No hay planes, crear planes predeterminados
    $basic_plan = [
        'name' => 'Básico',
        'description' => 'Acceso a vehículos económicos y compactos',
        'price_monthly' => 49.99,
        'special_features' => json_encode([
            ['name' => 'Acceso a vehículos compactos', 'included' => true],
            ['name' => 'Cambio de vehículo mensual', 'included' => true],
            ['name' => '1500 km mensuales', 'included' => true],
            ['name' => 'Mantenimiento básico incluido', 'included' => true],
            ['name' => 'Seguro a terceros', 'included' => true],
            ['name' => 'Asistencia en carretera', 'included' => false],
            ['name' => 'Entrega y recogida', 'included' => false],
            ['name' => 'Acceso a eventos exclusivos', 'included' => false]
        ]),
        'is_active' => 1
    ];
    
    $premium_plan = [
        'name' => 'Premium',
        'description' => 'Acceso a vehículos de gama media y SUVs',
        'price_monthly' => 99.99,
        'special_features' => json_encode([
            ['name' => 'Acceso a vehículos de gama media', 'included' => true],
            ['name' => 'Cambio de vehículo cada 2 semanas', 'included' => true],
            ['name' => '2500 km mensuales', 'included' => true],
            ['name' => 'Mantenimiento completo incluido', 'included' => true],
            ['name' => 'Seguro a todo riesgo', 'included' => true],
            ['name' => 'Asistencia en carretera', 'included' => true],
            ['name' => 'Entrega y recogida', 'included' => false],
            ['name' => 'Acceso a eventos exclusivos', 'included' => false]
        ]),
        'is_active' => 1
    ];
    
    $elite_plan = [
        'name' => 'Elite',
        'description' => 'Acceso a todos los vehículos, incluidos deportivos y de lujo',
        'price_monthly' => 199.99,
        'special_features' => json_encode([
            ['name' => 'Acceso a todos los vehículos', 'included' => true],
            ['name' => 'Cambio de vehículo ilimitado', 'included' => true],
            ['name' => 'Kilometraje ilimitado', 'included' => true],
            ['name' => 'Mantenimiento premium incluido', 'included' => true],
            ['name' => 'Seguro a todo riesgo con ampliación', 'included' => true],
            ['name' => 'Asistencia en carretera VIP', 'included' => true],
            ['name' => 'Entrega y recogida', 'included' => true],
            ['name' => 'Acceso a eventos exclusivos', 'included' => true]
        ]),
        'is_active' => 1
    ];
    
    // Insertar planes
    db_insert('subscription_plans', $basic_plan);
    db_insert('subscription_plans', $premium_plan);
    db_insert('subscription_plans', $elite_plan);
    
    // Mostrar mensaje de éxito (opcional)
    set_flash_message('success', 'Planes de suscripción creados correctamente');
}

// Obtener los planes (ya se insertaron si era necesario)
$plans = get_all_plans();

include 'includes/header.php';
?>

<!-- Plans Banner -->
<section class="plans-banner">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h1>Planes de Suscripción</h1>
                <p class="lead">Elige el plan que mejor se adapte a tus necesidades y estilo de vida.</p>
            </div>
        </div>
    </div>
</section>

<!-- Plans Section -->
<section class="plans-section py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Nuestros Planes de Suscripción</h2>
        <div class="row g-4 justify-content-center">
            <?php if ($plans): ?>
                <?php foreach ($plans as $index => $plan): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="plan-card <?php echo $index == 1 ? 'featured' : ''; ?>" style="opacity: 1; transform: translateY(0px); transition: opacity 0.5s, transform 0.5s;">
                            <?php if ($index == 1): ?>
                                <div class="plan-badge">Más Popular</div>
                            <?php endif; ?>
                            <div class="plan-header">
                                <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                                <p class="price"><?php echo format_price($plan['price_monthly']); ?><span>/mes</span></p>
                            </div>
                            <div class="plan-body">
                                <ul class="plan-features">
                                    <?php 
                                    // Mostrar las características del plan
                                    if (!empty($plan['features'])): 
                                        foreach ($plan['features'] as $feature): 
                                            // Verificar si $feature es un array y si contiene la clave 'included'
                                            if (is_array($feature) && isset($feature['included'])):
                                                $included = $feature['included'];
                                                $feature_name = $feature['name'] ?? '';
                                            else:
                                                // Si $feature es una string o no tiene 'included', asumimos que está incluida
                                                $included = true;
                                                $feature_name = is_string($feature) ? $feature : '';
                                            endif;
                                    ?>
                                        <li>
                                            <i class="bi <?php echo $included ? 'bi-check-circle' : 'bi-x-lg text-danger'; ?>"></i> 
                                            <?php echo htmlspecialchars($feature_name); ?>
                                        </li>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <li><i class="bi bi-check-circle"></i> Acceso a vehículos compactos</li>
                                        <li><i class="bi bi-check-circle"></i> Cambio de vehículo mensual</li>
                                        <li><i class="bi bi-check-circle"></i> 1500 km mensuales</li>
                                        <li><i class="bi bi-check-circle"></i> Mantenimiento básico incluido</li>
                                        <li><i class="bi bi-check-circle"></i> Seguro a terceros</li>
                                        <li><i class="bi bi-x-lg text-danger"></i> Asistencia en carretera</li>
                                        <li><i class="bi bi-x-lg text-danger"></i> Entrega y recogida</li>
                                        <li><i class="bi bi-x-lg text-danger"></i> Acceso a eventos exclusivos</li>
                                    <?php endif; ?>
                                </ul>
                                <?php if (is_logged_in()): ?>
                                    <?php 
                                    $user_subscription = get_user_subscription($_SESSION['user_id']);
                                    $current_plan_id = $user_subscription ? $user_subscription['plan_id'] : 0;
                                    
                                    if ($current_plan_id == $plan['id']): 
                                    ?>
                                        <button class="btn btn-success w-100" disabled>Plan Actual</button>
                                    <?php elseif ($current_plan_id > $plan['id']): ?>
                                        <a href="checkout-plan.php?plan_id=<?php echo $plan['id']; ?>" class="btn btn-outline-primary w-100">Cambiar Plan</a>
                                    <?php else: ?>
                                        <a href="checkout-plan.php?plan_id=<?php echo $plan['id']; ?>" class="btn <?php echo $index == 1 ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">Elegir Plan</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php?registro=true" class="btn <?php echo $index == 1 ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">Elegir Plan</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No hay planes de suscripción disponibles en este momento.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Comparison Features Section -->
<section class="features-comparison py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Características Detalladas</h2>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-container">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <h3>Vehículos Disponibles</h3>
                    <p><strong>Básico:</strong> Compactos</p>
                    <p><strong>Premium:</strong> Compactos, Sedán, SUVs</p>
                    <p><strong>Elite:</strong> Todos, incluyendo deportivos</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-container">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h3>Cambio de Vehículo</h3>
                    <p><strong>Básico:</strong> Mensual</p>
                    <p><strong>Premium:</strong> Cada 2 semanas</p>
                    <p><strong>Elite:</strong> Ilimitado</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-container">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h3>Kilometraje</h3>
                    <p><strong>Básico:</strong> 1.500 km/mes</p>
                    <p><strong>Premium:</strong> 2.500 km/mes</p>
                    <p><strong>Elite:</strong> Ilimitado</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-container">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Seguro</h3>
                    <p><strong>Básico:</strong> A terceros</p>
                    <p><strong>Premium:</strong> A todo riesgo</p>
                    <p><strong>Elite:</strong> A todo riesgo con ampliación</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Join Section -->
<section class="why-join-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title mb-4">¿Por qué unirte a DriveClub?</h2>
                <div class="why-join-feature mb-4">
                    <div class="icon-container">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <h3>Sin inversión inicial</h3>
                        <p>Olvídate de la entrada inicial, depreciación y gastos ocultos asociados a la compra de un vehículo.</p>
                    </div>
                </div>
                <div class="why-join-feature mb-4">
                    <div class="icon-container">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div>
                        <h3>Sin preocupaciones de mantenimiento</h3>
                        <p>Nos encargamos de todas las revisiones y mantenimiento para que disfrutes de tu vehículo sin complicaciones.</p>
                    </div>
                </div>
                <div class="why-join-feature mb-4">
                    <div class="icon-container">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div>
                        <h3>Variedad y flexibilidad</h3>
                        <p>Cambia de vehículo según tus necesidades: un SUV para el fin de semana, un deportivo para impresionar o un eléctrico para el día a día.</p>
                    </div>
                </div>
                <div class="why-join-feature">
                    <div class="icon-container">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h3>Tranquilidad total</h3>
                        <p>Vehículos nuevos, revisados y con todas las garantías. Además, nuestros planes incluyen seguros adaptados a cada necesidad.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="why-join-image">
                    <img src="https://images.unsplash.com/photo-1532581140115-3e355d1ed1de?q=80&w=1770&auto=format&fit=crop" alt="Experiencia DriveClub" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Preguntas Frecuentes</h2>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                        ¿Cómo funciona la suscripción a DriveClub?
                    </button>
                </h3>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Muy sencillo: eliges el plan que mejor se adapte a tus necesidades, seleccionas el vehículo que deseas conducir, y nosotros nos encargamos del resto. Tu suscripción incluye seguro, mantenimiento, asistencia en carretera (según el plan) y la posibilidad de cambiar de vehículo periódicamente.</p>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                        ¿Qué incluye el precio mensual?
                    </button>
                </h3>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>El precio mensual incluye: uso del vehículo, seguro, mantenimiento regular, asistencia en carretera (en planes Premium y Elite), impuestos y la posibilidad de cambiar de vehículo según la frecuencia de tu plan. No hay costes ocultos ni sorpresas.</p>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                        ¿Puedo cancelar mi suscripción en cualquier momento?
                    </button>
                </h3>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Por supuesto. La flexibilidad es una de nuestras ventajas. Puedes cancelar o cambiar tu plan en cualquier momento con un preaviso de 30 días. No hay penalizaciones por cancelación después del primer mes de suscripción.</p>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                        ¿Qué ocurre si excedo el kilometraje mensual?
                    </button>
                </h3>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Si excedes el kilometraje incluido en tu plan, se aplicará un cargo adicional por kilómetro. Este cargo varía según el tipo de vehículo. Te recomendamos escoger un plan que se adapte a tus necesidades de conducción habituales.</p>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                        ¿Los vehículos son nuevos?
                    </button>
                </h3>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p>Sí, nuestra flota está compuesta por vehículos nuevos o seminuevos (menos de 2 años). Todos nuestros vehículos pasan por un riguroso control de calidad y se mantienen en perfectas condiciones.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="mb-3">¿Listo para disfrutar de tu experiencia de conducción?</h2>
                <p class="lead mb-4">Únete a DriveClub hoy y descubre la libertad de conducir sin compromisos a largo plazo.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="login.php?registro=true" class="btn btn-primary btn-lg">Comenzar Ahora</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>