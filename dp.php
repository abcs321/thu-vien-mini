<?php
// 1. Khởi tạo Session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php"; 

if (!isset($nav)) {
    $nav = [
        'logo'  => 'THƯ VIỆN',
        'links' => [
            ['label' => 'TRANG CHỦ',      'href' => 'index.php',          'key' => 'home'],
            ['label' => 'VỀ CHÚNG TÔI',   'href' => '#',                   'key' => 'about'],
            ['label' => 'DANH SÁCH SÁCH', 'href' => 'danh-sach-sach.php', 'key' => 'books'],
            ['label' => 'PHIẾU MƯỢN',     'href' => '#',                   'key' => 'borrow'],
            ['label' => 'KHÁM PHÁ',       'href' => '#',                   'key' => 'explore'],
            ['label' => 'LIÊN LẠC',       'href' => '#',                   'key' => 'contact'],
        ],
        'login' => 'Đăng nhập',
    ];
}

if (!isset($footer)) {
    $footer = [
        'logo'    => 'THƯ VIỆN',
        'address' => 'TRẦN PHÚ, HÀ ĐÔNG, HÀ NỘI',
        'phone'   => '0985792118',
        'email'   => 'thaibinhan06@gmail.com',
        'social'  => [
            ['label' => 'Instagram', 'href' => '#', 'key' => 'instagram'],
            ['label' => 'Facebook',  'href' => '#', 'key' => 'facebook'],
        ],
    ];
}

