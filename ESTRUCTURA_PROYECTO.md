# 📁 ESTRUCTURA DEL PROYECTO - ANÁLISIS COMPLETO

## ✅ ARCHIVOS EN USO ACTIVO

### 🏠 **RAÍZ DEL PROYECTO** (`/myweb/`)

**✅ En Uso:**
- `index.php` - Página principal del sitio público
- `registro_visita.php` - Sistema automático de tracking de visitas
- `cuenta_usuario.php` - Perfil de usuario (vista pública)
- `perfil.php` - Perfil de usuario (alternativo)
- `articulos.php` - Vista de artículos/noticias públicas

**Carpetas Activas:**
- `css/` - Estilos del sitio público
- `js/` - JavaScript del sitio público
- `images/` - Imágenes del sitio público
- `uploads/` - Imágenes subidas (noticias, banners)
- `docs/` - Documentación del sistema
- `sql/` - Scripts SQL (tabla_visitas.sql, policies, etc.)

---

## 🔐 **PANEL ADMIN** (`/myweb/adm/`)

### ✅ **ARCHIVOS CORE EN USO**

**Autenticación:**
- ✅ `index.php` - Login (formulario)
- ✅ `login.php` - Procesar login
- ✅ `register.php` - Registro (formulario)
- ✅ `process_registration.php` - Procesar registro
- ✅ `logout.php` - Cerrar sesión
- ✅ `router.php` - Enrutador por roles
- ✅ `session_config.php` - Configuración de sesión
- ✅ `verificar_sesion.php` - Middleware de autenticación

**Gestión de Contenido (CRUD):**
- ✅ `usuarios_admin.php` - Gestión de usuarios
- ✅ `banners_admin.php` - Gestión de banners
- ✅ `noticias_admin.php` - Gestión de noticias
- ✅ `reportes_visitas.php` - Reportes y estadísticas

**Subida de Archivos:**
- ✅ `upload_imagen.php` - Subir imágenes (noticias)

**Diagnóstico/Testing:**
- ✅ `diagnostico_supabase.php` - Test de conexión a Supabase
- ✅ `test_registro.php` - Test de registro de usuarios

---

### ✅ **CARPETAS EN USO**

**`adm/script/`** - Scripts PHP backend:
- ✅ `Supabase.php` - Cliente REST de Supabase (CORE)
- ✅ `generax.php` - Generador de captcha
- ❓ Otros archivos (revisar si existen)

**`adm/css/`** - Estilos del panel:
- ✅ `admin-style.css` (v3.0) - Estilos responsive del panel
- ✅ `login-style.css` - Estilos del login/registro
- ✅ `bootstrap.min.css` - Bootstrap local (si existe)
- ❓ Otros archivos

**`adm/js/`** - JavaScript del panel:
- ✅ `admin-mobile.js` - Menú responsive
- ✅ `generax.js` - Validación de captcha
- ❓ Otros archivos

**`adm/roles/`** - Dashboards por rol:
- ✅ `super-admin/super-admin_dashboard.php` - Dashboard Super Admin (activo)
- ✅ `admin/admin_dashboard.php` - Dashboard Admin
- ✅ `editor/editor_dashboard.php` - Dashboard Editor
- ✅ `visualizador/visualizador_dashboard.php` - Dashboard Visualizador
- ✅ `lector-informes/lector-informes_dashboard.php` - Dashboard Lector
- ✅ `invitado-demo/invitado-demo_dashboard.php` - Dashboard Invitado
- ✅ `usuario-base/usuario-base_dashboard.php` - Dashboard Usuario Base

---

## ❌ ARCHIVOS LEGACY / DUPLICADOS (CANDIDATOS PARA ELIMINAR)

### **En `/myweb/adm/`:**

**❌ Duplicados de Dashboards:**
- ❌ `dashboard.php` - DUPLICADO (ahora está en `roles/super-admin/super-admin_dashboard.php`)
- ❌ `reader_dashboard.php` - LEGACY (reemplazado por sistema de roles)
- ❌ `viewer_dashboard.php` - LEGACY (reemplazado por sistema de roles)
- ❌ `view_dashboard.php` - LEGACY

**❌ Archivos de Vista Legacy:**
- ❌ `view_banners.php` - LEGACY (reemplazado por `banners_admin.php`)
- ❌ `view_news.php` - LEGACY (reemplazado por `noticias_admin.php`)

**❌ Gestión de Usuarios Duplicada:**
- ❌ `manage_users.php` - DUPLICADO (ahora usamos `usuarios_admin.php`)
- ❌ `process_user_action.php` - LEGACY
- ❌ `user.php` - LEGACY

**❌ Autenticación Legacy:**
- ❌ `funciones_autenticacion.php` - LEGACY (ya no se usa con Supabase)
- ❌ `cerrar_sesion.php` - DUPLICADO (usamos `logout.php`)

**❌ Procesamiento de Imágenes:**
- ❌ `procesar_imagen.php` - DUPLICADO (usamos `upload_imagen.php`)
- ❌ `upload_banner.php` - POSIBLE LEGACY (si banners usan upload_imagen.php)

**❌ Reportes Legacy:**
- ❌ `reports.php` - DUPLICADO (usamos `reportes_visitas.php`)

