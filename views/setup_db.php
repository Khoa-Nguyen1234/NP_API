<?php

/**
 * =====================================================
 *  SETUP_DB.PHP — v2
 *  Tự động tạo DB + bảng students, rồi import từ
 *  Google Sheets (Ca A21 + Ca B81) vào MySQL.
 *
 *  Chạy thủ công: php setup_db.php
 *  Hoặc truy cập qua trình duyệt để xem log HTML.
 * =====================================================
 */

// ── Cấu hình DB ──────────────────────────────────────
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'np4';

// ── Cấu hình Google Sheets ───────────────────────────
const SPREADSHEET_ID   = '1QIpax_ruAJeZETenKARrlnmfAAIY0eL5oi3H7o578BE';
const CREDENTIALS_FILE = __DIR__ . '/credentials.json';
const TOKEN_FILE       = __DIR__ . '/token_cache.json';

// Sheet nào cần import → [tên sheet, range]
const SHEETS = [
    ['name' => 'Hoa 12H1 Ca A21', 'range' => 'Hoa 12H1 Ca A21!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca A31', 'range' => 'Hoa 12H1 Ca A31!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca A41', 'range' => 'Hoa 12H1 Ca A41!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca A51', 'range' => 'Hoa 12H1 Ca A51!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca A52', 'range' => 'Hoa 12H1 Ca A52!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca A61', 'range' => 'Hoa 12H1 Ca A61!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca B71', 'range' => 'Hoa 12H1 Ca B71!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca B72', 'range' => 'Hoa 12H1 Ca B72!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca B73', 'range' => 'Hoa 12H1 Ca B73!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca B81', 'range' => 'Hoa 12H1 Ca B81!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca B82', 'range' => 'Hoa 12H1 Ca B82!A3:Z1000'],
    ['name' => 'Hoa 12H1 Ca B83', 'range' => 'Hoa 12H1 Ca B83!A3:Z1000'],
    ['name' => 'Hóa 9 Chuyên 1',  'range' => 'Hóa 9 Chuyên 1!A3:Z1000'],
    ['name' => 'Hóa 9 Chuyên 2',  'range' => 'Hóa 9 Chuyên 2!A3:Z1000'],
];

// ─────────────────────────────────────────────────────
//  TIỆN ÍCH JWT / HTTP
// ─────────────────────────────────────────────────────
function b64u(string $d): string
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
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $r = curl_exec($ch);
    if (curl_errno($ch)) throw new RuntimeException('cURL GET: ' . curl_error($ch));
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
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $r = curl_exec($ch);
    if (curl_errno($ch)) throw new RuntimeException('cURL POST: ' . curl_error($ch));
    curl_close($ch);
    return $r;
}

// ─────────────────────────────────────────────────────
//  GOOGLE ACCESS TOKEN
// ─────────────────────────────────────────────────────
function getAccessToken(): string
{
    // Dùng cache nếu còn hạn
    if (file_exists(TOKEN_FILE)) {
        $c = json_decode(file_get_contents(TOKEN_FILE), true);
        if (!empty($c['access_token']) && $c['expires_at'] > time() + 60) {
            return $c['access_token'];
        }
    }

    if (!file_exists(CREDENTIALS_FILE)) {
        throw new RuntimeException('Không tìm thấy credentials.json');
    }

    $creds = json_decode(file_get_contents(CREDENTIALS_FILE), true);
    if (!isset($creds['private_key'], $creds['client_email'])) {
        throw new RuntimeException('credentials.json không hợp lệ');
    }

    $iat    = time();
    $header = b64u(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claim  = b64u(json_encode([
        'iss'   => $creds['client_email'],
        'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $iat,
        'exp'   => $iat + 3600,
    ]));

    openssl_sign("$header.$claim", $sig, $creds['private_key'], 'SHA256');
    $jwt = "$header.$claim." . b64u($sig);

    $resp = json_decode(httpPost(
        'https://oauth2.googleapis.com/token',
        http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ])
    ), true);

    if (empty($resp['access_token'])) {
        throw new RuntimeException('Lỗi lấy token: ' . ($resp['error_description'] ?? json_encode($resp)));
    }

    file_put_contents(TOKEN_FILE, json_encode([
        'access_token' => $resp['access_token'],
        'expires_at'   => $iat + ($resp['expires_in'] ?? 3600),
    ]));

    return $resp['access_token'];
}

