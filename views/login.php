<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng Nhập – Trung Tâm NP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sky1: #c2e8f8;
            --sky2: #7ec8e8;
            --sky3: #aaddf5;
            --blue: #1a90d9;
            --blue2: #0e6fad;
            --lite: #5dbfee;
            --white: #ffffff;
            --text: #1a3a55;
            --gray: #64748b;
            --error-bg: #fef2f2;
            --error-bdr: #fca5a5;
            --error-txt: #b91c1c;
        }

        html,
        body {
            min-height: 100%;
            height: 100%;
            font-family: 'Nunito', sans-serif;
        }

        body {
            background: linear-gradient(170deg, var(--sky1) 0%, var(--sky2) 45%, var(--sky3) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100svh;
            position: relative;
            overflow-x: hidden;
            padding: 20px 16px;
        }

        /* ── Đám mây nền ──────────────────────────────── */
        .clouds {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, .55);
            border-radius: 50%;
            filter: blur(36px);
        }

        .cloud-1 {
            width: 320px;
            height: 160px;
            top: 3%;
            left: -80px;
        }

        .cloud-2 {
            width: 260px;
            height: 120px;
            top: 10%;
            right: -50px;
        }

        .cloud-3 {
            width: 200px;
            height: 90px;
            bottom: 8%;
            left: 10%;
        }

        .cloud-4 {
            width: 180px;
            height: 80px;
            bottom: 5%;
            right: 5%;
        }

        /* ── Bong bóng ────────────────────────────────── */
        .bubbles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 1;
        }

        .bubble {
            position: absolute;
            bottom: -140px;
            border-radius: 50%;
            background: radial-gradient(circle at 32% 30%,
                    rgba(255, 255, 255, .88),
                    rgba(93, 191, 238, .35) 60%,
                    rgba(26, 144, 217, .18));
            border: 1.8px solid rgba(255, 255, 255, .75);
            box-shadow:
                inset 0 -3px 8px rgba(255, 255, 255, .5),
                0 4px 14px rgba(26, 144, 217, .18);
            animation: floatUp linear infinite;
        }

        @keyframes floatUp {
            0% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: .85;
            }

            50% {
                transform: translateY(-50vh) translateX(12px) scale(.96);
            }

            100% {
                transform: translateY(-115vh) translateX(-8px) scale(.88);
                opacity: 0;
            }
        }

        /* ── Card đăng nhập ───────────────────────────── */
        .card {
            position: relative;
            z-index: 10;
            width: min(90vw, 390px);
            background: rgba(255, 255, 255, .84);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 22px;
            padding: 38px 30px 30px;
            box-shadow:
                0 12px 40px rgba(26, 144, 217, .22),
                0 2px 0 rgba(255, 255, 255, .9) inset;
            border: 1.5px solid rgba(255, 255, 255, .8);
            animation: slideUp .5s cubic-bezier(.22, .68, 0, 1.15) both;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Logo */
        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 9px;
            margin-bottom: 22px;
        }

        .logo-ring {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            border: 2.5px solid var(--blue);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 18px rgba(26, 144, 217, .22);
        }

        .logo-ring svg {
            width: 46px;
            height: 29px;
        }

        .logo-name {
            font-size: .72rem;
            font-weight: 800;
            color: var(--text);
            text-align: center;
            line-height: 1.45;
            letter-spacing: .3px;
        }

        .logo-name span {
            color: var(--blue);
        }

        /* Tiêu đề */
        h1 {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--blue2);
            margin-bottom: 20px;
            letter-spacing: -.2px;
        }

        /* Badge role real-time */
        .role-badge {
            height: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
            transition: height .25s ease, margin-bottom .25s ease;
            margin-bottom: 0;
        }

        .role-badge.show {
            height: 28px;
            margin-bottom: 12px;
            animation: popIn .25s ease both;
        }

        @keyframes popIn {
            from {
                transform: scale(.75);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .role-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .role-badge.admin {
            color: #b45309;
        }

        .role-badge.admin .dot {
            background: #d97706;
        }

        .role-badge.student {
            color: var(--blue2);
        }

        .role-badge.student .dot {
            background: var(--blue);
        }

        /* Thông báo lỗi */
        .error-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--error-bg);
            border: 1.5px solid var(--error-bdr);
            color: var(--error-txt);
            border-radius: 10px;
            padding: 10px 13px;
            font-size: .8rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        /* Fields */
        .field {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: .75rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: .3px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid rgba(26, 144, 217, .22);
            border-radius: 11px;
            font-family: inherit;
            font-size: .93rem;
            color: var(--text);
            background: rgba(255, 255, 255, .92);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3.5px rgba(26, 144, 217, .14);
        }

        /* Ô mật khẩu + toggle */
        .pw-wrap {
            position: relative;
        }

        .pw-wrap input {
            padding-right: 44px;
        }

        .pw-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: .95rem;
            color: var(--lite);
            padding: 4px;
            line-height: 1;
            user-select: none;
        }

        /* Gợi ý mật khẩu */
        .hint {
            font-size: .7rem;
            color: var(--gray);
            margin-top: -9px;
            margin-bottom: 16px;
            padding-left: 2px;
        }

        /* Nút đăng nhập */
        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue2) 100%);
            color: #fff;
            font-family: inherit;
            font-size: .97rem;
            font-weight: 900;
            letter-spacing: .4px;
            cursor: pointer;
            box-shadow: 0 5px 18px rgba(26, 144, 217, .38);
            transition: transform .15s, box-shadow .15s, opacity .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 144, 217, .48);
        }

        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(26, 144, 217, .28);
        }

        .btn:disabled {
            opacity: .65;
            cursor: not-allowed;
            transform: none;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: .67rem;
            color: #94a3b8;
            margin-top: 18px;
        }

        /* ── Responsive ───────────────────────────────── */
        @media (max-width: 380px) {
            .card {
                padding: 28px 18px 24px;
            }

            h1 {
                font-size: 1.1rem;
            }

            .logo-ring {
                width: 62px;
                height: 62px;
            }
        }
    </style>
