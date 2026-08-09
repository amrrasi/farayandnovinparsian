/* ==================================================================
   products.js
   Rewritten for: true circular orbit motion, lower CPU/GPU cost on
   low-end devices, and simple event wiring.
   ================================================================== */

/* ------------------------------------------------------------------
   0. Orbit rings — radius is stored as a FRACTION of the stage's own
      half-width (not a fixed px value), so it is recomputed from the
      live, rendered size of #orbitStage. That's what keeps every
      chip on a true circle and safely inside the visible rings at
      every breakpoint, instead of overflowing a stage that CSS
      shrinks on smaller screens.
      Fractions below match the percentage sizes of .orbit-ring-1..5
      in CSS (radius = diameter% / 2) — keep both in sync.
   ------------------------------------------------------------------ */
const ORBIT_RINGS = [
    { rFraction: 0.386, duration: 16 },
    { rFraction: 0.636, duration: 26 },
    { rFraction: 0.886, duration: 38 },
    { rFraction: 1.182, duration: 48 },
    { rFraction: 1.432, duration: 60 },
];

/**
 * Builds the orbiting category chips using pure CSS transforms
 * (no per-item <style> injection, no JS animation loop).
 *
 * How the circle works:
 *  - `.orbit-item` sits dead-center on the stage (0×0 box) and spins
 *    a full 360° via CSS animation — this alone traces a perfect
 *    circle for anything positioned at a fixed offset from it.
 *  - `.orbit-chip` inside it is offset by `--r` (translateX) and
 *    spins the same duration in reverse, cancelling the parent's
 *    rotation so the chip's text always stays upright.
 *  - Items on the same ring are spread evenly by giving each a
 *    negative animation-delay of a fraction of the duration, which
 *    starts them at different points on the same circular path
 *    instead of stacking them all at angle 0.
 *  - Rings whose guide circle is hidden at the current breakpoint
 *    (see CSS `display: none` on .orbit-ring-4/5 etc.) are skipped
 *    so chips never render outside what's visible on the ring.
 */
function buildOrbit() {
    const stage = document.getElementById('orbitStage');
    if (!stage || typeof MENU_DATA === 'undefined' || !MENU_DATA.length) return;

    stage.querySelectorAll('.orbit-item').forEach(el => el.remove());

    const stageRadius = stage.clientWidth / 2;
    if (!stageRadius) return;

    const guides = stage.querySelectorAll('.orbit-ring');
    const activeRings = ORBIT_RINGS.filter((ring, i) => {
        const guide = guides[i];
        return !guide || getComputedStyle(guide).display !== 'none';
    });
    const rings = (activeRings.length ? activeRings : ORBIT_RINGS).map(ring => ({ ...ring, items: [] }));

    MENU_DATA.forEach((menu, i) => rings[i % rings.length].items.push(menu));

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const frag = document.createDocumentFragment();

    rings.forEach(ring => {
        const count = ring.items.length;
        if (!count) return;
        const radiusPx = stageRadius * ring.rFraction;

        ring.items.forEach((menu, idx) => {
            const delay = reduceMotion ? 0 : -(ring.duration * idx) / count;

            const item = document.createElement('div');
            item.className = 'orbit-item';
            item.style.setProperty('--r', `${radiusPx}px`);
            item.style.setProperty('--duration', `${ring.duration}s`);
            item.style.setProperty('--delay', `${delay}s`);
            if (reduceMotion) item.style.setProperty('--angle', `${(360 / count) * idx}deg`);

            const chip = document.createElement('a');
            chip.className = 'orbit-chip';
            chip.textContent = menu.name;
            chip.title = menu.name;
            chip.href = `products/${menu.slug}`;

            if (menu.slug === CURRENT_SLUG) chip.classList.add('is-active');

            item.appendChild(chip);
            frag.appendChild(item);
        });
    });

    stage.appendChild(frag);
    stage.classList.toggle('orbit-static', reduceMotion);
}

buildOrbit();

let orbitResizeTimer = null;
window.addEventListener('resize', () => {
    clearTimeout(orbitResizeTimer);
    orbitResizeTimer = setTimeout(buildOrbit, 200);
});


/* ------------------------------------------------------------------
   1. Data-center background canvas
      Lightened for low-end devices:
      - the dot grid is pre-rendered once to an offscreen canvas
        instead of being redrawn every frame
      - fewer moving lines, capped animation rate (~30fps)
      - paused entirely when the tab is hidden or the user has
        requested reduced motion
   ------------------------------------------------------------------ */
