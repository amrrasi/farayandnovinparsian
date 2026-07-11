(() => {

    'use strict';


    const navItems = [
        ...document.querySelectorAll('.pf-nav-item')
    ];

    const rail =
        document.querySelector('.pf-nav-rail');

    const panelBody =
        document.getElementById('pf-panel-body');

    const loadingEl =
        document.getElementById('pf-loading');

    const toastEl =
        document.getElementById('pf-toast');



    const TAB_ENDPOINTS = {

        overview:
            'ajax/profile/tab_overview.php',

        orders:
            'ajax/profile/tab_orders.php',

        messages:
            'ajax/profile/tab_messages.php',

        personal:
            'ajax/profile/tab_personal.php',

        security:
            'ajax/profile/tab_security.php'

    };



    let currentTab = null;
    let requestController = null;



// ------------------------------------------------
// Toast
// ------------------------------------------------

    let toastTimer;


    function showToast(message,error=false){

        if(!toastEl) return;


        toastEl.textContent = message;


        toastEl.classList.toggle(
            'pf-toast--error',
            error
        );


        toastEl.classList.add(
            'is-visible'
        );


        clearTimeout(toastTimer);


        toastTimer=setTimeout(()=>{

            toastEl.classList.remove(
                'is-visible'
            );

        },3200);

    }



// ------------------------------------------------
// Rail
// ------------------------------------------------

    function moveRailTo(btn){

        if(!rail || !btn) return;


        rail.style.transform =
            `translateY(${btn.offsetTop}px)`;


        rail.style.height =
            `${btn.offsetHeight}px`;

    }




// ------------------------------------------------
// Load tab
// ------------------------------------------------

    async function loadTab(tabName, options={}){


        const {
            pushState=true
        } = options;



        const endpoint =
            TAB_ENDPOINTS[tabName];


        if(!endpoint) return;



        if(requestController){

            requestController.abort();

        }


        requestController =
            new AbortController();



        currentTab = tabName;



        updateActiveTab(tabName);



        loadingEl?.classList.add(
            'is-visible'
        );



        try{


            const res =
                await fetch(
                    endpoint,
                    {
                        signal:
                        requestController.signal,

                        headers:{
                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );



            if(res.status===401){

                location.href='/login';

                return;

            }



            const html =
                await res.text();



            panelBody.innerHTML = html;



            panelBody.classList.remove(
                'pf-panel-body'
            );


            void panelBody.offsetWidth;


            panelBody.classList.add(
                'pf-panel-body'
            );



            bindPanelEvents(tabName);



            if(pushState){


                const url =
                    `/profile?tab=${tabName}`;


                history.pushState(
                    {
                        tab:tabName
                    },
                    '',
                    url
                );


            }



        }
        catch(err){


            if(err.name==='AbortError')
                return;



            panelBody.innerHTML = `

        <div class="pf-empty">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <p>
            خطا در بارگذاری اطلاعات
            </p>

        </div>

        `;


        }
        finally{


            loadingEl?.classList.remove(
                'is-visible'
            );

        }


    }




// ------------------------------------------------
// Active button
// ------------------------------------------------

    function updateActiveTab(tab){


        navItems.forEach(btn=>{


            const active =
                btn.dataset.tab===tab;



            btn.classList.toggle(
                'is-active',
                active
            );


            btn.setAttribute(
                'aria-selected',
                active
                    ?
                    'true'
                    :
                    'false'
            );


            if(active)
                moveRailTo(btn);


        });


    }





// ------------------------------------------------
// Navigation
// ------------------------------------------------

    navItems.forEach(btn=>{


        btn.addEventListener(
            'click',
            ()=>{


                const tab =
                    btn.dataset.tab;



                if(tab!==currentTab)

                    loadTab(tab);


            }
        );


    });





// داخل صفحات Ajax

    document.addEventListener(
        'click',
        e=>{


            const btn =
                e.target.closest(
                    '[data-goto-tab]'
                );


            if(!btn)
                return;



            loadTab(
                btn.dataset.gotoTab
            );


        });





// ------------------------------------------------
// Browser back forward
// ------------------------------------------------

    window.addEventListener(
        'popstate',
        ()=>{


            const params =
                new URLSearchParams(
                    location.search
                );



            const tab =
                params.get('tab')
                ||
                'overview';



            loadTab(
                TAB_ENDPOINTS[tab]
                    ?
                    tab
                    :
                    'overview',
                {
                    pushState:false
                }
            );


        });




// ------------------------------------------------
// Resize
// ------------------------------------------------

    window.addEventListener(
        'resize',
        ()=>{

            const active =
                document.querySelector(
                    '.pf-nav-item.is-active'
                );


            if(active)
                moveRailTo(active);


        });




// ------------------------------------------------
// Events
// ------------------------------------------------

    function bindPanelEvents(tab){


        if(tab==='messages')
            bindMessagesTab();


        if(tab==='personal')
            bindPersonalForm();


        if(tab==='security')
            bindSecurityForm();

    }






// ------------------------------------------------
// Messages
// ------------------------------------------------

    function bindMessagesTab(){


        const rows =
            document.querySelectorAll('.pf-msg-row');


        const overlay =
            document.getElementById(
                'pf-thread-overlay'
            );


        const threadBody =
            document.getElementById(
                'pf-thread-body'
            );



        rows.forEach(row=>{


            row.onclick=async()=>{


                const id =
                    row.dataset.messageId;


                overlay.hidden=false;


                threadBody.innerHTML =
                    '<span class="pf-spinner"></span>';



                const res =
                    await fetch(
                        `ajax/profile/action_message_thread.php?id=${id}`
                    );



                threadBody.innerHTML =
                    await res.text();


                bindThreadEvents(
                    overlay
                );



            };

        });


    }





    function bindThreadEvents(overlay){


        const back =
            overlay.querySelector(
                '#pf-thread-back'
            );



        back?.addEventListener(
            'click',
            ()=>{

                overlay.hidden=true;

            });



    }






// ------------------------------------------------
// Forms
// ------------------------------------------------

    function bindPersonalForm(){


        const form =
            document.getElementById(
                'pf-personal-form'
            );


        if(!form)
            return;


        form.onsubmit=async e=>{


            e.preventDefault();


            const fd =
                new FormData(form);



            const res =
                await fetch(
                    'ajax/profile/action_update_personal.php',
                    {
                        method:'POST',
                        body:fd
                    }
                );


            const data =
                await res.json();



            if(data.ok){

                showToast(
                    'اطلاعات ذخیره شد'
                );


            }

        };


    }




    function bindSecurityForm(){


        const form =
            document.getElementById(
                'pf-password-form'
            );


        if(!form)
            return;


        form.onsubmit=async e=>{


            e.preventDefault();



            const fd =
                new FormData(form);



            const res =
                await fetch(
                    'ajax/profile/action_update_password.php',
                    {
                        method:'POST',
                        body:fd
                    }
                );



            const data =
                await res.json();



            if(data.ok){

                showToast(
                    'رمز عبور تغییر کرد'
                );

                form.reset();

            }



        };



    }






// ------------------------------------------------
// Init
// ------------------------------------------------


    const params =
        new URLSearchParams(
            location.search
        );


    const firstTab =
        params.get('tab')
        ||
        'overview';



    loadTab(
        TAB_ENDPOINTS[firstTab]
            ?
            firstTab
            :
            'overview',
        {
            pushState:false
        }
    );



})();