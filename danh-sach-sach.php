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
];

/* ---------- Lấy các TAG (thể loại/genres) + bìa sách từ CSDL ---------- */
// Đổi từ gom theo categories (danh mục lớn) sang gom trực tiếp theo genres
// (thể loại/tag thật của từng sách, vd "Manga"), vì mỗi sách chỉ có 1
// id_genre — đây chính là "tag" mà các sách cùng tag sẽ được nhóm chung.

function fetch_genre_tags_from_db(PDO $pdo): array
{
    $tags = [];

    $stmt = $pdo->query('SELECT id_genre, ten_genre FROM genres ORDER BY ten_genre');

    foreach ($stmt->fetchAll() as $genre) {
        // Lấy các sách thuộc đúng tag này, xáo ngẫu nhiên thứ tự hiển thị
        $bookStmt = $pdo->prepare(
            'SELECT id_sach, anh_bia, ten_sach
             FROM sach
             WHERE id_genre = :id_genre
             ORDER BY RAND()'
        );
        $bookStmt->execute(['id_genre' => $genre['id_genre']]);

        $covers = [];
        foreach ($bookStmt->fetchAll() as $book) {
            $covers[] = [
                'id'  => (int) $book['id_sach'],
                'src' => $book['anh_bia'] ?: 'images/no-cover.jpg',
                'alt' => $book['ten_sach'],
            ];
        }

        $tags[] = [
            'id'     => (int) $genre['id_genre'],
            'label'  => $genre['ten_genre'],
            'covers' => $covers,
        ];
    }

    return $tags;
}

$categories = fetch_genre_tags_from_db($pdo);

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
                <!-- LƯU Ý: $cat['id'] giờ là id_genre (tag), không còn là id_category như trước.
                     Cần kiểm tra quan-ly-danh-muc.php có xử lý đúng theo id_genre không,
                     nếu không thì đổi link này sang trang quản lý thể loại tương ứng. -->
                <a class="btn-edit-category" href="quan-ly-danh-muc.php?id=<?= (int) $cat['id'] ?>">Chỉnh sửa</a>
            <?php endif; ?>
        </div>
        <div class="category-track-wrap">
            <?php if ($cat['covers']): ?>
                <div class="category-track">
                    <?php foreach ($cat['covers'] as $book): ?>
                        <!-- ẢNH: bìa sách trong danh mục "<?= esc($cat['label']) ?>" -->
                        <a href="discover.php#sach-<?= (int) $book['id'] ?>" class="book-cover-link">
                            <img src="<?= esc($book['src']) ?>" alt="<?= esc($book['alt']) ?>">
                        </a>
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
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Montserrat:wght@700;800;900&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
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
.book-cover-link { display: inline-block; text-decoration: none; }
.book-cover-link img { display: block; }
</style>
</head>
<body>

<?php render_header($nav, 'books'); ?>
<?php render_catalog_hero($catalog_hero); ?>

<div class="catalog-body">
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