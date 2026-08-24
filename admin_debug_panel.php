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
 * Lưu ý: đây MỚI CHỈ LÀ GIAO DIỆN (chưa lưu vào database). Nút
 * "xác nhận" / "thêm mục" hiện tại chỉ thao tác trên trình duyệt
 * (JS). Khi có file trang thật (danh-sach-sach.php, trang chủ,
 * khám phá) và quyết định cách lưu ảnh bìa, mình sẽ nối nó vào
 * INSERT bảng `sach` thật sự.
 */

$debug_mode  = $debug_mode ?? "form";
$debug_label = $debug_label ?? "hiện tại";
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

            <div class="debug-item-row" data-debug-item>

                <label class="debug-cover-box">
                    <input type="file" accept="image/*">
                    chọn ảnh bìa
                </label>

                <div class="debug-book-form">

                    <input
                        type="text"
                        class="debug-title-input"
                        placeholder="Chọn tên"
                    >

                    <div class="debug-field">
                        <label>Thể loại</label>
                        <select class="debug-pill debug-pill-gray">
                            <option value="">Chọn thể loại</option>
                            <?php foreach ($the_loai_list ?? [] as $tl): ?>
                                <option value="<?= (int) $tl["id_genre"] ?>">
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
                            placeholder="Chọn tác giả"
                        >
                    </div>

                    <div class="debug-field">
                        <label>Tình trạng:</label>
                        <select class="debug-pill debug-pill-yellow">
                            <option value="">Chọn tình trạng</option>
                            <option>Có sẵn</option>
                            <option>Đang được mượn</option>
                            <option>Ngừng phát hành</option>
                        </select>
                    </div>

                    <div class="debug-field">
                        <label>Sách vật lý:</label>
                        <select class="debug-pill debug-pill-red">
                            <option value="">Chọn tình trạng</option>
                            <option>Còn sách</option>
                            <option>Hết sách</option>
                        </select>
                    </div>

                    <div class="debug-field">
                        <label>Số lượt mượn/đọc:</label>
                        <span class="debug-static-value">0</span>
                    </div>

                    <div class="debug-field">
                        <label>Phim chuyển thể:</label>
                        <select class="debug-pill debug-pill-orange">
                            <option value="">Chọn tình trạng</option>
                            <option>Có</option>
                            <option>Không</option>
                        </select>
                    </div>

                    <button type="button" class="debug-confirm-btn">
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
                placeholder="TÊN MỤC"
                value="<?= htmlspecialchars($debug_label) ?>"
            >

            <div class="debug-cover-row" data-debug-item>

                <label class="debug-cover-box">
                    <input type="file" accept="image/*">
                    chọn ảnh bìa
                </label>

                <label class="debug-cover-box">
                    <input type="file" accept="image/*">
                    chọn ảnh bìa
                </label>

                <label class="debug-cover-box">
                    <input type="file" accept="image/*">
                    chọn ảnh bìa
                </label>

            </div>

        <?php endif; ?>

    </div>

    <button type="button" class="debug-add-item-btn">
        thêm mục
    </button>

</div>

<script>
(function () {

    // Mỗi panel trên trang chạy độc lập, không đụng panel khác
    document.querySelectorAll(".admin-debug-panel").forEach(function (panel) {

        var mode      = panel.dataset.debugMode;
        var addBtn    = panel.querySelector(".debug-add-item-btn");
        var container = panel.querySelector(".debug-items");

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

                container.appendChild(clone);

            } else {

                var row = container.querySelector("[data-debug-item]");
                var box = row.querySelector(".debug-cover-box").cloneNode(true);

                box.querySelectorAll("input").forEach(function (el) {
                    el.value = "";
                });

                row.appendChild(box);
            }

        });

    });

})();
</script>
