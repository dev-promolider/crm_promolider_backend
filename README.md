# Promolíder - Backend API (Arquitectura Hexagonal)

Este repositorio contiene la API REST del sistema Promolíder, migrada desde un antiguo monolito Laravel hacia una **Arquitectura Hexagonal (Puertos y Adaptadores)**. Este enfoque permite que el código sea altamente mantenible, testable e independiente de frameworks o bases de datos específicas en la capa de negocio.

## Estructura del Proyecto

El código de negocio y la infraestructura están separados en la carpeta `app/`:

*   **`1-Domain/` (Dominio):** Contiene las interfaces (Puertos), entidades de negocio y lógica pura. No debe tener dependencias de infraestructura ni de Laravel.
    *   Ejemplo: `DashboardRepositoryInterface.php` (Puerto de Salida).
*   **`2-Application/` (Aplicación):** Contiene los Casos de Uso (Use Cases) que orquestan el flujo de información entre el exterior y el dominio.
    *   Ejemplo: `GetBinaryTreeUseCase.php`
*   **`3-Infrastructure/` (Infraestructura):** Implementa los adaptadores concretos. Aquí vive Laravel, Eloquent, Controladores HTTP, etc.
    *   **`In/` (Entrada):** Controladores (`DashboardController.php`), rutas, peticiones entrantes.
    *   **`Out/` (Salida):** Repositorios concretos (`EloquentDashboardRepository.php`), llamadas a APIs externas.

## Reglas y Directrices para el Equipo

1.  **Cero Acoplamiento en Casos de Uso:** Un Caso de Uso jamás debe llamar a un modelo de Eloquent (`User::find()`) directamente. Siempre debe inyectarse un Puerto (Interfaz) a través del constructor.
2.  **Controladores Limpios:** Los controladores en la capa de Infraestructura (`In\Http\Controllers`) solo deben encargarse de recibir la Request, llamar a un Caso de Uso, y devolver una Response JSON. Toda la lógica pesada de bases de datos va en los Repositorios de Infraestructura de Salida.
3.  **Seguridad CORS:** El backend está configurado para aceptar peticiones del frontend en `http://localhost:5173`. Si se sube a producción, actualiza los dominios permitidos en `config/cors.php`.
4.  **Autenticación:** Todo endpoint protegido debe usar middleware `auth:sanctum`. El frontend enviará automáticamente el token Bearer en las cabeceras.

## Ejecución Local

1.  Copia `.env.example` a `.env` y ajusta las credenciales de la base de datos local.
2.  Ejecuta `composer install`.
3.  Sirve la API con `php artisan serve`.
