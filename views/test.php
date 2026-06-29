<?php

/**
 * =====================================================
 *  HIỂN THỊ DỮ LIỆU GOOGLE SHEETS BẰNG PHP THUẦN
 *  + AJAX POLLING TỰ ĐỘNG MỖI 30 GIÂY
 * =====================================================
 *
 * CẤU TRÚC FILE:
 *   index.php  — trang hiển thị (file này)
 *   api.php    — endpoint JSON cho AJAX
 *   credentials.json — Service Account key từ Google Cloud
 *
 * HƯỚNG DẪN CÀI ĐẶT:
 * 1. Vào https://console.cloud.google.com/
 * 2. Tạo Project → Enable "Google Sheets API"
 * 3. APIs & Services → Credentials → Create Credentials → Service Account
 * 4. Vào Service Account → tab Keys → Add Key → JSON → Tải về
 * 5. Đặt file vào cùng thư mục, đổi tên: credentials.json
 * 6. Google Sheets → Share → thêm email Service Account (Viewer)
 */

// =====================================================
//  CẤU HÌNH
// =====================================================
define('SPREADSHEET_ID',   '1QIpax_ruAJeZETenKARrlnmfAAIY0eL5oi3H7o578BE');
define('SHEET_RANGE',      'Hoa 12H1 Ca A21!A3:Z1000');
define('CREDENTIALS_FILE', __DIR__ . '/credentials.json');
define('TOKEN_FILE',       __DIR__ . '/token_cache.json');
define('CACHE_DURATION',   300); // Cache PHP ban đầu 5 phút (api.php dùng 30s)

// =====================================================
//  HÀM TIỆN ÍCH
// =====================================================
function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function httpGet(string $url, string $token): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $r = curl_exec($ch);
    if (curl_errno($ch)) throw new Exception('cURL: ' . curl_error($ch));
    curl_close($ch);
    return $r;
}

function httpPost(string $url, string $body): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $r = curl_exec($ch);
    if (curl_errno($ch)) throw new Exception('cURL: ' . curl_error($ch));
    curl_close($ch);
    return $r;
}

function getAccessToken(): string
{
    if (file_exists(TOKEN_FILE)) {
        $c = json_decode(file_get_contents(TOKEN_FILE), true);
        if ($c && $c['expires_at'] > time() + 30) return $c['access_token'];
    }

    if (!file_exists(CREDENTIALS_FILE))
        throw new Exception('Không tìm thấy credentials.json. Hãy tải từ Google Cloud Console.');

    $creds = json_decode(file_get_contents(CREDENTIALS_FILE), true);
    if (!isset($creds['private_key'], $creds['client_email']))
        throw new Exception('File credentials.json không hợp lệ.');

    $now     = time() - 60;
    $header  = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $payload = base64UrlEncode(json_encode([
        'iss'   => $creds['client_email'],
        'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now,
    ]));

    $signInput = "$header.$payload";
    openssl_sign($signInput, $sig, $creds['private_key'], 'SHA256');
    $jwt = "$signInput." . base64UrlEncode($sig);

    $data = json_decode(httpPost('https://oauth2.googleapis.com/token', http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt,
    ])), true);

    if (empty($data['access_token']))
        throw new Exception('Lỗi lấy token: ' . ($data['error_description'] ?? 'unknown'));

    file_put_contents(TOKEN_FILE, json_encode([
        'access_token' => $data['access_token'],
        'expires_at'   => $now + ($data['expires_in'] ?? 3600),
    ]));

    return $data['access_token'];
}

function getSheetData(): array
{
    $cacheFile = __DIR__ . '/sheet_cache.json';

    if (file_exists($cacheFile)) {
        $c = json_decode(file_get_contents($cacheFile), true);
        if ($c && $c['cached_at'] > time() - CACHE_DURATION) return $c['data'];
    }

    $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
        urlencode(SPREADSHEET_ID),
        urlencode(SHEET_RANGE)
    );

    $json = json_decode(httpGet($url, getAccessToken()), true);
    if (isset($json['error']))
        throw new Exception('Lỗi Sheets API: ' . $json['error']['message']);

    $rows = $json['values'] ?? [];
    file_put_contents($cacheFile, json_encode(['cached_at' => time(), 'data' => $rows]));
    return $rows;
}

