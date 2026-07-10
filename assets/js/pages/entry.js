$(function () {

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const redirectTarget = (window.ENTRY && window.ENTRY.redirect) || '/';

    /* ---------------------------------------------------------
       TOAST
    --------------------------------------------------------- */
    function showToast(message, isError) {

        const $toast = $('#facToast');

        $toast.find('span').text(message);
        $toast.toggleClass('error', !!isError);
        $toast.addClass('show');

        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => $toast.removeClass('show'), 3500);
    }

    /* ---------------------------------------------------------
       TABS (login / register)
    --------------------------------------------------------- */
    $('.tab-btn').on('click', function () {

        const tab = $(this).data('tab');

        $('.tab-btn').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');

        $('.auth-tabs').attr('data-active', tab);

        $('.auth-form').removeClass('active');
        $('#' + tab + 'Form').addClass('active');
    });

    /* ---------------------------------------------------------
       PASSWORD SHOW/HIDE
    --------------------------------------------------------- */
    $('.toggle-pass').on('click', function () {

        const $input = $('#' + $(this).data('target'));
        const isPassword = $input.attr('type') === 'password';

        $input.attr('type', isPassword ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    /* ---------------------------------------------------------
       PASSWORD STRENGTH (register form only)
    --------------------------------------------------------- */
    $('#regPassword').on('input', function () {

        const val = $(this).val();
        let score = 0;

        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const $bar = $('#passStrength span');
        const widths = ['8%', '35%', '65%', '85%', '100%'];
        const colors = ['var(--clr-danger)', 'var(--clr-danger)', 'var(--clr-warning)', 'var(--clr-success)', 'var(--clr-success)'];

        $bar.css({
            width: val.length ? widths[score] : '0%',
            background: colors[score],
        });
    });

    /* ---------------------------------------------------------
       LOGIN
    --------------------------------------------------------- */
    $('#loginForm').on('submit', function (e) {

        e.preventDefault();

        const $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).text('در حال ورود...');

        $.post('ajax/auth/login.php', {
            csrf: csrfToken,
            identifier: $('#loginIdentifier').val().trim(),
            password: $('#loginPassword').val(),
            remember: $('input[name=remember]').is(':checked') ? 1 : 0,
            redirect: redirectTarget,
        }, null, 'json')
            .done(function (res) {

                if (res.status) {
                    showToast(res.message);
                    setTimeout(() => window.location = res.redirect || '/', 600);
                } else {
                    $btn.prop('disabled', false).text('ورود');
                    showToast(res.message, true);
                }
            })
            .fail(function () {
                $btn.prop('disabled', false).text('ورود');
                showToast('خطا در ارتباط با سرور', true);
            });
    });

    /* ---------------------------------------------------------
       REGISTER
    --------------------------------------------------------- */
    $('#registerForm').on('submit', function (e) {

        e.preventDefault();

        const password = $('#regPassword').val();
        const confirmPassword = $('#regConfirmPassword').val();

        if (password !== confirmPassword) {
            showToast('رمز عبور و تکرار آن یکسان نیستند.', true);
            return;
        }

        const $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).text('در حال ثبت‌نام...');

        $.post('ajax/auth/register.php', {
            csrf: csrfToken,
            name: $('#regName').val().trim(),
            mobile: $('#regMobile').val().trim(),
            email: $('#regEmail').val().trim(),
            password: password,
            confirmPassword: confirmPassword,
            terms: $('#regTerms').is(':checked') ? 1 : 0,
            redirect: redirectTarget,
        }, null, 'json')
            .done(function (res) {

                if (res.status) {
                    showToast(res.message);
                    setTimeout(() => window.location = res.redirect || '/', 600);
                } else {
                    $btn.prop('disabled', false).text('ایجاد حساب');
                    showToast(res.message, true);
                }
            })
            .fail(function () {
                $btn.prop('disabled', false).text('ایجاد حساب');
                showToast('خطا در ارتباط با سرور', true);
            });
    });

    /* ---------------------------------------------------------
       FORGOT PASSWORD — navigation between views
    --------------------------------------------------------- */
    $('#goForgotPassword').on('click', function () {
        $('#authTabsView').addClass('hidden');
        $('#forgotPasswordView').removeClass('hidden');
        $('#fpRequestForm').addClass('active');
        $('#fpVerifyForm').removeClass('active');
    });

    $('#backToLogin').on('click', function () {
        $('#forgotPasswordView').addClass('hidden');
        $('#authTabsView').removeClass('hidden');
    });

    let fpEmail = '';
    let resendInterval = null;

    function startResendCountdown(seconds) {

        clearInterval(resendInterval);

        const $btn = $('#resendOtpBtn');
        const $timer = $('#resendTimer');

        $btn.prop('disabled', true);

        let remaining = seconds;

        function tick() {
            if (remaining <= 0) {
                clearInterval(resendInterval);
                $timer.text('');
                $btn.prop('disabled', false);
                return;
            }
            $timer.text('ارسال مجدد تا ' + remaining + ' ثانیه دیگر');
            remaining--;
        }

        tick();
        resendInterval = setInterval(tick, 1000);
    }

    function requestOtp(email, $btn) {

        $btn && $btn.prop('disabled', true);

        $.post('ajax/auth/forgotPassword.php', {
            csrf: csrfToken,
            email: email,
        }, null, 'json')
            .done(function (res) {

                showToast(res.message, !res.status);

                if (res.status) {

                    fpEmail = email;
                    $('#fpSentToEmail').text(email);

                    $('#fpRequestForm').removeClass('active');
                    $('#fpVerifyForm').addClass('active');

                    $('.otp-box').val('');
                    $('.otp-box').first().trigger('focus');

                    startResendCountdown(res.retryAfter || 60);
                }

                $btn && $btn.prop('disabled', false);
            })
            .fail(function () {
                showToast('خطا در ارتباط با سرور', true);
                $btn && $btn.prop('disabled', false);
            });
    }

    $('#fpRequestForm').on('submit', function (e) {

        e.preventDefault();

        const email = $('#fpEmail').val().trim();

        if (!email) {
            showToast('ایمیل را وارد کنید.', true);
            return;
        }

        requestOtp(email, $('#fpSendBtn'));
    });

    $('#resendOtpBtn').on('click', function () {
        if (fpEmail) requestOtp(fpEmail, $(this));
    });

    /* ---------------------------------------------------------
       OTP BOXES — auto advance / backspace / paste
    --------------------------------------------------------- */
    $('.otp-box').on('input', function () {

        const $this = $(this);
        $this.val($this.val().replace(/\D/g, '').slice(0, 1));

        if ($this.val()) {
            $this.next('.otp-box').trigger('focus');
        }
    });

    $('.otp-box').on('keydown', function (e) {

        if (e.key === 'Backspace' && !$(this).val()) {
            $(this).prev('.otp-box').trigger('focus');
        }
    });

    $('.otp-box').on('paste', function (e) {

        const pasted = (e.originalEvent.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');

        if (!pasted) return;

        e.preventDefault();

        $('.otp-box').each(function (i) {
            $(this).val(pasted[i] || '');
        });

        $('.otp-box').filter((i, el) => !$(el).val()).first().trigger('focus');
    });

    function collectOtp() {
        return $('.otp-box').map(function () { return $(this).val(); }).get().join('');
    }

    /* ---------------------------------------------------------
       VERIFY OTP + RESET PASSWORD
    --------------------------------------------------------- */
    $('#fpVerifyForm').on('submit', function (e) {

        e.preventDefault();

        const otp = collectOtp();

        if (otp.length !== 6) {
            showToast('کد ۶ رقمی را کامل وارد کنید.', true);
            return;
        }

        const newPassword = $('#fpNewPassword').val();
        const confirmPassword = $('#fpConfirmPassword').val();

        if (newPassword !== confirmPassword) {
            showToast('رمز عبور و تکرار آن یکسان نیستند.', true);
            return;
        }

        const $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).text('در حال بررسی...');

        $.post('ajax/auth/verifyOtp.php', {
            csrf: csrfToken,
            email: fpEmail,
            otp: otp,
            newPassword: newPassword,
            confirmPassword: confirmPassword,
        }, null, 'json')
            .done(function (res) {

                $btn.prop('disabled', false).text('تغییر رمز عبور');
                showToast(res.message, !res.status);

                if (res.status) {
                    setTimeout(function () {
                        $('#forgotPasswordView').addClass('hidden');
                        $('#authTabsView').removeClass('hidden');
                        $('.tab-btn[data-tab=login]').trigger('click');
                        $('#loginIdentifier').val(fpEmail);
                    }, 800);
                }
            })
            .fail(function () {
                $btn.prop('disabled', false).text('تغییر رمز عبور');
                showToast('خطا در ارتباط با سرور', true);
            });
    });

});
