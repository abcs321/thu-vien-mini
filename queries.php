<?php
// queries.php — Các hàm truy vấn CSDL riêng cho trang chủ (index.php)
// Tất cả đều dùng prepared statement qua PDO.

/**
 * Lấy 1 cuốn sách nổi bật (nhiều lượt mượn nhất) để đổ vào khối "Lựa chọn cho độc giả mới".
 */
function fetch_featured_book(PDO $pdo): ?array
{
    $sql = "SELECT s.id_sach, s.ten_sach, s.anh_bia, s.tinh_trang, s.sach_vat_ly,
                   s.so_luot_muon, s.phim_chuyen_the,
                   tg.ten_tac_gia,
                   g.ten_genre
            FROM sach s
            LEFT JOIN tac_gia tg ON tg.id_tac_gia = s.id_tac_gia
            LEFT JOIN genres g   ON g.id_genre = s.id_genre
            ORDER BY s.so_luot_muon DESC
            LIMIT 1";

    $row = $pdo->query($sql)->fetch();
    if (!$row) {
        return null;
    }

    return [
        'id'        => (int) $row['id_sach'],
        'cover'     => $row['anh_bia'] ?: 'images/placeholder-cover.jpg',
        'bg_images' => $row['anh_bia'] ? [$row['anh_bia']] : [],
        'title'     => $row['ten_sach'],
        'genres'    => $row['ten_genre'] ? [mb_strtoupper($row['ten_genre'])] : [],
        'author'    => $row['ten_tac_gia'] ?: 'Đang cập nhật',
        'status'    => map_tinh_trang($row['tinh_trang']),
        'physical'  => [map_sach_vat_ly($row['sach_vat_ly'])],
        'reads'     => (int) $row['so_luot_muon'],
        'movie'     => [map_phim_chuyen_the($row['phim_chuyen_the'])],
    ];
}

/**
 * Lấy 1 cuốn sách theo id (kèm tên tác giả), dùng cho màn hình chỉnh sửa (sua-sach.php).
 */
function fetch_book_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        "SELECT s.*, tg.ten_tac_gia
         FROM sach s
         LEFT JOIN tac_gia tg ON tg.id_tac_gia = s.id_tac_gia
         WHERE s.id_sach = :id
         LIMIT 1"
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Lấy danh sách thể loại (genres) để đổ vào <select> trong form chỉnh sửa.
 */
function fetch_genres(PDO $pdo): array
{
    return $pdo->query("SELECT id_genre, ten_genre FROM genres ORDER BY ten_genre")->fetchAll();
}

/**
 * Tìm tác giả theo tên; nếu chưa có thì tạo mới. Trả về id_tac_gia (hoặc null nếu tên rỗng).
 */
