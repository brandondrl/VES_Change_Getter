# VES Change Getter

Un plugin de WordPress para obtener y mostrar tasas de cambio del Bolívar Soberano (VES) contra el Dólar (USD).

## Características

- Obtiene tasas de cambio desde una API externa
- Almacena las tasas en la base de datos de WordPress
- Proporciona endpoints REST para acceder a los datos
- Incluye un endpoint seguro para actualizaciones automáticas
- Interfaz de administración para visualizar y gestionar las tasas

## Estructura del Plugin

```
ves-change-getter/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── tailwind.min.css
│   └── js/
│       └── admin.js
├── includes/
│   ├── Admin/
│   │   └── AdminPage.php
│   ├── API/
│   │   └── APIEndpoint.php
│   ├── Core/
│   │   ├── Activator.php
│   │   ├── Deactivator.php
│   │   ├── Loader.php
│   │   └── Main.php
│   └── Models/
│       └── RatesModel.php
├── views/
│   └── admin/
│       └── main.php
├── README.md
└── ves-change-getter.php
```

## Instalación

1. Descarga el plugin
2. Sube el plugin a la carpeta `/wp-content/plugins/` de tu instalación de WordPress
3. Activa el plugin desde el menú de plugins de WordPress

## Configuración

Para utilizar el endpoint seguro de actualización automática, debes configurar las siguientes variables en tu archivo `wp-config.php`:

```php
// Configuración para VES Change Getter API
define('VES_API_ALLOWED_IP', 'xxx.xxx.xxx.xxx'); // Reemplaza con la IP del servidor
define('VES_API_SECRET_TOKEN', 'xxxxxxxxxxxxxxxxxxxxxxxx'); // Reemplaza con un token seguro
```

## Uso

### Endpoints Disponibles

1. **Obtener última tasa**
   ```
   GET /wp-json/ves-change-getter/v1/latest
   ```

2. **Obtener tasas por rango de fechas**
   ```
   GET /wp-json/ves-change-getter/v1/rates?start_date=2024-01-01&end_date=2024-01-31
   ```

3. **Actualización segura de tasas (para uso con cronjob)**
   ```
   GET /wp-json/ves-change-getter/v1/refresh-rates?token=TU_TOKEN_SECRETO
   ```

### Configuración del Cronjob

Para configurar la actualización automática de tasas, puedes usar el siguiente comando en tu cronjob:

```bash
# Actualizar tasas cada hora
0 * * * * curl "https://tudominio.com/wp-json/ves-change-getter/v1/refresh-rates?token=TU_TOKEN_SECRETO" > /dev/null 2>&1
```

Asegúrate de:
1. Reemplazar `TU_TOKEN_SECRETO` con el valor definido en `VES_API_SECRET_TOKEN`
2. Verificar que la IP del servidor coincida con `VES_API_ALLOWED_IP`
3. Ajustar la frecuencia del cronjob según tus necesidades

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- MySQL 5.6 o superior

## Licencia

Este plugin está licenciado bajo la Licencia Pública General de GNU v2 o posterior.

## Créditos

Desarrollado por IDSI 