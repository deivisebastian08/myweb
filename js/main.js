// COMENTARIO: Lógica para mostrar u ocultar el botón "Volver Arriba".

document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopButton = document.querySelector('.scroll-to-top');

    if (scrollToTopButton) {
        window.addEventListener('scroll', function() {
            // Si el usuario ha bajado más de 300 píxeles, muestra el botón.
            if (window.scrollY > 300) {
                scrollToTopButton.classList.add('visible');
            } else {
                // De lo contrario, lo oculta.
                scrollToTopButton.classList.remove('visible');
            }
        });
    }
});
