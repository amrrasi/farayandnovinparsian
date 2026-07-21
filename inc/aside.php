<aside class="floating-card" id="floatingCard" aria-label="ابزارهای مقاله">

    <div class="fac-item fac-time" title="زمان مطالعه">
        <i class="fa-regular fa-clock"></i>
        <span><?= $minutes ?> دقیقه</span>
    </div>

    <div class="fac-divider"></div>

    <div class="fac-item fac-views" title="تعداد بازدید">
        <i class="fa-regular fa-eye"></i>
        <span id="facViews"><?= number_format($blog['visit']) ?></span>
    </div>

    <div class="fac-divider"></div>

    <button class="fac-item fac-share" id="facShare" title="کپی لینک مقاله">
        <i class="fa-solid fa-link"></i>
        <span>اشتراک</span>
    </button>

    <div class="fac-divider"></div>

    <button class="fac-item fac-top" id="facTop" title="بازگشت به بالا">
        <i class="fa-solid fa-arrow-up"></i>
        <span>بالا</span>
    </button>
    <div class="fac-divider"></div>

    <div class="fac-progress">

        <div class="progress-track">

            <span id="readingProgress" class="progress-fill"></span>

        </div>

        <small id="readingPercent">0%</small>

    </div>
    <div class="fac-toast" id="facToast">لینک کپی شد!</div>

</aside>