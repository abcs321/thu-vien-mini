thành viên
224001841 Trần Minh tú-
224001825 Trịnh Minh Quý-
224001762 Vũ Thái Bình An-
224001831 Đỗ Đức Thành-
224001768 Ngô Đức Anh-

# Thư Viện Mini

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
| Đỗ Đức Thành | Độc giả (tài khoản, đăng ký/đăng nhập) |
| Bạn 3 | Tác giả |
| Bạn 4 | Danh mục / Thể loại |
| Bạn 5 | Phiếu mượn/trả |
| Bạn 6 | Nhà xuất bản |

## Đối tượng dữ liệu chính

- **Sách**: tên, tác giả, thể loại, số lượng, nhà xuất bản, tình trạng kho
- **Tác giả**: tên, quốc tịch, năm sinh
- **Nhà xuất bản**: tên, địa chỉ, năm thành lập
- **Độc giả**: họ tên, mã độc giả, số điện thoại, tài khoản đăng nhập
- **Danh mục** (cấp lớn): tên, mô tả — ví dụ: Công nghệ, Văn học, Kinh tế, Ngoại ngữ
- **Thể loại** (cấp con, thuộc 1 danh mục): tên, mô tả — ví dụ: danh mục "Công nghệ" gồm thể loại Lập trình, Cơ sở dữ liệu, Mạng máy tính
- **Phiếu mượn/trả**: độc giả, sách, ngày mượn, hạn trả, trạng thái (đang mượn / đã trả / quá hạn)
- **Tài khoản & vai trò**: phân quyền Độc giả / Thủ thư / Admin
- **Thông báo**: loại thông báo, nội dung, người nhận, trạng thái đã đọc

> **Lưu ý cho nhóm:** trường `the_loai` trong bảng `sach` hiện đang lưu dạng chữ đơn giản (Van hoc, Khoa hoc...), tách biệt với hệ thống Danh mục/Thể loại 2 cấp mới. Cần họp nhóm thống nhất có liên kết `sach` với `genres` hay không ở buổi sau.

## Chức năng dự kiến

### Độc giả
- Đăng ký tài khoản, xác minh email
- Đăng nhập / quên mật khẩu
- Tìm kiếm, xem danh sách và chi tiết sách theo danh mục/thể loại
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
- Quản lý sách, tác giả, nhà xuất bản, danh mục, thể loại (thêm/sửa/xóa)
- Xem thống kê toàn hệ thống
- Cấu hình các loại thông báo tự động

### Hệ thống (tự động)
- Gửi thông báo khi độc giả mượn sách thành công
- Nhắc nhở khi sách sắp/đã quá hạn trả
- Thông báo khi có sách mới nhập hoặc sắp nhập
- Gợi ý sách mượn dựa trên thể loại/lịch sử mượn của độc giả

## Tiến độ - Chức năng đã thực hiện đến hết Buổi 2

- Thiết kế và khởi tạo CSDL với 6 bảng: `sach`, `nha_xuat_ban`, `doc_gia`, `tac_gia`, `categories`, `genres` — có dữ liệu mẫu đầy đủ (30 dòng/bảng, riêng `genres` liên kết đúng tới `categories` qua khóa ngoại)
- Bảng `sach` có khóa ngoại liên kết tới `nha_xuat_ban`
- Form thêm sách (buổi cá nhân): xử lý mảng, hàm tự định nghĩa phân loại tình trạng kho, điều kiện, vòng lặp hiển thị danh sách
- Trang kiểm tra tổng quan hệ thống: thêm/xóa dữ liệu tương tác cho cả 6 bảng, thống kê số dòng tự động cập nhật
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

> **Lưu ý:** toàn bộ script dùng chung 1 database `thu_vien_mini` — khi tạo file SQL mới, cần dùng đúng tên này (không phải `thuvienmini` hay biến thể khác) để tránh dữ liệu bị tách rời khỏi phần còn lại của nhóm.

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

các validate
A.Form Liên hệ (contact.php)
Họ & Tên (ho, ten): Bắt buộc, tối đa 50 ký tự, loại bỏ ký tự đặc biệt nguy hiểm để chống XSS.

Số điện thoại (so_dien_thoai): Bắt buộc, đúng định dạng số điện thoại Việt Nam (10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09).

Địa chỉ Email (email): Bắt buộc, sử dụng hàm chuẩn filter_var($email, FILTER_VALIDATE_EMAIL).

Nội dung góp ý (noi_dung): Bắt buộc, độ dài từ 10 đến 500 ký tự. Dữ liệu đầu ra phải được mã hóa qua htmlspecialchars() trước khi hiển thị lại.

B.Form Đăng ký tài khoản register.php
Tên người dùng (username): Bắt buộc, từ 6 - 30 ký tự, chỉ chứa chữ cái, số và dấu gạch dưới ([a-zA-Z0-9_]).

Email (email): Bắt buộc, đúng định dạng và kiểm tra không trùng lặp trong CSDL.

Mật khẩu (password): Bắt buộc, tối thiểu 6 ký tự, bao gồm cả chữ và số.

Nhập lại mật khẩu (confirm_password): Phải khớp chính xác với trường mật khẩu.

Mã xác minh email (verify_code): Bắt buộc nhập đúng mã định dạng (6 ký tự chữ/số giả lập gửi qua email).

Điều khoản sử dụng: Bắt buộc phải tích chọn checkbox đồng ý.

C.Form Đăng nhập (login.php)
Tên người dùng / Email: Bắt buộc không được để trống.

Mật khẩu: Bắt buộc nhập đúng mật khẩu đã đăng ký.

D.Form Quản lý/Chỉnh sửa thành viên (Admin) (admin/members.php)
Họ và tên: Bắt buộc, chuỗi từ 3 - 50 ký tự.

Ngày sinh: Định dạng chuẩn dd/mm/yyyy, hợp lệ về mặt thời gian thực tế.

Nơi sống (Thành phố, xã, chi tiết): Các trường thông tin địa chỉ bắt buộc nhập đầy đủ định dạng văn bản.

Thông tin thanh toán (Thẻ tín dụng/CVV): Chỉ nhận định dạng số (nếu có nhập), mã CVV đúng 3 chữ số bảo mật.



Danh sách Route dự kiến cho hệ thống
index.php trang chủ
khampha.php tới các danh mục có trong hệ thống
login/register.php đăng nhập/đăng ký
danh-sach-sach.php các đầu sách được đề xuất
admin-book/admin-member.php điều chỉnh thông tin hệ thống
muon-sach.php đăng ký mượn sách


## Quy tắc Validation & Bảo mật Form (Cập nhật Buổi 3)
- **Kiểm tra dữ liệu phía Server:** Toàn bộ các form (`contact`, `register`, `login`, `admin-member`) đều được lọc dữ liệu trống, kiểm tra định dạng (`filter_var`, `preg_match`) trước khi xử lý.
- **Phòng chống XSS:** Dữ liệu hiển thị trực tiếp từ người dùng luôn được xử lý bằng hàm `htmlspecialchars()` để tránh mã độc chèn vào trình duyệt.
- **Giữ lại dữ liệu cũ (Old Input):** Khi form báo lỗi validation, hệ thống giữ nguyên các giá trị hợp lệ mà người dùng đã nhập trước đó để tăng trải nghiệm.
