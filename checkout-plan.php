<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;
$plan = $plan_id ? get_plan_by_id($plan_id) : null;
if (!$plan) {
    set_flash_message('danger', 'Plan no encontrado.');
    redirect('planes.php');
}

$user = get_user_data();
$credit_cards = get_user_credit_cards($user_id);

include 'includes/header.php';
?>
<section class="plans-banner">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="section-title mb-2" style="color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,0.18);">Checkout de Suscripción</h1>
                <p class="lead" style="color: #fff;">Revisa y completa tus datos para suscribirte al plan <strong style="color: #fff;"><?php echo htmlspecialchars($plan['name']); ?></strong>.</p>
            </div>
        </div>
    </div>
</section>
<section class="account-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div id="checkout-flash" class="mb-3"></div>
                <div id="checkout-personal-card" class="mb-4"></div>
                <div id="checkout-card-card" class="mb-4"></div>
                <div id="checkout-plan-summary" class="mb-4"></div>
                <div id="checkout-terms" class="mb-4"></div>
                <div id="checkout-cta" class="mb-4"></div>
            </div>
        </div>
    </div>
</section>
<script>
window.DRIVECLUB_CHECKOUT_PLAN = <?php echo json_encode([
    'plan_id' => $plan['id'],
    'plan_name' => $plan['name'],
    'plan_price' => $plan['price_monthly'],
    'plan_features' => $plan['features'] ?? [],
    'user' => [
        'nombre' => $user['first_name'] ?? '',
        'apellidos' => $user['last_name'] ?? '',
        'telefono' => $user['phone'] ?? '',
        'direccion' => $user['address'] ?? '',
        'ciudad' => $user['city'] ?? '',
        'postal' => $user['postal_code'] ?? '',
        'pais' => $user['country'] ?? '',
    ],
    'cards' => array_map(function($c) {
        return [
            'id' => $c['id'],
            'holder' => $c['card_holder'],
            'last_four' => $c['last_four'],
            'expiry_month' => $c['expiry_month'],
            'expiry_year' => $c['expiry_year'],
            'type' => $c['card_type'],
            'is_default' => $c['is_default'],
        ];
    }, $credit_cards),
]); ?>;
</script>
<script src="assets/js/checkout-plan.js"></script>
<?php include 'includes/footer.php'; ?>
<!-- Toast Notifications Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <?php if (isset($_SESSION['toast'])): ?>
        <div class="toast align-items-center text-bg-<?php echo $_SESSION['toast']['type']; ?> border-0 show" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo $_SESSION['toast']['message']; ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?> 