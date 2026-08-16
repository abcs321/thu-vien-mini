thành viên
224001841 Trần Minh tú-
224001825 Trịnh Minh Quý-
224001762 Vũ Thái Bình An-
224001831 Đỗ Đức Thành-
224001768 Ngô Đức Anh-

# Thư Viện Mini

Thư viện Online hướng đến việc đưa tri thức đến gần hơn với mọi người, tạo điều kiện thuận lợi cho việc tự học, nghiên cứu và phát triển kiến thức.

## Công nghệ sử dụng

| Thành phần       | Công nghệ                    |
|-------------------|-------------------------------|
| Ngôn ngữ backend   | PHP 8.3                       |
| Cơ sở dữ liệu      | MySQL 8.4 / MariaDB           |
| Frontend           | HTML, CSS, JavaScript         |
| Môi trường local   | Wampserver 3.4.1 (64bit)      |
| Quản lý mã nguồn   | Git & GitHub                  |

## Cấu trúc thư mục

```
thu-vien-mini/
├── src/          # Mã nguồn PHP chính (xử lý logic, kết nối DB)
├── docs/         # Tài liệu, file SQL, sơ đồ CSDL
├── assets/       # CSS, JavaScript, hình ảnh
├── about.php     # Trang giới thiệu nhóm và đề tài
└── README.md     # File hướng dẫn này
```

## Thành viên nhóm & phân công

| Thành viên | Đối tượng / Chức năng phụ trách |
|---|---|
| Bạn 1 | Sách |
| Bạn 2 | Độc giả (tài khoản, đăng ký/đăng nhập) |
| Bạn 3 | Tác giả |
| Bạn 4 | Thể loại / Danh mục |
| Bạn 5 | Phiếu mượn/trả |
| Bạn 6 | Nhà xuất bản |

## Đối tượng dữ liệu chính

- **Sách**: tên, tác giả, thể loại, số lượng, nhà xuất bản, tình trạng kho
- **Tác giả**: tên, quốc tịch, năm sinh
- **Nhà xuất bản**: tên, địa chỉ, năm thành lập
- **Độc giả**: họ tên, mã độc giả, số điện thoại, tài khoản đăng nhập
- **Thể loại / Danh mục**: tên thể loại, mô tả
- **Phiếu mượn/trả**: độc giả, sách, ngày mượn, hạn trả, trạng thái (đang mượn / đã trả / quá hạn)
- **Tài khoản & vai trò**: phân quyền Độc giả / Thủ thư / Admin
- **Thông báo**: loại thông báo, nội dung, người nhận, trạng thái đã đọc

## Chức năng dự kiến

### Độc giả
- Đăng ký tài khoản, xác minh email
- Đăng nhập / quên mật khẩu
- Tìm kiếm, xem danh sách và chi tiết sách
- Gửi yêu cầu mượn sách
- Xem lịch sử mượn/trả và hạn trả của bản thân
- Nhận thông báo: xác nhận vừa mượn sách, nhắc sắp/đã quá hạn trả, sách mới nhập, gợi ý sách theo lịch sử mượn

### Thủ thư
- Xác nhận yêu cầu mượn sách
- Xác nhận trả sách, ghi nhận sách hỏng/mất
- Xem thống kê: tổng số sách, số sách đang mượn, sách quá hạn, số lượt mượn
- Quản lý số lượng tồn kho sách

### Admin
- Quản lý tài khoản: thêm/sửa/xóa độc giả, thủ thư
- Quản lý sách, tác giả, nhà xuất bản, thể loại (thêm/sửa/xóa)
- Xem thống kê toàn hệ thống
- Cấu hình các loại thông báo tự động

### Hệ thống (tự động)
- Gửi thông báo khi độc giả mượn sách thành công
- Nhắc nhở khi sách sắp/đã quá hạn trả
- Thông báo khi có sách mới nhập hoặc sắp nhập
- Gợi ý sách mượn dựa trên thể loại/lịch sử mượn của độc giả

## Tiến độ - Chức năng đã thực hiện đến hết Buổi 2

- Thiết kế và khởi tạo CSDL với 4 bảng: `sach`, `nha_xuat_ban`, `doc_gia`, `tac_gia` — mỗi bảng có 30 dòng dữ liệu mẫu
- Bảng `sach` có khóa ngoại liên kết tới `nha_xuat_ban`
- Form thêm sách (buổi cá nhân): xử lý mảng, hàm tự định nghĩa phân loại tình trạng kho, điều kiện, vòng lặp hiển thị danh sách
- Trang kiểm tra kết nối CSDL, đếm số dòng, kiểm tra khóa ngoại hoạt động đúng
- Chức năng thêm/xóa độc giả (CRUD cơ bản) dùng prepared statement
- Các module còn lại (đăng ký/đăng nhập, mượn/trả, thống kê, thông báo tự động) đang được phát triển ở các buổi tiếp theo

## Yêu cầu môi trường

- **Wampserver** (Apache, PHP >= 8.0, MySQL >= 8.0) — [wampserver.com](https://www.wampserver.com/)
- **Git** — [git-scm.com](https://git-scm.com/)
- Trình duyệt web

## Hướng dẫn cài đặt và chạy project local

### Bước 1: Clone repository

```bash
cd D:\wamp\www
git clone https://github.com/abcs321/thu-vien-mini.git
```

### Bước 2: Khởi động Wampserver

Mở Wampserver, đợi icon dưới khay hệ thống chuyển sang màu xanh lá.

### Bước 3: Import cơ sở dữ liệu

1. Mở `http://localhost/phpmyadmin`
2. Vào tab **Import**, chọn file `docs/thu_vien_mini_full.sql`
3. Bấm **Go**

### Bước 4: Truy cập project

```
http://localhost/thu-vien-mini/
```

## Quy trình làm việc nhóm (Git)

Trước khi bắt đầu code mỗi ngày:
```bash
git pull origin main
```

Sau khi code xong:
```bash
git add .
git commit -m "Mo ta ngan gon thay doi"
git push origin main
```
