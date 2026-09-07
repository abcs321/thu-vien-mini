<?php
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU MYSQL
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "thu_vien_mini"; // 

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>Lỗi kết nối CSDL: " . $conn->connect_error . "<br>Vui lòng kiểm tra lại XAMPP/phpMyAdmin!</h2>");
}

$conn->set_charset("utf8mb4");

// 2. TRUY VẤN DỮ LIỆU SÁCH
// Bảng sach chỉ lưu id_genre và id_tac_gia (khóa ngoại), nên phải JOIN
// sang genres và tac_gia để lấy tên thật, đồng thời alias (AS) các cột
// cho khớp với tên mà phần HTML/PHP bên dưới đang gọi ($row['...']).
$sql = "SELECT 
            s.id_sach,
            s.ten_sach,
            s.anh_bia         AS bia_sach,
            s.id_genre,
            g.ten_genre       AS the_loai,
            s.id_tac_gia,
            t.ten_tac_gia     AS tac_gia,
            s.tinh_trang,
            s.sach_vat_ly,
            s.so_luong_con_lai,
            s.so_luot_muon    AS luot_muon,
            s.phim_chuyen_the
        FROM sach s
        LEFT JOIN genres  g ON s.id_genre = g.id_genre
        LEFT JOIN tac_gia t ON s.id_tac_gia = t.id_tac_gia
        ORDER BY s.id_sach DESC";
$result = $conn->query($sql);

// 3. KIỂM TRA QUYỀN ADMIN
// TODO: đổi điều kiện này cho khớp với hệ thống đăng nhập thực tế của nhóm bạn
// (ví dụ $_SESSION['role'], $_SESSION['is_admin'], v.v.)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';

// 4. DANH SÁCH THỂ LOẠI & TÁC GIẢ CHO FORM THÊM/SỬA SÁCH (chỉ cần khi là admin)
$dsTheLoai = [];
$dsTacGia = [];
if ($isAdmin) {
    $rsGenre = $conn->query("SELECT id_genre, ten_genre FROM genres ORDER BY ten_genre");
    while ($g = $rsGenre->fetch_assoc()) { $dsTheLoai[] = $g; }

    $rsTacGia = $conn->query("SELECT id_tac_gia, ten_tac_gia FROM tac_gia ORDER BY ten_tac_gia");
    while ($tg = $rsTacGia->fetch_assoc()) { $dsTacGia[] = $tg; }
}

