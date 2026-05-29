<?php

/**
 * =====================================================
 *  STUDENT_LOGIN.PHP
 *  Đăng nhập học sinh bằng Họ Tên + Mã Học Sinh
 *  ─ Họ Tên: phải khớp CHÍNH XÁC (case-sensitive UTF-8)
 *  ─ Mã HS:  phải khớp CHÍNH XÁC (NP1 ≠ np1)
 * =====================================================
 */

session_start();

// Nếu đã đăng nhập → chuyển thẳng vào dashboard
if (!empty($_SESSION['student_ma_hs'])) {
    header('Location: student_dashboard.php');
    exit;
}

// ── Cấu hình DB ──────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'np4');

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
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
    }
    return $pdo;
}

$error = '';
$fieldError = ''; // 'ho_ten' | 'ma_hs' | 'both'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten_input = trim($_POST['ho_ten'] ?? '');
    $ma_hs_input  = trim($_POST['ma_hs']  ?? '');

    if ($ho_ten_input === '' || $ma_hs_input === '') {
        $error      = 'Vui lòng điền đầy đủ họ tên và mã học sinh.';
        $fieldError = 'both';
    } else {
        try {
            $db = getDB();

            // Tìm theo mã HS trước (binary = case-sensitive)
            $stmt = $db->prepare("
                SELECT id, ma_hs, ho_ten, sdt
                FROM students
                WHERE BINARY ma_hs = :ma_hs
                LIMIT 1
            ");
            $stmt->execute([':ma_hs' => $ma_hs_input]);
            $student = $stmt->fetch();

            if (!$student) {
                // Mã HS không tồn tại hoặc sai case
                $error      = 'Mã học sinh không đúng. Lưu ý: mã học sinh phân biệt chữ hoa/thường (NP1 ≠ np1).';
                $fieldError = 'ma_hs';
            } else {
                // Mã HS đúng → kiểm tra họ tên (binary, UTF-8 case-sensitive)
                if ($student['ho_ten'] !== $ho_ten_input) {
                    $error      = 'Họ tên không đúng. Vui lòng nhập đúng họ tên đầy đủ (phân biệt hoa/thường và dấu).';
                    $fieldError = 'ho_ten';
                } else {
                    // Đăng nhập thành công
                    session_regenerate_id(true);
                    $_SESSION['student_id']     = $student['id'];
                    $_SESSION['student_ma_hs']  = $student['ma_hs'];
                    $_SESSION['student_ho_ten'] = $student['ho_ten'];
                    $_SESSION['student_sdt']    = $student['sdt'];
                    header('Location: student_dashboard.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng Nhập Học Sinh – NP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --navy: #0a1628;
            --navy2: #0f2040;
            --blue: #1560bd;
            --blue2: #1a75e8;
            --sky: #5ba4f5;
            --gold: #f0a500;
            --white: #ffffff;
            --text: #1e2d45;
            --muted: #6b7c96;
            --surface: rgba(255, 255, 255, .92);
            --err: #c0392b;
            --err-bg: #fff0ef;
            --warn: #d97706;
            --warn-bg: #fffbeb;
        }

        html,
        body {
            min-height: 100%;
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        body {
            background: linear-gradient(145deg, var(--navy) 0%, var(--navy2) 40%, #112259 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100svh;
            padding: 20px 16px;
            overflow-x: hidden;
            position: relative;
        }

        /* ── Nền hình học ─────────────────────────────────── */
        .bg-geo {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .geo-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(91, 164, 245, .12);
        }

        .geo-circle:nth-child(1) {
            width: 500px;
            height: 500px;
            top: -180px;
            right: -120px;
        }

        .geo-circle:nth-child(2) {
            width: 340px;
            height: 340px;
            bottom: -100px;
            left: -80px;
        }

        .geo-circle:nth-child(3) {
            width: 220px;
            height: 220px;
            top: 40%;
            left: 5%;
            border-color: rgba(240, 165, 0, .1);
        }

        .bg-dots {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .06) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ── Card ─────────────────────────────────────────── */
        .card {
            position: relative;
            z-index: 10;
            width: min(92vw, 420px);
            background: var(--surface);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 20px;
            padding: 40px 34px 32px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .4), 0 1px 0 rgba(255, 255, 255, .9) inset;
            border: 1.5px solid rgba(255, 255, 255, .75);
            animation: cardIn .55s cubic-bezier(.22, .68, 0, 1.12) both;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(28px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Logo ─────────────────────────────────────────── */
        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .logo-ring {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue2) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(21, 96, 189, .35), 0 0 0 6px rgba(21, 96, 189, .12);
        }

        .logo-ring svg {
            width: 42px;
            height: 26px;
        }

        .logo-name {
            font-size: .7rem;
            font-weight: 800;
            color: var(--text);
            text-align: center;
            line-height: 1.5;
            letter-spacing: .3px;
        }

        .logo-name span {
            color: var(--blue2);
        }

        h1 {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 4px;
            letter-spacing: -.3px;
        }

        .subtitle {
            text-align: center;
            font-size: .74rem;
            color: var(--muted);
            margin-bottom: 22px;
            font-weight: 500;
        }

        /* ── Error box ────────────────────────────────────── */
        .error-box {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: var(--err-bg);
            border: 1.5px solid #fca5a5;
            color: var(--err);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: .79rem;
            font-weight: 600;
            margin-bottom: 18px;
            line-height: 1.5;
            animation: shake .35s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0)
            }

            20% {
                transform: translateX(-6px)
            }

            60% {
                transform: translateX(6px)
            }
        }

        .error-icon {
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Hint box ─────────────────────────────────────── */
        .hint-box {
            background: var(--warn-bg);
            border: 1.5px solid #fde68a;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .76rem;
            color: var(--warn);
            font-weight: 600;
            margin-bottom: 18px;
            line-height: 1.55;
        }

        /* ── Fields ───────────────────────────────────────── */
        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: .74rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: .2px;
        }

        .input-wrap {
            position: relative;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 2px solid #dce6f5;
            border-radius: 11px;
            font-family: inherit;
            font-size: .92rem;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
        }

        input:focus {
            border-color: var(--blue2);
            box-shadow: 0 0 0 3.5px rgba(26, 117, 232, .13);
        }

        input.field-error {
            border-color: var(--err);
            box-shadow: 0 0 0 3px rgba(192, 57, 43, .1);
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            pointer-events: none;
        }

        /* ── Btn ──────────────────────────────────────────── */
        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--blue2) 0%, var(--blue) 100%);
            color: #fff;
            font-family: inherit;
            font-size: .95rem;
            font-weight: 800;
            cursor: pointer;
            letter-spacing: .3px;
            box-shadow: 0 6px 20px rgba(21, 96, 189, .36);
            transition: transform .15s, box-shadow .15s, opacity .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(21, 96, 189, .45);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Divider ──────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #e8eef7;
            margin: 20px 0 14px;
        }

        /* ── Info tip ─────────────────────────────────────── */
        .info-tip {
            font-size: .72rem;
            color: var(--muted);
            text-align: center;
            line-height: 1.6;
            font-weight: 500;
        }

        .info-tip strong {
            color: var(--blue2);
        }

        footer {
            text-align: center;
            margin-top: 1.2rem;
            font-size: .67rem;
            color: rgba(255, 255, 255, .35);
            position: relative;
            z-index: 10;
        }

        @media (max-width:380px) {
            .card {
                padding: 28px 18px 24px;
            }

            h1 {
                font-size: 1.05rem;
            }
        }
    </style>
</head>

<body>

    <div class="bg-geo">
        <div class="geo-circle"></div>
        <div class="geo-circle"></div>
        <div class="geo-circle"></div>
    </div>
    <div class="bg-dots"></div>

    <div class="card">
        <div class="logo-wrap">
            <div class="logo-ring">
                <svg viewBox="0 0 60 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 36V2h5L21 24V2h5v34h-5L7 12v24H2z" fill="white" />
                    <path d="M32 2h13c6.5 0 11 4.2 11 10.5S51.5 23 45 23H37v13h-5V2zm5 5v11h8c3.3 0 5.5-2.1 5.5-5.5S48.3 7 45 7h-8z" fill="white" />
                </svg>
            </div>
            <div class="logo-name">Trung Tâm Giáo Dục Tri Thức <span>NP</span></div>
        </div>

        <h1>Cổng Học Sinh</h1>
        <p class="subtitle">Đăng nhập để xem thông tin điểm danh của bạn</p>

        <?php if ($error): ?>
            <div class="error-box">
                <span class="error-icon">⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="hint-box">
            💡 <strong>Lưu ý:</strong> Họ tên và mã học sinh phải nhập <strong>đúng chính xác</strong>, phân biệt chữ hoa/thường.<br>
            Ví dụ: <strong>NP418</strong> khác với <strong>np418</strong>
        </div>

        <form method="POST" id="loginForm" autocomplete="off">
            <div class="field">
                <label for="ho_ten">Họ và Tên đầy đủ</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input
                        type="text"
                        id="ho_ten"
                        name="ho_ten"
                        placeholder="VD: Nguyễn Văn An"
                        value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>"
                        class="<?= $fieldError === 'ho_ten' || $fieldError === 'both' ? 'field-error' : '' ?>"
                        autocomplete="off"
                        spellcheck="false"
                        required>
                </div>
            </div>

            <div class="field">
                <label for="ma_hs">Mã Học Sinh</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input
                        type="text"
                        id="ma_hs"
                        name="ma_hs"
                        placeholder="VD: NP418"
                        value="<?= htmlspecialchars($_POST['ma_hs'] ?? '') ?>"
                        class="<?= $fieldError === 'ma_hs' || $fieldError === 'both' ? 'field-error' : '' ?>"
                        autocomplete="off"
                        spellcheck="false"
                        required>
                </div>
            </div>

            <button type="submit" class="btn" id="submitBtn">
                <span>🎓</span> Đăng Nhập
            </button>
        </form>

        <hr class="divider">
        <p class="info-tip">
            Chưa có tài khoản hoặc không nhớ mã học sinh?<br>
            Liên hệ <strong>Trung Tâm NP</strong> để được hỗ trợ.
        </p>
    </div>

    <footer>© 2025 Trung Tâm Giáo Dục Tri Thức NP · Lớp Hoa 12H1</footer>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Đang kiểm tra...';
        });
    </script>
</body>

</html>