**❌ Cambio de Contraseña:**
- ❌ `cambiar_password.php` - SIN INTEGRAR (puede conservarse si planeas usarlo)

---

## 📊 RESUMEN DE LIMPIEZA

### **Archivos a ELIMINAR (13 archivos):**

```
/myweb/adm/dashboard.php
/myweb/adm/reader_dashboard.php
/myweb/adm/viewer_dashboard.php
/myweb/adm/view_dashboard.php
/myweb/adm/view_banners.php
/myweb/adm/view_news.php
/myweb/adm/manage_users.php
/myweb/adm/process_user_action.php
/myweb/adm/user.php
/myweb/adm/funciones_autenticacion.php
/myweb/adm/cerrar_sesion.php
/myweb/adm/procesar_imagen.php
/myweb/adm/reports.php
```

### **Archivos a REVISAR (pueden eliminarse):**

```
/myweb/adm/upload_banner.php (si no se usa para banners)
/myweb/adm/cambiar_password.php (si no planeas implementarlo)
```

---

## 📁 ESTRUCTURA LIMPIA FINAL

```
/myweb/
├── index.php ✅
├── registro_visita.php ✅
├── cuenta_usuario.php ✅
├── perfil.php ✅
├── articulos.php ✅
├── css/ ✅
├── js/ ✅
├── images/ ✅
├── uploads/ ✅
├── docs/ ✅
└── sql/ ✅

/myweb/adm/
├── index.php ✅ (Login)
├── login.php ✅
├── register.php ✅
├── process_registration.php ✅
├── logout.php ✅
├── router.php ✅
├── session_config.php ✅
├── verificar_sesion.php ✅
├── usuarios_admin.php ✅
├── banners_admin.php ✅
├── noticias_admin.php ✅
├── reportes_visitas.php ✅
├── upload_imagen.php ✅
├── diagnostico_supabase.php ✅
├── test_registro.php ✅
├── css/ ✅
│   ├── admin-style.css (v3.0)
│   └── login-style.css
├── js/ ✅
│   ├── admin-mobile.js
│   └── generax.js
├── script/ ✅
│   ├── Supabase.php (CORE)
│   └── generax.php
└── roles/ ✅
    ├── super-admin/
    │   └── super-admin_dashboard.php
    ├── admin/
    │   └── admin_dashboard.php
    ├── editor/
    │   └── editor_dashboard.php
    ├── visualizador/
    │   └── visualizador_dashboard.php
    ├── lector-informes/
    │   └── lector-informes_dashboard.php
    ├── invitado-demo/
    │   └── invitado-demo_dashboard.php
    └── usuario-base/
        └── usuario-base_dashboard.php
```

---

## 🎯 FUNCIONALIDAD POR VISTA DE ROL

### **Super Admin** (`roles/super-admin/`)
✅ Dashboard con estadísticas completas
✅ Acceso a: Usuarios, Banners, Noticias, Reportes, Sistema

### **Admin** (`roles/admin/`)
✅ Dashboard con estadísticas básicas
✅ Acceso a: Usuarios (sin cambiar roles), Banners, Noticias, Reportes

### **Editor** (`roles/editor/`)
✅ Dashboard enfocado en contenido
✅ Acceso a: Banners (crear/editar), Noticias (crear/editar/publicar)

### **Visualizador** (`roles/visualizador/`)
✅ Dashboard de solo lectura
✅ Acceso a: Ver reportes, ver estadísticas, ver contenido

### **Lector de Informes** (`roles/lector-informes/`)
✅ Dashboard enfocado en reportes
✅ Acceso a: Ver reportes, exportar datos

### **Invitado Demo** (`roles/invitado-demo/`)
✅ Dashboard limitado para demostración
✅ Acceso de solo lectura a módulos básicos

### **Usuario Base** (`roles/usuario-base/`)
✅ Dashboard personal básico
✅ Acceso a: Perfil personal, cuenta

---

## 💾 COMANDOS PARA LIMPIEZA

### **Windows (PowerShell)**
```powershell
# Eliminar archivos legacy
Remove-Item "c:\xampp\htdocs\myweb\adm\dashboard.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\reader_dashboard.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\viewer_dashboard.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\view_dashboard.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\view_banners.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\view_news.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\manage_users.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\process_user_action.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\user.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\funciones_autenticacion.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\cerrar_sesion.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\procesar_imagen.php"
Remove-Item "c:\xampp\htdocs\myweb\adm\reports.php"
```

---

## ✅ ESTADO DESPUÉS DE LIMPIEZA

**Archivos Activos**: ~30 archivos PHP principales
**Carpetas de Roles**: 7 dashboards (1 por rol)
**Archivos Eliminados**: ~13 archivos legacy/duplicados
**Reducción**: ~30% menos archivos

**Sistema Optimizado**:
- ✅ Sin duplicación de código
- ✅ Estructura clara por módulos
- ✅ Un dashboard por rol
- ✅ Sistema unificado con Supabase
- ✅ Responsive en todos los módulos

---

**Última revisión**: Octubre 2025  
**Versión del sistema**: 3.0  
**Estado**: Listo para producción
