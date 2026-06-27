(() => {

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
