<?php
/**
 * admin_debug_panel.php
 * ------------------------------------------------------------
 * Component debug dành riêng cho admin, dùng chung cho các trang
 * Khám phá / Danh sách sách / Trang chủ.
 *
 * CÁCH DÙNG (đặt ở đầu file trang tương ứng, SAU session_start()):
 *
 *   <?php if (($_SESSION["vai_tro"] ?? "") === "admin"): ?>
 *
 *       <?php
 *       $debug_mode  = "form";              // "form" hoặc "carousel"
 *       $debug_label = "khám phá";          // tên mục hiển thị
 *       include "admin_debug_panel.php";
 *       ?>
 *
 *   <?php endif; ?>
 *
 * Ở chế độ "form": nếu được include từ bên trong 1 thẻ <form method="POST"
 * enctype="multipart/form-data"> và được truyền $book (từ fetch_book_by_id())
 * + $the_loai_list (từ fetch_genres()), panel sẽ hiển thị SẴN dữ liệu của
 * sách đó và nút "xác nhận" sẽ submit form thật để cập nhật vào bảng `sach`
 * (xem sua-sach.php làm ví dụ). Không truyền $book -> form trống như cũ.
 *
 * Chế độ "carousel" (thêm/sửa ảnh bìa cho 1 mục carousel trên trang chủ,
 * vd: SẮP RA MẮT): nếu được include từ trong 1 thẻ <form method="POST"
 * enctype="multipart/form-data"> và được truyền $debug_label (tiêu đề mục)
 * + $debug_existing_covers (mảng ['anh_bia' => string, 'id_sach' => int|null],
 * từ fetch_section_images()) + $debug_all_books (mảng ['id_sach','ten_sach'],
 * từ fetch_all_books_brief(), dùng đổ vào dropdown chọn sách liên kết cho mỗi
 * ảnh), panel sẽ hiển thị sẵn ảnh bìa hiện tại và nút "xác nhận" sẽ submit
 * form thật để lưu vào bảng `trang_chu_muc` / `trang_chu_muc_anh` (xem
 * sua-carousel.php làm ví dụ).
 *
 * Truyền $debug_show_add_button = false khi dùng để sửa đúng 1 sách (như
 * sua-sach.php) để ẩn nút "thêm mục" — nút đó dùng cho việc thêm nhiều mục
 * mới cùng lúc, không phù hợp khi đang sửa 1 bản ghi đã có id_sach cụ thể.
 */

$debug_mode  = $debug_mode ?? "form";
$debug_label = $debug_label ?? "hiện tại";

// Khi dùng để CHỈNH SỬA 1 cuốn sách có sẵn (vd: từ sua-sach.php), truyền $book
// (mảng dữ liệu từ fetch_book_by_id) và $the_loai_list (từ fetch_genres) để
// điền sẵn dữ liệu vào form. Nếu không truyền, form hiển thị trống như cũ (thêm mới).
$book                   = $book ?? null;
$the_loai_list          = $the_loai_list ?? [];
$debug_existing_covers  = $debug_existing_covers ?? [];
$debug_all_books        = $debug_all_books ?? []; // [['id_sach'=>, 'ten_sach'=>], ...] cho dropdown chọn sách liên kết (chế độ carousel)
$debug_show_add_button  = $debug_show_add_button ?? true;
?>

