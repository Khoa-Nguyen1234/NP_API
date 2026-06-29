<?php
session_start();

require_once __DIR__ . '/../config/db_np4.php';

// Tài khoản master - luôn được phép vào
define('MASTER_NAME',  'Nguyễn Minh Khoa');
define('MASTER_PHONE', '01212495427');

// Nếu đã đăng nhập thì chuyển thẳng vào admin
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone    = trim($_POST['phone']    ?? '');

    // Validate: phone chỉ gồm chữ số
    if (!preg_match('/^\d+$/', $phone)) {
        $error = 'Số điện thoại chỉ được nhập số.';
    }
    // Validate: họ tên phải đúng dạng viết hoa chữ cái đầu mỗi từ (có dấu tiếng Việt)
    elseif (!preg_match('/^[\p{Lu}\p{Lt}][\p{L}]*(?:\s[\p{Lu}\p{Lt}][\p{L}]*)+$/u', $fullname)) {
        $error = 'Họ tên không đúng định dạng (ví dụ: Nguyễn Minh Khoa).';
    } else {
        $isMaster = ($fullname === MASTER_NAME && $phone === MASTER_PHONE);

        if ($isMaster) {
            // Master account - cấp quyền, KHÔNG lưu tên vào session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_fullname']  = '';   // Ẩn danh
            $_SESSION['is_master']       = true;
            header('Location: admin.php');
            exit;
        }

        // Kiểm tra trong database (so sánh chính xác fullname & phone)
        try {
            $db  = Database::getInstance()->getConnection();
            $sql = 'SELECT fullname FROM admin WHERE fullname = :fn AND phone = :ph LIMIT 1';
            $st  = $db->prepare($sql);
            $st->execute([':fn' => $fullname, ':ph' => $phone]);
            $row = $st->fetch();

            if ($row) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_fullname']  = $row['fullname'];
                $_SESSION['is_master']       = false;
                header('Location: admin.php');
                exit;
            } else {
                $error = 'Thông tin đăng nhập không đúng. Vui lòng kiểm tra lại.';
            }
        } catch (Exception $e) {
            $error = 'Lỗi hệ thống. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Đăng nhập – Tri Thức NP Admin</title>
    <link href="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/Screenshot%202026-06-09%20221852-eFYcZs9Oh7Z8uLwh28PTSFXBYsw3v9.png" rel="icon" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Hanken+Grotesk:wght@600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#a2c9ff",
                        "primary-container": "#47a1ff",
                        "on-primary": "#00315b",
                        "surface": "#0b1326",
                        "surface-container": "#171f33",
                        "surface-container-low": "#131b2e",
                        "on-surface": "#dae2fd",
                        "outline-variant": "#404752",
                        "error": "#ffb4ab",
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary-color: #a2c9ff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0b1326;
            color: #dae2fd;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated background blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: floatBlob 12s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: 0;
        }

        .blob-1 {
            width: 420px;
            height: 420px;
            background: #a2c9ff;
            top: -120px;
            left: -100px;
            animation-delay: 0s;
        }

        .blob-2 {
            width: 320px;
            height: 320px;
            background: #47a1ff;
            bottom: -80px;
            right: -60px;
            animation-delay: 3s;
        }

        .blob-3 {
            width: 200px;
            height: 200px;
            background: #93cdfc;
            top: 50%;
            left: 60%;
            animation-delay: 6s;
        }

        @keyframes floatBlob {
            from {
                transform: translate(0, 0) scale(1);
            }

            to {
                transform: translate(30px, 20px) scale(1.08);
            }
        }

        .login-card {
            position: relative;
            z-index: 1;
            background: rgba(23, 31, 51, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(162, 201, 255, 0.12);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(32px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-control-dark {
            background-color: #131b2e;
            border: 1px solid #404752;
            color: #dae2fd;
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control-dark:focus {
            background-color: #131b2e;
            border-color: var(--primary-color);
            color: #dae2fd;
            box-shadow: 0 0 0 3px rgba(162, 201, 255, 0.15);
            outline: none;
        }

        .form-control-dark::placeholder {
            color: #8a919d;
        }

        /* Chỉ cho nhập số cho trường phone */
        #phone {
            letter-spacing: 0.5px;
        }

        .btn-login {
            background: var(--primary-color);
            color: #001c38;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 0.7rem;
            font-size: 1rem;
            width: 100%;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-login:hover {
            background: #d3e4ff;
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-box {
            background: rgba(255, 180, 171, 0.1);
            border: 1px solid rgba(255, 180, 171, 0.35);
            border-radius: 10px;
            padding: 0.65rem 1rem;
            color: #ffb4ab;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-6px);
            }

            40%,
            80% {
                transform: translateX(6px);
            }
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #8a919d;
            font-size: 18px;
            pointer-events: none;
        }

        .input-icon-wrap .form-control-dark {
            padding-left: 2.4rem;
        }

        .label-text {
            font-size: 0.8rem;
            font-weight: 600;
            color: #c0c7d4;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.4rem;
        }

        .logo-wrap img {
            filter: drop-shadow(0 0 12px rgba(162, 201, 255, 0.3));
        }
    </style>
</head>

<body>
    <!-- Background blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="login-card">
        <!-- Logo & Title -->
        <div class="text-center mb-4 logo-wrap">
            <img src="https://sf-static.upanhlaylink.com/img/image_2026062459ce7705445a58722665145a97edb5e5.jpg"
                alt="Logo" style="height: 52px; object-fit: contain;" class="mb-3" />
            <h1 class="fw-bold mb-1" style="font-family:'Hanken Grotesk',sans-serif; font-size:1.5rem; color:#a2c9ff;">
                Tri Thức NP
            </h1>
            <p class="text-secondary small mb-0">Đăng nhập vào trang quản trị</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box mb-4">
                <span class="material-symbols-outlined" style="font-size:18px;">error</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" novalidate>
            <!-- Họ và tên -->
            <div class="mb-3">
                <div class="label-text">Họ và tên</div>
                <div class="input-icon-wrap">
                    <span class="material-symbols-outlined input-icon">person</span>
                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        class="form-control form-control-dark"
                        placeholder="Nguyễn Minh Khoa"
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
                        required
                        autocomplete="off"
                        spellcheck="false" />
                </div>
            </div>

            <!-- Số điện thoại - tối đa 20 ký tự, chú thích ẩn -->
            <div class="mb-4">
                <div class="label-text">Số điện thoại</div>
                <div class="input-icon-wrap">
                    <span class="material-symbols-outlined input-icon">phone</span>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control form-control-dark"
                        placeholder="Nhập số điện thoại"
                        maxlength="20"
                        inputmode="numeric"
                        pattern="\d*"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                        required
                        autocomplete="off" />
                </div>
                <!-- Ghi chú ẩn: tối đa 20 số -->
                <small class="text-secondary" style="font-size:0.75rem; visibility:hidden;">Tối đa 20 số</small>
            </div>

            <button type="submit" class="btn-login">
                <span class="material-symbols-outlined" style="vertical-align:-4px; font-size:18px; margin-right:4px;">lock_open</span>
                Đăng nhập
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chỉ cho nhập số vào ô phone
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 20);
        });
        phoneInput.addEventListener('keydown', function(e) {
            const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter', 'Home', 'End'];
            if (allowed.includes(e.key)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            this.value = (this.value + text.replace(/\D/g, '')).slice(0, 20);
        });
    </script>
</body>

</html>