(function () {
    const canvas = document.getElementById('bg-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const CELL      = 90;   // grid spacing (bigger = fewer nodes to draw)
    const LINE_CNT  = reduceMotion ? 0 : 10;
    const FRAME_MS  = 1000 / 30; // throttle to ~30fps

    let W, H, lines = [], gridLayer, running = true, lastTs = 0, rafId = null;

    function buildGridLayer() {
        gridLayer = document.createElement('canvas');
        gridLayer.width  = W;
        gridLayer.height = H;
        const gctx = gridLayer.getContext('2d');

        const cols = Math.ceil(W / CELL);
        const rows = Math.ceil(H / CELL);

        for (let r = 0; r <= rows; r++) {
            gctx.beginPath();
            gctx.moveTo(0, r * CELL);
            gctx.lineTo(W, r * CELL);
            gctx.strokeStyle = 'rgba(61,126,255,0.04)';
            gctx.lineWidth = 1;
            gctx.stroke();
        }
        for (let c = 0; c <= cols; c++) {
            gctx.beginPath();
            gctx.moveTo(c * CELL, 0);
            gctx.lineTo(c * CELL, H);
            gctx.strokeStyle = 'rgba(61,126,255,0.04)';
            gctx.lineWidth = 1;
            gctx.stroke();
        }
        for (let r = 0; r <= rows; r++) {
            for (let c = 0; c <= cols; c++) {
                gctx.beginPath();
                gctx.arc(c * CELL, r * CELL, 1, 0, Math.PI * 2);
                gctx.fillStyle = `rgba(61,126,255,${0.08 + Math.random() * 0.07})`;
                gctx.fill();
            }
        }

        [[W * .15, H * .3], [W * .8, H * .6], [W * .5, H * .15]].forEach(([gx, gy]) => {
            const g = gctx.createRadialGradient(gx, gy, 0, gx, gy, 180);
            g.addColorStop(0, 'rgba(61,126,255,0.07)');
            g.addColorStop(1, 'transparent');
            gctx.fillStyle = g;
            gctx.beginPath();
            gctx.arc(gx, gy, 180, 0, Math.PI * 2);
            gctx.fill();
        });
    }

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
        buildGridLayer();
    }

    function initLines() {
        lines = [];
        for (let i = 0; i < LINE_CNT; i++) {
            const x1    = Math.random() * W;
            const y1    = Math.random() * H;
            const angle = Math.random() * Math.PI * 2;
            const len   = 80 + Math.random() * 220;
            lines.push({
                x1, y1,
                x2: x1 + Math.cos(angle) * len,
                y2: y1 + Math.sin(angle) * len,
                t:  Math.random(),
                speed: 0.003 + Math.random() * 0.006
            });
        }
    }

    function draw(ts) {
        if (!running) return;
        rafId = requestAnimationFrame(draw);

        if (ts - lastTs < FRAME_MS) return;
        lastTs = ts;

        ctx.clearRect(0, 0, W, H);
        if (gridLayer) ctx.drawImage(gridLayer, 0, 0);

        lines.forEach(l => {
            l.t += l.speed;
            if (l.t > 1) l.t = 0;

            ctx.beginPath(); ctx.moveTo(l.x1, l.y1); ctx.lineTo(l.x2, l.y2);
            ctx.strokeStyle = 'rgba(0,212,255,0.07)'; ctx.lineWidth = 1; ctx.stroke();

            const px = l.x1 + (l.x2 - l.x1) * l.t;
            const py = l.y1 + (l.y2 - l.y1) * l.t;
            ctx.beginPath(); ctx.arc(px, py, 2, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(0,212,255,0.5)'; ctx.fill();
        });
    }

    document.addEventListener('visibilitychange', () => {
        running = !document.hidden;
        if (running && !rafId) { lastTs = 0; rafId = requestAnimationFrame(draw); }
    });

    window.addEventListener('resize', () => { resize(); initLines(); });

    resize();
    initLines();

    if (reduceMotion) {
        // Draw a single static frame and stop — no continuous animation.
        ctx.drawImage(gridLayer, 0, 0);
    } else {
        rafId = requestAnimationFrame(draw);
    }
})();


/* ------------------------------------------------------------------
   2. Sticky nav shadow
   ------------------------------------------------------------------ */
window.addEventListener('scroll', () => {
    const nav = document.getElementById('catNav');
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 80);
}, { passive: true });


/* ------------------------------------------------------------------
   3. Product search + category chip filter
      Cards carry: data-menu-id, data-name
   ------------------------------------------------------------------ */
const searchInput = document.getElementById('productSearch');
if (searchInput) searchInput.addEventListener('input', filterProducts);

document.querySelectorAll('#catChips .brand-chip').forEach(chip => {
    chip.addEventListener('click', function () {
        document.querySelectorAll('#catChips .brand-chip')
            .forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        filterProducts();
    });
});

function filterProducts() {
    const q      = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const menuId = document.querySelector('#catChips .brand-chip.active')?.dataset.menuId || 'all';
    let visibleCount = 0;

    document.querySelectorAll('.product-card-wrap').forEach(wrap => {
        const name    = wrap.dataset.name  || '';
        const wMenuId = wrap.dataset.menuId || '';

        const matchSearch = !q || name.includes(q);
        const matchMenu   = menuId === 'all' || wMenuId === menuId;
        const visible     = matchSearch && matchMenu;

        wrap.classList.toggle('d-none', !visible);
        if (visible) visibleCount++;
    });

    const emptyState = document.getElementById('productsEmptyDynamic');
    if (emptyState) emptyState.classList.toggle('d-none', visibleCount !== 0);
}


/* ------------------------------------------------------------------
   4. DNA bar IntersectionObserver
   ------------------------------------------------------------------ */
const dnaObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in-view');
            dnaObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.15 });

document.querySelectorAll('.product-card').forEach(card => dnaObserver.observe(card));


/* ------------------------------------------------------------------
   5. Smooth scroll for hero arrow
   ------------------------------------------------------------------ */
document.querySelectorAll('a[href="#products-list"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.getElementById('products-list')
            ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});