function get_or_create_author(PDO $pdo, string $name): ?int
{
    $name = trim($name);
    if ($name === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id_tac_gia FROM tac_gia WHERE ten_tac_gia = :name LIMIT 1");
    $stmt->execute(['name' => $name]);
    $id = $stmt->fetchColumn();

    if ($id) {
        return (int) $id;
    }

    $ins = $pdo->prepare("INSERT INTO tac_gia (ten_tac_gia) VALUES (:name)");
    $ins->execute(['name' => $name]);

    return (int) $pdo->lastInsertId();
}

/**
 * Lấy 1 mục carousel trang chủ theo khoá (vd: 'sap_ra_mat').
 */
function fetch_section_by_key(PDO $pdo, string $khoa): ?array
{
    $stmt = $pdo->prepare("SELECT id_muc, khoa, tieu_de FROM trang_chu_muc WHERE khoa = :khoa LIMIT 1");
    $stmt->execute(['khoa' => $khoa]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Lấy danh sách ảnh bìa (theo thứ tự) của 1 mục carousel, kèm id_sach nếu ảnh
 * đó đã được admin gắn với 1 cuốn sách cụ thể (dùng để bấm ảnh -> nhảy tới
 * đúng sách ở discover.php).
 * Mỗi phần tử trả về dạng: ['anh_bia' => string, 'id_sach' => int|null]
 */
function fetch_section_images(PDO $pdo, int $id_muc): array
{
    $stmt = $pdo->prepare(
        "SELECT anh_bia, id_sach FROM trang_chu_muc_anh WHERE id_muc = :id_muc ORDER BY thu_tu ASC"
    );
    $stmt->execute(['id_muc' => $id_muc]);

    return array_map(
        fn($row) => ['anh_bia' => $row['anh_bia'], 'id_sach' => $row['id_sach'] !== null ? (int) $row['id_sach'] : null],
        $stmt->fetchAll()
    );
}

/**
 * Lấy danh sách (id_sach, ten_sach) của toàn bộ sách, dùng để đổ vào <select> cho admin
 * chọn sách tương ứng với từng ảnh carousel.
 */
function fetch_all_books_brief(PDO $pdo): array
{
    return $pdo->query("SELECT id_sach, ten_sach FROM sach ORDER BY ten_sach ASC")->fetchAll();
}

/**
 * Lưu lại tiêu đề + toàn bộ danh sách ảnh bìa (kèm sách liên kết nếu có) của 1 mục carousel
 * (ghi đè danh sách ảnh cũ). Tạo mới mục nếu $khoa chưa tồn tại.
 *
 * $items: mỗi phần tử dạng ['anh_bia' => string, 'id_sach' => int|null]
 */
function save_carousel_section(PDO $pdo, string $khoa, string $tieu_de, array $items): void
{
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("SELECT id_muc FROM trang_chu_muc WHERE khoa = :khoa LIMIT 1");
        $stmt->execute(['khoa' => $khoa]);
        $id_muc = $stmt->fetchColumn();

        if ($id_muc) {
            $upd = $pdo->prepare("UPDATE trang_chu_muc SET tieu_de = :tieu_de WHERE id_muc = :id_muc");
            $upd->execute(['tieu_de' => $tieu_de, 'id_muc' => $id_muc]);
        } else {
            $ins = $pdo->prepare("INSERT INTO trang_chu_muc (khoa, tieu_de) VALUES (:khoa, :tieu_de)");
            $ins->execute(['khoa' => $khoa, 'tieu_de' => $tieu_de]);
            $id_muc = (int) $pdo->lastInsertId();
        }

        $pdo->prepare("DELETE FROM trang_chu_muc_anh WHERE id_muc = :id_muc")->execute(['id_muc' => $id_muc]);

        $insImg = $pdo->prepare(
            "INSERT INTO trang_chu_muc_anh (id_muc, anh_bia, id_sach, thu_tu) VALUES (:id_muc, :anh_bia, :id_sach, :thu_tu)"
        );

        foreach (array_values($items) as $i => $item) {
            $anhBia = $item['anh_bia'] ?? '';
            if ($anhBia === '') {
                continue;
            }
            $insImg->execute([
                'id_muc'  => $id_muc,
                'anh_bia' => $anhBia,
                'id_sach' => $item['id_sach'] ?: null,
                'thu_tu'  => $i,
            ]);
        }

        $pdo->commit();
    } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
function update_book(PDO $pdo, int $id, array $data): void
{
    $stmt = $pdo->prepare(
        "UPDATE sach SET
            ten_sach = :ten_sach,
            id_genre = :id_genre,
            id_tac_gia = :id_tac_gia,
            tinh_trang = :tinh_trang,
            sach_vat_ly = :sach_vat_ly,
            phim_chuyen_the = :phim_chuyen_the,
            anh_bia = COALESCE(:anh_bia, anh_bia)
         WHERE id_sach = :id"
    );

    $stmt->execute([
        'ten_sach'        => $data['ten_sach'],
        'id_genre'        => $data['id_genre'] ?: null,
        'id_tac_gia'      => $data['id_tac_gia'] ?: null,
        'tinh_trang'      => $data['tinh_trang'],
        'sach_vat_ly'     => $data['sach_vat_ly'],
        'phim_chuyen_the' => $data['phim_chuyen_the'],
        'anh_bia'         => $data['anh_bia'],
        'id'              => $id,
    ]);
}

/**
 * Lấy N sách mới thêm gần đây nhất, dùng cho carousel "SẮP RA MẮT".
 */
function fetch_carousel_books(PDO $pdo, int $limit = 3): array
{
    $stmt = $pdo->prepare(
        "SELECT anh_bia
         FROM sach
         WHERE anh_bia IS NOT NULL
         ORDER BY ngay_them DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_column($stmt->fetchAll(), 'anh_bia');
}

/**
 * Lấy N sách thuộc một category cụ thể (vd: "Thể thao"), dùng cho khối grid.
 */
function fetch_books_by_category(PDO $pdo, string $categoryName, int $limit = 3): array
{
    $stmt = $pdo->prepare(
        "SELECT s.anh_bia AS cover, s.ten_sach AS label
         FROM sach s
         JOIN genres g     ON g.id_genre = s.id_genre
         JOIN categories c ON c.id_category = g.id_category
         WHERE c.ten_category = :cat
         ORDER BY s.ngay_them DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':cat', $categoryName, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lấy danh sách phiếu mượn CHƯA TRẢ của 1 độc giả, dùng cho bảng "sách đang mượn" ở hero trang chủ.
 */
function fetch_active_borrows(PDO $pdo, int $id_doc_gia): array
{
    $stmt = $pdo->prepare(
        "SELECT pm.id_phieu_muon    AS ma_phieu,
                s.ten_sach          AS ten_sach,
                pm.so_luong         AS so_luong,
                pm.ngay_tra_du_kien AS han_tra
         FROM phieu_muon pm
         JOIN sach s ON s.id_sach = pm.id_sach
         WHERE pm.id_doc_gia = :id_doc_gia
           AND pm.trang_thai IN ('Đang mượn', 'Quá hạn')
         ORDER BY pm.ngay_tra_du_kien ASC"
    );
    $stmt->execute(['id_doc_gia' => $id_doc_gia]);

    return $stmt->fetchAll() ?: [];
}

// ---- Các hàm ánh xạ enum trong CSDL sang nhãn hiển thị (tag) ----

function map_tinh_trang(?string $tinh_trang): array
{
    return match ($tinh_trang) {
        'Có sẵn'          => ['label' => 'CÓ SẴN', 'type' => 'green'],
        'Đang được mượn'  => ['label' => 'ĐANG ĐƯỢC MƯỢN', 'type' => 'blue'],
        'Ngừng phát hành' => ['label' => 'NGỪNG PHÁT HÀNH', 'type' => 'brown'],
        default           => ['label' => 'ĐANG CẬP NHẬT', 'type' => 'navy'],
    };
}

function map_sach_vat_ly(?string $sach_vat_ly): array
{
    return $sach_vat_ly === 'Còn sách'
        ? ['label' => 'CÒN SÁCH', 'type' => 'teal']
        : ['label' => 'HẾT SÁCH', 'type' => 'brown'];
}

function map_phim_chuyen_the(?string $phim_chuyen_the): array
{
    return $phim_chuyen_the === 'Có'
        ? ['label' => 'CÓ PHIM CHUYỂN THỂ', 'type' => 'green']
        : ['label' => 'CHƯA CÓ PHIM', 'type' => 'navy'];
}