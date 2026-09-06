<?php
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

/* ---------- Hàm escape HTML (chỉ định nghĩa nếu chưa có, tránh trùng includes.php) ---------- */
if (!function_exists('esc')) {
    function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }
}

/* ---------- Icon mạng xã hội ---------- */
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

/* ---------- Hàm dựng footer ---------- */
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
