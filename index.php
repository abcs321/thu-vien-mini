<?php
/**
 * Trang chủ thư viện (bản Harry Potter) — đã tách phần dùng chung ra includes.php.
 * Header/Footer/menu dùng chung: xem includes.php.
 * File này chỉ còn giữ dữ liệu + giao diện RIÊNG của trang chủ:
 *   - $featured / render_featured() : khối sách nổi bật, có info-bg dạng
 *     GHÉP DẢI nhiều ảnh (bg_images) thay vì 1 ảnh mờ như bản gốc.
 *   - $carousel / render_carousel() : dải "Sắp ra mắt", ảnh hiển thị kiểu
 *     background-image (object-fit contain) thay vì thẻ <img>.
 *   - $grid / render_grid()         : lưới sách thể thao.
 *
 * ================== GHI CHÚ: CÁC CHỖ CẦN THAY ẢNH ==================
 *   1. $featured['cover']            -> ảnh bìa lớn bên trái
 *   2. $featured['bg_images'][0..n]  -> DÃY ảnh nền ghép dải bên phải (info-bg)
 *   3. $carousel['covers'][0..n]     -> ảnh trong dải "Sắp ra mắt"
 *   4. $grid['items'][n]['cover']    -> ảnh từng cuốn trong lưới sách thể thao
 *   5. $hero['bg']                    -> ảnh nền banner đầu trang
 * =====================================================================
 */

require __DIR__ . '/includes.php'; // $nav, $footer, esc(), render_tag(), render_header(), render_footer()...

/* ---------- Dữ liệu Harry Potter + sách thể thao (giữ nguyên như bạn đã sửa) ---------- */

$featured = [
    'cover'   => 'images/Harry-Potter1.jpg', // ẢNH: bìa sách lớn bên trái

    'bg_images' => [
        // ẢNH: mỗi dòng là 1 ảnh trong dải ghép làm nền panel bên phải
        'images/Harry-Potter2.jpg',
        'images/Harry-Potter3.jpg',
        'images/Harry-Potter4.jpg',
        'images/Harry-Potter5.jpg',
    ],

    'title'   => 'Loạt tiểu thuyết harry potter',
    'genres'  => ['MA THUẬT', 'BÍ ẨN', 'PHIÊU LƯU'],
    'author'  => 'JK Rowling',
    'status'  => ['label' => 'HOÀN THIỆN', 'type' => 'green'],
    'physical'=> [
        ['label' => 'CÓ', 'type' => 'blue'],
        ['label' => 'CÒN SÁCH', 'type' => 'teal'],
    ],
    'reads'   => 3021789,
    'movie'   => [
        ['label' => 'LIVE ACTION', 'type' => 'brown'],
        ['label' => 'HOÀN THIỆN', 'type' => 'green'],
    ],
];

$carousel = [
    'title'  => 'SẮP RA MẮT',
    'covers' => [
        // ẢNH: từng ảnh trong dải carousel
        'images/cach-menh.jpg',
        'images/onepunch.jpg',
        'images/novek.jpg',
    ],
];

$grid = [
    'title' => 'SÁCH THỂ THAO',
    'items' => [
        // ẢNH: key 'cover' của từng phần tử
        ['cover' => 'images/sport1.jpg', 'label' => 'Mê Cung Bóng Tối'],
        ['cover' => 'images/sport2.jpg', 'label' => 'Đêm Không Trăng'],
        ['cover' => 'images/sport3.jpg', 'label' => 'Sắc Màu Định Mệnh'],
    ],
];

$hero = [
    'bg'      => 'images/hero-library.jpg', // ẢNH: nền banner đầu trang
    'heading' => 'Thư viện là nơi lưu giữ, sắp xếp và cung cấp các nguồn lực thông tin như sách, báo, tài liệu số và phương tiện điện tử',
    'cta'     => 'Đặt chỗ ngay',
    'search'  => [
        'from'   => 'Đi từ...',
        'to'     => 'Đến...',
        'date'   => 'dd/mm/yyyy',
        'submit' => 'Tìm kiếm',
    ],
];

/* ---------- Hàm dựng giao diện RIÊNG của trang chủ (đã tùy biến) ---------- */

