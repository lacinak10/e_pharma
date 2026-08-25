/* =====================================================================
   ePharma — comportements partagés
   Aucune dépendance : compte à rebours de vérification, tiroir de
   navigation, visionneuse d'ordonnance.
   ===================================================================== */
(function () {
    'use strict';

    /* ---- Compte à rebours de la vérification (5:00 → 0:00) ---------- */

    function formatClock(seconds) {
        var m = Math.floor(seconds / 60);
        var s = String(seconds % 60).padStart(2, '0');
        return m + ':' + s;
    }

    function initChronos() {
        var nodes = document.querySelectorAll('[data-ep-chrono]');
        if (!nodes.length) return;

        var timers = [];

        nodes.forEach(function (node) {
            var deadline = node.dataset.deadline;
            if (!deadline) return;

            var target = new Date(deadline).getTime();
            var duration = parseInt(node.dataset.duration || '300', 10);
            var value = node.querySelector('[data-ep-chrono-value]');
            var inline = node.querySelector('[data-ep-chrono-value-inline]');
            var bar = node.querySelector('[data-ep-chrono-bar]');
            var prefix = node.dataset.prefix || '';
            var reloadOnZero = node.hasAttribute('data-ep-chrono-reload');

            function tick() {
                var left = Math.max(0, Math.round((target - Date.now()) / 1000));

                if (value) value.textContent = formatClock(left);
                if (inline) inline.textContent = prefix + formatClock(left);
                if (bar) bar.style.width = Math.round((left / duration) * 100) + '%';

                // Côté manager le chrono passe en rouge sous 1:00.
                if (node.dataset.urgent === 'true') {
                    node.classList.toggle('ep-chrono--urgent', left <= 60);
                    node.classList.toggle('ep-chrono-inline--urgent', left <= 60);
                }

                if (left === 0) {
                    timers.forEach(clearInterval);
                    // Le verdict est attendu : on rafraîchit pour l'afficher.
                    if (reloadOnZero) {
                        window.setTimeout(function () { window.location.reload(); }, 4000);
                    }
                }
            }

            tick();
            timers.push(window.setInterval(tick, 1000));
        });
    }

    /* ---- Tiroir de navigation du back-office ------------------------ */

    function initDrawer() {
        var sidebar = document.querySelector('[data-ep-sidebar]');
        var toggles = document.querySelectorAll('[data-ep-sidebar-toggle]');
        if (!sidebar || !toggles.length) return;

        var scrim = null;

        function close() {
            sidebar.dataset.open = 'false';
            toggles.forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
            if (scrim) { scrim.remove(); scrim = null; }
        }

        function open() {
            sidebar.dataset.open = 'true';
            toggles.forEach(function (t) { t.setAttribute('aria-expanded', 'true'); });
            scrim = document.createElement('div');
            scrim.className = 'ep-sidebar-scrim';
            scrim.addEventListener('click', close);
            document.body.appendChild(scrim);
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                sidebar.dataset.open === 'true' ? close() : open();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') close();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) close();
        });
    }

    /* ---- Visionneuse d'ordonnance ----------------------------------- */

    function initViewers() {
        document.querySelectorAll('[data-ep-viewer]').forEach(function (viewer) {
            var image = viewer.querySelector('[data-ep-viewer-image]');
            if (!image) return;

            var zoom = 1;
            var angle = 0;

            function apply() {
                image.style.transform = 'scale(' + zoom + ') rotate(' + angle + 'deg)';
                image.style.transition = 'transform .15s ease';
            }

            viewer.querySelectorAll('[data-ep-zoom]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var action = button.dataset.epZoom;
                    if (action === 'in') zoom = Math.min(3, zoom + 0.25);
                    if (action === 'out') zoom = Math.max(0.5, zoom - 0.25);
                    if (action === 'rotate') angle = (angle + 90) % 360;
                    apply();
                });
            });
        });
    }

    /* ---- Rafraîchissement du suivi en direct ------------------------ */

    function initLiveRefresh() {
        var node = document.querySelector('[data-ep-live-refresh]');
        if (!node) return;

        var seconds = parseInt(node.dataset.epLiveRefresh || '30', 10);
        window.setInterval(function () {
            if (document.visibilityState === 'visible') window.location.reload();
        }, seconds * 1000);
    }

    function boot() {
        initChronos();
        initDrawer();
        initViewers();
        initLiveRefresh();
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', boot)
        : boot();
})();
