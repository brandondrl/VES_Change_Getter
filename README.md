# VES Change Getter

Un plugin para WordPress que obtiene tasas de cambio desde una API externa, las procesa y almacena en una base de datos con una arquitectura sencilla y moderna.

## Características

- Consulta automática de tasas de cambio desde la API externa
- Procesamiento y almacenamiento de datos en la base de datos
- Interfaz de administración moderna con Tailwind CSS
- API RESTful para integración con otros plugins
- Sistema de programación de tareas para actualizaciones periódicas

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
2. Sube el plugin a tu directorio `/wp-content/plugins/`
3. Activa el plugin a través del menú 'Plugins' en WordPress
4. Visita la página de administración del plugin bajo el menú 'VES Rates'

## Uso

### Interfaz de Administración

La interfaz de administración del plugin permite:

- Ver las tasas de cambio actuales
- Actualizar manualmente las tasas desde la API externa
- Ver el historial de tasas almacenadas
- Obtener información sobre los endpoints de la API

### API RESTful

El plugin expone los siguientes endpoints:

1. **Obtener la tasa más reciente**
   - Endpoint: `/wp-json/ves-change-getter/v1/latest`
   - Método: GET

2. **Obtener historial de tasas**
   - Endpoint: `/wp-json/ves-change-getter/v1/rates`
   - Método: GET
   - Parámetros opcionales:
     - `start_date`: Fecha inicial (formato YYYY-MM-DD)
     - `end_date`: Fecha final (formato YYYY-MM-DD)
     - `limit`: Número máximo de registros (por defecto: 100)

## Requerimientos

- WordPress 5.2 o superior
- PHP 7.2 o superior

## Licencia

Este plugin está licenciado bajo la GPL v2 o posterior.

## Créditos

Desarrollado por IDSI 