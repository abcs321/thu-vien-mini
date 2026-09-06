<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes.php'; // $nav, $footer, esc(), render_tag(), render_header(), render_footer()...
require __DIR__ . '/db.php';       // $pdo (PDO connection)
require __DIR__ . '/queries.php';  // fetch_featured_book(), fetch_carousel_books(), fetch_books_by_category()...

// Admin đang đăng nhập thì được phép bấm "Tìm hiểu thêm" để chỉnh sửa sách
$isAdmin = (($_SESSION['vai_tro'] ?? '') === 'admin');

// ---- Độc giả đang đăng nhập & danh sách sách đang mượn của họ ----
// TODO: đổi 'id_doc_gia' thành đúng key session mà trang login.php của bạn đang lưu
$currentReaderId = $_SESSION['id_doc_gia'] ?? null;
$isLoggedIn = $currentReaderId !== null;

// fetch_active_borrows($pdo, $id_doc_gia) cần được thêm vào queries.php, xem gợi ý cấu trúc bên dưới.
// Trả về mảng các phiếu mượn CHƯA TRẢ, mỗi phần tử dạng:
// ['ma_phieu' => ..., 'ten_sach' => ..., 'so_luong' => ..., 'han_tra' => 'YYYY-MM-DD']
$activeBorrows = $isLoggedIn && function_exists('fetch_active_borrows')
    ? fetch_active_borrows($pdo, $currentReaderId)
    : [];

// ---- Sách nổi bật (nhiều lượt mượn nhất) ----
$featured = fetch_featured_book($pdo);

// Nếu CSDL chưa có sách nào thì dùng dữ liệu mẫu để trang không bị trống khi demo
if ($featured === null) {
    $featured = [
        'id'       => 0,
        'cover'    => 'images/placeholder-cover.jpg',
        'bg_images'=> [],
        'title'    => 'Chưa có dữ liệu sách',
        'genres'   => [],
        'author'   => '',
        'status'   => ['label' => 'ĐANG CẬP NHẬT', 'type' => 'navy'],
        'physical' => [],
        'reads'    => 0,
        'movie'    => [],
    ];
}

// ---- Carousel "SẮP RA MẮT": mục do admin tự quản lý (tiêu đề + ảnh bìa + sách liên kết) ----
$carouselSection = fetch_section_by_key($pdo, 'sap_ra_mat');
$carouselItems   = $carouselSection ? fetch_section_images($pdo, $carouselSection['id_muc']) : [];

// Xáo trộn thứ tự ảnh bìa mỗi lần tải trang, để carousel hiển thị ngẫu nhiên trong số sách admin đã chọn
if (!empty($carouselItems)) {
    shuffle($carouselItems);
}

// Chỉ hiện tối đa 4 sách
$carouselItems = array_slice($carouselItems, 0, 4);

$carousel = [
    'key'    => 'sap_ra_mat',
    'title'  => $carouselSection['tieu_de'] ?? 'SẮP RA MẮT',
    'covers' => array_map(
        fn($item) => ['url' => $item['anh_bia'], 'id_sach' => $item['id_sach']],
        $carouselItems
    ),
];

// ---- Grid "SÁCH THỂ THAO": sách thuộc category "Thể thao" ----
$grid = [
    'title' => 'SÁCH THỂ THAO',
    'items' => fetch_books_by_category($pdo, 'Thể thao', 3),
];

$hero = [
    'bg'      => 'images/hero-library.jpg', // ẢNH: nền banner đầu trang
    'heading' => 'Thư viện là nơi lưu giữ, sắp xếp và cung cấp các nguồn lực thông tin như sách, báo, tài liệu số và phương tiện điện tử',
    'cta'     => 'Đăng nhập để xem chi tiết',
];

// Số ngày còn lại (âm = đã quá hạn) tính từ hôm nay tới hạn trả
function days_until(string $hanTra): int
{
    $today = new DateTime('today');
    $due   = new DateTime($hanTra);
    return (int) $today->diff($due)->format('%r%a');
}

// Class CSS theo mức độ gấp: còn <=7 ngày (kể cả quá hạn) thì cam->đỏ, ngược lại bình thường
function due_urgency_class(int $daysLeft): string
{
    if ($daysLeft < 0)  return 'due-overdue';   // đã quá hạn -> đỏ đậm
    if ($daysLeft <= 2) return 'due-critical';  // rất gấp -> đỏ
    if ($daysLeft <= 7) return 'due-warning';   // gấp -> cam
    return 'due-normal';                        // còn nhiều thời gian
}


