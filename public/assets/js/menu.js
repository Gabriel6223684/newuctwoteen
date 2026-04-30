/* ===================================
   Menu Minimalista - Vanilla JS
   Combina com Bootstrap para animações
   =================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Seta animated no dropdown toggle
    document.querySelectorAll('.nav-link.dropdown-toggle').forEach(function(toggle) {
        toggle.addEventListener('show.bs.dropdown', function() {
            this.setAttribute('aria-expanded', 'true');
        });
        toggle.addEventListener('hide.bs.dropdown', function() {
            this.setAttribute('aria-expanded', 'false');
        });
    });
});
