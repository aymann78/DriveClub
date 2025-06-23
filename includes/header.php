<?php
/**
 * Header Template
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>DriveClub - Alquiler de Coches por Suscripción</title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'DriveClub - Alquiler de coches por suscripción'; ?>" />
    <meta name="author" content="DriveClub" />

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <?php if (isset($additional_head)): ?>
        <?php echo $additional_head; ?>
    <?php endif; ?>
    
</head>
<body>
    <div id="root">
        <!-- Header -->
        <header class="header">
            <div class="container">
                <nav class="navbar navbar-expand-lg">
                    <a class="navbar-brand" href="index.php">
                        <h2 class="logo-text">
                            <span class="drive">Drive</span><span class="club">Club</span>
                        </h2>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="bi bi-list fs-1"></i>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'home' ? 'active' : ''; ?>" href="index.php">Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'vehiculos' ? 'active' : ''; ?>" href="vehiculos.php">Vehículos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'planes' ? 'active' : ''; ?>" href="planes.php">Planes</a>
                            </li>
                            <?php if (is_logged_in()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'mi-cuenta' ? 'active' : ''; ?>" href="mi-cuenta.php">Mi Cuenta</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <div class="header-actions">
                            <?php if (is_logged_in()): ?>
                                <a href="logout.php" class="btn btn-outline-secondary">Cerrar Sesión</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-secondary">Iniciar Sesión</a>
                                <a href="login.php?registro=true" class="btn btn-primary">Registrarse</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
