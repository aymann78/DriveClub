<?php
/**
 * Logout Page
 */
require_once 'includes/config.php';

// Logout user
logout_user();

// Clear remember me cookie if set
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Set success message
set_flash_message('success', 'Has cerrado sesión correctamente');

// Redirect to home page
redirect('index.php');
