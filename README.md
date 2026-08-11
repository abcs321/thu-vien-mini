thành viên
224001841 Trần Minh tú
224001825 Trịnh Minh Quý
224001762 Vũ Thái Bình An

 Thư Viện Mini

Website quản lý thư viện đơn giản — đề tài dự án nhóm học phần Lập trình Web.

 Công nghệ sử dụng

| Thành phần       | Công nghệ                    |
|-------------------|-------------------------------|
| Ngôn ngữ backend   | PHP 8.3                       |
| Cơ sở dữ liệu      | MySQL 8.4 / MariaDB           |
| Frontend           | HTML, CSS, JavaScript         |
| Môi trường local   | Wampserver 3.4.1 (64bit)      |
| Quản lý mã nguồn   | Git & GitHub                  |

 Cấu trúc thư mục# Thư Viện Mini

Website quản lý thư viện đơn giản — đề tài dự án nhóm học phần Lập trình Web.

 Công nghệ sử dụng

| Thành phần       | Công nghệ                    |
|-------------------|-------------------------------|
| Ngôn ngữ backend   | PHP 8.3                       |
| Cơ sở dữ liệu      | MySQL 8.4 / MariaDB           |
| Frontend           | HTML, CSS, JavaScript         |
| Môi trường local   | Wampserver 3.4.1 (64bit)      |
| Quản lý mã nguồn   | Git & GitHub                  |

## Yêu cầu môi trường

Trước khi chạy project, máy cần cài sẵn:

- **Wampserver** (bao gồm Apache, PHP >= 8.0, MySQL >= 8.0) — tải tại [wampserver.com](https://www.wampserver.com/)
- **Git** — tải tại [git-scm.com](https://git-scm.com/)
- Trình duyệt web (Chrome, Edge, Opera...)

## Hướng dẫn cài đặt và chạy project local

### Bước 1: Clone repository

Mở Command Prompt, gõ:

```bash
cd D:\wamp\www
git clone https://github.com/abcs321/thu-vien-mini.git
```

### Bước 2: Khởi động Wampserver

Mở Wampserver, đợi icon dưới khay hệ thống chuyển sang **màu xanh lá** (nghĩa là Apache và MySQL đã chạy).

### Bước 3: Import cơ sở dữ liệu (nếu có)

1. Mở trình duyệt, vào `http://localhost/phpmyadmin`
2. Tạo database mới tên `thu_vien_mini`
3. Vào tab **Import**, chọn file `docs/thu_vien_mini.sql` (nếu nhóm đã có file này)
4. Bấm **Go** để import

### Bước 4: Cấu hình kết nối database

Mở file `src/config.php` (nếu có), chỉnh lại thông tin kết nối cho đúng máy đang chạy:

```php
$host = "localhost";
$dbname = "thu_vien_mini";
$username = "root";
$password = "";
```

### Bước 5: Truy cập project

Mở trình duyệt, vào:## Yêu cầu môi trường

Trước khi chạy project, máy cần cài sẵn:

- **Wampserver** (bao gồm Apache, PHP >= 8.0, MySQL >= 8.0) — tải tại [wampserver.com](https://www.wampserver.com/)
- **Git** — tải tại [git-scm.com](https://git-scm.com/)
- Trình duyệt web (Chrome, Edge, Opera...)

## Hướng dẫn cài đặt và chạy project local

### Bước 1: Clone repository

Mở Command Prompt, gõ:

```bash
cd D:\wamp\www
git clone https://github.com/abcs321/thu-vien-mini.git
```

### Bước 2: Khởi động Wampserver

Mở Wampserver, đợi icon dưới khay hệ thống chuyển sang **màu xanh lá** (nghĩa là Apache và MySQL đã chạy).

### Bước 3: Import cơ sở dữ liệu (nếu có)

1. Mở trình duyệt, vào `http://localhost/phpmyadmin`
2. Tạo database mới tên `thu_vien_mini`
3. Vào tab **Import**, chọn file `docs/thu_vien_mini.sql` (nếu nhóm đã có file này)
4. Bấm **Go** để import

### Bước 4: Cấu hình kết nối database dùng cmd

Mở file `src/config.php` (nếu có), chỉnh lại thông tin kết nối cho đúng máy đang chạy:

```php
$host = "localhost";
$dbname = "thu_vien_mini";
$username = "root";
$password = "";

### Bước 5: Truy cập project

Mở trình duyệt, vào: http://localhost/thu-vien-mini/

## Quy trình làm việc nhóm (Git)

Trước khi bắt đầu code mỗi ngày, luôn lấy code mới nhất:

git pull origin main

Sau khi code xong, đẩy thay đổi lên:

git add .
git commit -m "Mo ta ngan gon thay doi"
git push origin main

Đề tài dự kiến

[Mô tả ngắn: Thư viện mini — quản lý danh sách sách, mượn/trả sách, tìm kiếm sách theo tên/tác giả...]
