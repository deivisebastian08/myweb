// ============================================================================
// APPLE EFFECTS JAVASCRIPT - Premium Interactions
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================================================
    // 1. PARALLAX SCROLLING EFFECT
    // ========================================================================
    const parallaxLayers = document.querySelectorAll('.parallax-layer');
    
    if (parallaxLayers.length > 0) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            
            parallaxLayers.forEach((layer, index) => {
                const speed = (index + 1) * 0.3;
                const yPos = -(scrolled * speed);
                layer.style.transform = `translateY(${yPos}px)`;
            });
        });
    }
    
    // ========================================================================
    // 2. TILT CARDS 3D EFFECT (Apple Card Style)
    // ========================================================================
    const tiltCards = document.querySelectorAll('.tilt-card');
    
    tiltCards.forEach(card => {
        card.addEventListener('mousemove', handleTilt);
        card.addEventListener('mouseleave', resetTilt);
    });
    
    function handleTilt(e) {
        const card = e.currentTarget;
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = ((y - centerY) / centerY) * -15;
        const rotateY = ((x - centerX) / centerX) * 15;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`;
        
        // Move shine effect
        const shine = card.querySelector('.tilt-card-shine');
        if (shine) {
            shine.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.4) 0%, transparent 50%)`;
            shine.style.opacity = '1';
        }
    }
    
    function resetTilt(e) {
        const card = e.currentTarget;
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
        
        const shine = card.querySelector('.tilt-card-shine');
        if (shine) {
            shine.style.opacity = '0';
        }
    }
    
    // ========================================================================
    // 3. SCROLL SNAP - Already handled by CSS
    // ========================================================================
    
    // ========================================================================
    // 4. MAGNETIC CARD EFFECT
    // ========================================================================
    const magneticCards = document.querySelectorAll('.magnetic-card');
    
    magneticCards.forEach(card => {
        const container = card.parentElement;
        
        container.addEventListener('mousemove', (e) => {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const deltaX = (x - centerX) / centerX;
            const deltaY = (y - centerY) / centerY;
            
            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
            
            if (distance < 1) {
                const strength = 30;
                const moveX = deltaX * strength;
                const moveY = deltaY * strength;
                
                card.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.05)`;
            }
        });
        
        container.addEventListener('mouseleave', () => {
            card.style.transform = 'translate(0, 0) scale(1)';
        });
    });
    
    // ========================================================================
    // 5. REVEAL ON SCROLL (Intersection Observer)
    // ========================================================================
    const revealItems = document.querySelectorAll('.reveal-item');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px'
    });
    
    revealItems.forEach(item => {
        revealObserver.observe(item);
    });
    
    // ========================================================================
    // SMOOTH SCROLL FOR ANCHORS
    // ========================================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // ========================================================================
    // PARALLAX MOUSE MOVE EFFECT (Hero Section)
    // ========================================================================
    const hero = document.querySelector('.apple-hero');
    
    if (hero) {
        hero.addEventListener('mousemove', (e) => {
            const { clientX, clientY } = e;
            const { innerWidth, innerHeight } = window;
            
            const xPercent = (clientX / innerWidth - 0.5) * 20;
            const yPercent = (clientY / innerHeight - 0.5) * 20;
            
            hero.style.backgroundPosition = `${50 + xPercent}% ${50 + yPercent}%`;
        });
    }
    
    // ========================================================================
    // SCROLL PROGRESS INDICATOR
    // ========================================================================
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const progress = (scrollTop / scrollHeight) * 100;
        progressBar.style.width = `${progress}%`;
    });
    
    // ========================================================================
    // FADE IN ON LOAD
    // ========================================================================
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease';
    
    window.addEventListener('load', () => {
        document.body.style.opacity = '1';
    });
    
    // ========================================================================
    // PERFORMANCE OPTIMIZATION: Disable effects on mobile if needed
    // ========================================================================
    const isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        // Disable complex effects on mobile for better performance
        tiltCards.forEach(card => {
            card.removeEventListener('mousemove', handleTilt);
            card.removeEventListener('mouseleave', resetTilt);
        });
    }
    
    console.log('🍎 Apple Effects Loaded Successfully');
});

// ============================================================================
// UTILITY: Debounce function for performance
// ============================================================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
