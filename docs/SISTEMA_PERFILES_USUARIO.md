# 👤 SISTEMA DE PERFILES DE USUARIO

## ✅ IMPLEMENTACIÓN COMPLETA

Todos los usuarios ahora pueden ver su perfil personal con estadísticas, información y accesos rápidos.

---

## 🎯 **CARACTERÍSTICAS DEL PERFIL**

### **1. 📊 Estadísticas Personales**

Cada usuario ve:
- **Artículos Creados** - Total de noticias que ha creado
- **Artículos Publicados** - Noticias con estado "publicado"
- **Vistas Totales** - Suma de vistas de todos sus artículos

### **2. 📋 Información Personal**

- ✅ Nombre completo
- ✅ Correo electrónico
- ✅ Rol de usuario
- ✅ Estado de la cuenta (Activo/Inactivo)
- ✅ Fecha de registro completa

### **3. 🔗 Accesos Rápidos**

Botones directos a:
- **Mi Panel de Control** - Dashboard según rol
- **Mis Artículos** - (solo si tiene artículos creados)
- **Ir al Inicio** - Página principal del sitio
- **Cerrar Sesión** - Logout

### **4. 🎨 Diseño Moderno**

- **Avatar circular** con inicial del nombre
- **Header con gradiente** morado/rosa
- **Tarjetas estadísticas** con hover effect
- **Diseño responsive** mobile-first
- **Colores profesionales** consistentes con el sitio

---

## 📍 **DÓNDE ACCEDER AL PERFIL**

### **Opción 1: Desde cualquier Dashboard**
1. Inicia sesión en el panel admin
2. Haz clic en tu nombre (esquina inferior izquierda del sidebar)
3. Selecciona "👤 Mi Perfil"

### **Opción 2: Desde el sitio público**
1. Navbar superior → "Mi Perfil"
2. O directamente: `http://localhost/myweb/perfil.php`

### **Opción 3: URL Directa**
```
http://localhost/myweb/perfil.php
```

---

## 🔐 **SEGURIDAD**

### **Verificaciones implementadas:**

1. **Sesión requerida:**
   - Si no hay sesión activa → Redirige al login
   
2. **Usuario existe en BD:**
   - Verifica que el usuario existe en Supabase
   - Si fue eliminado → Cierra sesión automáticamente

3. **Datos protegidos:**
   - Todos los datos escapados con `htmlspecialchars()`
   - Solo muestra datos del usuario logueado

---

## 📊 **ROLES Y ACCESO**

### **Todos los roles tienen acceso:**

✅ **Super Admin** - Ve todo + estadísticas completas  
✅ **Admin** - Ve todo + estadísticas completas  
✅ **Editor** - Ve sus artículos + estadísticas propias  
✅ **Visualizador** - Ve su perfil (sin artículos)  
✅ **Lector de Informes** - Ve su perfil (sin artículos)  
✅ **Usuario Público** - Ve su perfil básico  
✅ **Invitado Demo** - Ve su perfil básico  

---

## 📁 **ARCHIVO ACTUALIZADO**

### **`perfil.php`**

**Ubicación:** `c:\xampp\htdocs\myweb\perfil.php`

**Funcionalidades:**
- ✅ Carga datos del usuario desde Supabase
- ✅ Obtiene nombre del rol
- ✅ Calcula estadísticas de artículos
- ✅ Diseño moderno responsive
- ✅ Integración con navbar del sitio
- ✅ Links a dashboards y funciones

---

## 🔗 **ENLACES AGREGADOS EN DASHBOARDS**

Todos los dashboards ahora tienen el enlace "Mi Perfil" en el dropdown del usuario:

✅ **Super Admin Dashboard**  
✅ **Admin Dashboard**  
✅ **Editor Dashboard**  
✅ **Visualizador Dashboard**  
✅ **Lector de Informes Dashboard**  
✅ **Usuario Base Dashboard**  
✅ **Invitado Demo Dashboard**

**Ubicación del enlace:**
```
Sidebar → Footer → Click en nombre de usuario → "👤 Mi Perfil"
```

---

## 🎨 **SECCIONES DEL PERFIL**

### **1. Header con Avatar**
```
- Avatar circular con inicial del nombre
- Fondo con gradiente morado/rosa
- Nombre del usuario
- Badge con rol
- Fecha de miembro
```

### **2. Tarjetas de Estadísticas**
```
┌─────────────────────┐ ┌─────────────────────┐ ┌─────────────────────┐
│  📰                 │ │  ✅                 │ │  👁️                │
│  Artículos Creados  │ │  Art. Publicados    │ │  Vistas Totales     │
│        5            │ │        3            │ │      1,234          │
└─────────────────────┘ └─────────────────────┘ └─────────────────────┘
```

### **3. Información Personal**
```
┌────────────────────────────────────┐
│  📋 Información Personal           │
├────────────────────────────────────┤
│  Nombre: Juan Pérez               │
│  Email: juan@example.com          │
│  Rol: Editor                      │
│  Estado: ✅ Activo                │
│  Registro: 15 de Octubre de 2025 │
└────────────────────────────────────┘
```

