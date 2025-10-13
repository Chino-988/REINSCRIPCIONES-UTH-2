import './bootstrap';
import './theme';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* ============================
   Registro de Service Worker
   ============================ */
(function registerServiceWorker() {
  // Solo navegadores que lo soportan y en HTTPS o localhost
  const supportsSW = 'serviceWorker' in navigator;
  const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

  if (!supportsSW || !isSecure) return;

  // Ruta del SW (asegúrate de que exista public/sw.js)
  const SW_URL = '/sw.js';

  // Espera a que cargue toda la página para evitar carreras con Vite/HMR
  window.addEventListener('load', () => {
    navigator.serviceWorker.register(SW_URL)
      .then((registration) => {
        console.log('[SW] Registrado: - app.js:27', registration.scope);

        // Detectar nuevas versiones del SW
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          if (!newWorker) return;

          newWorker.addEventListener('statechange', () => {
            // Cuando el nuevo SW termina de instalarse
            if (newWorker.state === 'installed') {
              // Si ya hay un SW controlando la página, entonces hay una actualización lista
              if (navigator.serviceWorker.controller) {
                console.log('[SW] Nueva versión disponible - app.js:39');

                // Opción rápida: preguntar y refrescar
                const wantsUpdate = confirm('Hay una actualización de la app. ¿Aplicar ahora?');
                if (wantsUpdate) {
                  // Pedimos al SW nuevo que entre en vigor ya
                  newWorker.postMessage({ type: 'SKIP_WAITING' });
                }
              } else {
                console.log('[SW] Contenido cacheado para uso offline. - app.js:48');
              }
            }
          });
        });

        // Si el SW activo cambia (después de skipWaiting), recargamos
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
          if (refreshing) return;
          refreshing = true;
          window.location.reload();
        });
      })
      .catch((err) => {
        console.error('[SW] Error al registrar: - app.js:63', err);
      });
  });
})();
