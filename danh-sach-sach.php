<?php

require __DIR__ . '/includes.php';

$catalog_hero = [
    'bg'     => 'images/hero-bookshelf.jpg',
    'crumbs' => [
        ['label' => 'Danh sách sách', 'active' => true],
        ['label' => 'Trang chủ'],
    ],
    'search' => [
        'label'       => 'Tìm nhanh',
        'placeholder' => 'Nhập Tiêu Đề, LSBN, Tác giả, Số ĐKCB',
        'submit'      => 'Tìm kiếm',
    ],
];

$sort = [
    'label'   => 'Sắp xếp theo',
    'options' => ['Tất cả', 'Phổ biến', 'Đã ra mắt', 'Giá thấp nhất'],
    'active'  => 0, // chỉ số của mục đang chọn trong 'options'
];

$categories = [
    [
        'label'  => 'Sách thiếu nhi',
        'covers' => [
            // ẢNH: mỗi dòng dưới đây là 1 bìa sách trong danh mục "Sách thiếu nhi"
            ['src' => 'images/thieu-nhi1.jpg',   'alt' => 'Cổ Tích Của Bà'],
            ['src' => 'images/thieu-nhi2.jpg',   'alt' => 'Hiệp Sĩ Bọ Rùa Và Hội Mọt Sách'],
            ['src' => 'images/thieu-nhi3.jpg',   'alt' => 'Ước Trong Chai'],
        ],
    ],
    [
        'label'  => 'Manga',
        'covers' => [
            // ẢNH: mỗi dòng dưới đây là 1 bìa sách trong danh mục "Manga"
            ['src' => 'images/manga1.png',      'alt' => 'One Piece'],
            ['src' => 'images/manga2.jpg',  'alt' => 'Sakamoto Days'],
            ['src' => 'images/manga3.jpg',        'alt' => 'Dragon...'],
        ],
    ],
];

/* ---------- Hàm dựng giao diện riêng của trang danh sách sách ---------- */

function render_catalog_hero(array $hero): void
{
    ?>
    <div class="catalog-hero">
        <!-- ẢNH: nền banner, lấy từ $catalog_hero['bg'] -->
        <div class="catalog-hero-bg" style="background-image:url('<?= esc($hero['bg']) ?>');"></div>
        <div class="catalog-hero-overlay"></div>

        <div class="catalog-hero-content">
            <h1 class="breadcrumb">
                <?php foreach ($hero['crumbs'] as $i => $crumb): ?>
                    <?php if ($i > 0): ?><span class="crumb-sep">/</span><?php endif; ?>
                    <span class="<?= !empty($crumb['active']) ? 'crumb-active' : 'crumb' ?>"><?= esc($crumb['label']) ?></span>
                <?php endforeach; ?>
            </h1>
        </div>

        <div class="quick-search">
            <div class="quick-search-text">
                <strong><?= esc($hero['search']['label']) ?></strong>
                <span>/ <?= esc($hero['search']['placeholder']) ?></span>
            </div>
            <button type="button" class="btn-search"><?= esc($hero['search']['submit']) ?></button>
        </div>
    </div>
    <?php
}

function render_sort_bar(array $sort): void
{
    ?>
    <div class="sort-bar">
        <span class="sort-label"><?= esc($sort['label']) ?></span>
        <div class="sort-tabs">
            <?php foreach ($sort['options'] as $i => $option): ?>
                <button type="button" class="sort-tab <?= $i === $sort['active'] ? 'active' : '' ?>"><?= esc($option) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function render_book_category(array $cat): void
{
    ?>
    <div class="book-category">
        <span class="category-label"><?= esc($cat['label']) ?></span>
        <div class="category-track-wrap">
            <div class="category-track">
                <?php foreach ($cat['covers'] as $book): ?>
                    <!-- ẢNH: bìa sách trong danh mục "<?= esc($cat['label']) ?>" -->
                    <img src="<?= esc($book['src']) ?>" alt="<?= esc($book['alt']) ?>">
                <?php endforeach; ?>
            </div>
            <div class="category-dots">
                <?php foreach ($cat['covers'] as $book): ?>
                    <span></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Danh sách sách - Thư viện</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php render_header($nav, 'books'); ?>
<?php render_catalog_hero($catalog_hero); ?>

<div class="catalog-body">
    <?php render_sort_bar($sort); ?>
    <?php foreach ($categories as $cat): ?>
        <?php render_book_category($cat); ?>
    <?php endforeach; ?>
</div>

<?php render_footer($footer); ?>

</body>
</html>
