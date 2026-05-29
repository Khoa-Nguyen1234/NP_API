<?php

/**
 * =====================================================
 *  STUDENT_DASHBOARD.PHP — Multi-sheet, All Ca A + B
 *  - Tìm học sinh trên TẤT CẢ sheet (Ca A + Ca B)
 *  - Khớp theo MÃ HS (cột 1) + HỌ TÊN (cột 3) + SĐT (cột 2)
 *  - Chỉ lấy dữ liệu TRÊN dòng màu vàng (dòng trống phân cách)
 *  - Nếu HS xuất hiện nhiều ca → hiển thị tab cho từng ca
 * =====================================================
 */

session_start();

if (empty($_SESSION['student_ma_hs'])) {
    header('Location: student_login.php');
    exit;
}

$student_ma_hs  = $_SESSION['student_ma_hs'];
$student_ho_ten = $_SESSION['student_ho_ten'];
$student_sdt    = $_SESSION['student_sdt'];

// ── Cấu hình ─────────────────────────────────────────
define('SPREADSHEET_ID',   '1QIpax_ruAJeZETenKARrlnmfAAIY0eL5oi3H7o578BE');
define('CREDENTIALS_FILE', __DIR__ . '/credentials.json');
define('TOKEN_FILE',       __DIR__ . '/token_cache.json');
define('CACHE_DURATION',   60);

// Tất cả các sheet cần tìm (Ca A + Ca B)
$ALL_SHEETS = [
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
];

// ── Helpers HTTP / JWT ────────────────────────────────
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
    if (curl_errno($ch)) throw new Exception(curl_error($ch));
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
    if (curl_errno($ch)) throw new Exception(curl_error($ch));
    curl_close($ch);
    return $r;
}

function getAccessToken(): string
{
    if (file_exists(TOKEN_FILE)) {
        $c = json_decode(file_get_contents(TOKEN_FILE), true);
        if ($c && $c['expires_at'] > time() + 30) return $c['access_token'];
    }
    $creds   = json_decode(file_get_contents(CREDENTIALS_FILE), true);
    $now     = time() - 60;
    $h       = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $p       = base64UrlEncode(json_encode([
        'iss'   => $creds['client_email'],
        'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now,
    ]));
    openssl_sign("$h.$p", $sig, $creds['private_key'], 'SHA256');
    $data = json_decode(httpPost(
        'https://oauth2.googleapis.com/token',
        http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => "$h.$p." . base64UrlEncode($sig)])
    ), true);
    if (empty($data['access_token'])) throw new Exception($data['error_description'] ?? 'Token error');
    file_put_contents(TOKEN_FILE, json_encode(['access_token' => $data['access_token'], 'expires_at' => $now + ($data['expires_in'] ?? 3600)]));
    return $data['access_token'];
}

/**
 * Lấy dữ liệu 1 sheet, CHỈ phần TRÊN dòng màu vàng
 * (dòng màu vàng = dòng hoàn toàn trống phân cách danh sách chính thức
 *  với danh sách học sinh thử/vãng lai bên dưới)
 * Trả về mảng rows (hàng 0 = header, hàng 1+ = dữ liệu)
 */
function getSheetAboveYellow(string $range): array
{
    $cacheKey  = sys_get_temp_dir() . '/np_sheet_' . md5($range) . '.json';
    if (file_exists($cacheKey)) {
        $c = json_decode(file_get_contents($cacheKey), true);
        if ($c && $c['cached_at'] > time() - CACHE_DURATION) return $c['data'];
    }

    $url  = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
        urlencode(SPREADSHEET_ID),
        urlencode($range)
    );
    $json = json_decode(httpGet($url, getAccessToken()), true);
    if (isset($json['error'])) throw new Exception('Sheets API: ' . $json['error']['message']);
    $rows = $json['values'] ?? [];

    // Giữ header (hàng 0), cắt tại dòng trống đầu tiên sau header
    $cut = [];
    foreach ($rows as $i => $row) {
        if ($i === 0) {
            $cut[] = $row;
            continue;
        } // header luôn giữ
        $allEmpty = true;
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') {
                $allEmpty = false;
                break;
            }
        }
        if ($allEmpty) break; // dừng → bỏ phần dưới dòng vàng
        $cut[] = $row;
    }

    file_put_contents($cacheKey, json_encode(['cached_at' => time(), 'data' => $cut]));
    return $cut;
}