### **4. Accesos Rápidos**
```
┌────────────────────────┐
│  🔗 Accesos Rápidos    │
├────────────────────────┤
│  ▶ Mi Panel de Control │
│  ▶ Mis Artículos       │
│  ▶ Ir al Inicio        │
│  ────────────────      │
│  ❌ Cerrar Sesión      │
└────────────────────────┘
```

---

## 💡 **DATOS MOSTRADOS POR ROL**

### **Super Admin / Admin:**
- ✅ Todas las estadísticas
- ✅ Total de artículos creados por ellos
- ✅ Total de artículos publicados
- ✅ Suma de todas las vistas de sus artículos

### **Editor:**
- ✅ Solo sus artículos
- ✅ Artículos que ha creado
- ✅ Artículos que ha publicado
- ✅ Vistas de sus artículos

### **Otros Roles (Visualizador, Lector, etc.):**
- ✅ Información personal completa
- ✅ Estadísticas en 0 (no crean artículos)
- ✅ Accesos rápidos adaptados a su rol

---

## 🔄 **FLUJO DE NAVEGACIÓN**

```
1. Usuario hace login
   ↓
2. Entra a su dashboard
   ↓
3. Click en su nombre (sidebar footer)
   ↓
4. Aparece dropdown con opciones:
   - 👤 Mi Perfil
   - 🌐 Ver Sitio Web
   - ────────
   - 🚪 Cerrar Sesión
   ↓
5. Click en "Mi Perfil"
   ↓
6. Carga perfil.php con:
   - Datos desde Supabase
   - Estadísticas calculadas
   - Accesos personalizados
```

---

## 📱 **RESPONSIVE DESIGN**

### **Desktop (> 992px)**
- Estadísticas en 3 columnas
- Información y accesos lado a lado
- Avatar grande (150px)

### **Tablet (768px - 991px)**
- Estadísticas en 2 columnas
- Secciones apiladas
- Avatar mediano

### **Mobile (< 768px)**
- Estadísticas en 1 columna
- Todo apilado verticalmente
- Avatar responsive
- Texto ajustado

---

## 🎯 **ESTADÍSTICAS CALCULADAS**

### **Lógica de cálculo:**

```php
// Obtener noticias del usuario
$noticiasUsuario = $sb->from('noticias', [
    'select' => 'id,estado,vistas',
    'autor_id' => 'eq.' . $user_id
]);

// Contar
$noticias_creadas = count($noticiasUsuario);
$noticias_publicadas = count donde estado === 'publicado';
$total_vistas = suma de todas las vistas;
```

---

## ✨ **CARACTERÍSTICAS ESPECIALES**

### **1. Condicionales Inteligentes**

Si el usuario tiene artículos creados:
```php
<?php if ($estadisticas['noticias_creadas'] > 0): ?>
    <a href="adm/noticias_admin_editorial.php" class="btn btn-outline-success">
        <i class="fas fa-newspaper"></i> Mis Artículos
    </a>
<?php endif; ?>
```

### **2. Badge de Estado Dinámico**

Color según estado:
- **Verde** - Cuenta activa
- **Amarillo** - Cuenta inactiva

### **3. Formato de Fechas**

```php
// Fecha completa
<?php echo date('d \d\e F \d\e Y, H:i', strtotime($usuario['created_at'])); ?>
// Resultado: 15 de Octubre de 2025, 14:30

// Fecha simple
<?php echo date('F Y', strtotime($usuario['created_at'])); ?>
// Resultado: Octubre 2025
```

---

## 🔧 **PERSONALIZACIÓN**

### **Cambiar colores del avatar:**
```css
.profile-avatar {
    background: white;  /* Fondo del avatar */
    color: #667eea;     /* Color de la letra */
}
```

### **Cambiar gradiente del header:**
```css
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* Cambia por tus colores */
}
```

### **Ajustar tamaño de estadísticas:**
```css
.stat-number {
    font-size: 2.5rem;  /* Cambia el tamaño */
}
```

---

## 📋 **CHECKLIST DE VERIFICACIÓN**

- [ ] Perfil.php carga correctamente
- [ ] Estadísticas se calculan bien
- [ ] Avatar muestra inicial correcta
- [ ] Rol se muestra correctamente
- [ ] Links de accesos rápidos funcionan
- [ ] Botón "Cerrar Sesión" funciona
- [ ] Responsive funciona en mobile
- [ ] Todos los dashboards tienen el enlace
- [ ] Dropdown en sidebar funciona
- [ ] Redirección si no hay sesión

---

## 🎉 **RESULTADO FINAL**

Cada usuario ahora tiene:
- ✅ Perfil personal completo
- ✅ Estadísticas de actividad
- ✅ Información detallada
- ✅ Accesos rápidos
- ✅ Diseño moderno y profesional
- ✅ Accesible desde todos los dashboards

---

**¡Sistema de perfiles completado!** 👤✨

**Última actualización:** Octubre 2025  
**Versión:** 1.0  
**Estado:** Implementado ✅
