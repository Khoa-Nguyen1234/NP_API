<?php

/**
 * =====================================================
 *  api/auth.php — REST API xác thực học sinh
 *  POST /api/auth.php  { "ho_ten": "...", "ma_hs": "..." }
 *  → 200 { "ok": true,  "student": { ... } }
 *  → 401 { "ok": false, "field": "ma_hs"|"ho_ten"|"both", "message": "..." }
 * =====================================================
 */

// ── CORS (cho phép frontend ở cùng origin hoặc localhost dev) ──
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── Đọc body JSON ──────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);

// Fallback: nhận từ form-data
if (empty($body)) {
    $body = $_POST;
}

$ho_ten_input = trim($body['ho_ten'] ?? '');
$ma_hs_input  = trim($body['ma_hs']  ?? '');

// ── Validate đầu vào ───────────────────────────────────
if ($ho_ten_input === '' || $ma_hs_input === '') {
    http_response_code(422);
    echo json_encode([
        'ok'      => false,
        'field'   => 'both',
        'message' => 'Vui lòng điền đầy đủ họ tên và mã học sinh.',
    ]);
    exit;
}

// ── Kết nối DB ─────────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'np4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_bin",
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
    exit;
}

// ── Tìm theo mã HS (case-sensitive) ───────────────────
$stmt = $pdo->prepare("
    SELECT id, ma_hs, ho_ten, sdt, lop
    FROM students
    WHERE BINARY ma_hs = :ma_hs
    LIMIT 1
");
$stmt->execute([':ma_hs' => $ma_hs_input]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(401);
    echo json_encode([
        'ok'      => false,
        'field'   => 'ma_hs',
        'message' => 'Mã học sinh không đúng. Lưu ý: mã học sinh phân biệt chữ hoa/thường (NP1 ≠ np1).',
    ]);
    exit;
}

// ── Kiểm tra họ tên (binary, UTF-8 case-sensitive) ────
if ($student['ho_ten'] !== $ho_ten_input) {
    http_response_code(401);
    echo json_encode([
        'ok'      => false,
        'field'   => 'ho_ten',
        'message' => 'Họ tên không đúng. Vui lòng nhập đúng họ tên đầy đủ (phân biệt hoa/thường và dấu).',
    ]);
    exit;
}

// ── Đăng nhập thành công ───────────────────────────────
session_start();
session_regenerate_id(true);
$_SESSION['student_id']     = $student['id'];
$_SESSION['student_ma_hs']  = $student['ma_hs'];
$_SESSION['student_ho_ten'] = $student['ho_ten'];
$_SESSION['student_sdt']    = $student['sdt'];

http_response_code(200);
echo json_encode([
    'ok'      => true,
    'message' => 'Đăng nhập thành công.',
    'student' => [
        'id'     => $student['id'],
        'ma_hs'  => $student['ma_hs'],
        'ho_ten' => $student['ho_ten'],
        'sdt'    => $student['sdt'],
        'lop'    => $student['lop'] ?? '',
    ],
]);
