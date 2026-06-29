/**
 * blog.js  —  Blog page interactions
 * Search (live filter) + Sort (latest / popular / order)
 */

(function ($) {
    'use strict';

    // ─── Elements ────────────────────────────────────────────
    const $grid   = $('#blogGrid');
    const $empty  = $('#blogEmpty');
    const $search = $('#blogSearch');
    const $clear  = $('#searchClear');
    const $sort   = $('#blogSort');

    // ─── State ───────────────────────────────────────────────
    let searchTerm = '';
    let sortMode   = 'latest';

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Normalise Persian/Arabic digits + diacritics for fuzzy matching
     */
    function normalise(str) {
        return (str || '')
            .replace(/[\u0610-\u061A\u064B-\u065F]/g, '') // strip diacritics
            .replace(/[۰-۹]/g, d => d.charCodeAt(0) - 1776) // Persian → ASCII digits
            .replace(/[٠-٩]/g, d => d.charCodeAt(0) - 1632) // Arabic → ASCII digits
            .toLowerCase()
            .trim();
    }

    function getCards() {
        return $grid.children('.blog-col');
    }

    // ─── Filter ──────────────────────────────────────────────

    function applyFilter() {
        const needle = normalise(searchTerm);
        let visible  = 0;

        getCards().each(function () {
            const title  = normalise($(this).data('title'));
            const match  = !needle || title.includes(needle);

            $(this).toggle(match);
            if (match) visible++;
        });

        $empty.toggle(visible === 0);
    }

    // ─── Sort ────────────────────────────────────────────────

    function applySort() {
        const cards = getCards().toArray();

        cards.sort(function (a, b) {
            switch (sortMode) {
                case 'popular':
                    return parseInt($(b).data('visit'), 10) - parseInt($(a).data('visit'), 10);

                case 'order':
                    return parseInt($(a).data('order'), 10) - parseInt($(b).data('order'), 10);

                case 'latest':
                default:
                    return new Date($(b).data('date')) - new Date($(a).data('date'));
            }
        });

        // Re-append in new order
        $.each(cards, function (_, el) {
            $grid.append(el);
        });

        // Re-apply col width: every 7th visible card is wide
        let idx = 0;
        getCards().each(function () {
            if (!$(this).is(':visible')) return;
            idx++;
            const wide = (idx % 7 === 0);
            $(this)
                .removeClass('col-lg-4 col-lg-12')
                .addClass(wide ? 'col-lg-12' : 'col-lg-4');

            // Adjust internal card layout class
            const $card = $(this).find('.blog-card');
            if (wide) {
                $card.addClass('card-wide');
            } else {
                $card.removeClass('card-wide');
            }
        });
    }

    // ─── Combined render ─────────────────────────────────────

    function render() {
        applySort();
        applyFilter();
    }

    // ─── Event listeners ─────────────────────────────────────

    $search.on('input', function () {
        searchTerm = $(this).val();
        $clear.toggleClass('visible', searchTerm.length > 0);
        applyFilter();
    });

    $clear.on('click', function () {
        $search.val('').trigger('input').focus();
    });

    $sort.on('change', function () {
        sortMode = $(this).val();
        render();
    });

    // ─── Nice Select init (if loaded) ────────────────────────
    if ($.fn.niceSelect) {
        $sort.niceSelect();

        // Sync niceSelect changes back to native select → our handler
        $(document).on('change', '.nice-select', function () {
            const val = $(this).find('.option.selected').data('value');
            if (val) {
                $sort.val(val).trigger('change');
            }
        });
    }

    // ─── Scroll reveal ───────────────────────────────────────
    function revealOnScroll() {
        const threshold = window.innerHeight * 0.88;

        document.querySelectorAll('.reveal:not(.revealed)').forEach(function (el) {
            if (el.getBoundingClientRect().top < threshold) {
                el.classList.add('revealed');
            }
        });
    }

    window.addEventListener('scroll', revealOnScroll, { passive: true });
    revealOnScroll(); // run once on load

    // ─── Stat counter animation ───────────────────────────────
    function animateCounters() {
        $('.stat-card span').each(function () {
            const $el     = $(this);
            const raw     = $el.text().replace(/,/g, '');
            const target  = parseInt(raw, 10);
            if (isNaN(target) || target === 0) return;

            $el.text('0');
            let current = 0;
            const step  = Math.ceil(target / 60);

            const timer = setInterval(function () {
                current = Math.min(current + step, target);
                $el.text(current.toLocaleString('fa-IR'));
                if (current >= target) clearInterval(timer);
            }, 20);
        });
    }

    // Run counter animation when stats section enters viewport
    const statsEl = document.querySelector('.knowledge-stats');
    if (statsEl) {
        const observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        }, { threshold: 0.3 });

        observer.observe(statsEl);
    }

}(jQuery));
