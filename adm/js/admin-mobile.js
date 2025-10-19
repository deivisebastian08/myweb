/**
 * JavaScript para funcionalidad responsive del panel admin
 * Maneja el menú hamburguesa en móviles
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Crear botón de menú móvil si no existe
    if (!document.querySelector('.mobile-menu-toggle')) {
        const menuBtn = document.createElement('button');
        menuBtn.className = 'mobile-menu-toggle';
        menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        menuBtn.setAttribute('aria-label', 'Toggle menu');
        document.body.insertBefore(menuBtn, document.body.firstChild);
    }
    
    // Crear overlay si no existe
    if (!document.querySelector('.sidebar-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.insertBefore(overlay, document.body.firstChild);
    }
    
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!menuToggle || !sidebar || !overlay) return;
    
    // Toggle sidebar al hacer click en el botón
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Cambiar icono
        const icon = this.querySelector('i');
        if (sidebar.classList.contains('active')) {
            icon.className = 'fas fa-times';
        } else {
            icon.className = 'fas fa-bars';
        }
    });
    
    // Cerrar sidebar al hacer click en el overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        const icon = menuToggle.querySelector('i');
        icon.className = 'fas fa-bars';
    });
    
    // Cerrar sidebar al hacer click en un enlace (solo en móvil)
    const navLinks = sidebar.querySelectorAll('.sidebar-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                const icon = menuToggle.querySelector('i');
                icon.className = 'fas fa-bars';
            }
        });
    });
    
    // Cerrar sidebar al presionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            const icon = menuToggle.querySelector('i');
            icon.className = 'fas fa-bars';
        }
    });
    
    // Ajustar en cambio de orientación
    window.addEventListener('orientationchange', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            const icon = menuToggle.querySelector('i');
            icon.className = 'fas fa-bars';
        }
    });
    
});
