<?php
/**
 * Login Page
 */
session_start(); // Make sure the session is started
require_once 'includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('mi-cuenta.php');
}

// Set page title and current page
$page_title = 'Iniciar Sesión';
$current_page = 'login';
$page_description = 'Inicia sesión o regístrate en DriveClub';

// Define Google OAuth credentials
$client_id = '';
$redirect_uri = 'http://localhost/DriveClubSystem/google_callback.php';
$scope = 'email profile';

// Build the Google authentication URL
$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'offline',
]);

// Handle errors from Google authentication
$login_error = '';
if (isset($_GET['error'])) {
    $login_error = 'Error en la autenticación con Google: ' . htmlspecialchars($_GET['error']);
}

// Check if registration form should be shown by default
$show_register = isset($_GET['registro']) && $_GET['registro'] === 'true';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $login_error = 'Por favor, introduce email y contraseña';
    } else {
        $user = login_user($email, $password);
        
        if ($user) {
            // Set remember me cookie if requested
            if ($remember) {
                setcookie('remember_user', $user['id'], time() + (86400 * 30), '/'); // 30 days
            }
            
            // Set success message and redirect
            set_flash_message('success', 'Has iniciado sesión correctamente');
            redirect('mi-cuenta.php');
        } else {
            $login_error = 'Email o contraseña incorrectos';
        }
    }
}

// Process registration form
$register_error = '';
$register_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $terms_agree = isset($_POST['terms_agree']);
    
    // Validate form data
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $register_error = 'Por favor, completa todos los campos obligatorios';
        $show_register = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = 'Por favor, introduce un email válido';
        $show_register = true;
    } elseif (strlen($password) < 8) {
        $register_error = 'La contraseña debe tener al menos 8 caracteres';
        $show_register = true;
    } elseif (!$terms_agree) {
        $register_error = 'Debes aceptar los términos y condiciones';
        $show_register = true;
    } else {
        // Create user account
        $user_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password
        ];
        
        $user_id = register_user($user_data);
        
        if ($user_id) {
            // Auto login after registration
            login_user($email, $password);
            
            // Set success message and redirect
            set_flash_message('success', '¡Cuenta creada correctamente! Bienvenido a DriveClub.');
            redirect('mi-cuenta.php');
        } else {
            $register_error = 'El email ya está registrado o ha ocurrido un error';
            $show_register = true;
        }
    }
}

// HTML head additions
$additional_head = '<style>
    /* Estilos específicos para la página de login */
    .login-container {
      min-height: 100vh;
      width: 100%;
      display: flex;
    }
    
    .login-form-container {
      padding: 3rem;
      width: 100%;
      max-width: 550px;
      margin: 0 auto;
      height: 100%;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    
    .login-image {
      width: 100%;
      height: 100%;
      min-height: 100vh;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    
    .login-image::after {
      content: \'\';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.4);
      z-index: 1;
    }
    
    .login-image .overlay-content {
      position: absolute;
      bottom: 100px;
      left: 50px;
      right: 50px;
      color: white;
      z-index: 2;
    }
    
    .login-image .overlay-content h2 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: white;
    }
    
    .login-header {
      margin-bottom: 2.5rem;
    }
    
    .login-tabs {
      display: flex;
      border-bottom: 1px solid var(--gray-300);
      margin-bottom: 1.5rem;
    }
    
    .tab-btn {
      flex: 1;
      background: none;
      border: none;
      padding: 15px;
      font-weight: 600;
      color: var(--gray-600);
      cursor: pointer;
      position: relative;
    }
    
    .tab-btn.active {
      color: var(--primary-color);
    }
    
    .tab-btn.active::after {
      content: \'\';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 2px;
      background-color: var(--primary-color);
    }
    
    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 20px 0;
    }
    
    .divider::before,
    .divider::after {
      content: \'\';
      flex: 1;
      border-bottom: 1px solid var(--gray-300);
    }
    
    .divider span {
      padding: 0 10px;
      color: var(--gray-600);
      font-size: 14px;
    }
    
    .password-input {
      position: relative;
    }
    
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--gray-600);
      cursor: pointer;
      padding: 0;
    }
    
    .password-strength {
      width: 100%;
    }
    
    .strength-bar {
      height: 5px;
      background-color: var(--gray-200);
      border-radius: 3px;
      margin-bottom: 5px;
    }
    
    .strength-indicator {
      height: 100%;
      border-radius: 3px;
      transition: width 0.3s, background-color 0.3s;
    }
    
    @media (max-width: 991.98px) {
      .login-form-container {
        padding: 2rem;
      }
      
      .login-image {
        display: none;
      }
    }
