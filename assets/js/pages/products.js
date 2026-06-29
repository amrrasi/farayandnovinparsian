const ORBIT_RINGS = [
    { r: 85,  duration: 16, items: [] },
    { r: 140, duration: 26, items: [] },
    { r: 195, duration: 38, items: [] },
];

function buildOrbit() {
    const stage = document.getElementById('orbitStage');
    if (!stage || !MENU_DATA || !MENU_DATA.length) return;

    // Distribute menu items round-robin across rings
    MENU_DATA.forEach((menu, i) => {
        ORBIT_RINGS[i % ORBIT_RINGS.length].items.push(menu);
    });

    ORBIT_RINGS.forEach(ring => {
        if (!ring.items.length) return;
        const count = ring.items.length;

        ring.items.forEach((menu, idx) => {
            // Each item starts evenly spaced around the ring
            const startDeg  = (360 / count) * idx;
            const planet    = document.createElement('div');
            planet.className = 'orbit-planet';

            // Unique CSS animation per planet
            const animName  = `orbit-dyn-${ring.r}-${idx}`;
            const style     = document.createElement('style');
            style.textContent = `
                @keyframes ${animName} {
                    from { transform: rotate(${startDeg}deg) translateX(${ring.r}px) rotate(-${startDeg}deg); }
                    to   { transform: rotate(${startDeg + 360}deg) translateX(${ring.r}px) rotate(-${startDeg + 360}deg); }
                }
            `;
            document.head.appendChild(style);
            planet.style.animation = `${animName} ${ring.duration}s linear infinite`;

            // The clickable chip
            const chip = document.createElement('a');
            chip.className = 'chip';
            chip.textContent = menu.name;
            chip.href  = `products/${menu.slug}`;
            chip.title = menu.name;

            // Highlight the active category
            if (menu.slug === CURRENT_SLUG) {
                chip.style.borderColor = 'var(--cyan-accent)';
                chip.style.color       = 'var(--cyan-accent)';
                chip.style.boxShadow   = '0 0 14px rgba(0,212,255,0.35)';
            }

            planet.appendChild(chip);
            stage.appendChild(planet);
        });
    });
}

buildOrbit();


/* ------------------------------------------------------------------
   1. Data-center background canvas
   ------------------------------------------------------------------ */
(function () {
    const canvas = document.getElementById('bg-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let W, H, lines = [], nodes = [];

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    function initLines() {
        lines = []; nodes = [];
        const cols = Math.ceil(W / 60);
        const rows = Math.ceil(H / 60);

        for (let r = 0; r <= rows; r++)
            for (let c = 0; c <= cols; c++)
                nodes.push({ x: c * 60, y: r * 60, a: Math.random() });

        for (let i = 0; i < 18; i++) {
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

    function draw() {
        ctx.clearRect(0, 0, W, H);

        nodes.forEach(n => {
            ctx.beginPath();
            ctx.arc(n.x, n.y, 1, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(61,126,255,${0.08 + n.a * 0.07})`;
            ctx.fill();
        });

        for (let r = 0; r <= Math.ceil(H / 60); r++) {
            ctx.beginPath(); ctx.moveTo(0, r * 60); ctx.lineTo(W, r * 60);
            ctx.strokeStyle = 'rgba(61,126,255,0.04)'; ctx.lineWidth = 1; ctx.stroke();
        }
        for (let c = 0; c <= Math.ceil(W / 60); c++) {
            ctx.beginPath(); ctx.moveTo(c * 60, 0); ctx.lineTo(c * 60, H);
            ctx.strokeStyle = 'rgba(61,126,255,0.04)'; ctx.lineWidth = 1; ctx.stroke();
        }

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

        [[W * .15, H * .3], [W * .8, H * .6], [W * .5, H * .15]].forEach(([gx, gy]) => {
            const g = ctx.createRadialGradient(gx, gy, 0, gx, gy, 180);
            g.addColorStop(0, 'rgba(61,126,255,0.07)');
            g.addColorStop(1, 'transparent');
            ctx.fillStyle = g;
            ctx.beginPath(); ctx.arc(gx, gy, 180, 0, Math.PI * 2); ctx.fill();
        });

        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', () => { resize(); initLines(); });
    resize(); initLines(); draw();
})();


/* ------------------------------------------------------------------
   2. Sticky nav shadow
   ------------------------------------------------------------------ */
window.addEventListener('scroll', () => {
    const nav = document.getElementById('catNav');
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 80);
});


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

    document.querySelectorAll('.product-card-wrap').forEach(wrap => {
        const name    = wrap.dataset.name  || '';
        const wMenuId = wrap.dataset.menuId || '';

        const matchSearch = !q || name.includes(q);
        const matchMenu   = menuId === 'all' || wMenuId === menuId;

        wrap.style.display = (matchSearch && matchMenu) ? '' : 'none';
    });
}


/* ------------------------------------------------------------------
   4. DNA bar IntersectionObserver
   ------------------------------------------------------------------ */
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in-view');
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.15 });

document.querySelectorAll('.product-card').forEach(card => observer.observe(card));


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