/*==================================================
TEAM PAGE
SECTION 1
==================================================*/

$(function () {

    "use strict";

    TeamPage.init();

});

const TeamPage = {

    init() {

        this.counter();

        this.heroParallax();

        this.reveal();

        this.teamDNA();

    },



    /*==================================
        Counter
    ==================================*/

    counter() {

        const items = document.querySelectorAll(".counter-item strong");

        if (!items.length) return;

        const observer = new IntersectionObserver(entries => {

            entries.forEach(entry => {

                if (!entry.isIntersecting) return;

                const el = entry.target;

                const target = parseInt(el.innerText);

                let current = 0;

                const step = Math.ceil(target / 60);

                const timer = setInterval(() => {

                    current += step;

                    if (current >= target) {

                        current = target;

                        clearInterval(timer);

                    }

                    el.innerHTML = current + "+";

                }, 25);

                observer.unobserve(el);

            });

        }, {

            threshold: .6

        });

        items.forEach(i => observer.observe(i));

    },



    /*==================================
        Hero Mouse Move
    ==================================*/

    heroParallax() {

        const hero = document.querySelector(".team-hero");

        if (!hero) return;

        hero.addEventListener("mousemove", e => {

            const x = (window.innerWidth / 2 - e.clientX) / 40;

            const y = (window.innerHeight / 2 - e.clientY) / 40;

            hero.style.backgroundPosition = `${x}px ${y}px`;

        });

    },



    /*==================================
        Reveal
    ==================================*/

    reveal() {

        const elements = document.querySelectorAll(

            ".team-card,.dna-item,.counter-item,.values-grid article,.gallery-item"

        );

        if (!elements.length) return;

        const observer = new IntersectionObserver(entries => {

            entries.forEach(entry => {

                if (!entry.isIntersecting) return;

                entry.target.classList.add("show-item");

                observer.unobserve(entry.target);

            });

        }, {

            threshold: .15

        });

        elements.forEach(el => observer.observe(el));

    },



    /*==================================
        DNA Active
    ==================================*/

    teamDNA() {

        $(".dna-item").on("mouseenter", function () {

            $(".dna-item").removeClass("active");

            $(this).addClass("active");

        });

    }

};
/*==================================================
SECTION 2
Premium Interactions
==================================================*/

(function () {

    "use strict";

    TeamEffects.init();

})();

const TeamEffects = {

    init() {

        this.cardTilt();

        this.buttonMagnet();

        this.imageGlow();

        this.skillHover();

        this.galleryZoom();

    },

    /*==================================
    3D Card Tilt
    ==================================*/

    cardTilt() {

        const cards = document.querySelectorAll(".team-card");

        cards.forEach(card => {

            card.addEventListener("mousemove", function (e) {

                const rect = card.getBoundingClientRect();

                const x = e.clientX - rect.left;

                const y = e.clientY - rect.top;

                const rotateY = ((x / rect.width) - .5) * 14;

                const rotateX = ((y / rect.height) - .5) * -14;

                card.style.transform = `perspective(1000px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                translateY(-10px)`;

            });

            card.addEventListener("mouseleave", function () {

                card.style.transform =
                    "perspective(1000px) rotateX(0) rotateY(0) translateY(0)";

            });

        });

    },

    /*==================================
    Magnetic Button
    ==================================*/

    buttonMagnet() {

        $(".join-btn").on("mousemove", function (e) {

            const rect = this.getBoundingClientRect();

            const x = e.clientX - rect.left;

            const y = e.clientY - rect.top;

            const moveX = (x - rect.width / 2) / 6;

            const moveY = (y - rect.height / 2) / 6;

            this.style.transform =
                `translate(${moveX}px,${moveY}px)`;

        });

        $(".join-btn").on("mouseleave", function () {

            this.style.transform = "";

        });

    },

    /*==================================
    Mouse Glow
    ==================================*/

    imageGlow() {

        $(".member-image").on("mousemove", function (e) {

            const rect = this.getBoundingClientRect();

            const x = e.clientX - rect.left;

            const y = e.clientY - rect.top;

            $(this).css({

                "--mouse-x": x + "px",

                "--mouse-y": y + "px"

            });

        });

    },

    /*==================================
    Skills
    ==================================*/

    skillHover() {

        $(".member-skills span").hover(

            function () {

                $(this)

                    .siblings()

                    .css({

                        opacity: .45,

                        transform: "scale(.9)"

                    });

            },

            function () {

                $(this)

                    .siblings()

                    .css({

                        opacity: 1,

                        transform: "scale(1)"

                    });

            }

        );

    },

    /*==================================
    Gallery
    ==================================*/

    galleryZoom() {

        $(".gallery-item").on("mousemove", function (e) {

            const rect = this.getBoundingClientRect();

            const x = ((e.clientX - rect.left) / rect.width) * 100;

            const y = ((e.clientY - rect.top) / rect.height) * 100;

            $(this)

                .find("img")

                .css("transform-origin", `${x}% ${y}%`);

        });

    }

};