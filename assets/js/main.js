(() => {

    (function () {
        const dock     = document.getElementById('floatingDock');
        const goTop    = document.getElementById('goTop');
        const bar      = document.getElementById('scrollBar');
        const CIRCUM   = 138.23;
        const SHOW_AT  = 180;
        const EXPAND_AT = 480;

        let ticking = false;

        function onScroll() {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                const scrollY  = window.scrollY;
                const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
                const pct = maxScroll > 0 ? scrollY / maxScroll : 0;

                bar.style.strokeDashoffset = CIRCUM * (1 - pct);

                dock.classList.toggle('visible',  scrollY > SHOW_AT);

                dock.classList.toggle('expanded', scrollY > EXPAND_AT);

                ticking = false;
            });
        }

        window.addEventListener('scroll', onScroll, { passive: true });

        goTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        onScroll();
    })();
    const html = document.documentElement;

    const saved = localStorage.getItem("theme");

    if(saved){

        html.dataset.theme = saved;

    }else if(window.matchMedia("(prefers-color-scheme: dark)").matches){

        html.dataset.theme = "dark";

    }else{

        html.dataset.theme = "light";

    }

})();

const btn = document.getElementById("theme-toggle");
const icon = btn.querySelector("i");

function setTheme(theme){

    document.documentElement.dataset.theme = theme;

    localStorage.setItem("theme",theme);

    icon.className =
        theme==="dark"
            ? "fas fa-sun"
            : "fas fa-moon";

}

setTheme(document.documentElement.dataset.theme);

btn.addEventListener("click",()=>{

    setTheme(
        document.documentElement.dataset.theme==="dark"
            ? "light"
            : "dark"
    );

});

(function(){
  if(!('IntersectionObserver' in window)) return;
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){
        e.target.classList.add('revealed');
        obs.unobserve(e.target);
      }
    });
  },{threshold:.15});
  document.querySelectorAll('.reveal').forEach(function(el){ obs.observe(el); });
})();

(function(){
    /* Hamburger + drawer */
    var burger  = document.getElementById('hpBurger');
    var drawer  = document.getElementById('hpDrawer');
    if(burger && drawer){
        burger.addEventListener('click', function(){
            var open = drawer.classList.toggle('hp-open');
            burger.classList.toggle('hp-open', open);
            document.body.style.overflow = open ? 'hidden' : '';
        });
    }
    /* Desktop dropdowns — hover */
    if(window.innerWidth >= 1200){

        document.querySelectorAll('.hp-has-drop').forEach(function(li){

            li.addEventListener('mouseenter', function(){

                document
                    .querySelectorAll('.hp-has-drop')
                    .forEach(function(x){
                        x.classList.remove('hp-open');
                    });

                li.classList.add('hp-open');

            });

            li.addEventListener('mouseleave', function(){

                li.classList.remove('hp-open');

            });

        });

    }

    /* Bar fill animation — runs once when card enters viewport */
    function animateBars(){
        document.querySelectorAll('.hp-fill').forEach(function(bar, i){
            var w = bar.getAttribute('data-w') || '0.5';
            setTimeout(function(){
                bar.style.transition = 'transform 1.1s cubic-bezier(.22,1,.36,1)';
                bar.style.width      = '100%';
                bar.style.transform  = 'scaleX(' + w + ')';
                bar.style.transformOrigin = 'right';
            }, 300 + i * 180);
        });
    }

    if('IntersectionObserver' in window){
        var obs = new IntersectionObserver(function(entries){
            entries.forEach(function(e){ if(e.isIntersecting){ animateBars(); obs.disconnect(); } });
        }, {threshold:.3});
        var card = document.querySelector('.hp-card');
        if(card) obs.observe(card);
    } else {
        animateBars();
    }
})();

/*=====================================
    Order Process Animation
=====================================*/

document.addEventListener("DOMContentLoaded",function(){

    const section=document.querySelector(".order-process");

    if(!section) return;

    const items=section.querySelectorAll(".process-col");

    const observer=new IntersectionObserver(function(entries){

        entries.forEach(function(entry){

            if(!entry.isIntersecting) return;

            section.classList.add("active");

            items.forEach(function(item,index){

                setTimeout(function(){

                    item.classList.add("show");

                },index*180);

            });

            observer.disconnect();

        });

    },{

        threshold:.25

    });

    observer.observe(section);

});
const qty = document.getElementById('qty')

qty.addEventListener('input', function(){

    this.value = this.value
        .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
        .replace(/\D/g, '');

});
(() => {
    'use strict';

    const allowed = (el) => {
        return el.closest('input, textarea, [contenteditable="true"], pre code');
    };

    document.addEventListener('contextmenu', e => {
        if (!allowed(e.target)) e.preventDefault();
    });

    document.addEventListener('selectstart', e => {
        if (!allowed(e.target)) e.preventDefault();
    });

    document.addEventListener('dragstart', e => {
        if (!allowed(e.target)) e.preventDefault();
    });

    document.addEventListener('copy', e => {
        if (!allowed(e.target)) e.preventDefault();
    });

    document.addEventListener('cut', e => {
        if (!allowed(e.target)) e.preventDefault();
    });

    document.addEventListener('paste', e => {
        if (!allowed(e.target)) e.preventDefault();
    });

    document.addEventListener('keydown', e => {

        const key = e.key.toLowerCase();

        if ((e.ctrlKey || e.metaKey) &&
            ['a', 'c', 'x', 'u', 's', 'p'].includes(key)) {

            if (!allowed(document.activeElement)) {
                e.preventDefault();
                e.stopPropagation();
            }
        }

        if ((e.ctrlKey || e.metaKey) &&
            e.shiftKey &&
            ['i', 'j', 'c'].includes(key)) {

            e.preventDefault();
            e.stopPropagation();
        }

        if (e.key === 'F12') {
            e.preventDefault();
            e.stopPropagation();
        }

    });

    document.addEventListener('touchstart', function (e) {
        if (!allowed(e.target)) {
            e.target.style.webkitTouchCallout = 'none';
        }
    }, { passive: true });

})();