function render_hero(array $hero): void
{
    ?>
    <div class="hero-section">
        <!-- ẢNH: nền banner, lấy từ $hero['bg'] -->
        <div class="hero-bg" style="background-image:url('<?= esc($hero['bg']) ?>');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="hero-heading"><?= esc($hero['heading']) ?></p>
            <a href="#" class="btn-cta"><?= esc($hero['cta']) ?></a>

            <div class="search-bar">
                <div class="search-field">
                    <label><?= esc($hero['search']['from']) ?></label>
                </div>
                <div class="search-divider"></div>
                <div class="search-field">
                    <label><?= esc($hero['search']['to']) ?></label>
                </div>
                <div class="search-divider"></div>
                <div class="search-field search-field-date">
                    <label><?= esc($hero['search']['date']) ?></label>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="3" x2="8" y2="7"/><line x1="16" y1="3" x2="16" y2="7"/></svg>
                </div>
                <button type="button" class="btn-search"><?= esc($hero['search']['submit']) ?></button>
            </div>
        </div>
    </div>
    <?php
}

function render_featured(array $book): void
{
    ?>
    <div class="book-showcase">
        <div class="section-header">
            <h1>Lựa chọn cho độc giả mới</h1>
        </div>
        <div class="showcase-body">
            <div class="showcase-cover">
                <!-- ẢNH: bìa sách lớn, lấy từ $book['cover'] -->
                <img src="<?= esc($book['cover']) ?>" alt="<?= esc($book['title']) ?>">
            </div>
            <div class="showcase-info">
                <!-- ẢNH: dải nền ghép nhiều ảnh, lấy từ $book['bg_images'] -->
                <div class="info-bg">
                    <?php if (!empty($book['bg_images'])): ?>
                        <?php foreach ($book['bg_images'] as $bgImg): ?>
                            <div class="bg-item" style="background-image:url('<?= esc($bgImg) ?>');"></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="info-overlay"></div>
                <div class="info-content">
                    <h2><?= esc($book['title']) ?></h2>

                    <div class="info-row">
                        <span class="label">Thể loại</span>
                        <?php foreach ($book['genres'] as $genre): ?>
                            <?= render_tag(['label' => $genre, 'type' => 'navy']) ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="info-row">
                        <span class="label">Tác giả:</span>
                        <span class="value"><?= esc($book['author']) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="label">Tình trạng:</span>
                        <?= render_tag($book['status']) ?>
                    </div>

                    <div class="info-row">
                        <span class="label">Sách vật lý:</span>
                        <?php foreach ($book['physical'] as $tag): ?>
                            <?= render_tag($tag) ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="info-row">
                        <span class="label">Số lượt mượn/đọc:</span>
                        <span class="value"><?= number_format($book['reads'], 0, ',', '.') ?></span>
                    </div>

                    <div class="info-row">
                        <span class="label">Phim chuyển thể:</span>
                        <?php foreach ($book['movie'] as $tag): ?>
                            <?= render_tag($tag) ?>
                        <?php endforeach; ?>
                    </div>

                    <a href="#" class="btn-more">Tìm hiểu thêm</a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function render_carousel(array $section): void
{
    ?>
    <div class="carousel-section">
        <div class="section-header">
            <h1><?= esc($section['title']) ?></h1>
        </div>
        <div class="carousel-track-wrap">
            <div class="carousel-track">
                <?php foreach ($section['covers'] as $cover): ?>
                    <!-- ẢNH: từng ảnh carousel, lấy từ $carousel['covers'] -->
                    <div class="carousel-item" style="background-image: url('<?= esc($cover) ?>');"></div>
                <?php endforeach; ?>
            </div>
            <div class="carousel-dots">
                <?php foreach ($section['covers'] as $i => $cover): ?>
                    <span></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="carousel-footer">
            <a href="#" class="btn-more">Tìm hiểu thêm</a>
        </div>
    </div>
    <?php
}

function render_grid(array $section): void
{
    ?>
    <div class="grid-section">
        <div class="section-header">
            <h1><?= esc($section['title']) ?></h1>
        </div>
        <div class="grid-body">
            <?php foreach ($section['items'] as $item): ?>
                <div class="grid-item">
                    <!-- ẢNH: bìa sách trong lưới, lấy từ $item['cover'] -->
                    <img src="<?= esc($item['cover']) ?>" alt="<?= esc($item['label']) ?>">
                    <a href="#" class="btn-more">Tìm hiểu thêm</a>
                </div>
            <?php endforeach; ?>
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
<title>Trang chủ thư viện</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php render_header($nav, 'home'); ?>
<?php render_hero($hero); ?>
<div class="page-body">
    <?php render_featured($featured); ?>
    <?php render_carousel($carousel); ?>
    <?php render_grid($grid); ?>
</div>
<?php render_footer($footer); ?>

</body>
</html>