function render_hero(array $hero, bool $isLoggedIn, array $activeBorrows): void
{
    ?>
    <div class="hero-section">
        <!-- ẢNH: nền banner, lấy từ $hero['bg'] -->
        <div class="hero-bg" style="background-image:url('<?= esc($hero['bg']) ?>');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="hero-heading"><?= esc($hero['heading']) ?></p>

            <?php if (!$isLoggedIn): ?>
                <a href="login.php" class="btn-cta"><?= esc($hero['cta']) ?></a>
            <?php else: ?>
                <div class="borrow-table-wrap">
                    <?php if (empty($activeBorrows)): ?>
                        <p class="empty-state">Bạn hiện không mượn sách nào.</p>
                    <?php else: ?>
                    <table class="borrow-table">
                        <thead>
                            <tr>
                                <th>TÊN SÁCH</th>
                                <th>MÃ SÁCH</th>
                                <th>SỐ LƯỢNG</th>
                                <th>Trả trong</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeBorrows as $borrow): ?>
                                <?php
                                    $daysLeft = days_until($borrow['han_tra']);
                                    $urgencyClass = due_urgency_class($daysLeft);
                                    $dueLabel = $daysLeft < 0
                                        ? 'Quá hạn ' . abs($daysLeft) . ' ngày'
                                        : $daysLeft . ' ngày';
                                ?>
                                <tr>
                                    <td><?= esc($borrow['ten_sach']) ?></td>
                                    <td><?= esc($borrow['ma_phieu']) ?></td>
                                    <td><?= esc($borrow['so_luong']) ?></td>
                                    <td class="<?= esc($urgencyClass) ?>"><?= esc($dueLabel) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function render_featured(array $book, bool $isAdmin = false): void
{
    ?>
    <div class="book-showcase">
        <div class="section-header">
            <h1>Lựa chọn cho độc giả mới</h1>
        </div>
        <div class="showcase-body">
            <div class="showcase-cover">
                <img src="<?= esc($book['cover']) ?>" alt="<?= esc($book['title']) ?>">
            </div>
            <div class="showcase-info">
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

                    <?php if ($isAdmin && !empty($book['id'])): ?>
                        <a href="sua-sach.php?id=<?= (int) $book['id'] ?>" class="btn-more">Chỉnh sửa (Admin)</a>
                    <?php else: ?>
                        <a href="#" class="btn-more">Tìm hiểu thêm</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function render_carousel(array $section, bool $isAdmin = false): void
{
    ?>
    <div class="carousel-section">
        <div class="section-header">
            <h1><?= esc($section['title']) ?></h1>
        </div>
        <?php if (empty($section['covers'])): ?>
            <p class="empty-state">Chưa có dữ liệu.</p>
        <?php else: ?>
        <div class="carousel-track-wrap">
            <div class="carousel-track">
                <?php foreach ($section['covers'] as $cover): ?>
                    <?php if (!empty($cover['id_sach'])): ?>
                        <a href="discover.php#sach-<?= (int) $cover['id_sach'] ?>" class="carousel-item" style="background-image: url('<?= esc($cover['url']) ?>'); display: block; text-decoration: none;"></a>
                    <?php else: ?>
                        <div class="carousel-item" style="background-image: url('<?= esc($cover['url']) ?>');"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="carousel-dots">
                <?php foreach ($section['covers'] as $i => $cover): ?>
                    <span></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="carousel-footer">
            <a href="login.php" class="btn-login">Đăng nhập</a>
            <?php if ($isAdmin): ?>
                <a href="sua-carousel.php?key=<?= urlencode($section['key'] ?? '') ?>" class="btn-more">Chỉnh sửa (Admin)</a>
            <?php else: ?>
                <a href="#" class="btn-more">Tìm hiểu thêm</a>
            <?php endif; ?>
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
        <?php if (empty($section['items'])): ?>
            <p class="empty-state">Chưa có dữ liệu.</p>
        <?php else: ?>
        <div class="grid-body">
            <?php foreach ($section['items'] as $item): ?>
                <div class="grid-item">
                    <img src="<?= esc($item['cover']) ?>" alt="<?= esc($item['label']) ?>">
                    <a href="#" class="btn-more">Tìm hiểu thêm</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
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
<style>
/* Bảng "sách đang mượn" trong hero — nhúng trực tiếp để chắc chắn áp dụng */
.borrow-table-wrap {
    width: 100%;
    max-width: 720px;
    background: #fff;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    margin-top: 16px;
}

.borrow-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.borrow-table thead th {
    background: #B7B7B7;
    color: #fff;
    font-size: 13px;
    letter-spacing: .03em;
    padding: 10px 14px;
    text-transform: uppercase;
}

.borrow-table thead th:first-child { border-radius: 8px 0 0 8px; }
.borrow-table thead th:last-child  { border-radius: 0 8px 8px 0; }

.borrow-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    color: #333;
}

.due-normal, .due-warning, .due-critical, .due-overdue {
    background: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    display: inline !important;
}

.due-normal {
    color: #333 !important;
    font-weight: 600 !important;
}

/* Còn <= 7 ngày: chữ tông cam, không tạo khung nền */
.due-warning {
    color: #d97706 !important;
    font-weight: 700 !important;
}

/* Còn <= 2 ngày: chữ đỏ cam đậm hơn */
.due-critical {
    color: #e0431f !important;
    font-weight: 700 !important;
}

/* Đã quá hạn: chữ đỏ đậm nhất */
.due-overdue {
    color: #c81e1e !important;
    font-weight: 700 !important;
}
</style>
</head>
<body>

<?php render_header($nav, 'home'); ?>
<?php render_hero($hero, $isLoggedIn, $activeBorrows); ?>
<div class="page-body">
    <?php render_featured($featured, $isAdmin); ?>
    <?php render_carousel($carousel, $isAdmin); ?>
    <?php render_grid($grid); ?>
</div>
<?php render_footer($footer); ?>

</body>
</html>