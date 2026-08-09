(function ($) {
    "use strict";

    const $form = $("#contactForm");
    if (!$form.length) return;

    /*──────────────────────────────────────────
     Cache
    ──────────────────────────────────────────*/
    const $fullname    = $("#fullname");
    const $mobile      = $("#mobile");
    const $email       = $("#email");
    const $subject     = $("#subject");
    const $message     = $("#message");
    const $privacy     = $("#privacy");
    const $submitBtn   = $("#submitBtn");
    const $charCounter = $("#charCounter");
    const $progressFill= $(".progress-fill");
    // FIX: was $(".form-progress strong") — HTML uses .progress-text strong
    const $progressText= $(".progress-text strong");
    const $alertBox    = $("#formAlert");
    const $attachment  = $("#attachment");
    const $uploadBox   = $(".upload-box");

    /*──────────────────────────────────────────
     Regex
    ──────────────────────────────────────────*/
    const mobileRegex = /^09[0-9]{9}$/;
    const emailRegex  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /*──────────────────────────────────────────
     Floating label
    ──────────────────────────────────────────*/
    function syncFloating($input) {
        $input.parent().toggleClass("active", $.trim($input.val()) !== "");
    }

    $(".floating-input input").each(function () { syncFloating($(this)); });
    $(".floating-input input").on("keyup blur", function () { syncFloating($(this)); });

    /*──────────────────────────────────────────
     Character counter
    ──────────────────────────────────────────*/
    $message.on("input", function () {
        $charCounter.text($(this).val().length);
    });

    /*──────────────────────────────────────────
     Validation helpers
    ──────────────────────────────────────────*/
    function showError($input, text) {
        const $w = $input.closest(".floating-input,.floating-select,.floating-textarea");
        $w.removeClass("success is-valid").addClass("error is-invalid");
        $w.parent().find(".error-text").text(text);
    }

    function showSuccess($input) {
        const $w = $input.closest(".floating-input,.floating-select,.floating-textarea");
        $w.removeClass("error is-invalid").addClass("success is-valid");
        $w.parent().find(".error-text").text("");
    }

    function clearState($input) {
        const $w = $input.closest(".floating-input,.floating-select,.floating-textarea");
        $w.removeClass("success error is-valid is-invalid");
        $w.parent().find(".error-text").text("");
    }

    /*──────────────────────────────────────────
     Per-field live validation
    ──────────────────────────────────────────*/
    $fullname.on("keyup blur", function () {
        const v = $.trim($(this).val());
        if (!v)          { clearState($(this)); return; }
        if (v.length < 3){ showError($(this), "نام باید حداقل ۳ کاراکتر باشد."); return; }
        showSuccess($(this));
    });

    $mobile.on("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });
    $mobile.on("keyup blur", function () {
        const v = $.trim($(this).val());
        if (!v)                        { clearState($(this)); return; }
        if (!mobileRegex.test(v))      { showError($(this), "شماره موبایل معتبر نیست."); return; }
        showSuccess($(this));
    });

    $email.on("keyup blur", function () {
        const v = $.trim($(this).val());
        if (!v)                       { clearState($(this)); return; }
        if (!emailRegex.test(v))      { showError($(this), "فرمت ایمیل صحیح نیست."); return; }
        showSuccess($(this));
    });

    $subject.on("keyup blur", function () {
        const v = $.trim($(this).val());
        if (!v)          { clearState($(this)); return; }
        if (v.length < 2){ showError($(this), "موضوع درخواست را وارد کنید."); return; }
        showSuccess($(this));
    });

    $message.on("keyup blur", function () {
        const v = $.trim($(this).val());
        if (!v)           { clearState($(this)); return; }
        if (v.length < 20){ showError($(this), "حداقل ۲۰ کاراکتر وارد کنید."); return; }
        showSuccess($(this));
    });

    /*──────────────────────────────────────────
     Progress bar
     Tracks: fullname, mobile, email, subject, message, privacy (6 steps)
     Email is optional but counts toward progress once valid
    ──────────────────────────────────────────*/
    function updateProgress() {
        let completed = 0;
        if ($.trim($fullname.val()).length >= 3)          completed++;
        if (mobileRegex.test($.trim($mobile.val())))      completed++;
        if (emailRegex.test($.trim($email.val())))        completed++;   // optional — counts if filled
        if ($.trim($subject.val()).length >= 2)            completed++;
        if ($.trim($message.val()).length >= 20)           completed++;
        if ($privacy.is(":checked"))                       completed++;

        const percent = Math.round((completed / 6) * 100);
        $progressFill.css("width", percent + "%");
        $progressText.text(percent + "%");

        return completed;
    }

    /*──────────────────────────────────────────
     Overall form validity (submit gate)
     Email is optional — not required for submission
    ──────────────────────────────────────────*/
    function formIsValid() {
        const nameOk    = $.trim($fullname.val()).length >= 3;
        const mobileOk  = mobileRegex.test($.trim($mobile.val()));
        const subjectOk = $.trim($subject.val()).length >= 2;
        const messageOk = $.trim($message.val()).length >= 20;
        const emailVal  = $.trim($email.val());
        const emailOk   = emailVal === "" || emailRegex.test(emailVal);
        const privacyOk = $privacy.is(":checked");
        return nameOk && mobileOk && subjectOk && messageOk && emailOk && privacyOk;
    }

    function toggleSubmit() {
        $submitBtn.prop("disabled", !formIsValid());
    }

    $form.on("keyup change", function () { updateProgress(); toggleSubmit(); });
    $privacy.on("change",    function () { updateProgress(); toggleSubmit(); });

    /*──────────────────────────────────────────
     File upload — click
    ──────────────────────────────────────────*/
    $attachment.on("change", function () {
        if (!this.files.length) {
            $uploadBox.removeClass("has-file");
            $uploadBox.find("span").text("در صورت نیاز فایل خود را بارگذاری کنید");
            return;
        }
        $uploadBox.addClass("has-file");
        $uploadBox.find("span").text(this.files[0].name);
    });

    /*──────────────────────────────────────────
     File upload — drag & drop
    ──────────────────────────────────────────*/
    $uploadBox.on("dragover", function (e) {
        e.preventDefault();
        $(this).addClass("drag");
    });
    $uploadBox.on("dragleave", function () {
        $(this).removeClass("drag");
    });
    $uploadBox.on("drop", function (e) {
        e.preventDefault();
        $(this).removeClass("drag");
        const files = e.originalEvent.dataTransfer.files;
        if (!files.length) return;
        const dt = new DataTransfer();
        for (let i = 0; i < files.length; i++) dt.items.add(files[i]);
        $attachment[0].files = dt.files;
        $attachment.trigger("change");
    });

    /*──────────────────────────────────────────
     Button ripple / hover
    ──────────────────────────────────────────*/
    $submitBtn.on("mouseenter", function () { $(this).addClass("hover"); });
    $submitBtn.on("mouseleave", function () { $(this).removeClass("hover"); });

    /*──────────────────────────────────────────
     Loading state helpers
    ──────────────────────────────────────────*/
    function startLoading() {
        $submitBtn.addClass("loading").prop("disabled", true);
    }
    function stopLoading() {
        $submitBtn.removeClass("loading");
        toggleSubmit();
    }

    /*──────────────────────────────────────────
     Alert helper
     FIX: unified — previously contact-us.js used raw html inject while
     contact_submit.js used Bootstrap alert classes; now one consistent style.
    ──────────────────────────────────────────*/
    function showAlert(status, message) {
        const iconMap = {
            success: "fa-circle-check",
            warning: "fa-triangle-exclamation",
            error:   "fa-circle-xmark"
        };
        const icon = iconMap[status] || "fa-circle-exclamation";
        $alertBox
            .removeClass("success warning error")
            .addClass(status)
            .html('<i class="fa-solid ' + icon + '"></i> ' + message)
            .fadeIn();
    }

    /*──────────────────────────────────────────
     Reset helpers
    ──────────────────────────────────────────*/
    function resetForm() {
        $form[0].reset();
        $attachment.val("");
        $uploadBox.removeClass("has-file");
        $uploadBox.find("span").text("در صورت نیاز فایل خود را بارگذاری کنید");
        $charCounter.text("0");
        $progressFill.css("width", "0%");
        $progressText.text("0%");
        $(".floating-input").removeClass("active success error is-valid is-invalid");
        $(".floating-textarea").removeClass("success error is-valid is-invalid");
        $(".floating-select").removeClass("success error is-valid is-invalid");
        $(".error-text").text("");
        $submitBtn.prop("disabled", true);
    }

    /*──────────────────────────────────────────
     Submit — single binding
     FIX: action URL is now set in contact-us.php form[action] attribute.
     FIX: response.status is "success"/"warning"/"error" (string), not boolean.
    ──────────────────────────────────────────*/
    $form.on("submit", function (e) {
        e.preventDefault();

        if (!formIsValid()) {
            showAlert("error", "لطفاً فیلدهای الزامی را به‌درستی تکمیل کنید.");
            return;
        }

        startLoading();
        $alertBox.removeClass("success warning error").hide().html("");

        $.ajax({
            url:         $form.attr("action"),   // "ajax/contact_process.php"
            method:      "POST",
            data:        new FormData(this),
            processData: false,
            contentType: false,
            dataType:    "json",
            timeout:     30000,

            success: function (response) {
                stopLoading();
                showAlert(response.status, response.message);

                if (response.status === "success") {
                    $(".contact-form-card").addClass("success");
                    setTimeout(function () {
                        $(".contact-form-card").removeClass("success");
                    }, 1500);

                    resetForm();

                    // Show tracking ticket number if returned
                    if (response.ticket) {
                        $alertBox.append(
                            '<br><small>شماره پیگیری: <strong>' + response.ticket + '</strong></small>'
                        );
                    }
                }
            },

            error: function (xhr) {
                stopLoading();
                let msg = "خطایی در ارتباط با سرور رخ داده است.";
                if (xhr.status === 422) msg = "اطلاعات وارد شده معتبر نیست.";
                if (xhr.status === 500) msg = "خطای داخلی سرور.";
                showAlert("error", msg);
            }
        });
    });

    /*──────────────────────────────────────────
     Initial state
    ──────────────────────────────────────────*/
    updateProgress();
    toggleSubmit();

}(jQuery));