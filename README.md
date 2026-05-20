<div align="center">

# 🍵 InfuDiana

**Tienda online de infusiones y tés — Práctica de Navidad**

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

*Proyecto de práctica desarrollado durante las "vacaciones de Navidad" para la asignatura de Desarrollo Web en Entorno Servidor.*

</div>

---

## ¿Qué es InfuDiana?

InfuDiana es un sistema de **e-commerce completo** especializado en la venta de infusiones y tés. Desarrollado íntegramente en PHP puro sin frameworks, implementa desde cero todas las capas de una aplicación web real: autenticación, control de acceso por roles, gestión de productos, carrito de compras e historial de pedidos.

---

## Funcionalidades principales

### Para clientes (rol Comprador)
- Explorar el catálogo de infusiones con filtros por nombre y categoría
- Ver el detalle de cada producto (precio, fabricante, stock, IVA)
- Agregar productos al carrito y gestionar cantidades
- Finalizar la compra eligiendo método de pago
- Consultar el historial completo de compras realizadas
- Editar su perfil y cambiar su contraseña

### Para el equipo (rol Administrativo)
- Todo lo anterior, más:
- Crear, editar y borrar productos del catálogo
- Exportar el listado de productos a un fichero `.txt`

### Para administradores (rol Administrador)
- Gestión completa de usuarios: crear, editar, ver detalle y borrado lógico
- CRUD completo de productos

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8+ (sin framework) |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML5 · CSS3 · JavaScript vanilla |
| Sesiones | `$_SESSION` nativo de PHP |
| Acceso a BD | MySQLi |
| Autenticación | Sistema ACL propio con roles y permisos |

---

## Estructura del proyecto

```
InfuDiana-main/
│
├── cabecera.php                      ← Bootstrap: carga clases, inicia sesión y BD
├── index.php                         ← Portada pública con listado de productos
│
├── aplicacion/
│   ├── acceso/
│   │   ├── login.php                 ← Formulario de identificación
│   │   ├── loggout.php               ← Cierre de sesión
│   │   └── modificarMiPerfil.php     ← Edición del perfil del usuario logueado
│   │
│   ├── config/
│   │   └── acceso_bd.php             ← Credenciales de conexión a la base de datos
│   │
│   ├── plantilla/
│   │   └── plantilla.php             ← Plantilla HTML compartida (cabecera y pie)
│   │
│   ├── productos/
│   │   ├── listaProductos.php        ← Catálogo filtrable
│   │   ├── verProducto.php           ← Detalle de producto
│   │   ├── nuevoProducto.php         ← Alta de producto (perm. 9)
│   │   ├── modificarProducto.php     ← Edición de producto (perm. 9)
│   │   ├── borrarProducto.php        ← Borrado lógico de producto (perm. 9)
│   │   ├── carrito.php               ← Gestión del carrito y proceso de pago
│   │   ├── misCompras.php            ← Historial de pedidos del usuario (perm. 8)
│   │   └── descargar.php             ← Exportar catálogo a TXT (perm. 9)
│   │
│   └── usuarios/
│       ├── index.php                 ← Listado de usuarios (perm. 10)
│       ├── verUsuario.php            ← Detalle de usuario (perm. 10)
│       ├── nuevoUsuario.php          ← Alta de usuario (perm. 10)
│       ├── modificarUsuario.php      ← Edición de usuario (perm. 10)
│       └── borrarUsuario.php         ← Borrado lógico de usuario (perm. 10)
│
├── scripts/
│   ├── clases/
│   │   ├── Acceso.php                ← Gestiona la sesión del usuario activo
│   │   ├── ACLBase.php               ← Clase abstracta: interfaz del sistema ACL
│   │   └── ACLBD.php                 ← Implementación del ACL contra la base de datos
│   │
│   └── librerias/
│       └── validacion.php            ← Funciones de validación (enteros, fechas, etc.)
│
├── estilos/
│   └── base.css                      ← Hoja de estilos principal con variables CSS
│
├── imagenes/
│   └── fotos/                        ← Fotos de perfil de los usuarios
│
├── img/
│   ├── IconoLogo.png~                ← Logo de la tienda
│   ├── carro-de-la-compra.png~       ← Icono del carrito
│   ├── 16x16/ · 24x24/               ← Iconos de acciones (ver, editar, borrar)
│   └── productos/                    ← Imágenes de los productos
│
└── DatosCargarConFich.txt            ← Datos de ejemplo para poblar la BD
```

---

## Base de datos

El nombre de la base de datos es **`tiendaproductos`**. A continuación se muestran las tablas principales y su función.

### `usuarios` — Datos personales
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `cod_usuario` | INT PK | Identificador único |
| `nick` | VARCHAR(50) | Nombre de usuario único |
| `nombre` | VARCHAR(50) | Nombre completo |
| `nif` | VARCHAR(10) | NIF/DNI |
| `direccion` | VARCHAR(100) | Dirección postal |
| `poblacion` | VARCHAR(50) | Población |
| `provincia` | VARCHAR(50) | Provincia |
| `codigo_postal` | VARCHAR(5) | Código postal |
| `fecha_nacimiento` | DATE | Fecha de nacimiento |
| `foto` | VARCHAR(100) | Ruta de la foto de perfil |
| `borrado` | BOOLEAN | Borrado lógico (0 = activo) |