</style>';

// Additional scripts
$additional_scripts = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tab switching
        const loginTabBtn = document.getElementById("loginTabBtn");
        const registerTabBtn = document.getElementById("registerTabBtn");
        const loginForm = document.getElementById("loginForm");
        const registerForm = document.getElementById("registerForm");
        const switchToRegister = document.getElementById("switchToRegister");
        const switchToLogin = document.getElementById("switchToLogin");
        
        // Check URL params for direct access to registration
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get("registro") === "true") {
            showRegisterForm();
        }
        
        if (loginTabBtn && registerTabBtn) {
            loginTabBtn.addEventListener("click", showLoginForm);
            registerTabBtn.addEventListener("click", showRegisterForm);
        }
        
        if (switchToRegister) {
            switchToRegister.addEventListener("click", function(e) {
                e.preventDefault();
                showRegisterForm();
            });
        }
        
        if (switchToLogin) {
            switchToLogin.addEventListener("click", function(e) {
                e.preventDefault();
                showLoginForm();
            });
        }
        
        function showLoginForm() {
            loginTabBtn.classList.add("active");
            registerTabBtn.classList.remove("active");
            loginForm.style.display = "block";
            registerForm.style.display = "none";
        }
        
        function showRegisterForm() {
            loginTabBtn.classList.remove("active");
            registerTabBtn.classList.add("active");
            loginForm.style.display = "none";
            registerForm.style.display = "block";
        }
    });
    
    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById("strengthIndicator");
        const strengthText = document.getElementById("strengthText");
        
        if (!strengthBar || !strengthText) return;
        
        let strength = 0;
        
        // Length check
        if (password.length >= 8) {
            strength += 1;
        }
        
        // Contains lowercase letters
        if (password.match(/[a-z]+/)) {
            strength += 1;
        }
        
        // Contains uppercase letters
        if (password.match(/[A-Z]+/)) {
            strength += 1;
        }
        
        // Contains numbers
        if (password.match(/[0-9]+/)) {
            strength += 1;
        }
        
        // Contains special characters
        if (password.match(/[$@#&!]+/)) {
            strength += 1;
        }
        
        // Update UI based on strength
        switch (strength) {
            case 0:
            case 1:
                strengthBar.style.width = "20%";
                strengthBar.style.backgroundColor = "#dc3545"; // red
                strengthText.textContent = "Muy débil";
                break;
            case 2:
                strengthBar.style.width = "40%";
                strengthBar.style.backgroundColor = "#ffc107"; // yellow
                strengthText.textContent = "Débil";
                break;
            case 3:
                strengthBar.style.width = "60%";
                strengthBar.style.backgroundColor = "#fd7e14"; // orange
                strengthText.textContent = "Media";
                break;
            case 4:
                strengthBar.style.width = "80%";
                strengthBar.style.backgroundColor = "#20c997"; // teal
                strengthText.textContent = "Fuerte";
                break;
            case 5:
                strengthBar.style.width = "100%";
                strengthBar.style.backgroundColor = "#28a745"; // green
                strengthText.textContent = "Muy fuerte";
                break;
            default:
                strengthBar.style.width = "0";
                strengthText.textContent = "La contraseña debe tener al menos 8 caracteres";
        }
    }
    
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }
    }