/* ---------- Hàm Escape HTML ---------- */
if (!function_exists('esc')) {
    function esc(?string $v): string
    {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/* ---------- Hàm Render Header ---------- */
if (!function_exists('render_header')) {
    function render_header(array $nav, string $activeKey = ''): void
    {
        ?>
        <header class="site-header">
            <div class="site-header-inner">
                <div class="brand">
                    <span class="brand-mark">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.3" y1="15.3" x2="20.5" y2="20.5"/></svg>
                    </span>
                    <span class="brand-name"><?= esc($nav['logo']) ?></span>
                </div>
                <nav class="main-nav">
                    <?php foreach ($nav['links'] as $link): ?>
                        <a href="<?= esc($link['href']) ?>" class="<?= ($link['key'] === $activeKey) ? 'active' : '' ?>"><?= esc($link['label']) ?></a>
                    <?php endforeach; ?>
                </nav>
                
                <?php if (isset($_SESSION["ten_tai_khoan"])): ?>
                    <span class="btn-login">
                        Xin chào, <?= esc($_SESSION["ten_tai_khoan"]) ?>
                        <?= (isset($_SESSION["vai_tro"]) && $_SESSION["vai_tro"] === "admin") ? " (admin)" : "" ?>
                    </span>
                <?php else: ?>
                    <a href="login.php" class="btn-login" id="loginBtn">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.4"/><path d="M4.5 20c1.4-3.6 4.4-5.6 7.5-5.6s6.1 2 7.5 5.6"/></svg>
                        <?= esc($nav['login']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </header>
        <?php
    }
}

/* ---------- Icon Mạng Xã Hội ---------- */
if (!function_exists('render_social_icon')) {
    function render_social_icon(string $key): void
    {
        if ($key === 'instagram') {
            echo '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg>';
        } elseif ($key === 'facebook') {
            echo '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h-2.5A4.5 4.5 0 0 0 8 7.5V10H5.5v3.5H8V21h3.5v-7.5h3l1-3.5h-4V7.5c0-.6.4-1 1-1H15V3z"/></svg>';
        }
    }
}

/* ---------- Hàm Render Footer ---------- */
if (!function_exists('render_footer')) {
    function render_footer(array $footer): void
    {
        ?>
        <footer class="site-footer">
            <div class="site-footer-inner">
                <div class="footer-brand-block">
                    <div class="brand">
                        <span class="brand-mark">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.3" y1="15.3" x2="20.5" y2="20.5"/></svg>
                        </span>
                        <span class="brand-name"><?= esc($footer['logo']) ?></span>
                    </div>

                    <div class="footer-info">
                        <p>
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z"/><circle cx="12" cy="10" r="2.3"/></svg>
                            <?= esc($footer['address']) ?>
                        </p>
                        <p>
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2 2C9.6 21 3 14.4 3 6a2 2 0 0 1 1-2z"/></svg>
                            <?= esc($footer['phone']) ?>
                        </p>
                        <p>
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 6l9 7 9-7"/></svg>
                            <?= esc($footer['email']) ?>
                        </p>
                    </div>
                </div>

                <div class="footer-social-block">
                    <span class="footer-social-label">Kết nối với chúng tôi</span>
                    <div class="footer-social-icons">
                        <?php foreach ($footer['social'] as $s): ?>
                            <a href="<?= esc($s['href']) ?>" class="social-icon" title="<?= esc($s['label']) ?>">
                                <?php render_social_icon($s['key']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </footer>
        <?php
    }
}

// TRUY VẤN DỮ LIỆU SÁCH (PDO)
try {
    $sql = "SELECT s.*, nxb.ten_nxb 
            FROM sach s 
            LEFT JOIN nha_xuat_ban nxb ON s.id_nxb = nxb.id 
            ORDER BY s.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>Lỗi kết nối CSDL: " . esc($e->getMessage()) . "</h2>");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khám phá - Thư Viện Mini</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    body {
      background-color: #1a1b22;
      color: #ffffff;
      width: 100%;
      overflow-x: hidden;
    }

    .site-header {
      background-color: #161622;
      border-top: 3px solid #e74c3c;
      width: 100%;
    }

    .site-header-inner {
      max-width: 1300px;
      margin: 0 auto;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-mark {
      width: 32px;
      height: 32px;
      background-color: #e74c3c;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .brand-name {
      font-size: 20px;
      font-weight: bold;
      letter-spacing: 1px;
      color: #ffffff;
    }

    .main-nav {
      display: flex;
      gap: 25px;
      align-items: center;
    }

    .main-nav a {
      color: #d1d1d1;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: color 0.3s;
    }

    .main-nav a:hover,
    .main-nav a.active {
      color: #e74c3c;
    }

    .btn-login {
      background-color: #e74c3c;
      color: white;
      text-decoration: none;
      padding: 8px 18px;
      border-radius: 4px;
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.3s;
      border: none;
      cursor: pointer;
    }

    .btn-login:hover {
      background-color: #c0392b;
    }

    .hero {
      position: relative;
      width: 100%;
      height: 350px;
      background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.75)), 
                  url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=1200&auto=format&fit=crop') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 20px;
    }

    .hero-title {
      font-size: 44px;
      font-weight: bold;
      margin-bottom: 15px;
    }

    .hero-title .highlight {
      color: #e74c3c;
    }

    .hero-description {
      max-width: 850px;
      font-size: 15px;
      line-height: 1.6;
      color: #f1f1f1;
      font-weight: 400;
    }

    .content-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }

    .search-container {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 30px;
    }

    .search-box {
      background-color: #ffffff;
      display: flex;
      align-items: center;
      padding: 8px 15px;
      border-radius: 4px;
      width: 300px;
      justify-content: space-between;
    }

    .search-box input {
      border: none;
      outline: none;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      width: 85%;
    }

    .search-box i {
      color: #e74c3c;
      font-size: 14px;
    }

    .section-title {
      font-size: 20px;
      text-transform: uppercase;
      font-weight: bold;
      margin-bottom: 25px;
      color: #ffffff;
      border-left: 4px solid #e74c3c;
      padding-left: 10px;
    }

    .book-detail-container {
      display: flex;
      gap: 30px;
      align-items: flex-start;
      margin-bottom: 30px;
      background-color: #20212b;
      padding: 20px;
      border-radius: 8px;
    }

    .book-cover-wrapper {
      position: relative;
      flex: 0 0 220px;
    }

    .book-cover-image {
      width: 100%;
      height: 310px;
      object-fit: cover;
      display: block;
      border-radius: 4px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }

    .watermark {
      position: absolute;
      bottom: 10px;
      left: 10px;
      color: rgba(255, 255, 255, 0.85);
      font-size: 14px;
      font-weight: bold;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
      pointer-events: none;
    }

    .book-info-block {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 12px;
      justify-content: space-between;
    }

    .book-main-title {
      font-size: 20px;
      font-weight: bold;
      color: #ffffff;
      margin-bottom: 10px;
    }

    .info-row {
      display: flex;
      align-items: center;
      font-size: 14px;
    }

    .info-label {
      width: 150px;
      color: #a0a0a0;
    }

    .info-value {
      color: #ffffff;
      font-weight: 600;
    }

    .tag {
      padding: 4px 10px;
      border-radius: 3px;
      text-transform: uppercase;
      font-size: 11px;
      font-weight: bold;
      display: inline-block;
      margin-right: 5px;
    }

    .tag-genre { background-color: #383a48; color: #ffffff; }
    .tag-status { background-color: #b58900; color: #ffffff; }
    .tag-physical { background-color: #0c4a91; color: #ffffff; }
    .tag-stock { background-color: #00695c; color: #ffffff; }
    .tag-adaptation { background-color: #795548; color: #ffffff; }

    .btn-more-info {
      background-color: #e74c3c;
      color: #ffffff;
      border: none;
      padding: 9px 18px;
      border-radius: 4px;
      font-size: 12px;
      text-transform: uppercase;
      font-weight: bold;
      cursor: pointer;
      width: fit-content;
      margin-top: 15px;
      transition: background-color 0.3s;
    }

    .btn-more-info:hover {
      background-color: #c0392b;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.7);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background: #24252f;
      padding: 30px;
      border-radius: 8px;
      width: 350px;
      text-align: center;
      position: relative;
    }

    .modal-content h3 { margin-bottom: 20px; color: #fff; }
    .modal-content input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 4px;
      border: 1px solid #444;
      background-color: #1a1b22;
      color: #fff;
      outline: none;
    }

    .close-btn {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 22px;
      cursor: pointer;
      color: #aaa;
    }

    .close-btn:hover { color: #fff; }

    .site-footer {
      background-color: #111217;
      border-top: 1px solid #2a2b36;
      padding: 40px 5%;
      margin-top: 60px;
      color: #a0a0a0;
      font-size: 14px;
    }

    .site-footer-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 30px;
    }

    .footer-brand-block .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .footer-brand-block .brand-mark {
      color: #e74c3c;
    }

    .footer-brand-block .brand-name {
      font-size: 18px;
      font-weight: bold;
      color: #ffffff;
    }

    .footer-info p {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      color: #b0b0b0;
    }

    .footer-info svg {
      color: #e74c3c;
      flex-shrink: 0;
    }

    .footer-social-block {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .footer-social-label {
      font-size: 14px;
      font-weight: 600;
      color: #ffffff;
      text-transform: uppercase;
    }

    .footer-social-icons {
      display: flex;
      gap: 12px;
    }

    .social-icon {
      width: 36px;
      height: 36px;
      background-color: #24252f;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .social-icon:hover {
      background-color: #e74c3c;
      transform: translateY(-3px);
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <?php render_header($nav, 'explore'); ?>

  <!-- HERO BANNER -->
  <section class="hero">
    <h1 class="hero-title"><span class="highlight">Khám phá</span> / Thư Viện Mini</h1>
    <p class="hero-description">
      Tra cứu hệ thống thông tin thư viện nhanh chóng, chính xác. Tìm kiếm hàng nghìn đầu sách phong phú từ cơ sở dữ liệu.
    </p>
  </section>

  <!-- CONTENT -->
  <div class="content-container">
    
    <!-- TIM KIEM -->
    <div class="search-container">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Tìm tên sách, tác giả...">
        <i class="fa-solid fa-magnifying-glass"></i>
      </div>
    </div>

    <!-- DANH SACH SACH TỪ CSDL -->
    <section class="featured-books-section">
      <h2 class="section-title">Danh sách sách trong hệ thống</h2>

      <div id="bookList">
        <?php if (!empty($books)): ?>
            <?php foreach ($books as $row): ?>
              <?php 
                // 1. Lấy dữ liệu
                $imgData = trim($row['bia_sach'] ?? '');
                $bookTitle = $row['ten_sach'] ?? 'Sách';

                // 2. Tạo bìa SVG dự phòng chuẩn (Không sợ lỗi mạng)
                $defaultImg = "data:image/svg+xml;utf8," . rawurlencode('
                  <svg xmlns="http://www.w3.org/2000/svg" width="300" height="400" viewBox="0 0 300 400">
                    <rect width="100%" height="100%" fill="#2a2e3d"/>
                    <text x="50%" y="40%" dominant-baseline="middle" text-anchor="middle" fill="#8b9bb4" font-size="22" font-family="sans-serif" font-weight="bold">BÌA SÁCH</text>
                    <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-size="24" font-family="sans-serif" font-weight="bold">' . htmlspecialchars($bookTitle) . '</text>
                  </svg>
                ');

                // 3. Xử lý đường dẫn ảnh (Tự động lọc trùng chữ uploads/)
                if (!empty($imgData) && !is_numeric($imgData)) {
                    if (filter_var($imgData, FILTER_VALIDATE_URL)) {
                        $coverImage = $imgData;
                    } else {
                        $cleanFileName = ltrim(str_replace('uploads/', '', $imgData), '/');
                        $coverImage = 'uploads/' . $cleanFileName;
                    }
                } else {
                    $coverImage = $defaultImg;
                }

                $searchKey = strtolower(($row['ten_sach'] ?? '') . ' ' . ($row['tac_gia'] ?? '') . ' ' . ($row['the_loai'] ?? ''));
              ?>

              <!-- Thẻ chứa từng cuốn sách -->
              <div class="book-detail-container" data-search="<?= esc($searchKey) ?>" style="display: flex; align-items: flex-start; gap: 24px; max-width: 850px; margin: 0 auto 25px auto; background-color: #1e222d; padding: 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                
                <!-- Bìa sách bên trái -->
                <div class="book-cover-wrapper" style="width: 170px; height: 240px; min-width: 170px; position: relative; overflow: hidden; border-radius: 8px; background-color: #2a2e3d; flex-shrink: 0;">
                  <img 
                    src="<?= $coverImage ?>" 
                    alt="<?= htmlspecialchars($bookTitle) ?>" 
                    class="book-cover-image"
                    style="width: 100%; height: 100%; object-fit: cover; display: block;"
                    referrerpolicy="no-referrer"
                    onerror="this.onerror=null; this.src='<?= $defaultImg ?>';"
                  >
                  <span class="watermark" style="position: absolute; bottom: 8px; left: 8px; font-size: 11px; color: rgba(255, 255, 255, 0.7); font-weight: bold; pointer-events: none;">ThuVienMini</span>
                </div>

                <!-- Khung thông tin bên phải -->
                <div class="book-info-block" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                  <div>
                    <h3 class="book-main-title" style="margin-top: 0; margin-bottom: 16px; font-size: 22px; color: #ffffff; font-weight: bold;"><?= esc($row['ten_sach'] ?? 'Chưa cập nhật') ?></h3>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Thể loại:</span>
                      <span class="info-value"><span class="tag tag-genre"><?= esc($row['the_loai'] ?? 'Khác') ?></span></span>
                    </div>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Tác giả:</span>
                      <span class="info-value" style="color: #fff; font-weight: bold;"><?= esc($row['tac_gia'] ?? 'Chưa cập nhật') ?></span>
                    </div>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Nhà xuất bản:</span>
                      <span class="info-value" style="color: #fff; font-weight: bold;"><?= esc($row['ten_nxb'] ?? 'Chưa cập nhật') ?></span>
                    </div>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Tình trạng:</span>
                      <span class="info-value"><span class="tag tag-status"><?= esc($row['tinh_trang'] ?? 'Sẵn có') ?></span></span>
                    </div>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Sách vật lý (Số lượng):</span>
                      <span class="info-value">
                        <span class="tag tag-physical"><?= esc($row['sach_vat_ly'] ?? 0) ?></span>
                        <span class="tag tag-stock">Còn <?= esc($row['so_luong'] ?? 0) ?> cuốn</span>
                      </span>
                    </div>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Lượt mượn:</span>
                      <span class="info-value" style="color: #fff;"><?= number_format($row['luot_muon'] ?? 0, 0, ',', '.') ?> lượt</span>
                    </div>

                    <div class="info-row" style="margin-top: 8px;">
                      <span class="info-label" style="color: #aaa; width: 150px; display: inline-block;">Phim chuyển thể:</span>
                      <span class="info-value"><span class="tag tag-adaptation"><?= esc($row['phim_chuyen_the'] ?? 'Không') ?></span></span>
                    </div>
                  </div>

                  <button class="btn-more-info" style="margin-top: 20px; align-self: flex-start;" onclick="showBookDetail('<?= esc($row['ten_sach'] ?? '') ?>')">CHI TIẾT SÁCH</button>
                </div>

              </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="padding: 20px; color: #aaa; text-align: center;">Không tìm thấy dữ liệu sách nào trong cơ sở dữ liệu.</p>
        <?php endif; ?>
        <p id="noResults" style="display: none; padding: 20px; color: #aaa; text-align: center;">Không tìm thấy sách phù hợp với từ khóa.</p>
      </div>
    </section>

  </div>

  <!-- FOOTER -->
  <?php render_footer($footer); ?>

  <!-- MODAL DANG NHAP -->
  <div class="modal" id="loginModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeLoginModal()">&times;</span>
      <h3>ĐĂNG NHẬP</h3>
      <form onsubmit="handleLogin(event)">
        <input type="text" placeholder="Tên đăng nhập" required>
        <input type="password" placeholder="Mật khẩu" required>
        <button type="submit" class="btn-more-info" style="width: 100%;">Đăng nhập</button>
      </form>
    </div>
  </div>

  <script>
    // Tìm kiếm sách Realtime
    document.getElementById('searchInput').addEventListener('keyup', function() {
      let keyword = this.value.toLowerCase().trim();
      let books = document.querySelectorAll('.book-detail-container');
      let foundCount = 0;

      books.forEach(book => {
        let text = book.getAttribute('data-search');
        if (text.includes(keyword)) {
          book.style.display = 'flex';
          foundCount++;
        } else {
          book.style.display = 'none';
        }
      });

      const noResults = document.getElementById('noResults');
      if (noResults) {
        noResults.style.display = (foundCount === 0 && books.length > 0) ? 'block' : 'none';
      }
    });

    // Quản lý Modal Đăng Nhập
    const loginModal = document.getElementById('loginModal');
    const loginBtn = document.getElementById('loginBtn');
    
    if (loginBtn) {
      loginBtn.addEventListener('click', (e) => {
        // Chỉ bật modal nếu thẻ A có href là '#'
        if (loginBtn.getAttribute('href') === '#') {
          e.preventDefault();
          loginModal.style.display = 'flex';
        }
      });
    }

    function closeLoginModal() {
      loginModal.style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target == loginModal) {
        closeLoginModal();
      }
    }

    function handleLogin(e) {
      e.preventDefault();
      alert('Đăng nhập thành công!');
      closeLoginModal();
    }

    function showBookDetail(bookName) {
      alert('Đang xem chi tiết cuốn sách: ' + bookName);
    }
  </script>
</body>
</html>
