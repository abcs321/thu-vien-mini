<?php

require __DIR__ . '/includes.php';
require_once __DIR__ . '/db.php'; // db.php có sẵn trong project (login.php cũng dùng file này), cung cấp $pdo

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// So sánh tài khoản đã đăng nhập: có phải admin không?
// Khớp với login.php: sau khi đăng nhập, $_SESSION['vai_tro'] = 'admin' nếu ten_tai_khoan === 'admin'
$is_admin = isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';

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

/* ---------- Lấy danh mục + bìa sách từ CSDL (thay cho mảng cứng cũ) ---------- */

function fetch_categories_from_db(PDO $pdo): array
{
    $categories = [];

    $stmt = $pdo->query('SELECT id_category, ten_category FROM categories ORDER BY id_category');

    foreach ($stmt->fetchAll() as $cat) {
        // Mỗi category có thể gồm nhiều genre (thể loại con); sách được gắn qua genres
        $bookStmt = $pdo->prepare(
            'SELECT s.anh_bia, s.ten_sach
             FROM sach s
             INNER JOIN genres g ON s.id_genre = g.id_genre
             WHERE g.id_category = :id_category
             ORDER BY s.ngay_them DESC'
        );
        $bookStmt->execute(['id_category' => $cat['id_category']]);

        $covers = [];
        foreach ($bookStmt->fetchAll() as $book) {
            $covers[] = [
                'src' => $book['anh_bia'] ?: 'images/no-cover.jpg',
                'alt' => $book['ten_sach'],
            ];
        }

        $categories[] = [
            'id'     => (int) $cat['id_category'],
            'label'  => $cat['ten_category'],
            'covers' => $covers,
        ];
    }

    return $categories;
}

$categories = fetch_categories_from_db($pdo);

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

function render_book_category(array $cat, bool $is_admin): void
{
    ?>
    <div class="book-category">
        <div class="category-header">
            <span class="category-label"><?= esc($cat['label']) ?></span>
            <?php if ($is_admin): ?>
                <a class="btn-edit-category" href="quan-ly-danh-muc.php?id=<?= (int) $cat['id'] ?>">Chỉnh sửa</a>
            <?php endif; ?>
        </div>
        <div class="category-track-wrap">
            <?php if ($cat['covers']): ?>
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
            <?php else: ?>
                <p class="category-empty">Mục này chưa có sách nào.</p>
            <?php endif; ?>
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
<style>
/* CSS bổ sung cho nút admin — nên chuyển vào style.css sau này */
.category-header { display: flex; align-items: center; justify-content: space-between; }
.btn-edit-category {
    background: #ff8c1a;
    color: #1c1c1c;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 6px 14px;
    border-radius: 4px;
    text-decoration: none;
}
.admin-add-category { margin: 16px 0 32px; }
.admin-add-category a {
    display: inline-block;
    background: #ff8c1a;
    color: #1c1c1c;
    font-weight: 700;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
}
.category-empty { color: #999; font-size: 0.9rem; padding: 12px 0; }
</style>
</head>
<body>

<?php render_header($nav, 'books'); ?>
<?php render_catalog_hero($catalog_hero); ?>

<div class="catalog-body">
    <?php render_sort_bar($sort); ?>
    <?php foreach ($categories as $cat): ?>
        <?php render_book_category($cat, $is_admin); ?>
    <?php endforeach; ?>

    <?php if ($is_admin): ?>
        <div class="admin-add-category">
            <a href="quan-ly-danh-muc.php">+ Thêm mục mới</a>
        </div>
    <?php endif; ?>
</div>

<?php render_footer($footer); ?>

</body>
</html>
