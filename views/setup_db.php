<?php

/**
 * =====================================================
 *  SETUP_DB.PHP
 *  Tự động tạo bảng students + import từ Google Sheets
 *  Chỉ thêm khi đủ 3 trường: mã_hs + họ_tên + sđt
 *  Không thêm trùng theo combo (ma_hs, ho_ten, sdt)
 * =====================================================
 */

// ── Cấu hình DB ──────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'np4');

// ── Cấu hình Google Sheets ───────────────────────────
define('SPREADSHEET_ID',   '1QIpax_ruAJeZETenKARrlnmfAAIY0eL5oi3H7o578BE');
define('SHEET_RANGE',      'Hoa 12H1 Ca B81!A3:Z1000');
define('CREDENTIALS_FILE', __DIR__ . '/credentials.json');
define('TOKEN_FILE',       __DIR__ . '/token_cache.json');

// ── Tiện ích JWT ──────────────────────────────────────
function base64UrlEncode(string $d): string
{
    return rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
}

function httpGet(string $url, string $token): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
        CURLOPT_TIMEOUT        => 15,
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
        CURLOPT_TIMEOUT        => 15,
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
        throw new Exception('Không tìm thấy credentials.json');

    $creds = json_decode(file_get_contents(CREDENTIALS_FILE), true);
    if (!isset($creds['private_key'], $creds['client_email']))
        throw new Exception('credentials.json không hợp lệ');

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
        throw new Exception('Lỗi token: ' . ($data['error_description'] ?? 'unknown'));

    file_put_contents(TOKEN_FILE, json_encode([
        'access_token' => $data['access_token'],
        'expires_at'   => $now + ($data['expires_in'] ?? 3600),
    ]));

    return $data['access_token'];
}

// ── Lấy dữ liệu sheet ────────────────────────────────
function getSheetRows(): array
{
    $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
        urlencode(SPREADSHEET_ID),
        urlencode(SHEET_RANGE)
    );
    $json = json_decode(httpGet($url, getAccessToken()), true);
    if (isset($json['error']))
        throw new Exception('Sheets API: ' . $json['error']['message']);
    return $json['values'] ?? [];
}

// ── Kết nối DB ────────────────────────────────────────
function getDB(): PDO
{
    // Tạo DB nếu chưa có
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    return $pdo;
}

