<?php
/**
 * Footer Template
 */
?>
<!-- Footer -->
<footer class="footer py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h2 class="mb-3">
                    <a href="index.php" class="footer-logo"><span class="drive" style="color: white;">Drive</span><span
                            class="club" style="color: var(--primary-color);">Club</span></a>
                </h2>
                <p>La mejor experiencia en alquiler de vehículos por suscripción. Disfruta de la libertad de conducir
                    sin compromisos.</p>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h5>Enlaces</h5>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="vehiculos.php">Vehículos</a></li>
                    <li><a href="planes.php">Planes</a></li>
                    <li><a href="mi-cuenta.php">Mi Cuenta</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h5>Información</h5>
                <ul class="footer-links">
                    <li><a href="#">Sobre Nosotros</a></li>
                    <li><a href="#">Contacto</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4">
                <h5>Contacto</h5>
                <ul class="contact-info">
                    <li>
                        <i class="bi bi-geo-alt"></i>
                        <span>C/ Gran Vía 1, Madrid 28013</span>
                    </li>
                    <li>
                        <i class="bi bi-telephone"></i>
                        <span>+34 91 123 45 67</span>
                    </li>
                    <li>
                        <i class="bi bi-envelope"></i>
                        <span>info@driveclub.com</span>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="mt-4 mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-md-0">&copy; <?php echo date('Y'); ?> DriveClub. Todos los derechos reservados.</p>
            </div>
            <div class="col-md-6">
                <ul class="legal-links">
                    <li><a href="#">Términos y Condiciones</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Cookies</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

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
</div>
</div><!-- End of #root -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Scripts comunes -->
<script src="assets/js/main.js"></script>
<script src="assets/js/screen.js"></script>

<?php if ($current_page === 'vehiculos'): ?>
    <!-- Scripts específicos de la página de vehículos -->
<?php elseif ($current_page === 'detalle-vehiculo'): ?>
    <!-- Scripts específicos de la página de detalle de vehículo -->
    <script src="assets/js/vehicle-detail.js"></script>
<?php elseif ($current_page === 'login'): ?>
    <!-- Scripts específicos de la página de login -->
    <script src="assets/js/login.js"></script>
<?php endif; ?>
<?php if (isset($additional_scripts)): ?>
    <?php echo $additional_scripts; ?>
<?php endif; ?>
</body>

</html>