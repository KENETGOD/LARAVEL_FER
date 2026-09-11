# Laravel CRUD - API Usuarios

Proyecto Laravel con API CRUD de usuarios, usando las capas **Controller → Service → Repository**.

## Requisitos

- PHP ^8.2
- Composer
- MySQL 8.0+
- Node.js (para Vite)

## Instalación

```bash
# 1. Clonar el repositorio
git clone <repo-url>
cd Ense-ar-Laravel

# 2. Instalar dependencias de PHP
composer install

# 3. Copiar y configurar variables de entorno
cp .env.example .env
```

Editar `.env` y configurar la conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=tu_password
```

```bash
# 4. Generar key de la aplicación
php artisan key:generate

# 5. Crear la base de datos en MySQL
mysql -u root -p -e "CREATE DATABASE laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Ejecutar migraciones
php artisan migrate

# 7. (Opcional) Poblar con datos de prueba
php artisan db:seed

# 8. Instalar dependencias de Node y compilar assets
npm install
npm run build

# 9. Iniciar servidor de desarrollo
php artisan serve
```

## Endpoints API

| Método | URL                    | Descripción          |
|--------|------------------------|----------------------|
| GET    | /api/usuarios          | Listar usuarios      |
| POST   | /api/usuarios          | Crear usuario        |
| GET    | /api/usuarios/{id}     | Mostrar usuario      |
| PUT    | /api/usuarios/{id}     | Actualizar usuario   |
| PATCH  | /api/usuarios/{id}     | Actualizar parcial   |
| DELETE | /api/usuarios/{id}     | Eliminar usuario     |

### Ejemplo de creación (POST /api/usuarios)

```json
{
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "secret123"
}
```

## Respuestas de error

Los errores de la API se centralizan en el enum `app/Enums/ErrorCode.php`. Todos siguen este formato:

```json
{
    "error": "USER_NOT_FOUND",
    "message": "Usuario no encontrado"
}
```

Las validaciones agregan el campo `details` con los errores por campo:

```json
{
    "error": "VALIDATION_ERROR",
    "message": "Los datos enviados no son válidos",
    "details": {
        "email": ["El campo email debe ser un correo electrónico válido."]
    }
}
```

| Código HTTP | Error                    | Cuándo ocurre                        |
|-------------|--------------------------|--------------------------------------|
| 401         | `UNAUTHORIZED`           | No autenticado                       |
| 403         | `FORBIDDEN`              | Sin permisos para la acción          |
| 404         | `NOT_FOUND`              | Ruta o recurso inexistente           |
| 404         | `USER_NOT_FOUND`         | Usuario no encontrado                |
| 405         | `METHOD_NOT_ALLOWED`     | Método no permitido para la ruta     |
| 422         | `VALIDATION_ERROR`       | Datos enviados no válidos            |
| 500         | `INTERNAL_SERVER_ERROR`  | Error interno del servidor           |

## Comandos útiles

```bash
php artisan migrate              # Ejecutar migraciones
php artisan migrate:rollback     # Revertir última migración
php artisan db:seed              # Poblar base de datos
php artisan make:migration       # Crear migración
php artisan route:list           # Ver todas las rutas registradas
php artisan tinker               # Consola interactiva de Laravel
```
