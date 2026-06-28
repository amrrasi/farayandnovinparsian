$(function () {

    "use strict";

    /*==============================================
    Cache
    ==============================================*/

    const form        = $("#contactForm");
    const fullname    = $("#fullname");
    const mobile      = $("#mobile");
    const email       = $("#email");
    const subject     = $("#subject");
    const message     = $("#message");
    const privacy     = $("#privacy");
    const submitBtn   = $("#submitBtn");
    const progressBar = $(".progress-fill");
    const progressText= $(".progress-text strong");
    const counter     = $("#charCounter");
    const attachment  = $("#attachment");
    const uploadBox   = $(".upload-box");

    /*==============================================
    Regex
    ==============================================*/

    const mobileRegex = /^09[0-9]{9}$/;
    const emailRegex  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /*==============================================
    Floating Label
    ==============================================*/

    $(".floating-input input").each(function () {
        toggleFloating($(this));
    });

    $(".floating-input input").on("keyup blur", function () {
        toggleFloating($(this));
    });

    function toggleFloating(input) {
        if ($.trim(input.val()) !== "") {
            input.parent().addClass("active");
        } else {
            input.parent().removeClass("active");
        }
    }

    /*==============================================
    Character Counter
    ==============================================*/

    message.on("input", function () {
        counter.text($(this).val().length);
    });

    /*==============================================
    Validation Helpers
    ==============================================*/

    function showError(input, text) {
        const wrapper = input.closest(".floating-input,.floating-select,.floating-textarea");
        wrapper.removeClass("success").addClass("error");
        wrapper.parent().find(".error-text").text(text);
    }

    function showSuccess(input) {
        const wrapper = input.closest(".floating-input,.floating-select,.floating-textarea");
        wrapper.removeClass("error").addClass("success");
        wrapper.parent().find(".error-text").text("");
    }

    function clearState(input) {
        const wrapper = input.closest(".floating-input,.floating-select,.floating-textarea");
        wrapper.removeClass("success error");
        wrapper.parent().find(".error-text").text("");
    }

    /*==============================================
    Fullname
    ==============================================*/

    fullname.on("keyup blur", function () {
        const value = $.trim($(this).val());
        if (value.length === 0) { clearState($(this)); return; }
        if (value.length < 3)   { showError($(this), "نام باید حداقل ۳ کاراکتر باشد."); return; }
        showSuccess($(this));
    });

    /*==============================================
    Mobile
    ==============================================*/

    mobile.on("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    mobile.on("keyup blur", function () {
        const value = $.trim($(this).val());
        if (value.length === 0) { clearState($(this)); return; }
        if (!mobileRegex.test(value)) { showError($(this), "شماره موبایل معتبر نیست."); return; }
        showSuccess($(this));
    });

    /*==============================================
    Email
    ==============================================*/

    email.on("keyup blur", function () {
        const value = $.trim($(this).val());
        if (value === "") { clearState($(this)); return; }
        if (!emailRegex.test(value)) { showError($(this), "فرمت ایمیل صحیح نیست."); return; }
        showSuccess($(this));
    });

    /*==============================================
    Subject  (now a plain text input — no niceSelect)
    ==============================================*/

    subject.on("keyup blur", function () {
        const value = $.trim($(this).val());
        if (value.length === 0) { clearState($(this)); return; }
        if (value.length < 2)   { showError($(this), "موضوع درخواست را وارد کنید."); return; }
        showSuccess($(this));
    });

    /*==============================================
    Message
    ==============================================*/

    message.on("keyup blur", function () {
        const value = $.trim($(this).val());
        if (value.length === 0) { clearState($(this)); return; }
        if (value.length < 20)  { showError($(this), "حداقل ۲۰ کاراکتر وارد کنید."); return; }
        showSuccess($(this));
    });

    /*==============================================
    Progress
    ==============================================*/

    function updateProgress() {
        let completed = 0;

        if ($.trim(fullname.val()).length >= 3)           completed++;
        if (mobileRegex.test($.trim(mobile.val())))       completed++;
        if (emailRegex.test($.trim(email.val())))         completed++;
        if ($.trim(subject.val()).length >= 2)            completed++;
        if ($.trim(message.val()).length >= 20)           completed++;
        if (privacy.is(":checked"))                       completed++;

        const percent = Math.round((completed / 6) * 100);
        progressBar.css("width", percent + "%");
        progressText.text(percent + "%");

        submitBtn.prop("disabled", completed < 6);
    }

    /*==============================================
    Realtime progress update
    ==============================================*/

    form.on("keyup change", function () { updateProgress(); });
    privacy.on("change",    function () { updateProgress(); });
    updateProgress();

    /*==============================================
    Upload — click
    ==============================================*/

    attachment.on("change", function () {
        if (!this.files.length) {
            uploadBox.removeClass("has-file");
            uploadBox.find("span").text("در صورت نیاز فایل خود را بارگذاری کنید");
            return;
        }
        const file = this.files[0];
        uploadBox.addClass("has-file");
        uploadBox.find("span").text(file.name);
    });

    /*==============================================
    Drag & Drop  (fixed: use DataTransfer to assign files)
    ==============================================*/

    uploadBox.on("dragover", function (e) {
        e.preventDefault();
        $(this).addClass("drag");
    });

    uploadBox.on("dragleave", function () {
        $(this).removeClass("drag");
    });

    uploadBox.on("drop", function (e) {
        e.preventDefault();
        $(this).removeClass("drag");

        const files = e.originalEvent.dataTransfer.files;
        if (!files.length) return;

        /* Assign dropped files to the hidden <input type="file"> */
        const dt = new DataTransfer();
        for (let i = 0; i < files.length; i++) {
            dt.items.add(files[i]);
        }
        attachment[0].files = dt.files;
        attachment.trigger("change");
    });

    /*==============================================
    Button ripple
    ==============================================*/

    submitBtn.on("mouseenter", function () { $(this).addClass("hover"); });
    submitBtn.on("mouseleave", function () { $(this).removeClass("hover"); });

    submitBtn.on("click", function (e) {
        if ($(this).prop("disabled")) { e.preventDefault(); return; }
    });

    /*==============================================
    Loading helpers
    ==============================================*/

    function startLoading() {
        submitBtn.addClass("loading").prop("disabled", true);
    }

    function stopLoading() {
        submitBtn.removeClass("loading");
        updateProgress();
    }

    /*==============================================
    Submit (single binding — no nesting)
    ==============================================*/

    form.on("submit", function (e) {
        e.preventDefault();
        updateProgress();
        if (submitBtn.prop("disabled")) return;
        startLoading();

        $("#formAlert").removeClass("success error").hide().html("");

        const formData = new FormData(this);

        $.ajax({
            url:         $(this).attr("action"),
            method:      "POST",
            data:        formData,
            processData: false,
            contentType: false,
            dataType:    "json",
            timeout:     30000,

            success: function (response) {
                stopLoading();

                if (response.status) {
                    $("#formAlert")
                        .addClass("success")
                        .html('<i class="fa-solid fa-circle-check"></i> ' + response.message)
                        .fadeIn();

                    $(".contact-form-card").addClass("success");
                    setTimeout(function () {
                        $(".contact-form-card").removeClass("success");
                    }, 1500);

                    /* Reset form */
                    form[0].reset();
                    attachment.val("");
                    uploadBox.removeClass("has-file");
                    uploadBox.find("span").text("در صورت نیاز فایل خود را بارگذاری کنید");
                    counter.text("0");
                    progressBar.css("width", "0%");
                    progressText.text("0%");
                    $(".floating-input").removeClass("active success error");
                    $(".floating-textarea").removeClass("success error");
                    $(".floating-select").removeClass("success error");
                    $(".error-text").text("");
                    submitBtn.prop("disabled", true);

                    if (response.ticket) {
                        $("#formAlert").append(
                            '<br><small>شماره پیگیری : <strong>' +
                            response.ticket + '</strong></small>'
                        );
                    }

                } else {
                    $("#formAlert")
                        .addClass("error")
                        .html('<i class="fa-solid fa-circle-xmark"></i> ' + response.message)
                        .fadeIn();
                }
            },

            error: function (xhr) {
                stopLoading();
                let msg = "خطایی در ارتباط با سرور رخ داده است.";
                if (xhr.status === 422) msg = "اطلاعات وارد شده معتبر نیست.";
                if (xhr.status === 500) msg = "خطای داخلی سرور.";
                $("#formAlert")
                    .addClass("error")
                    .html('<i class="fa-solid fa-circle-exclamation"></i> ' + msg)
                    .fadeIn();
            }
        });
    });

});