// ── Tạo bảng ─────────────────────────────────────────
function createTable(PDO $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ma_hs`      VARCHAR(30)  NOT NULL COMMENT 'Mã học sinh (case-sensitive)',
            `ho_ten`     VARCHAR(150) NOT NULL COMMENT 'Họ và tên đầy đủ',
            `sdt`        VARCHAR(20)  NOT NULL COMMENT 'Số điện thoại',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_student` (`ma_hs`, `ho_ten`(100), `sdt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Danh sách học sinh lớp Hoa 12H1 Ca B81';
    ");
}

// ── Import từ sheet ───────────────────────────────────
function importStudents(PDO $db, array $rows): array
{
    $log      = [];
    $inserted = 0;
    $skipped  = 0;
    $invalid  = 0;

    /**
     * Cấu trúc sheet (hàng đầu = header, bỏ qua):
     *   Cột 0 = STT
     *   Cột 1 = MÃ HS  ← cột B trong Google Sheet
     *   Cột 2 = SĐT
     *   Cột 3 = HỌ TÊN
     */

    // Bỏ qua hàng header (hàng 0 = "STT | MÃ HS | SĐT | HỌ TÊN ...")
    $dataRows = array_slice($rows, 1);

    $stmt = $db->prepare("
        INSERT IGNORE INTO `students` (`ma_hs`, `ho_ten`, `sdt`)
        VALUES (:ma_hs, :ho_ten, :sdt)
    ");

    foreach ($dataRows as $idx => $row) {
        $rowNum = $idx + 2; // dòng thực tế trong sheet (bắt đầu từ A3 → dòng 2)

        $ma_hs  = trim($row[1] ?? ''); // Cột B - Mã HS
        $sdt    = trim($row[2] ?? ''); // Cột C - SĐT
        $ho_ten = trim($row[3] ?? ''); // Cột D - Họ Tên

        // Bỏ qua hàng trống hoàn toàn
        if ($ma_hs === '' && $sdt === '' && $ho_ten === '') {
            continue;
        }

        // ── Kiểm tra đủ 3 điều kiện ──────────────────
        $missingFields = [];
        if ($ma_hs  === '') $missingFields[] = 'Mã HS';
        if ($ho_ten === '') $missingFields[] = 'Họ Tên';
        if ($sdt    === '') $missingFields[] = 'SĐT';

        if (!empty($missingFields)) {
            $invalid++;
            $log[] = [
                'type'    => 'skip_invalid',
                'row'     => $rowNum,
                'reason'  => 'Thiếu: ' . implode(', ', $missingFields),
                'data'    => "Mã=[$ma_hs] Tên=[$ho_ten] SĐT=[$sdt]",
            ];
            continue;
        }

        // ── Validate định dạng SĐT cơ bản ───────────
        if (!preg_match('/^0\d{9,10}$/', $sdt)) {
            $invalid++;
            $log[] = [
                'type'   => 'skip_invalid',
                'row'    => $rowNum,
                'reason' => "SĐT không hợp lệ [$sdt]",
                'data'   => "Mã=[$ma_hs] Tên=[$ho_ten]",
            ];
            continue;
        }

        // ── Thử INSERT IGNORE (tránh trùng unique key) ─
        $stmt->execute([
            ':ma_hs'  => $ma_hs,
            ':ho_ten' => $ho_ten,
            ':sdt'    => $sdt,
        ]);

        if ($stmt->rowCount() > 0) {
            $inserted++;
            $log[] = [
                'type' => 'inserted',
                'row'  => $rowNum,
                'data' => "[$ma_hs] $ho_ten — $sdt",
            ];
        } else {
            $skipped++;
            $log[] = [
                'type' => 'duplicate',
                'row'  => $rowNum,
                'data' => "[$ma_hs] $ho_ten — $sdt",
            ];
        }
    }

    return [
        'log'      => $log,
        'inserted' => $inserted,
        'skipped'  => $skipped,
        'invalid'  => $invalid,
    ];
}

// ── Render HTML ───────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup DB – NP System</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Sora:wght@400;600;800&display=swap');

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0d1117;
            --surface: #161b22;
            --border: #30363d;
            --green: #3fb950;
            --yellow: #d29922;
            --red: #f85149;
            --blue: #58a6ff;
            --text: #c9d1d9;
            --muted: #8b949e;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Sora', sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 860px;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .3rem;
        }

        .sub {
            color: var(--muted);
            font-size: .82rem;
            margin-bottom: 2rem;
        }

        .step {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
        }

        .step-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: .8rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            font-size: .75rem;
            font-weight: 800;
        }

        .badge-ok {
            background: #1f4a2a;
            color: var(--green);
        }

        .badge-err {
            background: #3d1c1c;
            color: var(--red);
        }

        .badge-num {
            background: #1c2d3d;
            color: var(--blue);
        }

        .log-list {
            list-style: none;
            font-family: 'JetBrains Mono', monospace;
            font-size: .76rem;
            line-height: 1.7;
        }

        .log-list li {
            padding: 3px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .04);
        }

        .log-list li:last-child {
            border: none;
        }

        .ins {
            color: var(--green);
        }

        .dup {
            color: var(--muted);
        }

        .inv {
            color: var(--yellow);
        }

        .stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .85rem 1.3rem;
            flex: 1;
            min-width: 140px;
        }

        .stat-card .num {
            font-size: 2rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-card .lbl {
            font-size: .72rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .err-box {
            background: #3d1c1c;
            border: 1px solid #5a2020;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            color: #f97171;
            font-size: .85rem;
        }

        .link-btn {
            display: inline-block;
            margin-top: 1.5rem;
            background: var(--blue);
            color: #0d1117;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            font-size: .88rem;
            text-decoration: none;
        }

        .link-btn:hover {
            opacity: .85;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>⚙️ Setup Database – NP System</h1>
        <p class="sub">Tự động tạo bảng <code>students</code> và import từ Google Sheets · Chỉ thêm khi đủ Mã HS + Họ Tên + SĐT</p>

        <?php
        $steps = [];

        // Bước 1: Kết nối DB
        try {
            $db = getDB();
            $steps[] = ['ok' => true, 'title' => 'Kết nối MySQL & tạo database np4', 'msg' => 'Thành công. Database <code>np4</code> sẵn sàng.'];
        } catch (Exception $e) {
            $steps[] = ['ok' => false, 'title' => 'Kết nối MySQL', 'msg' => $e->getMessage()];
            $db = null;
        }

        // Bước 2: Tạo bảng
        if ($db) {
            try {
                createTable($db);
                $steps[] = ['ok' => true, 'title' => 'Tạo bảng students', 'msg' => 'Bảng <code>students</code> đã được tạo (hoặc đã tồn tại).'];
            } catch (Exception $e) {
                $steps[] = ['ok' => false, 'title' => 'Tạo bảng', 'msg' => $e->getMessage()];
                $db = null;
            }
        }

        // Bước 3: Lấy dữ liệu sheet
        $rows = [];
        try {
            $rows = getSheetRows();
            $steps[] = ['ok' => true, 'title' => 'Đọc Google Sheets', 'msg' => 'Đã tải <strong>' . count($rows) . '</strong> hàng từ sheet <em>Hoa 12H1 Ca B81</em>.'];
        } catch (Exception $e) {
            $steps[] = ['ok' => false, 'title' => 'Đọc Google Sheets', 'msg' => $e->getMessage()];
        }

        // Bước 4: Import
        $result = null;
        if ($db && !empty($rows)) {
            try {
                $result = importStudents($db, $rows);
                $steps[] = ['ok' => true, 'title' => 'Import dữ liệu', 'msg' => sprintf(
                    '✅ Thêm mới: <strong style="color:#3fb950">%d</strong> &nbsp;·&nbsp; ⏭ Trùng (bỏ qua): <strong style="color:#8b949e">%d</strong> &nbsp;·&nbsp; ⚠️ Thiếu dữ liệu: <strong style="color:#d29922">%d</strong>',
                    $result['inserted'],
                    $result['skipped'],
                    $result['invalid']
                )];
            } catch (Exception $e) {
                $steps[] = ['ok' => false, 'title' => 'Import dữ liệu', 'msg' => $e->getMessage()];
            }
        }

        // ── Render steps ───
        foreach ($steps as $i => $s): ?>
            <div class="step">
                <div class="step-header">
                    <span class="badge <?= $s['ok'] ? 'badge-ok' : 'badge-err' ?>"><?= $s['ok'] ? '✓' : '✗' ?></span>
                    <span>Bước <?= $i + 1 ?>: <?= $s['title'] ?></span>
                </div>
                <div style="font-size:.83rem; color:<?= $s['ok'] ? 'var(--text)' : 'var(--red)' ?>">
                    <?= $s['msg'] ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($result): ?>
            <h2 style="color:#fff; font-size:1rem; margin:1.5rem 0 .8rem; font-weight:700">📋 Thống kê import</h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="num" style="color:var(--green)"><?= $result['inserted'] ?></div>
                    <div class="lbl">Học sinh mới được thêm</div>
                </div>
                <div class="stat-card">
                    <div class="num" style="color:var(--muted)"><?= $result['skipped'] ?></div>
                    <div class="lbl">Bỏ qua (đã tồn tại)</div>
                </div>
                <div class="stat-card">
                    <div class="num" style="color:var(--yellow)"><?= $result['invalid'] ?></div>
                    <div class="lbl">Thiếu dữ liệu (bỏ qua)</div>
                </div>
            </div>

            <div class="step">
                <div class="step-header"><span class="badge badge-num">#</span><span>Chi tiết từng dòng</span></div>
                <ul class="log-list">
                    <?php foreach ($result['log'] as $entry): ?>
                        <li class="<?= $entry['type'] === 'inserted' ? 'ins' : ($entry['type'] === 'duplicate' ? 'dup' : 'inv') ?>">
                            <?php
                            $icon = $entry['type'] === 'inserted' ? '+ ' : ($entry['type'] === 'duplicate' ? '~ ' : '! ');
                            $reason = isset($entry['reason']) ? " [{$entry['reason']}]" : '';
                            echo "Dòng {$entry['row']}: $icon{$entry['data']}$reason";
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <a href="student_login.php" class="link-btn">→ Đến trang đăng nhập học sinh</a>
        <?php endif; ?>

    </div>
</body>

</html>