// =====================================================
//  XỬ LÝ TRƯỚC KHI XUẤT HTML
// =====================================================
if (isset($_GET['refresh'])) {
    @unlink(__DIR__ . '/sheet_cache.json');
    @unlink(TOKEN_FILE);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$error    = null;
$rows     = [];

try {
    $rows = getSheetData();
} catch (Exception $e) {
    $error = $e->getMessage();
}

$headers  = $rows[0] ?? [];
$dataRows = array_slice($rows, 1);

// Hash ban đầu để JS so sánh sau này
$initialHash = md5(json_encode($rows));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách lớp Hoa 12H1 – Ca A21</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f0f4f8;
            color: #1a202c;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header .icon {
            font-size: 1.8rem;
        }

        .header h1 {
            font-size: 1.4rem;
            font-weight: 600;
        }

        .meta {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 4px;
        }

        /* ── Live indicator ── */
        .live-badge {
            display: flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            flex-shrink: 0;
            animation: pulse 2s infinite;
        }

        .live-dot.error {
            background: #f87171;
            animation: none;
        }

        .live-dot.syncing {
            background: #fbbf24;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .5;
                transform: scale(1.3);
            }
        }

        /* ── Card ── */
        .card {
            background: white;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        /* ── Toolbar ── */
        .toolbar {
            padding: .85rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            transition: background .4s;
        }

        .toolbar.flash {
            background: #f0fdf4;
        }

        .count {
            font-size: .82rem;
            color: #64748b;
        }

        .legend {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .78rem;
            color: #64748b;
        }

        /* ── Update toast ── */
        #update-toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            background: #166534;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .2);
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .3s, transform .3s;
            pointer-events: none;
            z-index: 999;
        }

        #update-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Table ── */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .9rem;
        }

        thead th {
            background: #f8fafc;
            padding: 11px 14px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        tbody tr:hover {
            background: #f8faff;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        /* highlight hàng mới cập nhật */
        tbody tr.row-updated {
            animation: rowFlash 1.5s ease-out;
        }

        @keyframes rowFlash {
            0% {
                background: #bbf7d0;
            }

            100% {
                background: transparent;
            }
        }

        tbody td {
            padding: 10px 14px;
            color: #334155;
            vertical-align: middle;
            max-width: 260px;
            word-break: break-word;
        }

        .td-num {
            color: #94a3b8;
            font-size: .73rem;
            font-weight: 600;
            min-width: 36px;
            text-align: center;
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .76rem;
            font-weight: 700;
            min-width: 32px;
        }

        .badge-present {
            background: #dcfce7;
            color: #166534;
        }

        .badge-absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-other {
            background: #fef9c3;
            color: #854d0e;
        }

        td.col-date,
        th.col-date {
            text-align: center;
        }

        td.col-sdt {
            font-family: monospace;
            font-size: .83rem;
            color: #64748b;
        }

        /* ── States ── */
        .empty {
            padding: 3rem;
            text-align: center;
            color: #94a3b8;
            font-size: 1rem;
        }

        .error-box {
            margin: 1.5rem;
            padding: 1rem 1.25rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #dc2626;
            font-size: .9rem;
            line-height: 1.5;
        }

        .error-box strong {
            display: block;
            margin-bottom: 4px;
        }

        .setup-steps {
            margin: 1.5rem;
            padding: 1.25rem;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            font-size: .85rem;
            line-height: 1.7;
        }

        .setup-steps h3 {
            margin-bottom: .5rem;
            color: #92400e;
        }

        .setup-steps ol {
            padding-left: 1.2rem;
            color: #78350f;
        }

        .setup-steps code {
            background: #fef3c7;
            padding: 1px 5px;
            border-radius: 3px;
            font-family: monospace;
            font-size: .8rem;
        }

        footer {
            text-align: center;
            margin-top: 1.2rem;
            font-size: .76rem;
            color: #94a3b8;
        }

        /* ── Countdown bar ── */
        .countdown-wrap {
            height: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }

        #countdown-bar {
            height: 100%;
            background: linear-gradient(90deg, #1a73e8, #4ade80);
            width: 100%;
            transform-origin: left;
            transition: transform 1s linear;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <span class="icon">📊</span>
                <div>
                    <h1>Danh sách lớp Hoa 12H1 – Ca A21</h1>
                    <div class="meta">Tự động cập nhật mỗi 30 giây · Năm học 2025–2026</div>
                </div>
            </div>
            <div class="live-badge" id="live-badge">
                <span class="live-dot" id="live-dot"></span>
                <span id="live-text">Đang theo dõi</span>
            </div>
        </div>

        <!-- Countdown bar -->
        <div class="countdown-wrap">
            <div id="countdown-bar"></div>
        </div>

        <div class="card">

            <?php if ($error): ?>
                <div class="error-box" id="error-area">
                    <strong>⚠️ Lỗi khi tải dữ liệu:</strong>
                    <?= htmlspecialchars($error) ?>
                </div>
                <div class="setup-steps">
                    <h3>📋 Chưa cấu hình? Làm theo các bước sau:</h3>
                    <ol>
                        <li>Vào <strong>console.cloud.google.com</strong> → Tạo Project</li>
                        <li>Enable <strong>Google Sheets API</strong></li>
                        <li>Tạo <strong>Service Account</strong> → Tải file <code>JSON</code> về</li>
                        <li>Đặt file vào cùng thư mục, đổi tên thành <code>credentials.json</code></li>
                        <li>Vào Google Sheets → <strong>Share</strong> → thêm email Service Account (Viewer)</li>
                        <li>Chỉnh <code>SHEET_RANGE</code> nếu tên sheet khác <code>Hoa 12H1 Ca A21</code></li>
                    </ol>
                </div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="toolbar" id="toolbar">
                <span class="count" id="row-count">
                    <?php if (!$error): ?>
                        <?= count($dataRows) ?> học viên &nbsp;·&nbsp;
                        <?= max(0, count($headers) - 4) ?> buổi học &nbsp;·&nbsp;
                        Tải lúc: <span id="last-updated"><?= date('H:i:s') ?></span>
                    <?php endif; ?>
                </span>
                <div class="legend">
                    <span class="legend-item"><span class="badge badge-present">✓</span> Có mặt</span>
                    <span class="legend-item"><span class="badge badge-absent">✗</span> Vắng</span>
                    <span class="legend-item"><span class="badge badge-other">B••</span> Buổi khác</span>
                </div>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table id="main-table">
                    <thead id="table-head">
                        <?php if (!$error && !empty($headers)): ?>
                            <tr>
                                <th class="td-num">#</th>
                                <?php foreach ($headers as $h):
                                    $isDate = preg_match('/^\d{1,2}\/\d{2}/', trim($h));
                                ?>
                                    <th <?= $isDate ? 'class="col-date"' : '' ?>><?= htmlspecialchars($h) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody id="table-body">
                        <?php if (!$error): ?>
                            <?php if (empty($dataRows)): ?>
                                <tr>
                                    <td colspan="<?= count($headers) + 1 ?>" class="empty">📭 Không có dữ liệu.</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $rowNum = 0;
                                foreach ($dataRows as $i => $row):
                                    $allEmpty = true;
                                    foreach ($row as $cell) {
                                        if (trim($cell) !== '') {
                                            $allEmpty = false;
                                            break;
                                        }
                                    }
                                    if ($allEmpty) continue;
                                    $rowNum++;
                                ?>
                                    <tr data-row="<?= $i ?>">
                                        <td class="td-num"><?= $rowNum ?></td>
                                        <?php foreach ($headers as $ci => $h):
                                            $val    = trim($row[$ci] ?? '');
                                            $isDate = preg_match('/^\d{1,2}\/\d{2}/', trim($h));
                                            $isSdt  = mb_strtoupper(trim($h)) === 'SĐT';

                                            if ($isDate):
                                                if ($val === '') {
                                                    echo '<td class="col-date"><span style="color:#cbd5e1">—</span></td>';
                                                } elseif (strtolower($val) === 'x') {
                                                    echo '<td class="col-date"><span class="badge badge-present" title="Có mặt">✓</span></td>';
                                                } elseif (strtolower($val) === 'o') {
                                                    echo '<td class="col-date"><span class="badge badge-absent" title="Vắng mặt">✗</span></td>';
                                                } else {
                                                    echo '<td class="col-date"><span class="badge badge-other">' . htmlspecialchars($val) . '</span></td>';
                                                }
                                            elseif ($isSdt):
                                                echo '<td class="col-sdt">' . htmlspecialchars($val) . '</td>';
                                            else:
                                                echo '<td>' . htmlspecialchars($val) . '</td>';
                                            endif;
                                        endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- .card -->

        <footer>PHP thuần · Google Sheets API v4 · AJAX polling 30s · Không dùng thư viện ngoài</footer>
    </div>

    <!-- Toast thông báo cập nhật -->
    <div id="update-toast">✅ Dữ liệu vừa được cập nhật</div>

    <script>
        // =====================================================
        //  AJAX POLLING — tự động cập nhật mỗi 30 giây
        // =====================================================
        (function() {
            const INTERVAL = 30; // giây
            const API_URL = 'api.php';

            let currentHash = <?= json_encode($initialHash) ?>;
            let countdown = INTERVAL;
            let timerID = null;

            const bar = document.getElementById('countdown-bar');
            const dot = document.getElementById('live-dot');
            const liveText = document.getElementById('live-text');
            const lastUpd = document.getElementById('last-updated');
            const toolbar = document.getElementById('toolbar');
            const toast = document.getElementById('update-toast');
            const rowCount = document.getElementById('row-count');
            let toastTimer = null;

            // ── Countdown bar ──────────────────────────────────
            function tickCountdown() {
                countdown--;
                const pct = countdown / INTERVAL;
                bar.style.transform = `scaleX(${pct})`;

                if (countdown <= 0) {
                    countdown = INTERVAL;
                    fetchData();
                }
            }

            // ── Trạng thái live-badge ──────────────────────────
            function setStatus(state) {
                dot.className = 'live-dot' + (state === 'error' ? ' error' : state === 'syncing' ? ' syncing' : '');
                liveText.textContent =
                    state === 'syncing' ? 'Đang đồng bộ…' :
                    state === 'error' ? 'Lỗi kết nối' :
                    'Đang theo dõi';
            }

            // ── Show toast ─────────────────────────────────────
            function showToast(msg) {
                toast.textContent = msg;
                toast.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
            }

            // ── Render cell ────────────────────────────────────
            function renderCell(val, isDate, isSdt) {
                if (isDate) {
                    if (!val) return '<td class="col-date"><span style="color:#cbd5e1">—</span></td>';
                    const v = val.toLowerCase();
                    if (v === 'x') return '<td class="col-date"><span class="badge badge-present" title="Có mặt">✓</span></td>';
                    if (v === 'o') return '<td class="col-date"><span class="badge badge-absent" title="Vắng mặt">✗</span></td>';
                    return '<td class="col-date"><span class="badge badge-other">' + esc(val) + '</span></td>';
                }
                if (isSdt) return '<td class="col-sdt">' + esc(val) + '</td>';
                return '<td>' + esc(val) + '</td>';
            }

            function esc(s) {
                return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function isDateCol(h) {
                return /^\d{1,2}\/\d{2}/.test(h.trim());
            }

            function isSdtCol(h) {
                return h.trim().toUpperCase() === 'SĐT';
            }

            // ── Render full table ──────────────────────────────
            function renderTable(rows, changedIndexes) {
                if (!rows || rows.length === 0) return;

                const headers = rows[0];
                const dataRows = rows.slice(1).filter(r => r.some(c => c.trim() !== ''));

                // Header
                const thead = document.getElementById('table-head');
                let thHTML = '<tr><th class="td-num">#</th>';
                headers.forEach(h => {
                    thHTML += `<th ${isDateCol(h) ? 'class="col-date"' : ''}>${esc(h)}</th>`;
                });
                thHTML += '</tr>';
                thead.innerHTML = thHTML;

                // Body
                const tbody = document.getElementById('table-body');
                let bodyHTML = '';
                dataRows.forEach((row, idx) => {
                    const highlight = changedIndexes && changedIndexes.has(idx);
                    bodyHTML += `<tr data-row="${idx}"${highlight ? ' class="row-updated"' : ''}>`;
                    bodyHTML += `<td class="td-num">${idx + 1}</td>`;
                    headers.forEach((h, ci) => {
                        const val = (row[ci] || '').trim();
                        bodyHTML += renderCell(val, isDateCol(h), isSdtCol(h));
                    });
                    bodyHTML += '</tr>';
                });
                tbody.innerHTML = bodyHTML || `<tr><td colspan="${headers.length + 1}" class="empty">📭 Không có dữ liệu.</td></tr>`;

                // Cập nhật count
                if (rowCount) {
                    const buoiCount = Math.max(0, headers.length - 4);
                    const timeStr = new Date().toLocaleTimeString('vi-VN');
                    rowCount.innerHTML = `${dataRows.length} học viên &nbsp;·&nbsp; ${buoiCount} buổi học &nbsp;·&nbsp; Tải lúc: <span id="last-updated">${timeStr}</span>`;
                }
            }

            // ── So sánh để tìm hàng thay đổi ──────────────────
            function findChangedRows(oldRows, newRows) {
                const changed = new Set();
                const oldData = (oldRows || []).slice(1);
                const newData = (newRows || []).slice(1);

                newData.forEach((row, i) => {
                    const oldRow = oldData[i];
                    if (!oldRow || JSON.stringify(row) !== JSON.stringify(oldRow)) {
                        changed.add(i);
                    }
                });
                return changed;
            }

            let lastRows = null; // lưu rows trước để so sánh

            // ── Fetch từ api.php ───────────────────────────────
            function fetchData() {
                setStatus('syncing');

                fetch(API_URL + '?t=' + Date.now())
                    .then(r => r.json())
                    .then(data => {
                        if (!data.ok) throw new Error(data.error || 'API lỗi');

                        setStatus('live');

                        if (data.hash !== currentHash) {
                            // Có thay đổi → render lại
                            const changed = findChangedRows(lastRows, data.rows);
                            lastRows = data.rows;
                            currentHash = data.hash;

                            renderTable(data.rows, changed);

                            // Flash toolbar
                            toolbar.classList.add('flash');
                            setTimeout(() => toolbar.classList.remove('flash'), 800);

                            showToast('✅ Dữ liệu vừa được cập nhật');
                        }
                        // Nếu hash giống → không làm gì, bảng giữ nguyên
                    })
                    .catch(err => {
                        console.error('[Polling] Lỗi:', err);
                        setStatus('error');
                    });
            }

            // ── Khởi động ──────────────────────────────────────
            timerID = setInterval(tickCountdown, 1000);

            // Fetch ngay lần đầu sau 1s để lấy hash gốc
            setTimeout(() => {
                fetch(API_URL + '?t=' + Date.now())
                    .then(r => r.json())
                    .then(d => {
                        if (d.ok) {
                            lastRows = d.rows;
                            currentHash = d.hash;
                        }
                    })
                    .catch(() => {});
            }, 1000);

        })();
    </script>
</body>

</html>