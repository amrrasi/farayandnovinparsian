<!--**********************************
    Footer start
***********************************-->
<div class="footer">
    <div class="copyright">
        <p>کپی رایت © ارائه توسط <?php echo setting('name')?> <?php echo jdate('Y') ?></p>
    </div>
</div>
<!--**********************************
    Footer end
***********************************-->
<script>
    /* ---------------- CLOCK ---------------- */
    function updateClock() {
        const now = new Date();
        document.getElementById("clock").innerHTML =
            now.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>