</head>

<body>

    <!-- Nền mây -->
    <div class="clouds">
        <div class="cloud cloud-1"></div>
        <div class="cloud cloud-2"></div>
        <div class="cloud cloud-3"></div>
        <div class="cloud cloud-4"></div>
    </div>

    <!-- Bong bóng -->
    <div class="bubbles" id="bubbles"></div>

    <!-- Card đăng nhập -->
    <div class="card">

        <!-- Logo NP -->
        <div class="logo-wrap">
            <div class="logo-ring">
                <svg viewBox="0 0 60 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="npGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#1a90d9" />
                            <stop offset="100%" stop-color="#0e6fad" />
                        </linearGradient>
                    </defs>
                    <path d="M2 36V2h5L21 24V2h5v34h-5L7 12v24H2z" fill="url(#npGrad)" />
                    <path d="M32 2h13c6.5 0 11 4.2 11 10.5S51.5 23 45 23H37v13h-5V2zm5 5v11h8c3.3 0 5.5-2.1 5.5-5.5S48.3 7 45 7h-8z" fill="url(#npGrad)" />
                </svg>
            </div>
            <div class="logo-name">
                Trung Tâm Giáo Dục Tri Thức <span>NP</span>
            </div>
        </div>

        <h1>Đăng Nhập</h1>

        <!-- Badge role hiện real-time -->
        <div class="role-badge" id="roleBadge">
            <span class="dot"></span>
            <span id="roleText"></span>
        </div>

        <form method="POST" action="../controllers/AuthController.php" id="loginForm">

            <div class="field">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username"
                    placeholder="Nhập username..."
                    autocomplete="username"
                    required>
            </div>

            <div class="field">
                <label for="password">Mật khẩu</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password"
                        placeholder="Nhập mật khẩu..."
                        autocomplete="current-password"
                        required>
                    <button type="button" class="pw-eye" id="pwEye" title="Hiện/ẩn mật khẩu">👁</button>
                </div>
            </div>

            <p class="hint">💡 Mật khẩu mặc định: <strong>NP</strong> + mã số học sinh</p>

            <button type="submit" class="btn" id="submitBtn">
                <span>🔐</span> Đăng Nhập
            </button>

        </form>

        <p class="footer">© 2025 Trung Tâm Giáo Dục Tri Thức NP</p>
    </div>

    <script>
        /* ── Sinh bong bóng ──────────────────────────────────────────────── */
        (function() {
            var wrap = document.getElementById('bubbles');
            var total = window.innerWidth < 480 ? 14 : 22;
            for (var i = 0; i < total; i++) {
                var b = document.createElement('div');
                b.className = 'bubble';
                var size = 18 + Math.random() * 58;
                var left = Math.random() * 98;
                var delay = Math.random() * 14;
                var dur = 9 + Math.random() * 11;
                b.style.cssText =
                    'width:' + size + 'px;' +
                    'height:' + size + 'px;' +
                    'left:' + left + '%;' +
                    'animation-duration:' + dur + 's;' +
                    'animation-delay: -' + delay + 's;';
                wrap.appendChild(b);
            }
        })();

        /* ── Toggle hiển thị mật khẩu ───────────────────────────────────── */
        document.getElementById('pwEye').addEventListener('click', function() {
            var pw = document.getElementById('password');
            var show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            this.textContent = show ? '🙈' : '👁';
        });

        /* ── Real-time role badge khi gõ username ───────────────────────── */
        var roleTimer = null;
        var badge = document.getElementById('roleBadge');
        var roleText = document.getElementById('roleText');
        var dot = badge.querySelector('.dot');

        document.getElementById('username').addEventListener('input', function() {
            clearTimeout(roleTimer);
            var val = this.value.trim();
            if (!val) {
                badge.className = 'role-badge';
                return;
            }
            roleTimer = setTimeout(function() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '../controllers/AuthController.php?action=get_role&username=' + encodeURIComponent(val), true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.role === 'admin') {
                                badge.className = 'role-badge admin show';
                                roleText.textContent = '🛡 Quản trị viên';
                            } else if (data.role === 'student') {
                                badge.className = 'role-badge student show';
                                roleText.textContent = '🎒 Học sinh';
                            } else {
                                badge.className = 'role-badge';
                            }
                        } catch (e) {
                            badge.className = 'role-badge';
                        }
                    }
                };
                xhr.send();
            }, 420);
        });

        /* ── Vô hiệu hoá nút khi submit ─────────────────────────────────── */
        document.getElementById('loginForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Đang xử lý...';
        });
    </script>
</body>

</html>