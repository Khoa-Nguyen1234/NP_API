<?php
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['admin_fullname'] ?? '';
require_once __DIR__ . '/../config/db_np4.php';
$pdo = Database::getInstance()->getConnection();

// ── AJAX endpoint ──────────────────────────────────────────────────
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $act = $_POST['act'] ?? $_GET['act'] ?? '';

    // GET: lấy 1 row để sửa
    if ($act === 'get' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE schedules_id=?");
        $stmt->execute([(int)$_GET['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row ?: ['error' => 'not found']);
        exit;
    }

    // POST: thêm
    if ($act === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            echo json_encode(['ok' => false, 'msg' => 'Tên không được để trống.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO schedules (name,image1,image2,image3,image4) VALUES (?,?,?,?,?)");
        $stmt->execute([
            $name,
            trim($_POST['image1'] ?? '') ?: null,
            trim($_POST['image2'] ?? '') ?: null,
            trim($_POST['image3'] ?? '') ?: null,
            trim($_POST['image4'] ?? '') ?: null,
        ]);
        echo json_encode(['ok' => true, 'msg' => 'Đã thêm lịch thành công.', 'id' => $pdo->lastInsertId()]);
        exit;
    }

    // POST: sửa
    if ($act === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            echo json_encode(['ok' => false, 'msg' => 'Dữ liệu không hợp lệ.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE schedules SET name=?,image1=?,image2=?,image3=?,image4=? WHERE schedules_id=?");
        $stmt->execute([
            $name,
            trim($_POST['image1'] ?? '') ?: null,
            trim($_POST['image2'] ?? '') ?: null,
            trim($_POST['image3'] ?? '') ?: null,
            trim($_POST['image4'] ?? '') ?: null,
            $id,
        ]);
        echo json_encode(['ok' => true, 'msg' => 'Đã cập nhật lịch thành công.']);
        exit;
    }

    // POST: xóa
    if ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'ID không hợp lệ.']);
            exit;
        }
        $pdo->prepare("DELETE FROM schedules WHERE schedules_id=?")->execute([$id]);
        echo json_encode(['ok' => true, 'msg' => 'Đã xóa lịch thành công.']);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Hành động không xác định.']);
    exit;
}

// ── Load danh sách ─────────────────────────────────────────────────
$schedules  = $pdo->query("SELECT * FROM schedules ORDER BY schedules_id ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalCount = count($schedules);
?>
<!DOCTYPE html>
<html class="dark" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Schedules Management - Tri Thức NP</title>
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
                        "outline": "#8a919d",
                        "inverse-surface": "#dae2fd",
                        "secondary-fixed-dim": "#93cdfc",
                        "surface-container": "#171f33",
                        "surface-container-highest": "#2d3449",
                        "on-tertiary-fixed": "#2b1700",
                        "on-error": "#690005",
                        "surface-container-lowest": "#060e20",
                        "inverse-primary": "#0060a9",
                        "inverse-on-surface": "#283044",
                        "tertiary-container": "#e08a00",
                        "surface-container-high": "#222a3d",
                        "primary-container": "#47a1ff",
                        "on-tertiary-fixed-variant": "#673d00",
                        "background": "#0b1326",
                        "primary-fixed-dim": "#a2c9ff",
                        "error-container": "#93000a",
                        "tertiary": "#ffb868",
                        "secondary": "#93cdfc",
                        "on-tertiary": "#482900",
                        "on-tertiary-container": "#4f2d00",
                        "on-surface-variant": "#c0c7d4",
                        "secondary-container": "#00527b",
                        "surface-dim": "#0b1326",
                        "secondary-fixed": "#cbe6ff",
                        "outline-variant": "#404752",
                        "tertiary-fixed": "#ffddbb",
                        "tertiary-fixed-dim": "#ffb868",
                        "surface-tint": "#a2c9ff",
                        "error": "#ffb4ab",
                        "on-secondary-fixed": "#001e30",
                        "on-primary": "#00315b",
                        "on-primary-fixed-variant": "#004881",
                        "on-secondary": "#00344f",
                        "primary-fixed": "#d3e4ff",
                        "on-surface": "#dae2fd",
                        "surface-container-low": "#131b2e",
                        "surface-variant": "#2d3449",
                        "surface": "#0b1326",
                        "on-background": "#dae2fd",
                        "on-primary-container": "#003663",
                        "on-error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#004b71",
                        "on-secondary-container": "#8bc5f4",
                        "on-primary-fixed": "#001c38",
                        "surface-bright": "#31394d",
                        "primary": "#a2c9ff"
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary-color: #a2c9ff;
            --primary-dark: #004881;
            --sidebar-bg: #171f33;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdf8f6;
            color: #1c1b1a;
            overflow-x: hidden;
            transition: background-color .3s, color .3s;
        }

        html.dark body {
            background-color: #0b1326;
            color: #dae2fd;
        }

        h1,
        h2,
        h3 {
            font-family: 'Hanken Grotesk', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* Sidebar */
        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            transition: all .3s cubic-bezier(.4, 0, .2, 1);
            background: var(--sidebar-bg);
            border-right: 1px solid rgba(255, 255, 255, .05);
        }

        html:not(.dark) #sidebar {
            background: #1565c0;
        }

        #sidebar.collapsed {
            margin-left: -260px;
        }

        #main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            transition: all .3s cubic-bezier(.4, 0, .2, 1);
        }

        #main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        @media(max-width:991.98px) {
            #sidebar {
                margin-left: -260px;
            }

            #sidebar.show {
                margin-left: 0;
            }

            #main-content {
                margin-left: 0;
                width: 100%;
            }
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: #c0c7d4;
            text-decoration: none;
            transition: all .25s;
            border-left: 4px solid transparent;
        }

        .nav-link-custom:hover {
            color: #dae2fd;
            background: rgba(255, 255, 255, .05);
            padding-left: 28px;
        }

        .nav-link-custom.active {
            color: #fff;
            background: rgba(162, 201, 255, .15);
            border-left-color: var(--primary-color);
            font-weight: 700;
        }

        /* Cards */
        .glass-card {
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(10px);
            border: 1px solid #E9ECEF;
            border-radius: 12px;
            padding: 24px;
            transition: transform .3s, box-shadow .3s, background .3s, border-color .3s;
        }

        html.dark .glass-card {
            background: #171f33;
            border-color: #2d3449;
            color: #dae2fd;
        }

        /* Buttons */
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #001c38;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-primary-custom:hover {
            background-color: #d3e4ff;
            border-color: #d3e4ff;
            color: #001c38;
            transform: translateY(-1px);
        }

        .btn-outline-primary-custom {
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            transition: all .2s;
        }

        .btn-outline-primary-custom:hover {
            background-color: var(--primary-color);
            color: #001c38;
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .animate-entrance {
            animation: fadeInUp .6s cubic-bezier(.16, 1, .3, 1) both;
        }

        .stagger-1 {
            animation-delay: .1s
        }

        .stagger-2 {
            animation-delay: .2s
        }

        .stagger-3 {
            animation-delay: .3s
        }

        /* Dark overrides */
        html.dark .form-control {
            background-color: #131b2e;
            border-color: #2d3449;
            color: #dae2fd;
        }

        html.dark .form-control:focus {
            background-color: #0b1326;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(162, 201, 255, .15);
        }

        html.dark .form-label {
            color: #c0c7d4;
        }

        html.dark .table thead {
            background-color: #222a3d !important;
            color: #a2c9ff !important;
        }

        html.dark .table {
            color: #dae2fd;
            border-color: #2d3449;
        }

        html.dark .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, .02);
        }

        html.dark header {
            background-color: #0b1326 !important;
            border-color: #2d3449 !important;
        }

        html.dark .btn-light {
            background-color: #171f33;
            border-color: #2d3449;
            color: #dae2fd;
        }

        html.dark .btn-light:hover {
            background-color: #2d3449;
            color: #fff;
        }

        html.dark .text-secondary {
            color: #8a919d !important;
        }

        html.dark .bg-light {
            background-color: #131b2e !important;
        }

        html.dark .border-bottom {
            border-color: #2d3449 !important;
        }

        /* Modal dark */
        html.dark .modal-content {
            background-color: #171f33;
            border-color: #2d3449;
            color: #dae2fd;
        }

        html.dark .modal-header {
            border-color: #2d3449;
        }

        html.dark .modal-footer {
            border-color: #2d3449;
        }

        html.dark .modal-backdrop {
            background-color: #000;
        }

        /* Image preview */
        .schedule-img-preview {
            width: 80px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
            background: #eee;
            border: 1px solid rgba(0, 0, 0, .05);
        }

        html.dark .schedule-img-preview {
            background: #2d3449;
            border-color: #404752;
        }

        .img-preview-box {
            width: 100%;
            height: 110px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #2d3449;
            margin-top: 6px;
            display: none;
        }

        /* Toast */
        #toast-wrap {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast-msg {
            min-width: 260px;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .25);
            animation: fadeInUp .3s both;
        }

        .toast-msg.success {
            background: #1a3a2a;
            border: 1px solid #2d6a4f;
            color: #b7e4c7;
        }

        .toast-msg.error {
            background: #3a1a1a;
            border: 1px solid #93000a;
            color: #ffdad6;
        }

        @keyframes notificationPulse {
            0% {
                transform: scale(1);
                opacity: 1
            }

            50% {
                transform: scale(1.8);
                opacity: 0
            }

            100% {
                transform: scale(1);
                opacity: 0
            }
        }

        .notify-badge::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            border-radius: inherit;
            animation: notificationPulse 2s infinite;
        }

        /* Loading spinner on buttons */
        .btn-loading {
            pointer-events: none;
            opacity: .7;
        }
    </style>