### `acl_usuarios` — Credenciales y roles
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `cod_usuario` | INT PK | Referencia a `usuarios` |
| `nick` | VARCHAR(50) | Nombre de usuario |
| `contrasenia` | VARCHAR(100) | Hash MD5 con prefijo interno |
| `cod_rol` | INT FK | Rol asignado |
| `borrado` | BOOLEAN | Borrado lógico |

### `acl_roles` — Roles y permisos
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `cod_acl_role` | INT PK AI | Identificador del rol |
| `nombre` | VARCHAR(30) | Nombre del rol |
| `perm1`...`perm10` | TINYINT(1) | Permisos individuales (0/1) |

### `productos` — Catálogo
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `cod_producto` | INT PK | Identificador único |
| `nombre` | VARCHAR(50) | Nombre del producto |
| `fabricante` | VARCHAR(50) | Fabricante |
| `cod_categoria` | INT FK | Categoría |
| `fecha_alta` | DATE | Fecha de registro |
| `unidades` | INT | Stock disponible |
| `precio_base` | DECIMAL(10,2) | Precio sin IVA |
| `iva` | INT | Porcentaje de IVA (ej: 21) |
| `precio_venta` | DECIMAL(10,2) | Precio final de venta |
| `foto` | VARCHAR(100) | Ruta de la imagen |
| `borrado` | BOOLEAN | Borrado lógico |

### `compras` y `compra_lineas` — Pedidos
`compras` almacena la cabecera de cada pedido (fecha, importe total, método de pago). `compra_lineas` guarda cada línea del pedido con el producto, cantidad, precio unitario e IVA aplicado.

> Existen también vistas (`cons_productos`, `cons_compras`, `cons_compra_lineas`) que simplifican las consultas JOIN más frecuentes.

---

## Sistema de roles y permisos

| Rol | Perm 8 | Perm 9 | Perm 10 | Descripción |
|-----|:------:|:------:|:-------:|-------------|
| **Comprador** | ✅ | ❌ | ❌ | Puede comprar y ver su historial |
| **Administrativo** | ✅ | ✅ | ❌ | Comprar + gestionar productos |
| **Administrador** | ❌ | ✅ | ✅ | Gestionar productos y usuarios |

| Permiso | Acceso que concede |
|---------|--------------------|
| **8** | Carrito, finalizar compra, ver mis compras |
| **9** | CRUD de productos, exportar catálogo |
| **10** | CRUD de usuarios |

---

## Instalación y puesta en marcha

### Requisitos
- PHP 8.0 o superior
- MySQL 5.7 / MariaDB 10.3 o superior
- Servidor web (Apache con XAMPP, Laragon, etc.)

### Pasos

**1. Clona o copia el proyecto** en la carpeta pública de tu servidor:
```bash
# Ejemplo con XAMPP en Windows
C:\xampp\htdocs\InfuDiana\
```

**2. Crea la base de datos** en MySQL:
```sql
CREATE DATABASE tiendaproductos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**3. Ajusta las credenciales** en `aplicacion/config/acceso_bd.php`:
```php
$servidor  = "localhost";
$usuario   = "root";
$contrasenia = "tu_contraseña";
$base_datos = "tiendaproductos";
```

**4. Accede a la aplicación** desde el navegador:
```
http://localhost/InfuDiana/
```

Al entrar por primera vez, el sistema crea automáticamente los roles y usuarios de prueba gracias al constructor de `ACLBD`.

---

## Usuarios de prueba

| Usuario | Contraseña | Rol | Puede |
|---------|-----------|-----|-------|
| `diana` | `1234` | Administrador | Gestionar productos y usuarios |
| `rebeca` | `1234` | Administrativo | Comprar + gestionar productos |
| `lucia` | `1234` | Comprador | Comprar y ver su historial |

> Las contraseñas se almacenan con un hash MD5 interno. Cámbialas en producción.

---

## Cómo funciona el carrito

El carrito se gestiona completamente con la sesión de PHP (`$_SESSION["carrito"]`). Cada producto añadido se guarda como un objeto con nombre y unidades. Al finalizar la compra, el sistema:

1. Calcula el importe base, el IVA y el total
2. Pide el método de pago (tarjeta, transferencia, etc.)
3. Inserta una fila en `compras` y una fila por cada producto en `compra_lineas`
4. Vacía el carrito de la sesión

---

## Datos de ejemplo

El archivo `DatosCargarConFich.txt` incluye productos reales para poblar la base de datos:
- **Tila**, **Manzanilla**, **Hinojo**, **Valeriana**
- Fabricantes: Hornimans, Pompadour
- Con precios, stock y categorías ya definidos

---

<div align="center">

Desarrollado con mucho té ☕ — Práctica de Navidad · 2ºDAW

</div>
