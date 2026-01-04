(function () {
  // Small, self-contained eye-following module for auth pages
  // Runs on DOMContentLoaded, looks for #auth-left and operates only if present.
  function init() {
    const container = document.getElementById('auth-left');
    if (!container) return;

    // Disable on touch / coarse pointers and respect reduced motion
    if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const eyes = Array.from(container.querySelectorAll('.eye'));
    const pupils = Array.from(container.querySelectorAll('.pupil'));
    if (!pupils.length) return;

    // Debugging: confirm module initialization (and guard against exceptions)
    try { console.info('[auth-eyes] init, pupils=', pupils.length, container); } catch(e) {}

    // Global error hooks to capture runtime errors potentially caused by CSP or third-party scripts
    try {
      window.addEventListener('error', function (ev) { console.error('[auth-eyes] window error:', ev && (ev.error || ev.message) ); });
      window.addEventListener('unhandledrejection', function (ev) { console.error('[auth-eyes] unhandledrejection:', ev.reason); });
    } catch(e) { /* ignore if listener cannot be attached */ }

    function updateFrame() {
      let needContinue = false;
      for (let i = 0; i < pupils.length; i++) {
        const tx = targets[i].x, ty = targets[i].y;
        const nx = currents[i].x + (tx - currents[i].x) * LERP;
        const ny = currents[i].y + (ty - currents[i].y) * LERP;
        currents[i].x = Math.abs(nx) < 0.01 ? 0 : nx;
        currents[i].y = Math.abs(ny) < 0.01 ? 0 : ny;
        pupils[i].style.transform = `translate(${currents[i].x}px, ${currents[i].y}px)`;
        if (Math.abs(tx - currents[i].x) > 0.5 || Math.abs(ty - currents[i].y) > 0.5) {
          needContinue = true;
        }
      }
      if (needContinue) {
        rafId = requestAnimationFrame(updateFrame);
        running = true;
      } else {
        running = false;
        rafId = null;
      }
    }

    function computeTargets(clientX, clientY) {
      const rect = container.getBoundingClientRect();
      const x = clientX - rect.left;
      const y = clientY - rect.top;
      eyes.forEach((eye, i) => {
        const eyeBox = eye.getBoundingClientRect();
        const cx = (eyeBox.left + eyeBox.right) / 2 - rect.left;
        const cy = (eyeBox.top + eyeBox.bottom) / 2 - rect.top;
        const dx = x - cx, dy = y - cy;
        const angle = Math.atan2(dy, dx);
        const maxDist = Math.min(Math.hypot(dx, dy) * SCALE, CAP);
        targets[i].x = Math.cos(angle) * maxDist;
        targets[i].y = Math.sin(angle) * maxDist;
      });
      if (!running) { rafId = requestAnimationFrame(updateFrame); running = true; }
    }

    function onMove(e) { computeTargets(e.clientX, e.clientY); }
    container.addEventListener('mousemove', onMove);
    container.addEventListener('mouseleave', () => {
      pupils.forEach((p, i) => { targets[i].x = 0; targets[i].y = 0; });
      if (!running) { rafId = requestAnimationFrame(updateFrame); running = true; }
    });

    // Fallback: some elements may capture pointer events; also listen globally and only compute when inside rect
    document.addEventListener('mousemove', function (e) {
      const rect = container.getBoundingClientRect();
      if (e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom) {
        // Debug occasional coordinates (throttled via simple flag)
        if (!this._authEyesLast || (Date.now() - this._authEyesLast) > 120) {
          try { console.debug('[auth-eyes] pointer', e.clientX, e.clientY); } catch (err) {}
          this._authEyesLast = Date.now();
        }
        computeTargets(e.clientX, e.clientY);
      }
    });
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})();