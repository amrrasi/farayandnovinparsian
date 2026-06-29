/*======================================
PRODUCT PAGE
======================================*/

$(function () {

    initGallery();

    initTabs();

    initQuantity();

    initFAQ();

    initStickyCart();

    initScrollSpy();

});


/*======================================
IMAGE GALLERY
======================================*/

function initGallery(){

    const thumbs=$(".gallery-thumbs button");

    const image=$("#mainProductImage");

    thumbs.on("click",function(){

        thumbs.removeClass("active");

        $(this).addClass("active");

        let src=$(this).find("img").attr("src");

        image.fadeOut(120,function(){

            image.attr("src",src);

            image.fadeIn(120);

        });

    });

}


/*======================================
TABS
======================================*/

function initTabs(){

    $(".product-tabs button").on("click",function(){

        let tab=$(this).data("tab");

        $(".product-tabs button").removeClass("active");

        $(this).addClass("active");

        $(".tab-content").removeClass("active");

        $("#"+tab).addClass("active");

    });

}


/*======================================
QUANTITY
======================================*/

function initQuantity(){

    $(".plus").click(function(){

        let input=$("#qty");

        input.val(parseInt(input.val())+1);

    });

    $(".minus").click(function(){

        let input=$("#qty");

        let value=parseInt(input.val());

        if(value>1){

            input.val(value-1);

        }

    });

}


/*======================================
FAQ
======================================*/

function initFAQ(){

    $(".faq-item button").click(function(){

        let item=$(this).parent();

        if(item.hasClass("active")){

            item.removeClass("active");

            item.find(".faq-body").stop().slideUp(250);

        }else{

            $(".faq-item").removeClass("active");

            $(".faq-body").stop().slideUp(250);

            item.addClass("active");

            item.find(".faq-body").stop().slideDown(250);

        }

    });

}
/*======================================
SMART FLOATING CART
======================================*/

function initStickyCart(){

    const hero=$(".product-single");

    const cart=$(".floating-cart");

    cart.hide();

    $(window).on("scroll",function(){

        let scroll=$(this).scrollTop();

        let trigger=hero.offset().top+hero.outerHeight()-250;

        if(scroll>trigger){

            cart.stop(true,true).fadeIn(250);

        }else{

            cart.stop(true,true).fadeOut(200);

        }

    });

}


/*======================================
SCROLL SPY
======================================*/

function initScrollSpy(){

    const sections=[
        "#overview",
        "#features",
        "#specs",
        "#downloads"
    ];

    $(window).on("scroll",function(){

        let scroll=$(window).scrollTop()+180;

        sections.forEach(function(id){

            if($(id).length){

                let top=$(id).offset().top;
                let bottom=top+$(id).outerHeight();

                if(scroll>=top && scroll<bottom){

                    $(".product-tabs button").removeClass("active");

                    $('.product-tabs button[data-tab="'+id.replace("#","")+'"]')
                        .addClass("active");

                }

            }

        });

    });

}


/*======================================
SMOOTH TAB SCROLL
======================================*/

$(".product-tabs button").on("click",function(){

    let id=$(this).data("tab");

    $("html,body").animate({

        scrollTop:$("#"+id).offset().top-120

    },500);

});


/*======================================
IMAGE ZOOM
======================================*/

$(".gallery-main").mousemove(function(e){

    const image=$(this).find("img");

    const x=(e.pageX-$(this).offset().left)/$(this).width()*100;

    const y=(e.pageY-$(this).offset().top)/$(this).height()*100;

    image.css({

        "transform-origin":x+"% "+y+"%",

        "transform":"scale(1.45)"

    });

});

$(".gallery-main").mouseleave(function(){

    $(this).find("img").css({

        "transform":"scale(1)",

        "transform-origin":"center"

    });

});


/*======================================
RIPPLE EFFECT
======================================*/

$(".btn-main").click(function(e){

    let btn=$(this);

    let ripple=$("<span class='ripple'></span>");

    let x=e.pageX-btn.offset().left;

    let y=e.pageY-btn.offset().top;

    ripple.css({

        left:x,

        top:y

    });

    btn.append(ripple);

    setTimeout(function(){

        ripple.remove();

    },700);

});


/*======================================
ADD TO CART ANIMATION
======================================*/

$(".add-cart").click(function(){

    let img=$("#mainProductImage");

    let clone=img.clone();

    clone.css({

        position:"fixed",

        width:img.width(),

        height:img.height(),

        left:img.offset().left,

        top:img.offset().top,

        zIndex:99999,

        pointerEvents:"none"

    });

    $("body").append(clone);

    let cart=$(".floating-cart");

    clone.animate({

        left:cart.offset().left+40,

        top:cart.offset().top+20,

        width:40,

        height:40,

        opacity:.2

    },700,function(){

        clone.remove();

    });

});