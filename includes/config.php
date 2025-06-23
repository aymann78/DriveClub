<?php
/**
 * Main Configuration File
 * 
 * Contains all the configuration settings for the DriveClub application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define database connection constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'driveclub');

// Define site constants
define('SITE_NAME', 'DriveClub');
define('SITE_URL', 'http://localhost/driveclub');

// Error reporting - set to 0 in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Europe/Madrid');

// Load database connection
require_once 'db.php';

// Load helper functions
require_once 'functions.php';

// Detectar la página actual para el menú
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    if ($current_page == 'index') {
        $current_page = 'home';
    }
}

define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', 'http://localhost/DriveClubSystem/google_callback.php');