require __DIR__ . '/includes.php'; // $nav, $footer, esc(), render_header(), render_footer()
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khám phá - Thư Viện</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&family=Noto+Sans:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  
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

    /* HEADER và FOOTER giờ lấy style từ style.css dùng chung (xem link trong <head>) */

    .hero {
      position: relative;
      width: 100%;
      height: 400px;
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
      font-size: 48px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .hero-title .highlight {
      color: #e74c3c;
    }

    .hero-description {
      max-width: 850px;
      font-size: 16px;
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
      margin-bottom: 40px;
    }

    .search-box {
      background-color: #ffffff;
      display: flex;
      align-items: center;
      padding: 8px 15px;
      border-radius: 4px;
      width: 280px;
      justify-content: space-between;
    }

    .search-box input {
      border: none;
      outline: none;
      font-size: 15px;
      font-weight: 500;
      color: #333;
      width: 85%;
    }

    .search-box i {
      color: #e74c3c;
      font-size: 14px;
    }

    .featured-books-section {
      width: 100%;
    }

    .section-title {
      font-size: 20px;
      text-transform: uppercase;
      font-weight: bold;
      margin-bottom: 25px;
      color: #ffffff;
    }

    .book-detail-container {
      display: flex;
      gap: 30px;
      align-items: flex-start;
      margin-bottom: 40px;
    }

    .book-cover-wrapper {
      position: relative;
      flex: 0 0 300px;
    }

    .book-cover-image {
      width: 100%;
      height: 420px;
      object-fit: cover;
      display: block;
      border-radius: 4px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }

    .watermark {
      position: absolute;
      bottom: 12px;
      left: 15px;
      color: rgba(255, 255, 255, 0.85);
      font-size: 20px;
      font-weight: bold;
      letter-spacing: 0.5px;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
      pointer-events: none;
    }

    .book-info-block {
      background-color: #24252f;
      padding: 30px 35px;
      border-radius: 12px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 16px;
      min-height: 420px;
      justify-content: space-between;
    }

    .book-main-title {
      font-size: 18px;
      font-weight: bold;
      text-transform: uppercase;
      text-align: center;
      margin-bottom: 15px;
      color: #ffffff;
      letter-spacing: 1px;
    }

    .info-row {
      display: flex;
      align-items: center;
      font-size: 14px;
    }

    .info-label {
      width: 140px;
      color: #a0a0a0;
      font-weight: normal;
    }

    .info-value {
      color: #ffffff;
      font-weight: bold;
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
    .tag-status-updating { background-color: #b58900; color: #ffffff; }
    .tag-physical-yes { background-color: #0c4a91; color: #ffffff; }
    .tag-physical-stock { background-color: #00695c; color: #ffffff; }
    .tag-adaptation {
      background-color: #795548;
      color: #ffffff;
      font-size: 12px;
      width: 110px;
      text-align: center;
    }

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
      margin-top: 10px;
      transition: background-color 0.3s;
      text-decoration: none;
      display: inline-block;
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

    /* ===== MODAL THÊM / SỬA SÁCH ===== */
    .book-form-modal .modal-content {
      background: transparent;
      width: auto;
      max-width: 900px;
      padding: 0;
      text-align: left;
      box-shadow: none;
    }

    .book-form-layout {
      display: flex;
      gap: 20px;
      align-items: flex-start;
    }

    .cover-upload-box {
      flex: 0 0 260px;
      height: 360px;
      background-color: #000000;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      cursor: pointer;
      position: relative;
      border: 1px dashed #444;
    }

    .cover-upload-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .cover-upload-box .cover-placeholder {
      color: #ffd54f;
      font-size: 18px;
      font-weight: 500;
    }

    .book-form-panel {
      flex: 1;
      background-color: #24252f;
      border-radius: 12px;
      padding: 25px 30px;
      position: relative;
    }

    .book-form-panel .close-btn { top: 12px; right: 15px; }

    .book-form-title-input {
      width: 100%;
      background: transparent;
      border: none;
      outline: none;
      color: #ffffff;
      font-size: 22px;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
      margin-bottom: 22px;
      border-bottom: 1px solid #3a3b46;
      padding-bottom: 10px;
    }

    .book-form-panel .form-row {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
      gap: 12px;
    }

    .book-form-panel .form-row label {
      width: 130px;
      color: #a0a0a0;
      font-size: 14px;
      flex-shrink: 0;
    }

    .book-form-panel select,
    .book-form-panel input[type="number"] {
      border: none;
      outline: none;
      padding: 7px 14px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
      cursor: pointer;
      color: #ffffff;
    }

    .book-form-panel input[type="number"] {
      background-color: #1a1b22;
      text-transform: none;
      width: 120px;
      cursor: text;
    }

    .select-genre { background-color: #383a48; }
    .select-author { background-color: transparent; color: #fff; font-weight: bold; text-transform: none; padding-left: 0; }
    .select-status { background-color: #b58900; }
    .select-physical { background-color: #7a1f2b; }
    .select-adaptation { background-color: #795548; }

    .book-form-panel select option { background-color: #24252f; color: #fff; }

    .btn-confirm-book {
      background-color: #e74c3c;
      color: #fff;
      border: none;
      padding: 10px 24px;
      border-radius: 4px;
      font-size: 13px;
      text-transform: uppercase;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
    }

    .btn-confirm-book:hover { background-color: #c0392b; }

    .btn-add-item {
      background-color: #e74c3c;
      color: #fff;
      border: none;
      padding: 10px 22px;
      border-radius: 4px;
      font-size: 13px;
      text-transform: uppercase;
      font-weight: bold;
      cursor: pointer;
      margin-top: 25px;
      display: inline-block;
    }

    .btn-add-item:hover { background-color: #c0392b; }

    .btn-edit-book {
      background-color: transparent;
      color: #a0a0a0;
      border: 1px solid #555;
      padding: 9px 18px;
      border-radius: 4px;
      font-size: 12px;
      text-transform: uppercase;
      font-weight: bold;
      cursor: pointer;
      width: fit-content;
      margin-top: 10px;
      margin-left: 10px;
    }

    .btn-edit-book:hover { border-color: #e74c3c; color: #e74c3c; }

    .btn-delete-book {
      background-color: transparent;
      color: #e57373;
      border: 1px solid #7a2b2b;
      padding: 9px 18px;
      border-radius: 4px;
      font-size: 12px;
      text-transform: uppercase;
      font-weight: bold;
      cursor: pointer;
      width: fit-content;
      margin-top: 10px;
      margin-left: 10px;
    }

    .btn-delete-book:hover { background-color: #e74c3c; border-color: #e74c3c; color: #fff; }

    .book-actions-row { display: flex; }

    .book-detail-container:target {
      outline: 2px solid #e74c3c;
      outline-offset: 6px;
      border-radius: 6px;
    }
  </style>
</head>
<body>

  <!-- 1. HEADER TRÀN VIỀN (dùng chung từ header.php) -->
  <?php render_header($nav, 'explore'); ?>

  <!-- 2. HERO BANNER TRÀN VIỀN -->
  <section class="hero">
    <h1 class="hero-title"><span class="highlight">Khám phá</span> / Trang chủ</h1>
    <p class="hero-description">
      Khám phá kho tri thức phong phú cùng Thư viện Online. Tìm kiếm và khám phá những cuốn sách yêu thích, các tài liệu học tập và nhiều nội dung bổ ích thuộc nhiều lĩnh vực khác nhau.
    </p>
  </section>

  <!-- 3. KHU VỰC NỘI DUNG TÌM KIẾM & DANH SÁCH SÁCH -->
  <div class="content-container">
    
    <!-- SEARCH BOX -->
    <div class="search-container">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Tìm kiếm sách...">
        <i class="fa-solid fa-magnifying-glass"></i>
      </div>
    </div>

    <!-- HIỂN THỊ DANH SÁCH SÁCH TỪ MYSQL -->
    <section class="featured-books-section">
      <h2 class="section-title">Những đầu sách NỔI BẬT</h2>

      <div id="bookList">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <div class="book-detail-container" id="sach-<?php echo (int)$row['id_sach']; ?>" data-title="<?php echo strtolower($row['ten_sach']); ?>">
              <div class="book-cover-wrapper">
                <img src="<?php echo htmlspecialchars($row['bia_sach'] ?? 'assets/no-cover.jpg'); ?>" alt="<?php echo htmlspecialchars($row['ten_sach']); ?>" class="book-cover-image">
                <span class="watermark">skibidi</span>
              </div>

              <div class="book-info-block">
                <div>
                  <h3 class="book-main-title"><?php echo $row['ten_sach']; ?></h3>

                  <div class="info-row">
                    <span class="info-label">Thể loại:</span>
                    <div class="info-value">
                      <span class="tag tag-genre"><?php echo htmlspecialchars($row['the_loai'] ?? 'Chưa phân loại'); ?></span>
                    </div>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Tác giả:</span>
                    <span class="info-value"><?php echo htmlspecialchars($row['tac_gia'] ?? 'Chưa rõ'); ?></span>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Tình trạng:</span>
                    <span class="info-value">
                      <span class="tag tag-status-updating"><?php echo htmlspecialchars($row['tinh_trang']); ?></span>
                    </span>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Sách vật lý:</span>
                    <span class="info-value">
                      <span class="tag tag-physical-yes"><?php echo htmlspecialchars($row['sach_vat_ly']); ?></span>
                      <span class="tag tag-physical-stock">Còn lại: <?php echo (int)$row['so_luong_con_lai']; ?></span>
                    </span>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Số lượt mượn/đọc:</span>
                    <span class="info-value"><?php echo number_format($row['luot_muon'], 0, ',', ''); ?></span>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Phim chuyển thể:</span>
                    <span class="info-value">
                      <span class="tag tag-adaptation"><?php echo $row['phim_chuyen_the']; ?></span>
                    </span>
                  </div>
                </div>

                <div class="book-actions-row">
                  <a class="btn-more-info" href="borrow.php?id_sach=<?php echo (int)$row['id_sach']; ?>">Tìm hiểu thêm</a>
                  <?php if ($isAdmin): ?>
                    <button
                      class="btn-edit-book"
                      onclick='openBookModal("edit", <?php echo json_encode($row, JSON_UNESCAPED_UNICODE); ?>)'
                    >Chỉnh sửa</button>
                    <button
                      class="btn-delete-book"
                      onclick='deleteBook(<?php echo (int)$row['id_sach']; ?>, "<?php echo htmlspecialchars($row['ten_sach'], ENT_QUOTES); ?>")'
                    >Xóa</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="padding: 20px; color: #aaa;">Chưa có dữ liệu sách trong cơ sở dữ liệu.</p>
        <?php endif; ?>
      </div>
    </section>

    <?php if ($isAdmin): ?>
      <button class="btn-add-item" onclick='openBookModal("add", null)'>
        <i class="fa-solid fa-plus"></i> Thêm mục
      </button>
    <?php endif; ?>

  </div>

  <?php if ($isAdmin): ?>
  <!-- MODAL THÊM / SỬA SÁCH (chỉ admin mới thấy) -->
  <div class="modal book-form-modal" id="bookFormModal">
    <div class="modal-content">
      <form id="bookForm" class="book-form-layout" enctype="multipart/form-data">
        <input type="hidden" name="action" id="bf_action" value="add">
        <input type="hidden" name="id_sach" id="bf_id_sach" value="">
        <input type="hidden" name="anh_bia_hien_tai" id="bf_anh_bia_hien_tai" value="">

        <!-- Ô CHỌN ẢNH BÌA -->
        <label class="cover-upload-box" id="coverUploadBox">
          <span class="cover-placeholder" id="coverPlaceholder">chọn ảnh bìa</span>
          <img id="coverPreview" src="" alt="Xem trước ảnh bìa">
          <input type="file" name="anh_bia_file" id="bf_anh_bia_file" accept="image/*" style="display:none;">
        </label>

        <!-- THÔNG TIN SÁCH -->
        <div class="book-form-panel">
          <span class="close-btn" onclick="closeBookModal()">&times;</span>

          <input type="text" name="ten_sach" id="bf_ten_sach" class="book-form-title-input" placeholder="Chọn tên" required>

          <div class="form-row">
            <label>Thể loại</label>
            <select name="id_genre" id="bf_id_genre" class="select-genre">
              <option value="">Chọn thể loại</option>
              <?php foreach ($dsTheLoai as $tl): ?>
                <option value="<?php echo (int)$tl['id_genre']; ?>"><?php echo htmlspecialchars($tl['ten_genre']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <label>Tác giả:</label>
            <select name="id_tac_gia" id="bf_id_tac_gia" class="select-author">
              <option value="">Chọn tác giả</option>
              <?php foreach ($dsTacGia as $tg): ?>
                <option value="<?php echo (int)$tg['id_tac_gia']; ?>"><?php echo htmlspecialchars($tg['ten_tac_gia']); ?></option>
              <?php endforeach; ?>
              <option value="__new__">+ Thêm tác giả mới</option>
            </select>
          </div>
          <div class="form-row" id="rowTacGiaMoi" style="display:none;">
            <label>Tên tác giả mới:</label>
            <input type="text" name="tac_gia_moi" id="bf_tac_gia_moi" placeholder="Nhập tên tác giả" style="flex:1; background:#1a1b22; color:#fff; border:1px solid #444; border-radius:4px; padding:7px 12px; text-transform:none; font-weight:normal;">
          </div>

          <div class="form-row">
            <label>Tình trạng:</label>
            <select name="tinh_trang" id="bf_tinh_trang" class="select-status">
              <option value="Có sẵn">Có sẵn</option>
              <option value="Đang được mượn">Đang được mượn</option>
              <option value="Ngừng phát hành">Ngừng phát hành</option>
            </select>
          </div>

          <div class="form-row">
            <label>Sách vật lý:</label>
            <select name="sach_vat_ly" id="bf_sach_vat_ly" class="select-physical">
              <option value="Còn sách">Còn sách</option>
              <option value="Hết sách">Hết sách</option>
            </select>
          </div>

          <div class="form-row">
            <label>Số lượng còn lại:</label>
            <input type="number" name="so_luong_con_lai" id="bf_so_luong_con_lai" min="0" value="0">
          </div>

          <div class="form-row">
            <label>Số lượt mượn/đọc:</label>
            <input type="number" name="so_luot_muon" id="bf_so_luot_muon" min="0" value="0">
          </div>

          <div class="form-row">
            <label>Phim chuyển thể:</label>
            <select name="phim_chuyen_the" id="bf_phim_chuyen_the" class="select-adaptation">
              <option value="Không">Không</option>
              <option value="Có">Có</option>
            </select>
          </div>

          <button type="submit" class="btn-confirm-book">Xác nhận</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- FOOTER (dùng chung từ footer.php) -->
  <?php render_footer($footer); ?>

  <!-- JAVASCRIPT XỬ LÝ CHỨC NĂNG -->
  <script>
    // 1. Chức năng tìm kiếm sách Realtime
    document.getElementById('searchInput').addEventListener('keyup', function() {
      let keyword = this.value.toLowerCase().trim();
      let books = document.querySelectorAll('.book-detail-container');

      books.forEach(book => {
        let title = book.getAttribute('data-title');
        if (title.includes(keyword)) {
          book.style.display = 'flex';
        } else {
          book.style.display = 'none';
        }
      });
    });

    // (Đã bỏ popup đăng nhập — nút/liên kết "Đăng nhập" trong header giờ điều
    // hướng thẳng tới login.php như một link bình thường, không còn chặn bằng JS.)

    // 3. Nút "Tìm hiểu thêm" giờ là link <a href="borrow.php?id_sach=..."> nên
    // không cần xử lý JS ở đây nữa — trình duyệt sẽ tự điều hướng sang borrow.php.

    <?php if ($isAdmin): ?>
    // 4. Chức năng Thêm / Sửa sách (chỉ chạy khi là admin)
    const bookFormModal = document.getElementById('bookFormModal');
    const bookForm = document.getElementById('bookForm');
    const coverPreview = document.getElementById('coverPreview');
    const coverPlaceholder = document.getElementById('coverPlaceholder');
    const coverFileInput = document.getElementById('bf_anh_bia_file');

    function openBookModal(mode, data) {
      bookForm.reset();
      document.getElementById('bf_action').value = mode;
      coverPreview.style.display = 'none';
      coverPreview.src = '';
      coverPlaceholder.style.display = 'block';
      rowTacGiaMoi.style.display = 'none';
      inputTacGiaMoi.required = false;

      if (mode === 'edit' && data) {
        document.getElementById('bf_id_sach').value = data.id_sach;
        document.getElementById('bf_ten_sach').value = data.ten_sach;
        document.getElementById('bf_tinh_trang').value = data.tinh_trang;
        document.getElementById('bf_sach_vat_ly').value = data.sach_vat_ly;
        document.getElementById('bf_so_luong_con_lai').value = data.so_luong_con_lai ?? 0;
        document.getElementById('bf_so_luot_muon').value = data.luot_muon;
        document.getElementById('bf_phim_chuyen_the').value = data.phim_chuyen_the;
        document.getElementById('bf_anh_bia_hien_tai').value = data.bia_sach || '';
        document.getElementById('bf_id_genre').value = data.id_genre || '';
        document.getElementById('bf_id_tac_gia').value = data.id_tac_gia || '';

        if (data.bia_sach) {
          coverPreview.src = data.bia_sach;
          coverPreview.style.display = 'block';
          coverPlaceholder.style.display = 'none';
        }
      }

      bookFormModal.style.display = 'flex';
    }

    function closeBookModal() {
      bookFormModal.style.display = 'none';
    }

    document.getElementById('coverUploadBox').addEventListener('click', () => {
      coverFileInput.click();
    });

    // Hiện/ẩn ô nhập tên khi chọn "+ Thêm tác giả mới"
    const selectTacGia = document.getElementById('bf_id_tac_gia');
    const rowTacGiaMoi = document.getElementById('rowTacGiaMoi');
    const inputTacGiaMoi = document.getElementById('bf_tac_gia_moi');

    selectTacGia.addEventListener('change', () => {
      if (selectTacGia.value === '__new__') {
        rowTacGiaMoi.style.display = 'flex';
        inputTacGiaMoi.required = true;
        inputTacGiaMoi.focus();
      } else {
        rowTacGiaMoi.style.display = 'none';
        inputTacGiaMoi.required = false;
        inputTacGiaMoi.value = '';
      }
    });

    coverFileInput.addEventListener('change', () => {
      const file = coverFileInput.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        coverPreview.src = e.target.result;
        coverPreview.style.display = 'block';
        coverPlaceholder.style.display = 'none';
      };
      reader.readAsDataURL(file);
    });

    bookForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(bookForm);

      try {
        const res = await fetch('xu_ly_sach.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          location.reload();
        } else {
          alert('Lỗi: ' + (data.message || 'Không lưu được sách.'));
        }
      } catch (err) {
        alert('Không kết nối được tới máy chủ.');
      }
    });

    window.addEventListener('click', function(event) {
      if (event.target === bookFormModal) {
        closeBookModal();
      }
    });

    // 5. Chức năng Xóa sách
    async function deleteBook(idSach, tenSach) {
      const xacNhan = confirm('Bạn có chắc muốn xóa cuốn sách "' + tenSach + '"? Hành động này không thể hoàn tác.');
      if (!xacNhan) return;

      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id_sach', idSach);

      try {
        const res = await fetch('xu_ly_sach.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          location.reload();
        } else {
          alert('Lỗi: ' + (data.message || 'Không xóa được sách.'));
        }
      } catch (err) {
        alert('Không kết nối được tới máy chủ.');
      }
    }
    <?php endif; ?>
  </script>

</body>
</html>