<style>

    .admin-debug-panel {
        background: #141414;
        color: #eee;
        padding: 16px;
        border-radius: 8px;
        font-family: inherit;
        margin-bottom: 24px;
    }

    .admin-debug-panel .debug-panel-label {
        font-size: 13px;
        color: #ccc;
        margin: 0 0 12px;
    }

    .admin-debug-panel .debug-item-row {
        display: flex;
        gap: 16px;
        background: #1e1e1e;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .admin-debug-panel .debug-cover-box {
        background: #000;
        min-width: 160px;
        min-height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #bbb;
        font-size: 13px;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }

    .admin-debug-panel .debug-cover-box input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .admin-debug-panel .debug-book-form {
        flex: 1;
        padding: 14px 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .admin-debug-panel .debug-title-input {
        background: transparent;
        border: none;
        border-bottom: 1px solid #444;
        color: #fff;
        font-size: 16px;
        padding: 4px 0;
        margin-bottom: 4px;
    }

    .admin-debug-panel .debug-field {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #ddd;
    }

    .admin-debug-panel .debug-field label {
        min-width: 105px;
        color: #ccc;
    }

    .admin-debug-panel .debug-text-input {
        background: transparent;
        border: none;
        border-bottom: 1px solid #444;
        color: #fff;
        padding: 2px 0;
        flex: 1;
    }

    .admin-debug-panel .debug-static-value {
        color: #fff;
    }

    .admin-debug-panel .debug-pill {
        border: none;
        border-radius: 14px;
        padding: 4px 12px;
        font-size: 12px;
        color: #1a1a1a;
        cursor: pointer;
    }

    .admin-debug-panel .debug-pill-gray   { background: #9a9a9a; }
    .admin-debug-panel .debug-pill-yellow { background: #d4b62c; }
    .admin-debug-panel .debug-pill-red    { background: #c0392b; color: #fff; }
    .admin-debug-panel .debug-pill-orange { background: #d98a2b; color: #fff; }

    .admin-debug-panel .debug-confirm-btn,
    .admin-debug-panel .debug-add-item-btn {
        align-self: flex-start;
        background: #c0392b;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 6px 18px;
        font-size: 12px;
        letter-spacing: .05em;
        cursor: pointer;
    }

    .admin-debug-panel .debug-add-item-btn {
        margin-top: 10px;
    }

    .admin-debug-panel .debug-section-name-input {
        background: #d98a2b;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .admin-debug-panel .debug-cover-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .admin-debug-panel .debug-cover-row .debug-cover-box {
        min-width: 140px;
        min-height: 180px;
    }

    .admin-debug-panel .debug-cover-slot {
        position: relative;
    }

    .admin-debug-panel .debug-remove-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 22px;
        height: 22px;
        border: none;
        border-radius: 50%;
        background: #c0392b;
        color: #fff;
        font-size: 14px;
        line-height: 22px;
        text-align: center;
        padding: 0;
        cursor: pointer;
        z-index: 2;
    }

    .admin-debug-panel .debug-remove-btn:hover {
        background: #e74c3c;
    }

    .admin-debug-panel .debug-cover-book-select {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 12px;
        padding: 4px 6px;
        border-radius: 4px;
        border: 1px solid #555;
        background: #222;
        color: #fff;
    }

</style>

<div class="admin-debug-panel" data-debug-mode="<?= htmlspecialchars($debug_mode) ?>">

    <p class="debug-panel-label">
        giao diện thêm trong mục <?= htmlspecialchars($debug_label) ?>
    </p>

    <div class="debug-items">

        <?php if ($debug_mode === "form"): ?>

            <!-- ============================================
                 CHẾ ĐỘ FORM: thêm 1 cuốn sách đầy đủ thông tin
                 (khớp panel "Chọn tên" / Mục 1)
                 ============================================ -->

            <?php if (!empty($book['id_sach'])): ?>
                <input type="hidden" name="id_sach" value="<?= (int) $book['id_sach'] ?>">
            <?php endif; ?>

            <div class="debug-item-row" data-debug-item>

                <label class="debug-cover-box" <?php if (!empty($book['anh_bia'])): ?>style="background-image:url('<?= htmlspecialchars($book['anh_bia'], ENT_QUOTES) ?>');background-size:cover;background-position:center;"<?php endif; ?>>
                    <input type="file" name="anh_bia_moi" accept="image/*">
                    <span <?php if (!empty($book['anh_bia'])): ?>style="background:rgba(0,0,0,.55);padding:4px 8px;border-radius:4px;"<?php endif; ?>>
                        <?= !empty($book['anh_bia']) ? 'đổi ảnh bìa' : 'chọn ảnh bìa' ?>
                    </span>
                </label>

                <div class="debug-book-form">

                    <input
                        type="text"
                        class="debug-title-input"
                        name="ten_sach"
                        placeholder="Chọn tên"
                        value="<?= htmlspecialchars($book['ten_sach'] ?? '') ?>"
                    >

                    <div class="debug-field">
                        <label>Thể loại</label>
                        <select class="debug-pill debug-pill-gray" name="id_genre">
                            <option value="">Chọn thể loại</option>
                            <?php foreach ($the_loai_list as $tl): ?>
                                <option
                                    value="<?= (int) $tl["id_genre"] ?>"
                                    <?= (isset($book['id_genre']) && (int) $book['id_genre'] === (int) $tl['id_genre']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($tl["ten_genre"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="debug-field">
                        <label>Tác giả:</label>
                        <input
                            type="text"
                            class="debug-text-input"
                            name="ten_tac_gia"
                            placeholder="Chọn tác giả"
                            value="<?= htmlspecialchars($book['ten_tac_gia'] ?? '') ?>"
                        >
                    </div>

                    <div class="debug-field">
                        <label>Tình trạng:</label>
                        <select class="debug-pill debug-pill-yellow" name="tinh_trang">
                            <option value="">Chọn tình trạng</option>
                            <?php foreach (['Có sẵn', 'Đang được mượn', 'Ngừng phát hành'] as $opt): ?>
                                <option <?= (($book['tinh_trang'] ?? '') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="debug-field">
                        <label>Sách vật lý:</label>
                        <select class="debug-pill debug-pill-red" name="sach_vat_ly">
                            <option value="">Chọn tình trạng</option>
                            <?php foreach (['Còn sách', 'Hết sách'] as $opt): ?>
                                <option <?= (($book['sach_vat_ly'] ?? '') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="debug-field">
                        <label>Số lượt mượn/đọc:</label>
                        <span class="debug-static-value"><?= (int) ($book['so_luot_muon'] ?? 0) ?></span>
                    </div>

                    <div class="debug-field">
                        <label>Phim chuyển thể:</label>
                        <select class="debug-pill debug-pill-orange" name="phim_chuyen_the">
                            <option value="">Chọn tình trạng</option>
                            <?php foreach (['Có', 'Không'] as $opt): ?>
                                <option <?= (($book['phim_chuyen_the'] ?? '') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="debug-confirm-btn">
                        xác nhận
                    </button>

                </div>

            </div>

        <?php else: ?>

            <!-- ============================================
                 CHẾ ĐỘ CAROUSEL: chỉ thêm ảnh bìa vào 1 mục
                 (khớp panel "TÊN MỤC" / Mục 2)
                 ============================================ -->

            <input
                type="text"
                class="debug-section-name-input"
                name="tieu_de_muc"
                placeholder="TÊN MỤC"
                value="<?= htmlspecialchars($debug_label) ?>"
            >

            <div class="debug-cover-row" data-debug-item>

                <?php
                $existingCovers = $debug_existing_covers ?? [];
                $allBooks       = $debug_all_books ?? [];
                $coverSlots = max(3, count($existingCovers)); // luôn ít nhất 3 ô, nhiều hơn nếu đã có sẵn nhiều ảnh
                for ($i = 0; $i < $coverSlots; $i++):
                    $coverUrl      = $existingCovers[$i]['anh_bia'] ?? '';
                    $selectedBook  = $existingCovers[$i]['id_sach'] ?? '';
                ?>
                    <div class="debug-cover-slot" data-cover-slot>
                        <label class="debug-cover-box" <?php if ($coverUrl): ?>style="background-image:url('<?= htmlspecialchars($coverUrl, ENT_QUOTES) ?>');background-size:cover;background-position:center;"<?php endif; ?>>
                            <input type="file" name="anh_bia_moi[]" accept="image/*">
                            <input type="hidden" name="anh_bia_hien_tai[]" value="<?= htmlspecialchars($coverUrl) ?>">
                            <span <?php if ($coverUrl): ?>style="background:rgba(0,0,0,.55);padding:4px 8px;border-radius:4px;"<?php endif; ?>>
                                <?= $coverUrl ? 'đổi ảnh bìa' : 'chọn ảnh bìa' ?>
                            </span>
                        </label>
                        <button type="button" class="debug-remove-btn" title="Xóa ảnh này">×</button>
                        <select name="id_sach_lien_ket[]" class="debug-cover-book-select" title="Sách này bấm vào sẽ đưa tới trang sách nào ở discover.php">
                            <option value="">— Không liên kết sách —</option>
                            <?php foreach ($allBooks as $b): ?>
                                <option value="<?= (int) $b['id_sach'] ?>" <?= ((int) $selectedBook === (int) $b['id_sach']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['ten_sach']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>

            </div>

            <button type="submit" class="debug-confirm-btn" style="margin-top:10px;">
                xác nhận
            </button>

        <?php endif; ?>

    </div>

    <?php if ($debug_show_add_button): ?>
        <button type="button" class="debug-add-item-btn">
            thêm mục
        </button>
    <?php endif; ?>

</div>

<script>
(function () {

    // Mỗi panel trên trang chạy độc lập, không đụng panel khác
    document.querySelectorAll(".admin-debug-panel").forEach(function (panel) {

        var mode      = panel.dataset.debugMode;
        var addBtn    = panel.querySelector(".debug-add-item-btn");
        var container = panel.querySelector(".debug-items");

        // Xem trước ảnh vừa chọn (chưa upload lên server) ngay trên ô ảnh bìa
        panel.addEventListener("change", function (e) {
            var input = e.target;
            if (input.tagName !== "INPUT" || input.type !== "file") {
                return;
            }
            var box = input.closest(".debug-cover-box");
            if (!box || !input.files || !input.files[0]) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (ev) {
                box.style.backgroundImage = "url('" + ev.target.result + "')";
                box.style.backgroundSize = "cover";
                box.style.backgroundPosition = "center";

                var span = box.querySelector("span");
                if (span) {
                    span.style.background = "rgba(0,0,0,.55)";
                    span.style.padding = "4px 8px";
                    span.style.borderRadius = "4px";
                    span.textContent = "đổi ảnh bìa";
                }
            };
            reader.readAsDataURL(input.files[0]);
        });

        // Bấm nút "×" trên 1 ô ảnh (carousel) -> xóa hẳn ô đó khỏi form
        panel.addEventListener("click", function (e) {
            var removeBtn = e.target.closest(".debug-remove-btn");
            if (!removeBtn) {
                return;
            }
            var slot = removeBtn.closest("[data-cover-slot]");
            if (slot) {
                slot.remove();
            }
        });

        if (!addBtn) {
            return; // nút "thêm mục" bị ẩn (vd: màn hình sửa 1 cuốn sách) -> không cần gắn thêm sự kiện
        }

        addBtn.addEventListener("click", function () {

            if (mode === "form") {

                var lastRow = container.querySelector("[data-debug-item]:last-child");
                var clone = lastRow.cloneNode(true);

                clone.querySelectorAll("input, select").forEach(function (el) {
                    if (el.tagName === "SELECT") {
                        el.selectedIndex = 0;
                    } else if (el.type !== "file") {
                        el.value = "";
                    }
                });

                // Ô mới thêm không được dính ảnh bìa/nhãn của ô vừa clone
                clone.querySelectorAll(".debug-cover-box").forEach(function (box) {
                    box.removeAttribute("style");
                    var span = box.querySelector("span");
                    if (span) {
                        span.removeAttribute("style");
                        span.textContent = "chọn ảnh bìa";
                    }
                });

                container.appendChild(clone);

            } else {

                var row = container.querySelector("[data-debug-item]");
                var slot = row.querySelector("[data-cover-slot]").cloneNode(true);

                var box = slot.querySelector(".debug-cover-box");
                box.removeAttribute("style"); // không dính ảnh nền của ô được clone

                slot.querySelectorAll("input").forEach(function (el) {
                    el.value = "";
                });

                var span = slot.querySelector("span");
                if (span) {
                    span.removeAttribute("style");
                    span.textContent = "chọn ảnh bìa";
                }

                var select = slot.querySelector(".debug-cover-book-select");
                if (select) {
                    select.value = "";
                }

                row.appendChild(slot);
            }

        });

    });

})();
</script>