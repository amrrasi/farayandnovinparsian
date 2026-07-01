(function ($) {
    "use strict";

    const $form = $("#contactForm");
    if (!$form.length) return;

    const requiredFields = ["fullname", "mobile", "subject", "message"];
    const allFields       = ["fullname", "mobile", "email", "subject", "message"];

    const $submitBtn    = $("#submitBtn");
    const $charCounter  = $("#charCounter");
    const $progressFill = $(".progress-fill");
    const $progressText = $(".form-progress strong");
    const $alertBox     = $("#formAlert");

    const patterns = {
        mobile: /^09[0-9]{9}$/,
        email:  /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    };

    // ---- character counter ----
    $("#message").on("input", function () {
        $charCounter.text($(this).val().length);
    });

    // ---- per-field validity ----
    function fieldIsValid(name) {
        const $field = $("#" + name);
        const val = $field.length ? ($field.val() || "").trim() : "";

        if (requiredFields.includes(name) && val === "") return false;
        if (name === "mobile" && val !== "" && !patterns.mobile.test(val)) return false;
        if (name === "email" && val !== "" && !patterns.email.test(val)) return false;

        return true;
    }

    function updateFieldUI(name) {
        const $field = $("#" + name);
        const $wrapper = $field.closest(".floating-input, .floating-textarea");
        const val = ($field.val() || "").trim();

        $wrapper.removeClass("is-valid is-invalid");
        if (val !== "") {
            $wrapper.addClass(fieldIsValid(name) ? "is-valid" : "is-invalid");
        }
    }

    // ---- progress bar ----
    function updateProgress() {
        let filled = 0;
        allFields.forEach(function (name) {
            const $field = $("#" + name);
            if ($field.length && ($field.val() || "").trim() !== "") filled++;
        });

        const percent = Math.round((filled / allFields.length) * 100);
        $progressFill.css("width", percent + "%");
        $progressText.text(percent + "%");
    }

    // ---- overall form validity ----
    function formIsValid() {
        const fieldsOk   = allFields.every(fieldIsValid);
        const privacyOk  = $("#privacy").is(":checked");
        return fieldsOk && privacyOk;
    }

    function toggleSubmit() {
        $submitBtn.prop("disabled", !formIsValid());
    }

    $form.on("input change", "input, textarea", function () {
        const name = $(this).attr("id");
        if (name) updateFieldUI(name);
        updateProgress();
        toggleSubmit();
    });

    $("#privacy").on("change", toggleSubmit);

    // ---- alert helper ----
    function showAlert(type, message) {
        const alertClass = type === "success" ? "alert-success" : (type === "warning" ? "alert-warning" : "alert-danger");
        $alertBox.html(
            '<div class="alert ' + alertClass + '" role="alert">' + message + "</div>"
        );
    }

    // ---- submit ----
    $form.on("submit", function (e) {
        e.preventDefault();

        if (!formIsValid()) {
            showAlert("error", "لطفاً فیلدهای الزامی را به‌درستی تکمیل کنید.");
            return;
        }

        const formData = new FormData(this);
        $form.addClass("is-submitting");
        $submitBtn.prop("disabled", true);

        $.ajax({
            url: "ajax/contact_process.php",
            type: "POST",
            data: formData,
            cd Data: false,
            contentType: false,
            dataType: "json"
        })
            .done(function (response) {
                showAlert(response.status, response.message);
                if (response.status === "success") {
                    $form[0].reset();
                    $charCounter.text("0");
                    $(".floating-input, .floating-textarea").removeClass("is-valid is-invalid");
                    updateProgress();
                }
            })
            .fail(function () {
                showAlert("error", "خطا در برقراری ارتباط با سرور. لطفاً دوباره تلاش کنید.");
            })
            .always(function () {
                $form.removeClass("is-submitting");
                toggleSubmit();
            });
    });

    // initial state
    updateProgress();
    toggleSubmit();

}(jQuery));