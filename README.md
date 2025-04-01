
# DriveClub - Plataforma de Alquiler de Coches por Suscripción

## Descripción del Proyecto

DriveClub es una plataforma innovadora de alquiler de coches que opera mediante un modelo de suscripción flexible. Ofrece acceso a una flota diversificada de vehículos organizados en diferentes planes mensuales (Básico, Premium y Elite), permitiendo a los clientes disfrutar de experiencias de conducción únicas con coches de alto rendimiento sin los compromisos de los alquileres diarios tradicionales o la compra de un vehículo.

## Características Principales

- Catálogo de vehículos de diferentes categorías y marcas
- Tres planes de suscripción con diferentes beneficios
- Sistema de reservas con confirmación mediante código QR
- Gestión de cuenta de usuario y reservas
- Diseño responsive para todos los dispositivos

## Tecnologías Utilizadas

- HTML5
- CSS3 (con personalización de Bootstrap)
- JavaScript (ES6+)
- Bootstrap 5
- Google Fonts (Montserrat)

## Estructura del Proyecto

```
driveclub/
├── public/               # Archivos HTML de las páginas
│   ├── vehiculos.html    # Página de catálogo de vehículos
│   ├── planes.html       # Página de planes de suscripción
│   ├── vehiculo.html     # Página de detalle de vehículo
│   ├── mi-cuenta.html    # Página de cuenta de usuario
│   └── login.html        # Página de inicio de sesión/registro
├── src/
│   ├── assets/           # Imágenes y recursos
│   ├── js/               # Scripts JavaScript
│   │   ├── main.js       # Funcionalidad general
│   │   ├── vehicles.js   # Gestión de vehículos
│   │   ├── screen.js     # Transiciones entre páginas
│   │   ├── login.js      # Funcionalidad de login
│   │   └── vehicle-detail.js # Funcionalidad de detalle de vehículo
│   └── index.css         # Estilos CSS
└── index.html            # Página principal
```

## Instalación y Ejecución

Para ejecutar este proyecto localmente:

1. Clona este repositorio
2. Navega al directorio del proyecto
3. Abre los archivos HTML en tu navegador web

## Personalización

El proyecto utiliza una paleta de colores personalizada definida en las variables CSS:

```css
:root {
  --primary-color: #e63946;
  --secondary-color: #1d3557;
  --dark-color: #0d1b2a;
  --light-color: #f1faee;
  --accent-color: #ffd166;
  ...
}
```

## Uso

### Usuarios
- Explora los diferentes vehículos disponibles
- Compara planes de suscripción
- Registra una cuenta para realizar reservas
- Gestiona tus reservas y suscripción desde el panel de usuario

## Project info

**URL**: https://lovable.dev/projects/9d9cd8c3-9457-4922-be15-2372d08df38a
