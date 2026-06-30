/**
 * blog.js  —  Single article page
 *
 * Features:
 *  1. Top reading progress bar
 *  2. Floating Action Card (show/hide on scroll)
 *  3. Auto Table of Contents from h2/h3 tags
 *  4. TOC smooth scroll + active-section highlight
 *  5. Heading anchors (link icon next to each h2)
 *  6. Copy link / share
 *  7. Back to top
 *  8. Cover image soft parallax
 */

(function () {
    'use strict';

    /* ─── Cached elements ───────────────────────────── */
    const body         = document.body;
    const articleBody  = document.getElementById('articleBody');
    const topFill      = document.getElementById('topReadingFill');
    const floatingCard = document.getElementById('floatingCard');
    const facShare     = document.getElementById('facShare');
    const facTop       = document.getElementById('facTop');
    const facToast     = document.getElementById('facToast');
    const tocBox       = document.getElementById('tocBox');
    const tocNav       = document.getElementById('tocNav');
    const tocToggle    = document.getElementById('tocToggle');
    const coverImg     = document.getElementById('coverImg');
    const coverWrapper = document.getElementById('coverParallax');


    /* ══════════════════════════════════════════════════
       1. TOP READING PROGRESS BAR
    ══════════════════════════════════════════════════ */
    function updateReadingProgress() {
        if (!articleBody || !topFill) return;

        const articleTop    = articleBody.getBoundingClientRect().top + window.scrollY;
        const articleHeight = articleBody.offsetHeight;
        const scrolled      = window.scrollY - articleTop;
        const viewH         = window.innerHeight;
        const total         = articleHeight - viewH;
        const pct           = total > 0 ? Math.min(100, Math.max(0, (scrolled / total) * 100)) : 0;

        topFill.style.width = pct + '%';
    }


    /* ══════════════════════════════════════════════════
       2. FLOATING ACTION CARD
    ══════════════════════════════════════════════════ */
    const FAC_SHOW_AFTER = 220; // px scrolled before FAC appears

    function updateFAC() {
        if (!floatingCard) return;
        if (window.scrollY > FAC_SHOW_AFTER) {
            floatingCard.classList.add('fac-visible');
        } else {
            floatingCard.classList.remove('fac-visible');
        }
    }


    /* ══════════════════════════════════════════════════
       3 & 4. AUTO TABLE OF CONTENTS
    ══════════════════════════════════════════════════ */
    let tocLinks  = [];   // [{el, link}]
    let headings  = [];   // heading DOM nodes

    function buildTOC() {
        if (!articleBody || !tocNav || !tocBox) return;

        headings = Array.from(articleBody.querySelectorAll('h2, h3'));
        if (headings.length < 2) return; // not worth showing for <2 headings

        const ol = document.createElement('ol');
        let counter = 0;

        headings.forEach(function (h) {
            counter++;

            // Slugify heading text for an id
            const rawText = h.textContent.trim();
            const id      = 'heading-' + counter + '-' + slugify(rawText);
            h.id = id;

            // Build anchor icon next to every h2
            if (h.tagName === 'H2') {
                const anchor = document.createElement('a');
                anchor.className   = 'heading-anchor';
                anchor.href        = '#' + id;
                anchor.setAttribute('aria-label', 'لینک مستقیم');
                anchor.innerHTML   = '<i class="fa-solid fa-link"></i>';
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    copyToClipboard(window.location.origin + window.location.pathname + '#' + id);
                    showToast('لینک کپی شد!');
                    smoothScrollTo(h);
                });
                h.appendChild(anchor);
            }

            // TOC list item
            const li = document.createElement('li');
            li.className = h.tagName === 'H3' ? 'toc-h3' : 'toc-h2';

            const a = document.createElement('a');
            a.href      = '#' + id;
            a.textContent = rawText;
            a.addEventListener('click', function (e) {
                e.preventDefault();
                smoothScrollTo(h);
                // update URL hash silently
                history.replaceState(null, '', '#' + id);
            });

            li.appendChild(a);
            ol.appendChild(li);

            tocLinks.push({ heading: h, link: a });
        });

        tocNav.appendChild(ol);
        tocBox.style.display = 'block';
    }

    function slugify(text) {
        return text
            .replace(/\s+/g, '-')
            .replace(/[^\w\u0600-\u06FF-]/g, '')
            .slice(0, 60);
    }

    // Smooth scroll with header offset
    function smoothScrollTo(el) {
        const offset = 90; // header height + breathing room
        const top    = el.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    // Highlight active TOC item based on scroll
    let ticking = false;

    function updateActiveTOC() {
        if (!tocLinks.length) return;

        const scrollY  = window.scrollY;
        const offset   = 120;
        let   active   = tocLinks[0];

        for (let i = 0; i < tocLinks.length; i++) {
            const top = tocLinks[i].heading.getBoundingClientRect().top + scrollY;
            if (scrollY + offset >= top) {
                active = tocLinks[i];
            }
        }

        tocLinks.forEach(function (item) {
            item.link.classList.toggle('toc-active', item === active);
        });
    }

    // TOC collapse toggle
    if (tocToggle) {
        tocToggle.addEventListener('click', function () {
            const nav = document.getElementById('tocNav');
            nav.classList.toggle('collapsed');
            tocToggle.classList.toggle('collapsed');
        });
    }


    /* ══════════════════════════════════════════════════
       6. COPY LINK / SHARE
    ══════════════════════════════════════════════════ */
    let toastTimer;

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).catch(function () {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const el = document.createElement('textarea');
        el.value = text;
        el.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }

    function showToast(msg) {
        if (!facToast) return;
        facToast.textContent = msg;
        facToast.classList.add('fac-toast-show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            facToast.classList.remove('fac-toast-show');
        }, 2400);
    }

    if (facShare) {
        facShare.addEventListener('click', function () {
            const url = (window.ARTICLE && window.ARTICLE.url) || window.location.href;

            // Try native share on mobile
            if (navigator.share) {
                navigator.share({
                    title: (window.ARTICLE && window.ARTICLE.title) || document.title,
                    url:   url,
                }).catch(function () {}); // user cancelled — ignore
            } else {
                copyToClipboard(url);
                showToast('لینک کپی شد!');
            }
        });
    }


    /* ══════════════════════════════════════════════════
       7. BACK TO TOP
    ══════════════════════════════════════════════════ */
    if (facTop) {
        facTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }


    /* ══════════════════════════════════════════════════
       8. COVER PARALLAX  (soft, performance-friendly)
    ══════════════════════════════════════════════════ */
    function updateParallax() {
        if (!coverImg || !coverWrapper) return;

        const rect  = coverWrapper.getBoundingClientRect();
        const inVP  = rect.bottom > 0 && rect.top < window.innerHeight;
        if (!inVP) return;

        // How far the wrapper centre is from viewport centre (normalised)
        const centre  = rect.top + rect.height / 2;
        const vpCentre = window.innerHeight / 2;
        const ratio   = (centre - vpCentre) / window.innerHeight;

        // Move image at 25% of scroll speed → subtle parallax
        const shift = ratio * 25;
        coverImg.style.transform = 'translateY(' + shift + 'px)';
    }


    /* ══════════════════════════════════════════════════
       SCROLL HANDLER  (throttled with rAF)
    ══════════════════════════════════════════════════ */
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(function () {
                updateReadingProgress();
                updateFAC();
                updateActiveTOC();
                updateParallax();
                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });


    /* ══════════════════════════════════════════════════
       INIT
    ══════════════════════════════════════════════════ */
    function init() {
        buildTOC();
        updateReadingProgress();
        updateFAC();
        updateParallax();

        // Scroll-reveal for any .reveal elements on the page
        const reveals = document.querySelectorAll('.reveal');
        if (reveals.length) {
            const revealObs = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        revealObs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });

            reveals.forEach(function (el) { revealObs.observe(el); });
        }

        // Anchor from URL on load
        if (window.location.hash) {
            const target = document.getElementById(window.location.hash.slice(1));
            if (target) {
                setTimeout(function () { smoothScrollTo(target); }, 350);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());

const progress = $("#readingProgress");
const percent = $("#readingPercent");

function updateReadingProgress(){

    const scroll = $(window).scrollTop();

    const maxScroll = $(document).height() - $(window).height();

    let p = (scroll / maxScroll) * 100;

    p = Math.max(0, Math.min(100, p));

    if(window.innerWidth <= 768){

        progress.css({
            width: p + "%",
            height: "100%"
        });

    }else{

        progress.css({
            height: p + "%",
            width: "100%"
        });

    }

    // قرمز ➜ سبز
    progress.css(
        "background",
        `hsl(${p * 1.2}, 85%, 50%)`
    );

    percent.text(Math.round(p) + "%");

}

$(window).on("scroll resize", updateReadingProgress);

updateReadingProgress();