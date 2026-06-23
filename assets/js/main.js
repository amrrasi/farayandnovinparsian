 document
    .querySelectorAll('.submenu-toggle')
    .forEach(btn=>{

    btn.addEventListener('click',function(e){

        e.preventDefault();

        this.parentElement
            .nextElementSibling
            .classList
            .toggle('show-submenu');

    });

});

