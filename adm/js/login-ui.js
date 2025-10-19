// Modern-Login UI enhancements
// - Password visibility toggle
// - Background particles

(function () {
  // Password toggle for any input group
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      // Expect the previous sibling to be the input[type=password]
      const input = this.previousElementSibling;
      if (!input || input.tagName !== 'INPUT') return;
      const isText = input.getAttribute('type') === 'text';
      input.setAttribute('type', isText ? 'password' : 'text');
      this.classList.toggle('active', !isText);
      const icon = this.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      }
    });
  });

  // Particles
  const particlesRoot = document.getElementById('particles');
  if (particlesRoot) {
    const COUNT = 24;
    for (let i = 0; i < COUNT; i++) {
      const p = document.createElement('span');
      p.className = 'particle';
      p.style.left = Math.random() * 100 + 'vw';
      p.style.animationDelay = (Math.random() * 6).toFixed(2) + 's';
      p.style.animationDuration = (6 + Math.random() * 8).toFixed(2) + 's';
      p.style.opacity = (0.25 + Math.random() * 0.35).toFixed(2);
      particlesRoot.appendChild(p);
    }
  }
})();
