<!--==========================================
STICKY PRODUCT INFO
===========================================-->

<?php if (!empty($product)): ?>
    <aside class="sticky-info">

        <div class="mini-card">

            <span>دسته‌بندی</span>

            <strong><?= htmlspecialchars($product['category_name'] ?? '—') ?></strong>

        </div>

        <div class="mini-card">

            <span>بازدید</span>

            <strong><?= number_format($product['visit']) ?></strong>

        </div>

        <div class="mini-card">

            <span>وضعیت</span>

            <strong class="text-success">موجود</strong>

        </div>

        <div class="mini-card">

            <span>آخرین بروزرسانی</span>

            <strong>
                <?= $product['updated_at']
                        ? date('Y/m/d', strtotime($product['updated_at']))
                        : '—' ?>
            </strong>

        </div>

        <?php if ($product['price'] > 0): ?>
            <div class="mini-card">

                <span>قیمت</span>

                <strong class="price-highlight">
                    <?= number_format($product['price']) ?> تومان
                </strong>

            </div>
        <?php endif; ?>

    </aside>
<?php endif; ?>


<!--==========================================
FLOAT CART
===========================================-->

<div class="floating-cart">

    <div class="cart-info">

        <span>

            <i class="fa-solid fa-cart-shopping"></i>

            سبد خرید

        </span>

        <strong id="floatingCartCount">

            0

        </strong>

    </div>

    <div class="cart-actions">

        <a href="cart">

            مشاهده سبد

        </a>

    </div>

</div>