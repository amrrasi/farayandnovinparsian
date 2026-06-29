<section class="footer-cta">

    <div class="container">

        <div class="footer-cta-box">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <span class="section-badge">

                        شروع همکاری

                    </span>

                    <h2>

                        آماده ارتقای زیرساخت سازمان خود هستید؟

                    </h2>

                    <p>

                        کارشناسان ما آماده‌اند تا متناسب با نیاز کسب‌وکار شما بهترین راهکار ذخیره‌سازی،
                        سرور و تجهیزات شبکه را پیشنهاد دهند.

                    </p>

                </div>

                <div class="col-lg-4">

                    <div class="footer-cta-buttons">

                        <a href="about-us/" class="btn-main">

                           تماس با ما

                        </a>

                        <a href="contact-us/" class="btn-second">

                            دریافت مشاوره

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<footer class="footer">

    <div class="container">

        <div class="row gy-5">


            <div class="col-lg-4">

                <img src="assets/images/logo.png"
                     class="footer-logo">

                <p class="footer-about">

                    شرکت فرآیند نوین اطلاعات پارسیان با سال‌ها تجربه در زمینه تجهیزات ذخیره‌سازی،
                    سرور، دیتاسنتر و زیرساخت شبکه، راهکارهای تخصصی برای سازمان‌ها و شرکت‌های بزرگ
                    ارائه می‌دهد.

                </p>

                <div class="footer-social">

                    <a href="<?= setting('instagram') ?>"><i class="fab fa-instagram"></i></a>

                    <a href="<?= setting('linkedin') ?>"><i class="fab fa-linkedin"></i></a>

                    <a href="<?= setting('telegram') ?>"><i class="fab fa-telegram"></i></a>

                    <a href="<?= setting('whatsapp') ?>"><i class="fab fa-whatsapp"></i></a>

                </div>

            </div>


            <div class="col-lg-2 col-md-4">

                <h5>

                    محصولات

                </h5>

                <ul>

                    <?php

                    $stmt = $pdo->prepare("
                                SELECT name, seo_slug
                                FROM product
                                WHERE active = 1
                                  AND deleted = 0
                                ORDER BY myorder ASC, id DESC
                                LIMIT 5
                            ");

                    $stmt->execute();

                    $productsFooter = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($productsFooter as $product):

                        ?>

                        <li>

                            <a href="product/<?= htmlspecialchars($product['seo_slug']) ?>">

                                <?= htmlspecialchars($product['name']) ?>

                            </a>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>


            <div class="col-lg-2 col-md-4">

                <h5>

                    دسترسی سریع

                </h5>

                <ul>

                    <li><a href="about-us/">درباره ما</a></li>

                    <li><a href="#our-services">خدمات</a></li>

                    <li><a href="blogs/">مقالات</a></li>

                    <li><a href="contact-us/">تماس با ما</a></li>

                    <li><a href="team/">تیم ما</a></li>

                </ul>

            </div>


            <div class="col-lg-4 col-md-4">

                <h5>

                    اطلاعات تماس

                </h5>

                <div class="footer-contact">

                    <div>

                        <i class="fas fa-location-dot"></i>

                        <?= setting('address') ?>

                    </div>

                    <div>

                        <i class="fas fa-phone"></i>

                        <a href="tel:<?= setting('phone') ?>"><?= setting('phone') ?></a>

                    </div>

                    <div>

                        <i class="fas fa-envelope"></i>

                        <a href="mailto:<?= setting('email') ?>"><?= setting('email') ?></a>

                    </div>

                    <div>

                        <i class="fas fa-clock"></i>

                        <?= setting('workTime') ?>

                    </div>

                </div>

            </div>

        </div>

        <div class="footer-bottom">

             <span>

                © تمامی حقوق برای <?= setting('name') ?> محفوظ است.

            </span>

            <div class="footer-enamad">

                <img src="assets/images/enamad.png" alt="enamad logo">

                <img src="assets/images/samandehi.png" alt="samandehi logo">

            </div>



        </div>

    </div>

</footer>