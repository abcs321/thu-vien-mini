<?php
/**
 * includes.php — Phần DÙNG CHUNG cho mọi trang (header, footer, hàm tiện ích).
 * Các trang (index.php, danh-sach-sach.php, ...) đều require file này ở đầu file.
 * Sửa menu / thông tin footer ở đây sẽ áp dụng cho TẤT CẢ các trang cùng lúc.
 */

/* ---------- Menu điều hướng dùng chung ----------
 * 'key' dùng để đánh dấu mục đang active theo từng trang (xem render_header()).
 * Đổi 'href' sang tên file thật khi bạn tạo thêm trang mới. */
$nav = [
    'logo'  => 'THƯ VIỆN',
    'links' => [
        ['label' => 'TRANG CHỦ',      'href' => 'index.php',           'key' => 'home'],
        ['label' => 'VỀ CHÚNG TÔI',   'href' => '#',                    'key' => 'about'],
        ['label' => 'DANH SÁCH SÁCH', 'href' => 'danh-sach-sach.php',  'key' => 'books'],
        ['label' => 'PHIẾU MƯỢN',     'href' => '#',                    'key' => 'borrow'],
        ['label' => 'KHÁM PHÁ',       'href' => '#',                    'key' => 'explore'],
        ['label' => 'LIÊN LẠC',       'href' => '#',                    'key' => 'contact'],
    ],
    'login' => 'Đăng nhập',
];

/* ---------- Footer dùng chung ---------- */
$footer = [
    'logo'    => 'THƯ VIỆN',
    'address' => 'TRẦN PHÚ, HÀ ĐÔNG, HÀ NỘI',
    'phone'   => '0985792118',
    'email'   => 'thaibinhan06@gmail.com',
    'social'  => [
        ['label' => 'Instagram', 'href' => '#', 'icon' => 'instagram'],
        ['label' => 'Facebook',  'href' => '#', 'icon' => 'facebook'],
    ],
];

/* ---------- Hàm tiện ích ---------- */

function esc(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function render_tag(array $tag): string
{
    $label = htmlspecialchars($tag['label'], ENT_QUOTES, 'UTF-8');
    $type  = htmlspecialchars($tag['type'], ENT_QUOTES, 'UTF-8');
    return "<span class=\"tag tag-{$type}\">{$label}</span>";
}

function icon_svg(string $name): string
{
    if ($name === 'instagram') {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor" stroke="none"/></svg>';
    }
    if ($name === 'facebook') {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M13.5 21v-8.1h2.72l.41-3.16h-3.13V7.73c0-.92.25-1.54 1.57-1.54h1.68V3.36C15.98 3.25 15 3.16 13.86 3.16c-2.4 0-4.05 1.47-4.05 4.16v2.58H7.08v3.16h2.73V21h3.69z"/></svg>';
    }
    return '';
}

/* ---------- Header dùng chung ----------
 * $activeKey: khớp với 'key' trong $nav['links'] để tô đỏ mục đang ở trang nào. */
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
            <a href="#" class="btn-login">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.4"/><path d="M4.5 20c1.4-3.6 4.4-5.6 7.5-5.6s6.1 2 7.5 5.6"/></svg>
                <?= esc($nav['login']) ?>
            </a>
        </div>
    </header>
    <?php
}

/* ---------- Footer dùng chung ---------- */
function render_footer(array $footer): void
{
    ?>
    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="footer-brand">
                <div class="brand">
                    <span class="brand-mark">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.3" y1="15.3" x2="20.5" y2="20.5"/></svg>
                    </span>
                    <span class="brand-name"><?= esc($footer['logo']) ?></span>
                </div>
                <div class="footer-line">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s7-7.4 7-12.5A7 7 0 0 0 5 9.5C5 14.6 12 22 12 22z"/><circle cx="12" cy="9.5" r="2.3"/></svg>
                    <span><?= esc($footer['address']) ?></span>
                </div>
                <div class="footer-line">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2 2C9.6 21 3 14.4 3 6a2 2 0 0 1 1-2z"/></svg>
                    <span><?= esc($footer['phone']) ?></span>
                </div>
                <div class="footer-line">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                    <span><?= esc($footer['email']) ?></span>
                </div>
            </div>
            <div class="footer-social">
                <span class="footer-social-label">Kết nối với chúng tôi</span>
                <div class="footer-social-icons">
                    <?php foreach ($footer['social'] as $s): ?>
                        <a href="<?= esc($s['href']) ?>" aria-label="<?= esc($s['label']) ?>"><?= icon_svg($s['icon']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </footer>
    <?php
}
