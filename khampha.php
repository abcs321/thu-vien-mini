<?php
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU MYSQL
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "thuvien"; // 

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>Lỗi kết nối CSDL: " . $conn->connect_error . "<br>Vui lòng kiểm tra lại XAMPP/phpMyAdmin!</h2>");
}

$conn->set_charset("utf8mb4");

// 2. TRUY VẤN DỮ LIỆU SÁCH
$sql = "SELECT * FROM sach";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khám phá - Thư Viện</title>
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

    /* HEADER FULL WIDTH TRÀN MÀN HÌNH */
    header {
      background-color: #161622;
      border-top: 3px solid #e74c3c;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 5%;
      width: 100%;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    .logo-icon {
      width: 32px;
      height: 32px;
      background-color: #e74c3c;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 14px;
    }

    nav ul {
      display: flex;
      list-style: none;
      gap: 25px;
      align-items: center;
    }

    nav ul li a {
      color: #d1d1d1;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: color 0.3s;
    }

    nav ul li a:hover,
    nav ul li a.active {
      color: #e74c3c;
    }

    .btn-login {
      background-color: #e74c3c;
      color: white;
      border: none;
      padding: 8px 18px;
      border-radius: 4px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background-color 0.3s;
    }

    .btn-login:hover {
      background-color: #c0392b;
    }

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
  </style>
</head>
<body>

  <!-- 1. HEADER TRÀN VIỀN -->
  <header>
    <div class="logo">
      <div class="logo-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
      <span>THƯ VIỆN</span>
    </div>

    <nav>
      <ul>
        <li><a href="#">TRANG CHỦ</a></li>
        <li><a href="#">VỀ CHÚNG TÔI</a></li>
        <li><a href="#">DANH SÁCH SÁCH</a></li>
        <li><a href="#">PHIẾU MƯỢN</a></li>
        <li><a href="#" class="active">KHÁM PHÁ</a></li>
        <li><a href="#">LIÊN LẠC</a></li>
      </ul>
    </nav>

    <button class="btn-login" id="loginBtn">
      <i class="fa-regular fa-user"></i> Đăng nhập
    </button>
  </header>

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
            <div class="book-detail-container" data-title="<?php echo strtolower($row['ten_sach']); ?>">
              <div class="book-cover-wrapper">
                <img src="<?php echo $row['bia_sach']; ?>" alt="<?php echo $row['ten_sach']; ?>" class="book-cover-image">
                <span class="watermark">ThuVienSach.vn</span>
              </div>

              <div class="book-info-block">
                <div>
                  <h3 class="book-main-title"><?php echo $row['ten_sach']; ?></h3>

                  <div class="info-row">
                    <span class="info-label">Thể loại:</span>
                    <div class="info-value">
                      <?php 
                        $tags = explode(',', $row['the_loai']);
                        foreach($tags as $tag):
                      ?>
                        <span class="tag tag-genre"><?php echo trim($tag); ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Tác giả:</span>
                    <span class="info-value"><?php echo $row['tac_gia']; ?></span>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Tình trạng:</span>
                    <span class="info-value">
                      <span class="tag tag-status-updating"><?php echo $row['tinh_trang']; ?></span>
                    </span>
                  </div>

                  <div class="info-row" style="margin-top: 12px;">
                    <span class="info-label">Sách vật lý:</span>
                    <span class="info-value">
                      <span class="tag tag-physical-yes"><?php echo $row['sach_vat_ly']; ?></span>
                      <span class="tag tag-physical-stock"><?php echo $row['con_sach']; ?></span>
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

                <button class="btn-more-info" onclick="showBookDetail('<?php echo $row['ten_sach']; ?>')">Tìm hiểu thêm</button>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="padding: 20px; color: #aaa;">Chưa có dữ liệu sách trong cơ sở dữ liệu.</p>
        <?php endif; ?>
      </div>
    </section>

  </div>

  <!-- MODAL ĐĂNG NHẬP -->
  <div class="modal" id="loginModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeLoginModal()">&times;</span>
      <h3>ĐĂNG NHẬP</h3>
      <form onsubmit="handleLogin(event)">
        <input type="text" placeholder="Tên đăng nhập / Email" required>
        <input type="password" placeholder="Mật khẩu" required>
        <button type="submit" class="btn-more-info" style="width: 100%;">Đăng nhập</button>
      </form>
    </div>
  </div>

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

    // 2. Chức năng bật/tắt Modal Đăng nhập
    const loginModal = document.getElementById('loginModal');
    document.getElementById('loginBtn').addEventListener('click', () => {
      loginModal.style.display = 'flex';
    });

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

    // 3. Chức năng nút "Tìm hiểu thêm"
    function showBookDetail(bookName) {
      alert('Bạn vừa bấm xem thông tin chi tiết cuốn sách: ' + bookName);
    }
  </script>

</body>
</html>