</head>

<body class="dark:bg-background dark:text-on-background">

    <!-- Toast container -->
    <div id="toast-wrap"></div>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="text-white d-flex flex-column py-4" id="sidebar">
        <div class="px-4 mb-5 d-flex align-items-center gap-3">
            <img alt="Tri Thức NP Logo" class="rounded bg-white p-1" id="mainLogo"
                src="https://sf-static.upanhlaylink.com/img/image_2026062459ce7705445a58722665145a97edb5e5.jpg"
                style="width:40px;height:40px;object-fit:contain;" />
            <div>
                <h2 class="h5 mb-0 fw-bold text-white">Tri Thức NP</h2>
                <small class="text-white-50">Admin Portal</small>
            </div>
        </div>
        <nav class="flex-grow-1">
            <a class="nav-link-custom" href="admin.php"><span class="material-symbols-outlined">dashboard</span><span>Dashboard</span></a>
            <a class="nav-link-custom" href="#"><span class="material-symbols-outlined">school</span><span>Students</span></a>
            <a class="nav-link-custom" href="#"><span class="material-symbols-outlined">book</span><span>Courses</span></a>
            <a class="nav-link-custom active" href="schedules1.php"><span class="material-symbols-outlined">calendar_month</span><span>Schedule</span></a>
            <a class="nav-link-custom" href="#"><span class="material-symbols-outlined">how_to_reg</span><span>Attendance</span></a>
            <a class="nav-link-custom" href="#"><span class="material-symbols-outlined">quiz</span><span>Exams</span></a>
        </nav>
        <div class="px-2 mt-auto">
            <a class="nav-link-custom" href="#"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link-custom text-white-50" href="adminout.php"><span class="material-symbols-outlined">logout</span><span>Logout</span></a>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main id="main-content">
        <!-- Header -->
        <header class="bg-white border-bottom sticky-top py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border d-flex align-items-center p-2" id="toggleSidebar">
                    <span class="material-symbols-outlined" style="color:var(--primary-color)">menu</span>
                </button>
                <div class="d-none d-md-flex position-relative align-items-center">
                    <span class="material-symbols-outlined position-absolute ms-3 text-secondary">search</span>
                    <input id="searchInput" class="form-control ps-5 rounded-pill border-light-subtle shadow-sm" placeholder="Tìm kiếm lịch..." style="width:300px;" type="text" />
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light rounded-circle" id="themeToggle">
                    <span class="material-symbols-outlined" id="themeIcon" style="color:var(--primary-color)">light_mode</span>
                </button>
                <button class="btn btn-light rounded-circle position-relative">
                    <span class="material-symbols-outlined" style="color:var(--primary-color)">notifications</span>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle notify-badge"></span>
                </button>
                <div class="vr mx-2 d-none d-sm-block"></div>
                <div class="d-flex align-items-center gap-2">
                    <div class="text-end d-none d-sm-block">
                        <p class="mb-0 fw-bold small"><?= htmlspecialchars($adminName) ?></p>
                        <p class="mb-0 text-secondary" style="font-size:10px;">SYSTEM SUPER</p>
                    </div>
                    <img alt="Profile" class="rounded-circle border border-2 border-primary-container" id="userAvatar"
                        src="https://sf-static.upanhlaylink.com/img/image_202606138a35a649241c443bd9508782adfbf1ba.jpg"
                        style="width:38px;height:38px;object-fit:cover;" />
                    <a href="adminout.php" class="btn btn-light rounded-circle" title="Đăng xuất">
                        <span class="material-symbols-outlined" style="color:#ffb4ab;font-size:20px;vertical-align:middle;">logout</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Body -->
        <div class="container-fluid p-4">

            <!-- Title -->
            <div class="row align-items-end mb-4 gy-3 animate-entrance stagger-1">
                <div class="col-lg-8">
                    <h1 class="fw-bold mb-1 dark:text-primary">Schedules Management</h1>
                    <p class="text-secondary mb-0">Quản lý lịch học và ảnh thời khóa biểu của trung tâm.</p>
                </div>
                <div class="col-lg-4 d-flex justify-content-lg-end gap-2">
                    <button class="btn btn-primary-custom d-flex align-items-center gap-2" onclick="openAddModal()">
                        <span class="material-symbols-outlined fs-5">add</span> Thêm Lịch
                    </button>
                </div>
            </div>

            <!-- Table card -->
            <div class="glass-card shadow-sm p-0 overflow-hidden animate-entrance stagger-2">
                <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h3 class="h5 fw-bold mb-1 dark:text-primary">Tất cả lịch học</h3>
                        <p class="small text-secondary mb-0">Tổng <span id="countLabel"><?= $totalCount ?></span> lịch đang có.</p>
                    </div>
                    <button class="btn btn-light border btn-sm p-2" onclick="location.reload()" title="Làm mới">
                        <span class="material-symbols-outlined" style="color:var(--primary-color)">refresh</span>
                    </button>
                </div>

                <?php if (empty($schedules)): ?>
                    <div class="p-5 text-center text-secondary" id="emptyState">
                        <span class="material-symbols-outlined fs-1 d-block mb-2">calendar_month</span>
                        Chưa có lịch nào. <button class="btn btn-link p-0" onclick="openAddModal()">Thêm lịch đầu tiên</button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive" id="tableWrap" <?= empty($schedules) ? 'style="display:none"' : '' ?>>
                    <table class="table table-hover align-middle mb-0" id="mainTable">
                        <thead class="text-uppercase fw-bold" style="font-size:.75rem;">
                            <tr>
                                <th class="px-4 py-3 border-0" style="width:50px;">#</th>
                                <th class="py-3 border-0">Tên lịch</th>
                                <th class="py-3 border-0">Ảnh 1</th>
                                <th class="py-3 border-0">Ảnh 2</th>
                                <th class="py-3 border-0">Ảnh 3</th>
                                <th class="py-3 border-0">Ảnh 4</th>
                                <th class="px-4 py-3 border-0 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="small">
                            <?php foreach ($schedules as $i => $row): ?>
                                <?= renderRow($row, $i) ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center" id="tableFooter" <?= empty($schedules) ? 'style="display:none"' : '' ?>>
                    <small class="text-secondary">Hiển thị <span id="showingCount"><?= $totalCount ?></span> lịch</small>
                </div>
            </div>
        </div>
    </main>

    <!-- ═══ MODAL THÊM ═══ -->
    <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_circle</span> Thêm lịch mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên lịch <span class="text-danger">*</span></label>
                        <input type="text" id="add_name" class="form-control" placeholder="VD: Toán 12T1 Ca A21 – Thứ 2&4&6" />
                        <div class="text-danger small mt-1" id="add_name_err" style="display:none;">Tên không được để trống.</div>
                    </div>
                    <?php foreach (['1' => 'Ảnh 1', '2' => 'Ảnh 2', '3' => 'Ảnh 3', '4' => 'Ảnh 4'] as $n => $lbl): ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?= $lbl ?> <span class="text-secondary small">(tùy chọn – dán URL)</span></label>
                            <input type="url" id="add_image<?= $n ?>" class="form-control modal-img-input" data-target="add_prev<?= $n ?>" placeholder="https://..." />
                            <img id="add_prev<?= $n ?>" class="img-preview-box" alt="preview" />
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer border-top">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-primary-custom px-4" id="btnAdd" onclick="submitAdd()">
                        <span class="material-symbols-outlined fs-5 align-middle me-1">save</span> Lưu lịch
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ MODAL SỬA ═══ -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="color:var(--primary-color)">edit</span> Sửa lịch
                        <span class="badge text-secondary bg-light ms-1" id="editIdBadge" style="font-size:.7rem;"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id" />
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên lịch <span class="text-danger">*</span></label>
                        <input type="text" id="edit_name" class="form-control" />
                        <div class="text-danger small mt-1" id="edit_name_err" style="display:none;">Tên không được để trống.</div>
                    </div>
                    <?php foreach (['1' => 'Ảnh 1', '2' => 'Ảnh 2', '3' => 'Ảnh 3', '4' => 'Ảnh 4'] as $n => $lbl): ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?= $lbl ?> <span class="text-secondary small">(tùy chọn – dán URL)</span></label>
                            <input type="url" id="edit_image<?= $n ?>" class="form-control modal-img-input" data-target="edit_prev<?= $n ?>" placeholder="https://..." />
                            <img id="edit_prev<?= $n ?>" class="img-preview-box" alt="preview" />
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer border-top">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-primary-custom px-4" id="btnEdit" onclick="submitEdit()">
                        <span class="material-symbols-outlined fs-5 align-middle me-1">save</span> Cập nhật
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ MODAL XÓA ═══ -->
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-danger">
                        <span class="material-symbols-outlined">delete</span> Xóa lịch
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-start gap-3 p-3 rounded mb-3" style="background:rgba(255,180,171,.1);border:1px solid rgba(147,0,10,.4);">
                        <span class="material-symbols-outlined text-danger fs-2 mt-1">warning</span>
                        <div>
                            <p class="fw-bold mb-1" id="del_name_display"></p>
                            <p class="text-secondary small mb-0" id="del_id_display"></p>
                        </div>
                    </div>
                    <div id="del_imgs" class="d-flex gap-2 flex-wrap mb-1"></div>
                    <input type="hidden" id="del_id" />
                    <p class="text-secondary small mt-3 mb-0">Hành động này <strong>không thể hoàn tác</strong>. Bạn có chắc chắn muốn xóa?</p>
                </div>
                <div class="modal-footer border-top">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger px-4" id="btnDelete" onclick="submitDelete()">
                        <span class="material-symbols-outlined fs-5 align-middle me-1">delete</span> Xác nhận xóa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ── Dữ liệu PHP → JS ── */
        const SELF = 'schedules1.php';

        /* ── Toast ── */
        function showToast(msg, type = 'success') {
            const wrap = document.getElementById('toast-wrap');
            const el = document.createElement('div');
            el.className = `toast-msg ${type}`;
            const icon = type === 'success' ? 'check_circle' : 'error';
            el.innerHTML = `<span class="material-symbols-outlined">${icon}</span><span>${msg}</span>`;
            wrap.appendChild(el);
            setTimeout(() => el.style.opacity = '0', 3200);
            setTimeout(() => el.remove(), 3600);
        }

        /* ── Sidebar / Theme ── */
        const sidebar = document.getElementById('sidebar'),
            mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('toggleSidebar'),
            themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon'),
            mainLogo = document.getElementById('mainLogo');
        const userAvatar = document.getElementById('userAvatar'),
            html = document.documentElement;
        const LOGO_DARK = 'https://sf-static.upanhlaylink.com/img/image_2026062459ce7705445a58722665145a97edb5e5.jpg';
        const LOGO_LIGHT = 'https://sf-static.upanhlaylink.com/img/image_202606138a35a649241c443bd9508782adfbf1ba.jpg';
        const updateVisuals = d => {
            mainLogo.src = d ? LOGO_DARK : LOGO_LIGHT;
            userAvatar.src = d ? LOGO_DARK : LOGO_LIGHT;
        };
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth >= 992) {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            } else {
                sidebar.classList.toggle('show');
            }
        });
        themeToggle.addEventListener('click', () => {
            const d = html.classList.contains('dark');
            html.classList.toggle('dark', !d);
            html.classList.toggle('light', d);
            themeIcon.textContent = d ? 'dark_mode' : 'light_mode';
            localStorage.setItem('theme', d ? 'light' : 'dark');
            updateVisuals(!d);
        });
        const sv = localStorage.getItem('theme') || 'dark';
        if (sv === 'dark') {
            html.classList.add('dark');
            html.classList.remove('light');
            themeIcon.textContent = 'light_mode';
            updateVisuals(true);
        } else {
            html.classList.remove('dark');
            html.classList.add('light');
            themeIcon.textContent = 'dark_mode';
            updateVisuals(false);
        }
        document.addEventListener('click', e => {
            if (window.innerWidth < 992 && !sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) sidebar.classList.remove('show');
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) sidebar.classList.remove('show');
        });

        /* ── Live preview ảnh trong modal ── */
        document.querySelectorAll('.modal-img-input').forEach(inp => {
            const img = document.getElementById(inp.dataset.target);
            if (!img) return;
            inp.addEventListener('input', () => {
                const url = inp.value.trim();
                if (url) {
                    img.src = url;
                    img.style.display = 'block';
                    img.onerror = () => {
                        img.style.display = 'none';
                    };
                    img.onload = () => {
                        img.style.display = 'block';
                    };
                } else {
                    img.style.display = 'none';
                }
            });
        });

        /* ── Search filter ── */
        document.getElementById('searchInput').addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');
            let visible = 0;
            rows.forEach(r => {
                const name = r.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const show = name.includes(q);
                r.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            document.getElementById('showingCount').textContent = visible;
        });

        /* ══════════════════════════════════════════
           CRUD helpers
        ══════════════════════════════════════════ */

        /* ── Render một hàng HTML ── */
        function makeRowHtml(row, idx) {
            const imgs = ['image1', 'image2', 'image3', 'image4'].map(k => {
                if (row[k]) return `<img src="${escHtml(row[k])}" class="schedule-img-preview shadow-sm" onerror="this.style.opacity=.3" />`;
                return `<div class="schedule-img-preview d-flex align-items-center justify-content-center text-secondary bg-light" style="font-size:10px;font-style:italic;">Không có</div>`;
            });
            const delay = (0.3 + (idx % 20) * 0.03).toFixed(2);
            return `<tr data-id="${row.schedules_id}" class="animate-entrance" style="animation-delay:${delay}s">
        <td class="px-4"><span class="text-secondary" style="font-size:11px;">#${row.schedules_id}</span></td>
        <td class="px-2"><p class="mb-0 fw-bold">${escHtml(row.name)}</p></td>
        <td>${imgs[0]}</td><td>${imgs[1]}</td><td>${imgs[2]}</td><td>${imgs[3]}</td>
        <td class="px-4 text-end">
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center"
                        style="width:32px;height:32px;" title="Sửa"
                        onclick="openEditModal(${row.schedules_id})">
                    <span class="material-symbols-outlined fs-6 text-primary">edit</span>
                </button>
                <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center"
                        style="width:32px;height:32px;" title="Xóa"
                        onclick="openDeleteModal(${row.schedules_id}, '${escJs(row.name)}', '${escJs(row.image1||'')}', '${escJs(row.image2||'')}', '${escJs(row.image3||'')}', '${escJs(row.image4||'')}')">
                    <span class="material-symbols-outlined fs-6 text-danger">delete</span>
                </button>
            </div>
        </td>
    </tr>`;
        }

        function escHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function escJs(s) {
            return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

        function updateCount() {
            const total = document.querySelectorAll('#tableBody tr').length;
            document.getElementById('countLabel').textContent = total;
            document.getElementById('showingCount').textContent = total;
            const empty = document.getElementById('emptyState');
            if (empty) empty.style.display = total ? 'none' : 'block';
            document.getElementById('tableWrap').style.display = total ? '' : 'none';
            document.getElementById('tableFooter').style.display = total ? '' : 'none';
        }

        async function ajaxPost(data) {
            const fd = new FormData();
            for (const k in data) fd.append(k, data[k]);
            const res = await fetch(SELF, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            });
            return res.json();
        }

        /* ═══════════ THÊM ═══════════ */
        const mAdd = new bootstrap.Modal(document.getElementById('modalAdd'));

        function openAddModal() {
            document.getElementById('add_name').value = '';
            ['1', '2', '3', '4'].forEach(n => {
                document.getElementById('add_image' + n).value = '';
                const p = document.getElementById('add_prev' + n);
                p.src = '';
                p.style.display = 'none';
            });
            document.getElementById('add_name_err').style.display = 'none';
            mAdd.show();
            setTimeout(() => document.getElementById('add_name').focus(), 400);
        }

        async function submitAdd() {
            const name = document.getElementById('add_name').value.trim();
            const err = document.getElementById('add_name_err');
            if (!name) {
                err.style.display = '';
                return;
            }
            err.style.display = 'none';
            const btn = document.getElementById('btnAdd');
            btn.classList.add('btn-loading');
            btn.textContent = 'Đang lưu...';

            const res = await ajaxPost({
                act: 'add',
                name,
                image1: document.getElementById('add_image1').value.trim(),
                image2: document.getElementById('add_image2').value.trim(),
                image3: document.getElementById('add_image3').value.trim(),
                image4: document.getElementById('add_image4').value.trim(),
            });

            btn.classList.remove('btn-loading');
            btn.innerHTML = '<span class="material-symbols-outlined fs-5 align-middle me-1">save</span> Lưu lịch';

            if (res.ok) {
                mAdd.hide();
                showToast(res.msg, 'success');
                // Append row
                const newRow = {
                    schedules_id: res.id,
                    name,
                    image1: document.getElementById('add_image1').value.trim() || null,
                    image2: document.getElementById('add_image2').value.trim() || null,
                    image3: document.getElementById('add_image3').value.trim() || null,
                    image4: document.getElementById('add_image4').value.trim() || null,
                };
                const tbody = document.getElementById('tableBody');
                tbody.insertAdjacentHTML('beforeend', makeRowHtml(newRow, tbody.rows.length));
                updateCount();
            } else {
                showToast(res.msg, 'error');
            }
        }

        /* ═══════════ SỬA ═══════════ */
        const mEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
        async function openEditModal(id) {
            document.getElementById('edit_name_err').style.display = 'none';
            // Reset previews
            ['1', '2', '3', '4'].forEach(n => {
                const p = document.getElementById('edit_prev' + n);
                p.src = '';
                p.style.display = 'none';
            });

            const res = await fetch(`${SELF}?act=get&id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const row = await res.json();
            if (row.error) {
                showToast('Không tìm thấy lịch.', 'error');
                return;
            }

            document.getElementById('edit_id').value = row.schedules_id;
            document.getElementById('edit_name').value = row.name;
            document.getElementById('editIdBadge').textContent = '#' + row.schedules_id;

            ['1', '2', '3', '4'].forEach(n => {
                const url = row['image' + n] || '';
                const inp = document.getElementById('edit_image' + n);
                const img = document.getElementById('edit_prev' + n);
                inp.value = url;
                if (url) {
                    img.src = url;
                    img.style.display = 'block';
                    img.onerror = () => {
                        img.style.display = 'none';
                    };
                }
            });

            mEdit.show();
            setTimeout(() => document.getElementById('edit_name').focus(), 400);
        }

        async function submitEdit() {
            const id = document.getElementById('edit_id').value;
            const name = document.getElementById('edit_name').value.trim();
            const err = document.getElementById('edit_name_err');
            if (!name) {
                err.style.display = '';
                return;
            }
            err.style.display = 'none';
            const btn = document.getElementById('btnEdit');
            btn.classList.add('btn-loading');
            btn.textContent = 'Đang lưu...';

            const res = await ajaxPost({
                act: 'edit',
                id,
                name,
                image1: document.getElementById('edit_image1').value.trim(),
                image2: document.getElementById('edit_image2').value.trim(),
                image3: document.getElementById('edit_image3').value.trim(),
                image4: document.getElementById('edit_image4').value.trim(),
            });

            btn.classList.remove('btn-loading');
            btn.innerHTML = '<span class="material-symbols-outlined fs-5 align-middle me-1">save</span> Cập nhật';

            if (res.ok) {
                mEdit.hide();
                showToast(res.msg, 'success');
                // Cập nhật row trong bảng
                const tr = document.querySelector(`#tableBody tr[data-id="${id}"]`);
                if (tr) {
                    const updRow = {
                        schedules_id: id,
                        name,
                        image1: document.getElementById('edit_image1').value.trim() || null,
                        image2: document.getElementById('edit_image2').value.trim() || null,
                        image3: document.getElementById('edit_image3').value.trim() || null,
                        image4: document.getElementById('edit_image4').value.trim() || null,
                    };
                    const idx = [...document.querySelectorAll('#tableBody tr')].indexOf(tr);
                    tr.outerHTML = makeRowHtml(updRow, idx);
                }
            } else {
                showToast(res.msg, 'error');
            }
        }

        /* ═══════════ XÓA ═══════════ */
        const mDel = new bootstrap.Modal(document.getElementById('modalDelete'));

        function openDeleteModal(id, name, img1, img2, img3, img4) {
            document.getElementById('del_id').value = id;
            document.getElementById('del_name_display').textContent = name;
            document.getElementById('del_id_display').textContent = 'ID: #' + id;

            const imgWrap = document.getElementById('del_imgs');
            imgWrap.innerHTML = '';
            [img1, img2, img3, img4].filter(Boolean).forEach(url => {
                const img = document.createElement('img');
                img.src = url;
                img.className = 'schedule-img-preview shadow-sm';
                img.style.width = '100px';
                img.style.height = '56px';
                img.onerror = () => img.style.opacity = '.3';
                imgWrap.appendChild(img);
            });

            mDel.show();
        }

        async function submitDelete() {
            const id = document.getElementById('del_id').value;
            const btn = document.getElementById('btnDelete');
            btn.classList.add('btn-loading');
            btn.textContent = 'Đang xóa...';

            const res = await ajaxPost({
                act: 'delete',
                id
            });

            btn.classList.remove('btn-loading');
            btn.innerHTML = '<span class="material-symbols-outlined fs-5 align-middle me-1">delete</span> Xác nhận xóa';

            if (res.ok) {
                mDel.hide();
                showToast(res.msg, 'success');
                const tr = document.querySelector(`#tableBody tr[data-id="${id}"]`);
                if (tr) tr.remove();
                updateCount();
            } else {
                showToast(res.msg, 'error');
            }
        }
    </script>