</script>';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?> | DriveClub - Alquiler de Coches por Suscripción</title>
    <meta name="description" content="<?php echo $page_description; ?>" />
    <meta name="author" content="DriveClub" />

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <?php echo $additional_head; ?>
</head>
<body>
    <div id="root">
        <!-- Login Content -->
        <div class="login-container">
            <div class="row g-0 h-100 w-100">
                <!-- Left Panel - Form -->
                <div class="col-lg-5">
                    <div class="login-form-container">
                        <div class="login-header mb-5">
                            <a class="navbar-brand" href="index.php">
                                <h2 class="logo-text">
                                    <span class="drive">Drive</span><span class="club">Club</span>
                                </h2>
                            </a>
                        </div>
                        
                        <div class="login-tabs mb-4">
                            <button class="tab-btn <?php echo !$show_register ? 'active' : ''; ?>" id="loginTabBtn">Iniciar Sesión</button>
                            <button class="tab-btn <?php echo $show_register ? 'active' : ''; ?>" id="registerTabBtn">Registrarse</button>
                        </div>
                        
                        <!-- Login Form -->
                        <div class="form-container" id="loginForm" style="display: <?php echo !$show_register ? 'block' : 'none'; ?>;">
                            <h1 class="login-title mb-4">Bienvenido de nuevo</h1>
                            
                            <?php if ($login_error): ?>
                                <div class="alert alert-danger"><?php echo $login_error; ?></div>
                            <?php endif; ?>
                            
                            <div class="social-login mb-4">
                                <a href="<?php echo $auth_url; ?>" class="btn btn-outline-secondary w-100 mb-3">
                                    <i class="bi bi-google me-2"></i>Continuar con Google
                                </a>
                            </div>
                            
                            <div class="divider">
                                <span>O</span>
                            </div>
                            
                            <form method="post" action="login.php">
                                <input type="hidden" name="action" value="login">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="loginEmail" name="email" placeholder="tu@email.com" required>
                                </div>
                                <div class="mb-4">
                                    <label for="loginPassword" class="form-label">Contraseña</label>
                                    <div class="password-input">
                                        <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Tu contraseña" required>
                                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('loginPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                        <label class="form-check-label" for="rememberMe">Recordarme</label>
                                    </div>
                                    <a href="#" class="forgot-password mt-2 mt-sm-0">¿Olvidaste tu contraseña?</a>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-4">Iniciar Sesión</button>
                                <p class="text-center mb-0">¿No tienes cuenta? <a href="#" id="switchToRegister">Regístrate aquí</a></p>
                            </form>
                        </div>
                        
                        <!-- Register Form -->
                        <div class="form-container" id="registerForm" style="display: <?php echo $show_register ? 'block' : 'none'; ?>;">
                            <h1 class="login-title mb-4">Crear una cuenta</h1>
                            
                            <?php if ($register_error): ?>
                                <div class="alert alert-danger"><?php echo $register_error; ?></div>
                            <?php endif; ?>
                            
                            <div class="social-login mb-4">
                                <a href="<?php echo $auth_url; ?>" class="btn btn-outline-secondary w-100 mb-3">
                                    <i class="bi bi-google me-2"></i>Continuar con Google
                                </a>
                            </div>
                            
                            <div class="divider">
                                <span>O</span>
                            </div>
                            
                            <form method="post" action="login.php">
                                <input type="hidden" name="action" value="register">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="registerFirstName" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="registerFirstName" name="first_name" placeholder="Tu nombre" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="registerLastName" class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="registerLastName" name="last_name" placeholder="Tus apellidos" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="tu@email.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerPhone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="registerPhone" name="phone" placeholder="+34 612 345 678">
                                </div>
                                <div class="mb-4">
                                    <label for="registerPassword" class="form-label">Contraseña</label>
                                    <div class="password-input">
                                        <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Crea una contraseña" oninput="checkPasswordStrength(this.value)" required>
                                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('registerPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="strength-bar">
                                            <div class="strength-indicator" id="strengthIndicator" style="width: 0%"></div>
                                        </div>
                                        <small class="strength-text" id="strengthText">La contraseña debe tener al menos 8 caracteres</small>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="termsAgree" name="terms_agree" required>
                                        <label class="form-check-label" for="termsAgree">
                                            Acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a>
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-4">Crear Cuenta</button>
                                <p class="text-center mb-0">¿Ya tienes cuenta? <a href="#" id="switchToLogin">Inicia sesión aquí</a></p>
                            </form>
                        </div>
                        
                        <div class="login-footer mt-5">
                            <p class="text-center mb-0">© <?php echo date('Y'); ?> DriveClub. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Image -->
                <div class="col-lg-7 d-none d-lg-block">
                    <div class="login-image" style="background-image: url('https://images.unsplash.com/photo-1511919884226-fd3cad34687c?q=80&w=1770&auto=format&fit=crop');">
                        <div class="overlay-content">
                            <h2>Disfruta de la libertad al volante</h2>
                            <p class="lead">Únete a DriveClub y experimenta la conducción sin compromisos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <?php echo $additional_scripts; ?>
</body>
</html>