// ─────────────────────────────────────────────────────
//  LẤY HÀNG TỪ GOOGLE SHEETS
// ─────────────────────────────────────────────────────
function fetchSheetRows(string $range): array
{
    $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
        urlencode(SPREADSHEET_ID),
        urlencode($range)
    );

    $json = json_decode(httpGet($url, getAccessToken()), true);

    if (isset($json['error'])) {
        throw new RuntimeException('Sheets API: ' . $json['error']['message']);
    }

    return $json['values'] ?? [];
}

// ─────────────────────────────────────────────────────
//  KẾT NỐI DB
// ─────────────────────────────────────────────────────
function getDB(): PDO
{
    // Tạo database nếu chưa có
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    return $pdo;
}

// ─────────────────────────────────────────────────────
//  TẠO BẢNG
// ─────────────────────────────────────────────────────
function ensureTable(PDO $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
            `ma_hs`      VARCHAR(30)      NOT NULL COMMENT 'Mã học sinh',
            `ho_ten`     VARCHAR(150)     NOT NULL COMMENT 'Họ và tên đầy đủ',
            `sdt`        VARCHAR(20)      NOT NULL COMMENT 'Số điện thoại',
            `lop`        VARCHAR(60)      NOT NULL DEFAULT '' COMMENT 'Tên sheet / lớp',
            `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_student` (`ma_hs`, `ho_ten`(100), `sdt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Danh sách học sinh Trung Tâm NP';
    ");
}

// ─────────────────────────────────────────────────────
//  IMPORT HỌC SINH TỪ HÀNG SHEET
//
//  Cấu trúc cột (tính từ 0):
//    0 = STT      1 = MÃ HS
//    2 = SĐT      3 = HỌ TÊN
//    (các cột còn lại = ngày điểm danh, bỏ qua)
// ─────────────────────────────────────────────────────
function importRows(PDO $db, array $rows, string $lopName): array
{
    $inserted  = 0;
    $duplicate = 0;
    $invalid   = 0;
    $log       = [];

    // Bỏ hàng đầu (header)
    $data = array_slice($rows, 1);

    $stmt = $db->prepare("
        INSERT IGNORE INTO `students` (`ma_hs`, `ho_ten`, `sdt`, `lop`)
        VALUES (:ma_hs, :ho_ten, :sdt, :lop)
    ");

    foreach ($data as $i => $row) {
        $sheetLine = $i + 3; // bắt đầu từ A3

        $ma_hs  = trim($row[1] ?? '');
        $sdt    = trim($row[2] ?? '');
        $ho_ten = trim($row[3] ?? '');

        // Bỏ qua hàng hoàn toàn trống
        if ($ma_hs === '' && $sdt === '' && $ho_ten === '') {
            continue;
        }

        // Kiểm tra 3 trường bắt buộc
        $missing = [];
        if ($ma_hs  === '') $missing[] = 'Mã HS';
        if ($ho_ten === '') $missing[] = 'Họ Tên';
        if ($sdt    === '') $missing[] = 'SĐT';

        if ($missing) {
            $invalid++;
            $log[] = [
                'type' => 'invalid',
                'line' => $sheetLine,
                'msg'  => 'Thiếu: ' . implode(', ', $missing),
                'raw'  => "[$ma_hs] $ho_ten $sdt"
            ];
            continue;
        }

        // Validate SĐT cơ bản (10–11 chữ số, bắt đầu bằng 0)
        if (!preg_match('/^0\d{8,10}$/', $sdt)) {
            $invalid++;
            $log[] = [
                'type' => 'invalid',
                'line' => $sheetLine,
                'msg'  => "SĐT không hợp lệ: $sdt",
                'raw'  => "[$ma_hs] $ho_ten"
            ];
            continue;
        }

        $stmt->execute([
            ':ma_hs' => $ma_hs,
            ':ho_ten' => $ho_ten,
            ':sdt'   => $sdt,
            ':lop'    => $lopName
        ]);

        if ($stmt->rowCount() > 0) {
            $inserted++;
            $log[] = [
                'type' => 'inserted',
                'line' => $sheetLine,
                'msg'  => "[$ma_hs] $ho_ten — $sdt"
            ];
        } else {
            $duplicate++;
            $log[] = [
                'type' => 'duplicate',
                'line' => $sheetLine,
                'msg'  => "[$ma_hs] $ho_ten — $sdt"
            ];
        }
    }

    return compact('inserted', 'duplicate', 'invalid', 'log');
}

// ─────────────────────────────────────────────────────
//  CHẠY CHÍNH
// ─────────────────────────────────────────────────────
$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
}

// Kết quả mỗi bước
$steps   = [];
$results = [];

// Bước 1: Kết nối DB
try {
    $db = getDB();
    $steps[] = ['ok' => true, 'title' => 'Kết nối MySQL & tạo database', 'detail' => 'Database `' . DB_NAME . '` sẵn sàng.'];
} catch (Throwable $e) {
    $steps[] = ['ok' => false, 'title' => 'Kết nối MySQL', 'detail' => $e->getMessage()];
    $db = null;
}

// Bước 2: Tạo bảng
if ($db) {
    try {
        ensureTable($db);
        $steps[] = ['ok' => true, 'title' => 'Tạo bảng `students`', 'detail' => 'Bảng đã tạo (hoặc đã tồn tại).'];
    } catch (Throwable $e) {
        $steps[] = ['ok' => false, 'title' => 'Tạo bảng', 'detail' => $e->getMessage()];
        $db = null;
    }
}

// Bước 3+: Import từng sheet
$token = null;
if ($db) {
    try {
        $token = getAccessToken();
        $steps[] = ['ok' => true, 'title' => 'Lấy Google Access Token', 'detail' => 'Token hợp lệ.'];
    } catch (Throwable $e) {
        $steps[] = ['ok' => false, 'title' => 'Lấy Google Access Token', 'detail' => $e->getMessage()];
    }
}

if ($db && $token) {
    foreach (SHEETS as $sheet) {
        $name  = $sheet['name'];
        $range = $sheet['range'];

        try {
            $rows    = fetchSheetRows($range);
            $count   = count($rows) - 1; // trừ header
            $steps[] = ['ok' => true, 'title' => "Đọc sheet: $name", 'detail' => "Tải được $count dòng dữ liệu."];

            $result          = importRows($db, $rows, $name);
            $results[$name]  = $result;

            $steps[] = [
                'ok'     => true,
                'title'  => "Import: $name",
                'detail' => sprintf(
                    'Thêm mới: %d  |  Trùng (bỏ qua): %d  |  Thiếu dữ liệu: %d',
                    $result['inserted'],
                    $result['duplicate'],
                    $result['invalid']
                ),
            ];
        } catch (Throwable $e) {
            $steps[] = ['ok' => false, 'title' => "Sheet: $name", 'detail' => $e->getMessage()];
        }
    }
}

// ─────────────────────────────────────────────────────
//  OUTPUT: CLI
// ─────────────────────────────────────────────────────
if ($isCli) {
    $ok   = "\033[32m✓\033[0m";
    $fail = "\033[31m✗\033[0m";

    echo "\n=== NP Setup DB ===\n\n";

    foreach ($steps as $i => $s) {
        $icon = $s['ok'] ? $ok : $fail;
        printf("%s Bước %d: %s\n   → %s\n\n", $icon, $i + 1, $s['title'], $s['detail']);
    }

    foreach ($results as $lopName => $r) {
        echo "── Chi tiết: $lopName ──\n";
        foreach ($r['log'] as $entry) {
            $prefix = match ($entry['type']) {
                'inserted'  => "\033[32m+ \033[0m",
                'duplicate' => "\033[90m~ \033[0m",
                default     => "\033[33m! \033[0m",
            };
            echo "  {$prefix}[Dòng {$entry['line']}] {$entry['msg']}\n";
        }
        echo "\n";
    }

    echo "=== Xong ===\n\n";
    exit(0);
}

// ─────────────────────────────────────────────────────
//  OUTPUT: HTML
// ─────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Setup DB – NP</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
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
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 2rem 1rem;
            min-height: 100vh
        }

        .wrap {
            max-width: 860px;
            margin: 0 auto
        }

        h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .3rem
        }

        .sub {
            color: var(--muted);
            font-size: .82rem;
            margin-bottom: 2rem
        }

        .step {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem 1.4rem;
            margin-bottom: .8rem
        }

        .step-head {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            margin-bottom: .4rem
        }

        .ico {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            font-weight: 800;
            flex-shrink: 0
        }

        .ok {
            background: #1f4a2a;
            color: var(--green)
        }

        .err {
            background: #3d1c1c;
            color: var(--red)
        }

        .detail {
            font-size: .8rem;
            color: var(--muted);
            padding-left: 34px
        }

        h2 {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin: 1.6rem 0 .8rem
        }

        .stats {
            display: flex;
            gap: .8rem;
            flex-wrap: wrap;
            margin-bottom: 1.2rem
        }

        .stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .8rem 1.2rem;
            flex: 1;
            min-width: 120px
        }

        .stat .n {
            font-size: 1.8rem;
            font-weight: 800;
            font-family: monospace;
            line-height: 1
        }

        .stat .l {
            font-size: .7rem;
            color: var(--muted);
            margin-top: 3px
        }

        .log {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .9rem 1.2rem;
            margin-bottom: 1.2rem
        }

        .log h3 {
            font-size: .82rem;
            font-weight: 700;
            color: var(--blue);
            margin-bottom: .6rem
        }

        ul {
            list-style: none;
            font-family: monospace;
            font-size: .75rem;
            line-height: 1.75
        }

        .ins {
            color: var(--green)
        }

        .dup {
            color: var(--muted)
        }

        .inv {
            color: var(--yellow)
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1>⚙️ Setup Database – NP</h1>
        <p class="sub">Tự động tạo bảng <code>students</code> và import từ Google Sheets</p>

        <?php foreach ($steps as $i => $s): ?>
            <div class="step">
                <div class="step-head">
                    <span class="ico <?= $s['ok'] ? 'ok' : 'err' ?>"><?= $s['ok'] ? '✓' : '✗' ?></span>
                    <span>Bước <?= $i + 1 ?>: <?= htmlspecialchars($s['title']) ?></span>
                </div>
                <div class="detail"><?= htmlspecialchars($s['detail']) ?></div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($results as $lopName => $r): ?>
            <h2>📋 <?= htmlspecialchars($lopName) ?></h2>
            <div class="stats">
                <div class="stat">
                    <div class="n" style="color:var(--green)"><?= $r['inserted'] ?></div>
                    <div class="l">Thêm mới</div>
                </div>
                <div class="stat">
                    <div class="n" style="color:var(--muted)"><?= $r['duplicate'] ?></div>
                    <div class="l">Bỏ qua (trùng)</div>
                </div>
                <div class="stat">
                    <div class="n" style="color:var(--yellow)"><?= $r['invalid'] ?></div>
                    <div class="l">Thiếu dữ liệu</div>
                </div>
            </div>
            <div class="log">
                <h3>Chi tiết dòng</h3>
                <ul>
                    <?php foreach ($r['log'] as $e):
                        $cls  = match ($e['type']) {
                            'inserted' => 'ins',
                            'duplicate' => 'dup',
                            default => 'inv'
                        };
                        $icon = match ($e['type']) {
                            'inserted' => '+',
                            'duplicate' => '~',
                            default => '!'
                        };
                    ?>
                        <li class="<?= $cls ?>">[Dòng <?= $e['line'] ?>] <?= $icon ?> <?= htmlspecialchars($e['msg']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <p style="margin-top:1rem;font-size:.74rem;color:var(--muted)">
            ⚠️ Xóa hoặc bảo vệ file này sau khi setup xong để tránh lộ thông tin DB.
        </p>
    </div>
</body>

</html>