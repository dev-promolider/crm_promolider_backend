# Promolider CRM

Promolider CRM es el repositorio principal de la plataforma. Aqui viven el backend y parte del frontend del sistema: panel administrativo, vistas Blade, componentes Vue integrados, procesos de negocio, APIs y servicios que luego consumen otras aplicaciones de Promolider.

Si solo necesitas el frontend del aula virtual, revisa tambien el repositorio de `virtual_classroom`:

- Repo VCR: https://github.com/dev-promolider/virtual_classroom
- Documentacion de despliegue de este repo: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

## Que contiene este repositorio

Este proyecto no es solamente un CRM clasico. En la practica es un monolito Laravel que concentra varios dominios del negocio:

- CRM y panel administrativo
- Backend principal de autenticacion, usuarios, cursos y pagos
- Frontend acoplado con Blade + Vue dentro del mismo proyecto
- APIs usadas por clientes externos, incluyendo `virtual_classroom`
- Integraciones con almacenamiento, correos, websockets y pasarelas de pago

## Relacion con otros repositorios

- `promolider` (este repo): backend principal + frontend integrado del CRM
- `virtual_classroom`: frontend separado del aula virtual
- `virtual_classroom` consume este backend por HTTP, especialmente endpoints bajo `/api/v1`

En otras palabras: si `promolider` no esta levantado o no esta bien configurado, `virtual_classroom` no va a funcionar correctamente en local.

## Stack tecnico

- Backend: Laravel 8
- PHP: 8.1 configurado en `composer.json`
- Frontend integrado: Vue 2 + Blade
- Build frontend: Laravel Mix / Webpack
- Base de datos: MySQL
- Autenticacion API: Laravel Passport
- Websockets / realtime: Pusher y Laravel WebSockets
- Storage: local y AWS S3

## Estructura importante

Estas carpetas son las primeras que conviene entender:

- `app/`: logica de negocio, modelos, servicios, jobs, observers, mails y controllers
- `routes/web.php`: rutas web del CRM y vistas Blade
- `routes/api.php`: endpoints API consumidos por otros clientes
- `resources/views/`: vistas Blade
- `resources/js/`: componentes Vue integrados dentro del CRM
- `database/migrations/`: migraciones base del sistema
- `database/migrations/virtual_classroom/`: migraciones ligadas al dominio del aula virtual
- `database/migrations/master_class/`: migraciones del modulo de masterclasses
- `docs/DEPLOYMENT.md`: pipeline y despliegue a servidor

## Casos de uso que viven aqui

Dependiendo del modulo, aqui vas a encontrar logica para:

- Usuarios, roles, permisos y aprobaciones
- Cursos, clases, certificados y marketplace
- Pagos, ordenes y pasarelas
- Wallet, bonos y componentes de red / MLM
- Notificaciones, correos, chats y eventos en tiempo real

## Primer arranque en local

### Requisitos

- PHP 8.1+
- Composer 2+
- Node.js 14+ o 16+ recomendado
- NPM
- MySQL

### Instalacion

```bash
git clone https://github.com/dev-promolider/promolider.git
cd promolider
composer install
npm install
```

Duplica el archivo de entorno:

```bash
# Linux / macOS
cp .env.example .env

# PowerShell
Copy-Item .env.example .env
```

Luego genera la clave de Laravel:

```bash
php artisan key:generate
```

### Variables de entorno minimas

Abre `.env` y configura al menos:

```env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=promolider_local
DB_USERNAME=root
DB_PASSWORD=

# si vas a integrar con virtual_classroom en local
FRONTEND_APP_URL=http://localhost:8080/
MIX_FRONTEND_APP_URL=http://localhost:8080/
```

Nota: `FRONTEND_APP_URL` y `MIX_FRONTEND_APP_URL` suelen usarse para construir enlaces hacia el frontend externo. Si apuntas a `virtual_classroom`, normalmente conviene dejar la barra final.

Segun el modulo que vayas a tocar, tambien podrias necesitar credenciales para:

- AWS / S3
- Pusher / WebSockets
- Mailrelay o SMTP
- Openpay
- PayPal
- Mercado Pago
- OpenAI

## Base de datos

Este proyecto tiene migraciones separadas por dominios. Para evitar errores de dependencias, usa este orden:

```bash
php artisan migrate
php artisan migrate --path=database/migrations/virtual_classroom
php artisan migrate --path=database/migrations/master_class
php artisan db:seed
```

Si necesitas reiniciar localmente y volver a subir todo:

```bash
php artisan migrate:rollback --path=database/migrations/master_class
php artisan migrate:rollback --path=database/migrations/virtual_classroom
php artisan migrate:rollback
```

## Ejecutar el proyecto

```bash
php artisan storage:link
npm run dev
php artisan serve
```

Comandos utiles durante desarrollo:

```bash
# recompilacion continua del frontend integrado
npm run watch

# limpiar caches cuando cambias .env, rutas o config
php artisan optimize:clear

# procesar colas si estas probando trabajos async
php artisan queue:work
```

## Como pensar el frontend en este repo

En `promolider` el frontend no esta separado del backend. Conviene pensar el proyecto asi:

- Blade renderiza pantallas y layouts del CRM
- Vue agrega interactividad en componentes y modulos especificos
- Laravel expone las rutas, la seguridad y la logica de negocio

Si necesitas modificar una pantalla del CRM, probablemente tendras que revisar mas de una capa:

1. Ruta en `routes/web.php` o `routes/api.php`
2. Controller o Service en `app/`
3. Vista Blade o componente Vue en `resources/`
4. Migraciones o modelos si el cambio afecta datos

## Integracion con virtual_classroom

El repo `virtual_classroom` usa este backend como fuente de verdad:

- API base esperada: `PROMOLIDER_URL/api/v1`
- Autenticacion y reglas de negocio: viven aqui
- Datos de cursos, compras, cuenta y progreso: salen de este repo

Link directo al frontend separado:

- https://github.com/dev-promolider/virtual_classroom

## Recomendaciones para nuevos desarrolladores

- Empieza por leer `routes/`, `app/Http/Controllers/` y `app/Models/`
- No asumas que todo el frontend esta en otro repo: una parte importante vive aqui
- Revisa `docs/DEPLOYMENT.md` antes de tocar despliegues o GitHub Actions
- Si un cambio impacta al aula virtual, valida tambien si afecta al repo `virtual_classroom`

## Resumen rapido

Usa este repositorio cuando necesites trabajar en:

- Backend principal
- Base de datos
- APIs
- CRM interno
- Frontend integrado con Blade/Vue
- Integraciones y procesos del negocio

Usa `virtual_classroom` cuando necesites trabajar solo en la experiencia web del aula virtual.