function isDateCol(string $h): bool
{
    return (bool)preg_match('/^\d{1,2}\/\d{2}/', trim($h));
}

function renderAttBadge(string $val): string
{
    if ($val === '') return '<span class="att-empty">—</span>';
    $v = strtolower($val);
    if ($v === 'x') return '<span class="att-badge att-present" title="Có mặt">✓</span>';
    if ($v === 'o') return '<span class="att-badge att-absent"  title="Vắng mặt">✗</span>';
    if ($v === '-') return '<span class="att-badge att-dash"    title="Chưa học">–</span>';
    return '<span class="att-badge att-other">' . htmlspecialchars($val) . '</span>';
}

// ── Logout ────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: student_login.php');
    exit;
}

// ── Tìm học sinh trên TẤT CẢ sheet ──────────────────
// Cấu trúc cột cố định: 0=STT, 1=MÃ HS, 2=SĐT, 3=HỌ TÊN, 4+=ngày...
// Khớp theo: MÃ HS (cột 1) + HỌ TÊN (cột 3) + SĐT (cột 2)
$error     = null;
$matches   = []; // mảng: [['sheet'=>name,'headers'=>[],'row'=>[]]]

try {
    foreach ($ALL_SHEETS as $sheet) {
        $rows = getSheetAboveYellow($sheet['range']);
        if (count($rows) < 2) continue; // không có data
        $hdrs = $rows[0];

        foreach (array_slice($rows, 1) as $row) {
            $ma  = trim($row[1] ?? '');
            $sdt = trim($row[2] ?? '');
            $ten = trim($row[3] ?? '');

            // Bỏ qua hàng hoàn toàn trống
            if ($ma === '' && $sdt === '' && $ten === '') continue;

            /**
             * Quy tắc khớp theo loại ca:
             *   - Ca A (ca chính): bắt buộc khớp CẢ 3 — Mã HS + Họ Tên + SĐT
             *   - Ca B, C, D... : chỉ cần khớp 2   — Họ Tên + SĐT
             *
             * Nhận biết Ca A: tên sheet chứa " Ca A" (vd: "Hoa 12H1 Ca A21")
             * Về sau thêm lớp mới / Ca C / Ca D... logic này vẫn tự động đúng.
             */
            $isCaA = (stripos($sheet['name'], ' Ca A') !== false);

            $isMatch = $isCaA
                ? ($ma === $student_ma_hs && $ten === $student_ho_ten && $sdt === $student_sdt)
                : ($ten === $student_ho_ten && $sdt === $student_sdt);

            if ($isMatch) {
                $matches[] = [
                    'sheet'   => $sheet['name'],
                    'headers' => $hdrs,
                    'row'     => $row,
                ];
                break; // mỗi sheet tìm 1 dòng duy nhất
            }
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// ── Tổng hợp thống kê điểm danh qua tất cả ca ───────
function calcStats(array $headers, array $row): array
{
    $s = ['present' => 0, 'absent' => 0, 'other' => 0, 'total' => 0];
    foreach ($headers as $ci => $h) {
        if (!isDateCol($h)) continue;
        $v = strtolower(trim($row[$ci] ?? ''));
        if ($v === 'x') {
            $s['present']++;
            $s['total']++;
        } elseif ($v === 'o') {
            $s['absent']++;
            $s['total']++;
        } elseif ($v !== '' && $v !== '-') {
            $s['other']++;
            $s['total']++;
        }
    }
    return $s;
}

// Tổng hợp tất cả ca
$totalStats = ['present' => 0, 'absent' => 0, 'other' => 0, 'total' => 0];
foreach ($matches as $m) {
    $s = calcStats($m['headers'], $m['row']);
    foreach ($totalStats as $k => $_) $totalStats[$k] += $s[$k];
}
$totalRate = $totalStats['total'] > 0 ? round($totalStats['present'] / $totalStats['total'] * 100) : 0;

// ── Lấy điểm & ghi chú từ match đầu tiên ───────────
function getScoreAndNote(array $headers, array $row): array
{
    $score  = '';
    $ghiChu = '';
    // Điểm: cột 17 nếu có chữ số
    $v17 = trim($row[17] ?? '');
    if ($v17 !== '' && preg_match('/^\d/', $v17)) $score = $v17;
    // Điểm fallback: cột cuối trùng tên "12/04"
    if ($score === '') {
        foreach (array_reverse($headers, true) as $ci => $h) {
            if (trim($h) === '12/04') {
                $v = trim($row[$ci] ?? '');
                if ($v !== '' && preg_match('/^\d/', $v)) {
                    $score = $v;
                    break;
                }
            }
        }
    }
    // Ghi chú
    foreach ($headers as $ci => $h) {
        if (mb_strtoupper(trim($h)) === 'GHI CHÚ') {
            $ghiChu = trim($row[$ci] ?? '');
            break;
        }
    }
    return ['score' => $score, 'ghiChu' => $ghiChu];
}

$firstMatch  = $matches[0] ?? null;
$scoreNote   = $firstMatch ? getScoreAndNote($firstMatch['headers'], $firstMatch['row']) : ['score' => '', 'ghiChu' => ''];
$score       = $scoreNote['score'];
$ghiChu      = $scoreNote['ghiChu'];
$sheetLabels = array_column($matches, 'sheet');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($student_ho_ten) ?> – NP Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --navy: #0a1628;
            --blue: #1560bd;
            --blue2: #1a75e8;
            --green: #16a34a;
            --green2: #dcfce7;
            --red: #dc2626;
            --red2: #fee2e2;
            --amber: #d97706;
            --amber2: #fef3c7;
            --slate: #64748b;
            --border: #e2e8f0;
            --bg: #f0f5fb;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg);
            color: #1e2d45;
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .topbar {
            background: linear-gradient(135deg, var(--navy) 0%, #112258 100%);
            color: #fff;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 58px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .topbar-logo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar-logo svg {
            width: 22px;
            height: 14px
        }

        .topbar-title {
            font-size: .9rem;
            font-weight: 800;
            letter-spacing: -.2px
        }

        .topbar-sub {
            font-size: .65rem;
            color: rgba(255, 255, 255, .55);
            margin-top: 1px
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            border-radius: 8px;
            padding: 6px 14px;
            font-family: inherit;
            font-size: .75rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, .18)
        }

        /* ── Layout ── */
        .page {
            max-width: 1000px;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--blue2) 0%, var(--blue) 100%);
            color: #fff;
            border-radius: 16px;
            padding: 1.4rem 1.75rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
            box-shadow: 0 8px 28px rgba(21, 96, 189, .28);
            animation: slideDown .4s ease both;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-12px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .hero-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            border: 2.5px solid rgba(255, 255, 255, .5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .hero-info {
            flex: 1;
            min-width: 180px
        }

        .hero-name {
            font-size: 1.2rem;
            font-weight: 900;
            letter-spacing: -.3px
        }

        .hero-meta {
            font-size: .76rem;
            opacity: .8;
            margin-top: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hero-meta span {
            display: flex;
            align-items: center;
            gap: 4px
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px
        }

        .hero-badge {
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: .72rem;
            font-weight: 800;
            white-space: nowrap;
        }

        /* ── Stats row ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .8rem;
            margin-bottom: 1.2rem;
        }

        @media(max-width:560px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            animation: fadeUp .4s ease both;
        }

        .stat-card:nth-child(2) {
            animation-delay: .05s
        }

        .stat-card:nth-child(3) {
            animation-delay: .10s
        }

        .stat-card:nth-child(4) {
            animation-delay: .15s
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .stat-val {
            font-size: 1.9rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 4px
        }

        .stat-lbl {
            font-size: .7rem;
            color: var(--slate);
            font-weight: 600
        }

        .stat-green {
            color: var(--green)
        }

        .stat-red {
            color: var(--red)
        }

        .stat-amber {
            color: var(--amber)
        }

        .stat-blue {
            color: var(--blue2)
        }

        .rate-wrap {
            height: 6px;
            background: #e8f0fe;
            border-radius: 3px;
            margin-top: 7px;
            overflow: hidden
        }

        .rate-bar {
            height: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--green), #22d3ee);
            transition: width 1s ease
        }

        /* ── Section ── */
        .section {
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            overflow: hidden;
            margin-bottom: 1.2rem;
        }

        .section-header {
            padding: .85rem 1.4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .section-title {
            font-size: .88rem;
            font-weight: 800;
            color: #1e2d45;
            display: flex;
            align-items: center;
            gap: 7px
        }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))
        }

        .info-item {
            padding: .9rem 1.4rem;
            border-bottom: 1px solid var(--border);
            border-right: 1px solid var(--border);
        }

        .info-lbl {
            font-size: .68rem;
            font-weight: 800;
            color: var(--slate);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 5px;
        }

        .info-val {
            font-size: .92rem;
            font-weight: 700;
            color: #1e2d45
        }

        /* ── Tabs ── */
        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--border);
            overflow-x: auto;
            scrollbar-width: none;
        }

        .tabs::-webkit-scrollbar {
            display: none
        }

        .tab-btn {
            padding: .7rem 1.2rem;
            font-family: inherit;
            font-size: .78rem;
            font-weight: 700;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--slate);
            white-space: nowrap;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -2px;
            transition: color .2s, border-color .2s;
        }

        .tab-btn:hover {
            color: var(--blue2)
        }

        .tab-btn.active {
            color: var(--blue2);
            border-bottom-color: var(--blue2)
        }

        /* ── Attendance table ── */
        .att-table-wrap {
            overflow-x: auto
        }

        .tab-panel {
            display: none
        }

        .tab-panel.active {
            display: block
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .83rem
        }

        thead th {
            background: #f8fafc;
            padding: 9px 12px;
            text-align: center;
            font-weight: 800;
            color: var(--slate);
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        thead th:first-child {
            text-align: left
        }

        tbody td {
            padding: 10px 12px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9
        }

        tbody td:first-child {
            text-align: left;
            font-weight: 600;
            color: #1e2d45;
            font-size: .8rem
        }

        tbody tr:last-child td {
            border-bottom: none
        }

        tbody tr:hover {
            background: #f8faff
        }

        /* ── Attendance badges ── */
        .att-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: .75rem;
            font-weight: 800;
        }

        .att-present {
            background: var(--green2);
            color: var(--green)
        }

        .att-absent {
            background: var(--red2);
            color: var(--red)
        }

        .att-other {
            background: var(--amber2);
            color: var(--amber);
            font-size: .68rem;
            width: auto;
            border-radius: 20px;
            padding: 0 8px;
            height: 24px;
        }

        .att-dash {
            background: #f1f5f9;
            color: var(--slate)
        }

        .att-empty {
            color: #cbd5e1;
            font-size: 1rem
        }

        /* ── Legend ── */
        .legend {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center
        }

        .leg-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .7rem;
            color: var(--slate);
            font-weight: 600;
        }

        /* ── Not found ── */
        .not-found {
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 3rem;
            text-align: center;
        }

        .nf-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem
        }

        .not-found h2 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e2d45;
            margin-bottom: .5rem
        }

        .not-found p {
            font-size: .82rem;
            color: var(--slate);
            line-height: 1.6
        }

        /* ── Error ── */
        .err-panel {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            color: var(--red);
            font-size: .85rem;
            margin-bottom: 1rem;
        }

        /* ── Note box ── */
        .note-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 1rem 1.4rem;
        }

        .note-lbl {
            font-size: .72rem;
            font-weight: 800;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 6px;
        }

        .note-val {
            font-size: .88rem;
            color: #78350f;
            font-weight: 600
        }

        footer {
            text-align: center;
            margin-top: 2rem;
            font-size: .68rem;
            color: var(--slate);
        }

        @media(max-width:480px) {
            .hero {
                padding: 1.1rem 1.2rem
            }

            .hero-name {
                font-size: 1rem
            }
        }
    </style>
