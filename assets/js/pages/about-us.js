document.addEventListener("DOMContentLoaded", () => {

    initReveal();

    initTimeline();

});

/*==================================================
Reveal Animation
==================================================*/

function initReveal(){

    const elements = document.querySelectorAll(
        ".about-content,.about-image,.company-image,.company-content,.stat-card,.why-card,.timeline-card"
    );

    const observer = new IntersectionObserver((entries)=>{

        entries.forEach(entry=>{

            if(entry.isIntersecting){

                entry.target.classList.add("active");

            }

        });

    },{

        threshold:.18

    });

    elements.forEach(el=>observer.observe(el));

}

/*==================================================
Timeline
==================================================*/

function initTimeline(){

    const cards=document.querySelectorAll(".timeline-card");

    const fill=document.querySelector(".timeline-progress-fill");

    if(!cards.length) return;

    const observer=new IntersectionObserver(entries=>{

        entries.forEach(entry=>{

            if(entry.isIntersecting){

                entry.target.classList.add("active");

                updateTimeline(cards,fill);

            }

        });

    },{

        threshold:.45

    });

    cards.forEach(card=>observer.observe(card));

}

/*==================================================
Timeline Progress
==================================================*/

function updateTimeline(cards,fill){

    let active=0;

    cards.forEach(card=>{

        if(card.classList.contains("active"))

            active++;

    });

    const percent=((active-1)/(cards.length-1))*100;

    if(fill){

        fill.style.width=Math.max(0,percent)+"%";

    }

}

/*==================================================
TIMELINE MOBILE
==================================================*/

function initTimelineMobile(){

    if(window.innerWidth>991) return;

    if(typeof $.fn.owlCarousel==="undefined") return;

    const list=$(".timeline-list");

    if(!list.length) return;

    list.addClass("owl-carousel timeline-slider");

    list.owlCarousel({

        rtl:true,

        items:1,

        loop:false,

        margin:20,

        nav:true,

        dots:true,

        smartSpeed:700,

        navText:[

            '<i class="fas fa-chevron-right"></i>',

            '<i class="fas fa-chevron-left"></i>'

        ]

    });

}

/*==================================================
FLOATING EXPERIENCE CARD
==================================================*/

function initFloatingCard(){

    const card=document.querySelector(".experience-card");

    if(!card) return;

    let direction=1;

    setInterval(()=>{

        direction*=-1;

        card.style.transform=`translateY(${direction*10}px)`;

    },1800);

}

/*==================================================
ACTIVE MENU
==================================================*/

(function(){

    const path=window.location.pathname;

    document.querySelectorAll(".hp-links a").forEach(link=>{

        const href=link.getAttribute("href");

        if(!href) return;

        if(path.includes(href) && href!=="./"){

            link.classList.add("active");

        }

    });

})();

/*==================================================
SMOOTH SCROLL
==================================================*/

document.querySelectorAll('a[href^="#"]').forEach(anchor=>{

    anchor.addEventListener("click",function(e){

        e.preventDefault();

        const target=document.querySelector(this.getAttribute("href"));

        if(target){

            target.scrollIntoView({

                behavior:"smooth",

                block:"start"

            });

        }

    });

});

/*==================================================
WINDOW RESIZE
==================================================*/

let resizeTimer;

window.addEventListener("resize",()=>{

    clearTimeout(resizeTimer);

    resizeTimer=setTimeout(()=>{

        location.reload();

    },350);

});