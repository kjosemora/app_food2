# App Food - Sistema POS para Restaurante

Sistema de Punto de Venta (POS) optimizado para restaurantes con soporte para múltiples roles de usuario y gestión completa de órdenes.

## 🚀 Características

- ✅ **Sistema de autenticación** con roles (Gerente, Mesero, Cocina)
- ✅ **Dashboard personalizado** según el rol del usuario
- ✅ **Gestión de órdenes** con estados en tiempo real
- ✅ **Vista de cocina** con actualización automática
- ✅ **Gestión de productos** con imágenes y precios duales (USD/BS)
- ✅ **Gestión de usuarios y clientes**
- ✅ **Configuración de tasa de cambio**
- ✅ **Reportes de ventas** y cuadre de caja
- ✅ **Diseño responsive** optimizado para móviles y tablets
- ✅ **Sistema de auditoría** para registro de acciones

## 📱 Optimización Móvil

El sistema está completamente optimizado para dispositivos móviles:
- Diseño responsive con Bootstrap 5
- Botones táctiles de tamaño adecuado (mínimo 44px)
- Tablas adaptativas con columnas ocultas en pantallas pequeñas
- Cards compactas para mejor visualización
- Navegación simplificada en móviles

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 5.7+
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **JavaScript:** Vanilla JS para funcionalidades AJAX

## 📋 Requisitos

- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador web moderno

## 🔧 Instalación

1. **Clonar o copiar el proyecto** en la carpeta `htdocs` de XAMPP:
   ```
   C:\xampp\htdocs\app_food\
   ```

2. **Crear la base de datos:**
   - Abrir phpMyAdmin (http://localhost/phpmyadmin)
   - Importar el archivo `bd.sql`
   - Esto creará la base de datos `pos_restaurant` con datos de ejemplo

3. **Configurar la conexión:**
   - Editar `config/database.php` si es necesario
   - Por defecto usa: host=localhost, user=root, password=''

4. **Acceder al sistema:**
   - URL: http://localhost/app_food/
   - Será redirigido al login

## 👥 Usuarios de Prueba

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@restaurant.com | admin123 | Gerente |
| mesero@restaurant.com | admin123 | Mesero |
| cocina@restaurant.com | admin123 | Cocina |

## 📁 Estructura del Proyecto

```
app_food/
├── ajax/                    # Endpoints AJAX
│   ├── cambiar_estado_orden.php
│   └── get_ordenes_cocina.php
├── assets/
│   └── css/
│       └── style.css       # Estilos personalizados
├── config/
│   ├── config.php          # Configuración general
│   └── database.php        # Conexión a BD
├── include/
│   ├── header.php          # Header principal
│   └── footer.php          # Footer principal
├── models/                 # Modelos de datos
│   ├── Configuracion.php
│   ├── Orden.php
│   ├── Producto.php
│   └── Usuario.php
├── views/                  # Vistas del sistema
│   ├── clientes/
│   ├── configuracion/
│   ├── ordenes/
│   ├── perfil/
│   ├── productos/
│   ├── reportes/
│   └── usuarios/
├── index.php              # Dashboard principal
├── login.php              # Página de login
├── logout.php             # Cerrar sesión
└── bd.sql                 # Script de base de datos
```

## 🎯 Funcionalidades por Rol

### Gerente
- Acceso completo al sistema
- Gestión de productos, usuarios y clientes
- Configuración del sistema (tasa de cambio)
- Reportes de ventas y cuadre de caja
- Visualización de todas las órdenes

### Mesero
- Crear y gestionar sus propias órdenes
- Ver estadísticas personales
- Gestión de clientes
- Acceso a la tasa de cambio actual

### Cocina
- Vista especializada de órdenes pendientes
- Cambiar estado de órdenes (Pendiente → En Preparación → Lista)
- Actualización automática cada 30 segundos
- Notificaciones de nuevas órdenes

## 🔄 Estados de Órdenes

1. **Pendiente** - Orden recién creada
2. **En Preparación** - Cocina está preparando
3. **Lista** - Orden lista para entregar
4. **Entregada** - Orden entregada al cliente
5. **Pagada** - Orden pagada y completada

## 🎨 Personalización

### Cambiar colores del tema:
Editar `assets/css/style.css` y modificar las variables de color.

### Cambiar nombre de la aplicación:
Editar `config/config.php` y cambiar la constante `APP_NAME`.

### Ajustar tasa de cambio:
Acceder como Gerente → Configuración → Actualizar tasa.

## 📝 Notas de Desarrollo

### Funcionalidades Completadas:
- ✅ Sistema de autenticación
- ✅ Dashboard con 3 roles
- ✅ Vista de cocina con AJAX
- ✅ Gestión de productos
- ✅ Gestión de usuarios
- ✅ Configuración básica
- ✅ Optimización móvil
- ✅ Sistema de auditoría

### Funcionalidades Pendientes:
- ⏳ Crear/editar órdenes (formulario completo)
- ⏳ Gestión completa de clientes
- ⏳ Sistema de pagos
- ⏳ Reportes con gráficos
- ⏳ Impresión de tickets
- ⏳ Backup automático

## 🐛 Solución de Problemas

### Error de conexión a BD:
- Verificar que MySQL esté corriendo en XAMPP
- Revisar credenciales en `config/database.php`

### Sesión expirada:
- Verificar que las cookies estén habilitadas
- Revisar configuración de sesión en PHP

### Estilos no cargan:
- Verificar la constante `BASE_URL` en `config/config.php`
- Debe ser: `http://localhost/app_food`

## 📄 Licencia

Este proyecto es de uso educativo y puede ser modificado libremente.

## 👨‍💻 Soporte

Para reportar problemas o sugerencias, contactar al desarrollador.

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2025