</head>

<body>

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-logo">
                <svg viewBox="0 0 60 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 36V2h5L21 24V2h5v34h-5L7 12v24H2z" fill="white" />
                    <path d="M32 2h13c6.5 0 11 4.2 11 10.5S51.5 23 45 23H37v13h-5V2zm5 5v11h8c3.3 0 5.5-2.1 5.5-5.5S48.3 7 45 7h-8z" fill="white" />
                </svg>
            </div>
            <div>
                <div class="topbar-title">NP Student Portal</div>
                <div class="topbar-sub">Hoa 12H1 · 2025–2026</div>
            </div>
        </div>
        <a href="?logout=1" class="logout-btn">🚪 Đăng xuất</a>
    </div>

    <div class="page">

        <?php if ($error): ?>
            <div class="err-panel">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Hero -->
        <div class="hero">
            <div class="hero-avatar">🎓</div>
            <div class="hero-info">
                <div class="hero-name"><?= htmlspecialchars($student_ho_ten) ?></div>
                <div class="hero-meta">
                    <span>🆔 <?= htmlspecialchars($student_ma_hs) ?></span>
                    <span>📱 <?= htmlspecialchars($student_sdt) ?></span>
                </div>
            </div>
            <?php if (!empty($matches)): ?>
                <div class="hero-badges">
                    <?php foreach ($sheetLabels as $lbl): ?>
                        <span class="hero-badge">🏫 <?= htmlspecialchars($lbl) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($matches) && !$error): ?>
            <!-- Không tìm thấy -->
            <div class="not-found">
                <div class="nf-icon">😕</div>
                <h2>Không tìm thấy dữ liệu</h2>
                <p>
                    Mã HS <strong><?= htmlspecialchars($student_ma_hs) ?></strong>,
                    họ tên <strong><?= htmlspecialchars($student_ho_ten) ?></strong>,
                    SĐT <strong><?= htmlspecialchars($student_sdt) ?></strong><br>
                    chưa xuất hiện trong danh sách chính thức của bất kỳ ca nào.<br><br>
                    Vui lòng liên hệ <strong>Trung Tâm NP</strong> để được hỗ trợ.
                </p>
            </div>

        <?php else: ?>

            <!-- Stats tổng hợp -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-val stat-blue"><?= $totalStats['total'] ?></div>
                    <div class="stat-lbl">Tổng buổi đã tính</div>
                    <div class="rate-wrap">
                        <div class="rate-bar" style="width:<?= $totalRate ?>%"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-val stat-green"><?= $totalStats['present'] ?></div>
                    <div class="stat-lbl">Có mặt (✓)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-val stat-red"><?= $totalStats['absent'] ?></div>
                    <div class="stat-lbl">Vắng mặt (✗)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-val stat-amber"><?= $totalRate ?>%</div>
                    <div class="stat-lbl">Tỷ lệ có mặt</div>
                </div>
            </div>

            <!-- Thông tin cá nhân -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">👤 Thông Tin Cá Nhân</div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-lbl">Mã Học Sinh</div>
                        <div class="info-val" style="color:var(--blue2);font-family:monospace"><?= htmlspecialchars($student_ma_hs) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-lbl">Họ và Tên</div>
                        <div class="info-val"><?= htmlspecialchars($student_ho_ten) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-lbl">Số Điện Thoại</div>
                        <div class="info-val" style="font-family:monospace"><?= htmlspecialchars($student_sdt) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-lbl">Ca học</div>
                        <div class="info-val" style="color:var(--blue2);font-size:.82rem">
                            <?= htmlspecialchars(implode(', ', $sheetLabels) ?: '—') ?>
                        </div>
                    </div>
                    <?php if ($score !== ''): ?>
                        <div class="info-item">
                            <div class="info-lbl">Điểm Gần Nhất</div>
                            <div class="info-val">
                                <span style="color:var(--green);font-size:1.1rem;font-weight:900"><?= htmlspecialchars($score) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bảng điểm danh (tab nếu nhiều ca) -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">📅 Lịch Sử Điểm Danh</div>
                    <div class="legend">
                        <span class="leg-item"><span class="att-badge att-present" style="width:22px;height:22px;font-size:.65rem">✓</span> Có mặt</span>
                        <span class="leg-item"><span class="att-badge att-absent" style="width:22px;height:22px;font-size:.65rem">✗</span> Vắng</span>
                        <span class="leg-item"><span class="att-badge att-other" style="height:20px;padding:0 7px;font-size:.62rem">B82</span> Ca khác</span>
                    </div>
                </div>

                <?php if (count($matches) > 1): ?>
                    <!-- Nhiều ca → hiển thị tabs -->
                    <div class="tabs" id="att-tabs">
                        <?php foreach ($matches as $idx => $m): ?>
                            <button class="tab-btn <?= $idx === 0 ? 'active' : '' ?>"
                                onclick="switchTab(<?= $idx ?>)">
                                <?= htmlspecialchars($m['sheet']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($matches as $idx => $m):
                    $panelStats = calcStats($m['headers'], $m['row']);
                    $panelRate  = $panelStats['total'] > 0 ? round($panelStats['present'] / $panelStats['total'] * 100) : 0;
                ?>
                    <div class="tab-panel <?= $idx === 0 ? 'active' : '' ?>" id="panel-<?= $idx ?>">

                        <?php if (count($matches) > 1): ?>
                            <!-- Mini stats per ca -->
                            <div style="display:flex;gap:.6rem;padding:.8rem 1.2rem;border-bottom:1px solid var(--border);flex-wrap:wrap">
                                <span style="font-size:.76rem;color:var(--blue2);font-weight:800">
                                    📊 <?= $panelStats['present'] ?>✓ / <?= $panelStats['absent'] ?>✗ — <?= $panelRate ?>% có mặt
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="att-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="text-align:left">Buổi học</th>
                                        <th>Điểm danh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $hasDates = false;
                                    foreach ($m['headers'] as $ci => $h) {
                                        if (!isDateCol($h)) continue;
                                        $hasDates = true;
                                        $val = trim($m['row'][$ci] ?? '');
                                        echo '<tr>';
                                        echo '<td>📆 ' . htmlspecialchars(trim($h)) . '</td>';
                                        echo '<td>' . renderAttBadge($val) . '</td>';
                                        echo '</tr>';
                                    }
                                    if (!$hasDates): ?>
                                        <tr>
                                            <td colspan="2" style="padding:2rem;text-align:center;color:var(--slate)">Không có dữ liệu buổi học.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Ghi chú -->
            <?php if ($ghiChu !== ''): ?>
                <div class="note-box">
                    <div class="note-lbl">📝 Ghi Chú</div>
                    <div class="note-val"><?= htmlspecialchars($ghiChu) ?></div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <footer>© 2025 Trung Tâm Giáo Dục Tri Thức NP · Dữ liệu từ Google Sheets · Chỉ bạn mới thấy thông tin của mình</footer>
    </div>

    <script>
        // ── Tab switching ───────────────────────────────
        function switchTab(idx) {
            document.querySelectorAll('.tab-btn').forEach((b, i) => {
                b.classList.toggle('active', i === idx);
            });
            document.querySelectorAll('.tab-panel').forEach((p, i) => {
                p.classList.toggle('active', i === idx);
            });
        }

        // ── Animate rate bar ─────────────────────────────
        window.addEventListener('load', function() {
            document.querySelectorAll('.rate-bar').forEach(function(b) {
                var w = b.style.width;
                b.style.width = '0';
                setTimeout(function() {
                    b.style.width = w;
                }, 200);
            });
        });
    </script>
</body>

</html>