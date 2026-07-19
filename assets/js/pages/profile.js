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


        if(tab==='orders')
            bindOrdersTab();


        if(tab==='messages')
            bindMessagesTab();


        if(tab==='personal')
            bindPersonalForm();


        if(tab==='security')
            bindSecurityForm();

    }






// ------------------------------------------------
// Orders / receipt upload
// ------------------------------------------------

    function bindOrdersTab(){


        const CSRF =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.content
            ||
            '';


        const fileMap = {};



        function setFile(orderId, file){

            if(!file) return;


            if(file.size > 5 * 1024 * 1024){

                showToast(
                    'حجم فایل بیش از ۵ مگابایت است',
                    true
                );

                return;

            }


            fileMap[orderId] = file;


            const nameEl =
                document.getElementById(
                    'receipt-name-' + orderId
                );


            if(nameEl)
                nameEl.textContent = file.name;


            document.getElementById(
                'receipt-preview-' + orderId
            )?.classList.remove('hidden');


            const upBtn =
                document.getElementById(
                    'btn-upload-' + orderId
                );


            if(upBtn)
                upBtn.disabled = false;

        }



        /* drag-drop */

        document.querySelectorAll(
            '.pf-upload-zone'
        ).forEach(zone=>{


            zone.addEventListener(
                'dragover',
                e=>{

                    e.preventDefault();

                    zone.classList.add(
                        'drag-over'
                    );

                });


            zone.addEventListener(
                'dragleave',
                e=>{

                    if(!zone.contains(
                        e.relatedTarget
                    ))

                        zone.classList.remove(
                            'drag-over'
                        );

                });


            zone.addEventListener(
                'drop',
                e=>{

                    e.preventDefault();

                    zone.classList.remove(
                        'drag-over'
                    );

                    setFile(
                        zone.dataset.order,
                        e.dataTransfer.files[0]
                    );

                });

        });



        /* file input change */

        document.querySelectorAll(
            '.pf-receipt-input'
        ).forEach(input=>{


            input.addEventListener(
                'change',
                function(){

                    setFile(
                        this.dataset.order,
                        this.files[0]
                    );

                });

        });



        /* remove file */

        document.querySelectorAll(
            '.pf-remove-file'
        ).forEach(btn=>{


            btn.addEventListener(
                'click',
                function(){


                    const id =
                        this.dataset.order;


                    delete fileMap[id];


                    const inp =
                        document.getElementById(
                            'receipt-file-' + id
                        );


                    if(inp)
                        inp.value = '';


                    document.getElementById(
                        'receipt-preview-' + id
                    )?.classList.add('hidden');


                    const upBtn =
                        document.getElementById(
                            'btn-upload-' + id
                        );


                    if(upBtn)
                        upBtn.disabled = true;

                });

        });



        /* upload */

        document.querySelectorAll(
            '.btn-upload-receipt'
        ).forEach(btn=>{


            btn.addEventListener(
                'click',
                async function(){


                    const id =
                        this.dataset.order;


                    const file =
                        fileMap[id];


                    if(!file)
                        return;


                    this.disabled = true;

                    this.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin"></i> در حال ارسال…';


                    const fd =
                        new FormData();


                    fd.append('csrf_token', CSRF);
                    fd.append('order_id',   id);
                    fd.append('receipt',    file);


                    const msgEl =
                        document.getElementById(
                            'upload-msg-' + id
                        );


                    try{


                        const res =
                            await fetch(
                                'ajax/profile/uploadReceipt.php',
                                {
                                    method:'POST',
                                    body:fd
                                }
                            );


                        if(res.status===401){

                            location.href='/login';

                            return;

                        }


                        const data =
                            await res.json();


                        if(msgEl){

                            msgEl.textContent =
                                data.message
                                ||
                                (
                                    data.status
                                        ?
                                        'رسید با موفقیت ارسال شد.'
                                        :
                                        'خطا در ارسال'
                                );


                            msgEl.className =
                                'pf-upload-msg '
                                +
                                (
                                    data.status
                                        ?
                                        'success'
                                        :
                                        'error'
                                );


                            msgEl.classList.remove(
                                'hidden'
                            );

                        }


                        if(data.status){


                            showToast(
                                data.message
                                ||
                                'رسید با موفقیت ارسال شد.'
                            );


                            setTimeout(
                                ()=>loadTab(
                                    'orders',
                                    {
                                        pushState:false
                                    }
                                ),
                                1200
                            );


                        }
                        else{


                            this.disabled = false;

                            this.innerHTML =
                                '<i class="fa-solid fa-paper-plane"></i> ارسال رسید';


                            showToast(
                                data.message
                                ||
                                'خطا در ارسال رسید',
                                true
                            );

                        }


                    }
                    catch(err){


                        this.disabled = false;

                        this.innerHTML =
                            '<i class="fa-solid fa-paper-plane"></i> ارسال رسید';


                        if(msgEl){

                            msgEl.textContent =
                                'خطا در اتصال به سرور';

                            msgEl.className =
                                'pf-upload-msg error';

                            msgEl.classList.remove(
                                'hidden'
                            );

                        }


                        showToast(
                            'خطا در اتصال به سرور',
                            true
                        );

                    }


                });

        });


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