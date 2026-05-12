/*
 * Menu de Login - desativado na Home.
 * Esse arquivo existe para sobrescrever o path solicitado no layout: /assets/js/menu.js
 */
(function () {
  try {
    var path = window.location && window.location.pathname ? window.location.pathname : '';
    // Na home ("/" ou "/home"), não renderiza nada.
    if (path === '/' || path === '/home') return;
  } catch (e) {}
})();

