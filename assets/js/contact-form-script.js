/*==============================================================*/
// Contact Form  JS
/*==============================================================*/
(function ($) {
    "use strict"; // Start of use strict
    $("#contactForm").validator().on("submit", function (event) {
        if (event.isDefaultPrevented()) {
            formError();
            submitMSG(false, "آیا فرم را به درستی پر کردید؟");
        }
        else {
            event.preventDefault();
            submitForm();
        }
    });

    function submitForm(){
        // Initiate Variables With Form Content
        var name = $("#name").val();
        var email = $("#email").val();
        var msg_subject = $("#msg_subject").val();
        var phone_number = $("#phone_number").val();
        var message = $("#message").val();
        var gridCheck = $("#gridCheck").val();

        $.ajax({
            type: "POST",
            url: "assets/php/form-process.php",
            data: "name=" + name + "&email=" + email + "&msg_subject=" + msg_subject + "&phone_number=" + phone_number + "&message=" + message +"&gridCheck=" + gridCheck,
            success : function(text){
                if (text == "success"){
                    formSuccess();
                }
                else {
                    formError();
                    submitMSG(false,text);
                }
            }
        });
    }
    function formSuccess(){
        $("#contactForm")[0].reset();
        submitMSG(true, "پیام ارسال شد!")
    }
    function formError(){
        $("#contactForm").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
            $(this).removeClass();
        });
    }
    function submitMSG(valid, msg){
        if(valid){
            var msgClasses = "h4 tada animated text-success";
        }
        else {
            var msgClasses = "h4 text-danger";
        }
        $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
    }
    document.getElementById("newsletterForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        fetch("ajax/newsletter_save.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                let resultBox = document.getElementById("validator-newsletter");
                if (data.status === "success") {
                    resultBox.style.color = "green";
                    form.reset();
                } else {
                    resultBox.style.color = "red";
                }
                resultBox.innerText = data.message;
            })
            .catch(() => {
                document.getElementById("validator-newsletter").innerText = "خطای سرور! دوباره تلاش کنید.";
            });
    });
    $("#contactFormTwo").on("submit", function(e) {
        e.preventDefault();

        let $form = $(this);
        let $button = $form.find("button[type=submit]");
        let $msg = $("#msgSubmitTwo");

        $button.prop("disabled", true).text("در حال ارسال...");

        $.ajax({
            type: "POST",
            url: "ajax/contact_process.php",
            data: $form.serialize(),
            dataType: "json",
            success: function(response) {
                let alertClass = (response.status === "success") ? "success" : "danger";

                $msg.html(`
                <div class="alert alert-${alertClass} mt-3" role="alert">
                    ${response.message}
                </div>
            `);

                if (response.status === "success") {
                    $form[0].reset();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                $msg.removeClass("hidden text-success")
                    .addClass("text-danger")
                    .html(`
            <div class="alert alert-${alertClass} mt-3" role="alert">
                ${response.message}
            </div>
        `);
            },

            complete: function() {
                $button.prop("disabled", false).text("ارسال پیام");
            }
        });
    });

}(jQuery));