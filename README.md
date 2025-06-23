# DriveClubSystem

**DriveClub** es una plataforma web de alquiler de coches por suscripción, que permite a los usuarios registrarse, elegir un plan, gestionar reservas y acceder a una flota de vehículos premium, todo desde una interfaz moderna y responsiva.

---

## Tabla de Contenidos
- [Descripción General](#descripción-general)
- [Características Principales](#características-principales)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Instalación y Despliegue](#instalación-y-despliegue)
- [Estructura de la Base de Datos](#estructura-de-la-base-de-datos)
- [Scripts y Estilos Destacados](#scripts-y-estilos-destacados)
- [Funcionalidad Destacada](#funcionalidad-destacada)
- [Personalización y Extensibilidad](#personalización-y-extensibilidad)
- [Seguridad y Buenas Prácticas](#seguridad-y-buenas-prácticas)
- [Créditos y Licencias](#créditos-y-licencias)

---

## Descripción General
DriveClub es un sistema de alquiler de vehículos por suscripción, orientado a usuarios que buscan flexibilidad, variedad y comodidad. Permite gestionar todo el ciclo de vida del usuario: registro, selección de plan, reserva de vehículos, pagos y valoraciones, con una experiencia de usuario moderna y segura.

## Características Principales
- Registro y autenticación (incluye Google OAuth).
- Gestión de planes de suscripción (Básico, Premium, Elite).
- Búsqueda y filtrado de vehículos por marca, tipo y nombre.
- Reservas de vehículos con control de fechas y disponibilidad.
- Gestión de métodos de pago y tarjetas de crédito.
- Notificaciones y recordatorios automáticos.
- Panel de usuario para gestión de perfil, suscripciones y reservas.
- Sistema de reseñas y valoraciones de vehículos.
- Interfaz responsiva y animaciones modernas.
- **Visibilidad total de la flota:** Todos los usuarios pueden ver todos los vehículos disponibles (incluidos los premium), independientemente de su plan. El sistema solo restringe la reserva de vehículos según el plan del usuario.

## Estructura del Proyecto
```
├── driveclub.sql                # Esquema y datos de la base de datos
├── index.php                    # Página principal
├── login.php,                   # Autenticación y registro
├── mi-cuenta.php                # Panel de usuario
├── vehiculos.php                # Listado y filtrado de vehículos
├── detalle-vehiculo.php         # Detalle de vehículo y reservas
├── planes.php                   # Planes de suscripción
├── reserva.php                  # Gestión de reservas
├── includes/                    # Configuración, utilidades, plantillas, emails
├── lib/                         # Librerías externas (PHPMailer, phpqrcode)
├── assets/
│   ├── js/                      # Scripts de frontend
│   └── css/                     # Estilos principales
└── ...
```

## Instalación y Despliegue
### Requisitos
- PHP 8.2+
- MariaDB/MySQL 10.4+
- Servidor web (Apache recomendado)
- Composer (opcional, para dependencias externas)

### Pasos
1. Clona el repositorio en el directorio de tu servidor web.
2. Importa `driveclub.sql` en tu servidor MySQL.
3. Configura las credenciales de base de datos en `includes/config.php`.
4. Instala dependencias externas si es necesario (PHPMailer, phpqrcode).
5. Configura permisos de escritura para generación de QR y subida de documentos.
6. Accede vía navegador a la URL configurada.

## Estructura de la Base de Datos (Resumen)
- **users**: Usuarios registrados.
- **roles**: Roles de usuario (admin, user, etc.).
- **subscription_plans**: Planes de suscripción.
- **user_subscriptions**: Suscripciones activas de usuarios.
- **vehicles**: Vehículos disponibles.
- **brands**: Marcas de vehículos.
- **vehicle_types**: Tipos/categorías de vehículos.
- **reservations**: Reservas de vehículos.
- **reservation_statuses**: Estados de reserva.
- **payments**: Pagos realizados.
- **credit_cards**: Tarjetas de crédito de usuarios.
- **notifications**: Notificaciones del sistema.
- **reviews**: Reseñas de vehículos.
- **maintenance_records**: Mantenimiento de vehículos.
- **vehicle_images**, **vehicle_specifications**: Imágenes y especificaciones técnicas.

## Scripts y Estilos Destacados
- **assets/js/main.js**: Animaciones, scroll suave, validación y formateo de tarjetas, animaciones en scroll, gestión de modales.
- **assets/js/login.js**: Lógica de login/registro, cambio de formularios, medidor de fortaleza de contraseña.
- **assets/js/vehicle-detail.js**: Lógica de detalle de vehículo, selección de fechas, responsive de columnas, carrusel de imágenes.
- **assets/js/screen.js**: Inicialización de pantallas, transiciones, efectos de fade/slide, transición entre páginas.
- **assets/css/styles.css**: Estilos modernos, variables CSS, responsive, componentes reutilizables, animaciones, adaptaciones móviles.

## Funcionalidad Destacada
- **Autenticación**: Soporte para login tradicional y Google OAuth.
- **Planes**: Tres niveles de suscripción con diferentes beneficios y precios.
- **Reservas**: Control de disponibilidad, fechas, generación de QR para confirmación.
- **Pagos**: Gestión de tarjetas, validación y almacenamiento seguro (solo últimos 4 dígitos).
- **Notificaciones**: Recordatorios automáticos y mensajes del sistema.
- **Reseñas**: Los usuarios pueden dejar valoraciones y comentarios sobre los vehículos.
- **Responsive**: Interfaz adaptativa y animaciones suaves para una experiencia moderna.

## Personalización y Extensibilidad
- **Estilos**: Personalizables mediante variables CSS.
- **Lógica**: Modularidad en includes y assets para fácil extensión.
- **Base de datos**: Estructura relacional preparada para ampliaciones (nuevos roles, tipos de vehículos, etc.).

## Seguridad y Buenas Prácticas
- Validación de formularios en frontend y backend.
- Hashing de contraseñas.
- Control de sesiones y roles.
- Protección contra SQL Injection mediante consultas preparadas.
- Manejo de errores y logs.

## Créditos y Licencias
- PHPMailer y phpqrcode bajo sus respectivas licencias.
- Bootstrap y Bootstrap Icons para UI.
- **Desarrollado por Ayman Assidah. Trabajo Fin de Carrera (TFCS).**

## Notas importantes

- **Visibilidad de vehículos:** Todos los vehículos se muestran a todos los usuarios. Si intentas reservar un vehículo para el que tu plan no da acceso, el sistema mostrará un mensaje y bloqueará la reserva.
- **Restricciones de reserva:**
  - Solo puedes reservar a partir del día siguiente (no el mismo día)
  - Duración máxima de reserva: 7 días
  - No puedes eliminar tu única tarjeta si tienes una suscripción activa con renovación automática
- **Gestión de imágenes:** El avatar por defecto es un SVG gris. Puedes subir tu propia foto desde el panel de usuario.

## Buenas prácticas

- Usa contraseñas seguras y actualízalas periódicamente
- Mantén actualizada tu información de perfil y métodos de pago
- Revisa las condiciones de tu plan antes de reservar vehículos premium 