</body>

</html>
<?php
// Helper render row (dùng cho PHP render lần đầu)
function renderRow(array $row, int $i): string
{
    $delay = number_format(0.3 + ($i % 20) * 0.03, 2);
    $imgCols = '';
    foreach (['image1', 'image2', 'image3', 'image4'] as $k) {
        if ($row[$k]) {
            $imgCols .= '<td><img src="' . htmlspecialchars($row[$k]) . '" class="schedule-img-preview shadow-sm" onerror="this.style.opacity=.3" /></td>';
        } else {
            $imgCols .= '<td><div class="schedule-img-preview d-flex align-items-center justify-content-center text-secondary bg-light" style="font-size:10px;font-style:italic;">Không có</div></td>';
        }
    }
    $n    = htmlspecialchars($row['name']);
    $id   = (int)$row['schedules_id'];
    $i1   = addslashes($row['image1'] ?? '');
    $i2   = addslashes($row['image2'] ?? '');
    $i3   = addslashes($row['image3'] ?? '');
    $i4   = addslashes($row['image4'] ?? '');
    $name = addslashes($row['name']);
    return <<<HTML
<tr data-id="$id" class="animate-entrance" style="animation-delay:{$delay}s">
    <td class="px-4"><span class="text-secondary" style="font-size:11px;">#{$id}</span></td>
    <td class="px-2"><p class="mb-0 fw-bold">{$n}</p></td>
    {$imgCols}
    <td class="px-4 text-end">
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center"
                    style="width:32px;height:32px;" title="Sửa"
                    onclick="openEditModal({$id})">
                <span class="material-symbols-outlined fs-6 text-primary">edit</span>
            </button>
            <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center"
                    style="width:32px;height:32px;" title="Xóa"
                    onclick="openDeleteModal({$id}, '{$name}', '{$i1}', '{$i2}', '{$i3}', '{$i4}')">
                <span class="material-symbols-outlined fs-6 text-danger">delete</span>
            </button>
        </div>
    </td>
</tr>
HTML;
}
?>