<?php
session_start();

// Chưa đăng nhập → về trang login
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Lấy tên hiển thị (master account để trống)
$adminName = $_SESSION['admin_fullname'] ?? '';
// ── Kết nối DB & lấy tổng số học sinh ──────────────────────────
require_once __DIR__ . '/../config/db_np4.php';
$pdo = Database::getInstance()->getConnection();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
?>
<!DOCTYPE html>

<html class="dark" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Tri Thức NP - Admin</title>
    <link href="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/Screenshot%202026-06-09%20221852-eFYcZs9Oh7Z8uLwh28PTSFXBYsw3v9.png" rel="icon" />
    <!-- Google Fonts: Inter & Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Hanken+Grotesk:wght@600;700;800&amp;display=swap" rel="stylesheet" />
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Tailwind CSS (for utility classes and custom config) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
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
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
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
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        html.dark body {
            background-color: #0b1326;
            color: #dae2fd;
        }

        h1,
        h2,
        h3,
        .font-headline {
            font-family: 'Hanken Grotesk', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--sidebar-bg);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        @media (max-width: 991.98px) {
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
            transition: all 0.25s ease;
            border-left: 4px solid transparent;
        }

        .nav-link-custom:hover {
            color: #dae2fd;
            background: rgba(255, 255, 255, 0.05);
            padding-left: 28px;
        }

        .nav-link-custom.active {
            color: #ffffff;
            background: rgba(162, 201, 255, 0.15);
            border-left-color: var(--primary-color);
            font-weight: 700;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid #E9ECEF;
            border-radius: 12px;
            padding: 24px;
            height: 100%;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, background 0.3s ease, border-color 0.3s ease;
        }

        html.dark .glass-card {
            background: #171f33;
            border-color: #2d3449;
            color: #dae2fd;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-bottom: 16px;
            transition: transform 0.3s ease;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #001c38;
            font-weight: 600;
            transition: all 0.2s ease;
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
            transition: all 0.2s ease;
        }

        .btn-outline-primary-custom:hover {
            background-color: var(--primary-color);
            color: #001c38;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-entrance {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .stagger-1 {
            animation-delay: 0.1s;
        }

        .stagger-2 {
            animation-delay: 0.2s;
        }

        .stagger-3 {
            animation-delay: 0.3s;
        }

        .stagger-4 {
            animation-delay: 0.4s;
        }

        .stagger-5 {
            animation-delay: 0.5s;
        }

        .chart-line {
            stroke-dasharray: 2000;
            animation: drawLine 2s ease-out forwards;
        }

        @keyframes drawLine {
            from {
                stroke-dashoffset: 2000;
                opacity: 0;
            }

            to {
                stroke-dashoffset: 0;
                opacity: 1;
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

        @keyframes notificationPulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.5);
                opacity: 0.5;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        html.dark .form-control {
            background-color: #131b2e;
            border-color: #2d3449;
            color: #dae2fd;
        }

        html.dark .form-control:focus {
            background-color: #0b1326;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(162, 201, 255, 0.15);
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
            background-color: rgba(255, 255, 255, 0.02);
            color: #ffffff;
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
            color: #ffffff;
        }

        html.dark .text-secondary {
            color: #8a919d !important;
        }

        html.dark .bg-light {
            background-color: #131b2e !important;
        }
    </style>
</head>

<body class="dark:bg-background dark:text-on-background">
    <!-- Sidebar -->
    <aside class="text-white d-flex flex-column py-4" id="sidebar">
        <div class="px-4 mb-5 d-flex align-items-center gap-3">
            <img alt="Tri Thức NP Logo" class="rounded bg-white p-1" id="mainLogo" src="https://sf-static.upanhlaylink.com/img/image_202606138a35a649241c443bd9508782adfbf1ba.jpg" style="width: 40px; height: 40px; object-fit: contain;" />
            <div>
                <h2 class="h5 mb-0 fw-bold text-white dark:text-inverse-surface">Tri Thức NP</h2>
                <small class="text-white-50 dark:text-outline">Admin Portal</small>
            </div>
        </div>
        <nav class="flex-grow-1">
            <a class="nav-link-custom active" href="#">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a class="nav-link-custom" href="#">
                <span class="material-symbols-outlined">school</span>
                <span>Students</span>
            </a>
            <a class="nav-link-custom" href="#">
                <span class="material-symbols-outlined">book</span>
                <span>Courses</span>
            </a>
            <a class="nav-link-custom" href="schedules1.php">
                <span class="material-symbols-outlined">calendar_month</span>
                <span>Schedule</span>
            </a>
            <a class="nav-link-custom" href="#">
                <span class="material-symbols-outlined">how_to_reg</span>
                <span>Attendance</span>
            </a>
            <a class="nav-link-custom" href="#">
                <span class="material-symbols-outlined">quiz</span>
                <span>Exams</span>
            </a>
        </nav>
        <div class="px-2 mt-auto">
            <a class="nav-link-custom" href="#">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
            <a class="nav-link-custom text-white-50" href="adminout.php">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    <!-- Main Content -->
    <main id="main-content">
        <!-- Header -->
        <header class="bg-white border-bottom sticky-top py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border d-flex align-items-center p-2" id="toggleSidebar">
                    <span class="material-symbols-outlined" style="color: var(--primary-color)">menu</span>
                </button>
                <div class="d-none d-md-flex position-relative align-items-center">
                    <span class="material-symbols-outlined position-absolute ms-3 text-secondary">search</span>
                    <input class="form-control ps-5 rounded-pill border-light-subtle shadow-sm dark:border-outline-variant" placeholder="Search data..." style="width: 300px;" type="text" />
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Toggle Button -->
                <button class="btn btn-light rounded-circle" id="themeToggle" title="Toggle Dark/Light Mode">
                    <span class="material-symbols-outlined" id="themeIcon" style="color: var(--primary-color)">light_mode</span>
                </button>
                <button class="btn btn-light rounded-circle position-relative">
                    <span class="material-symbols-outlined" style="color: var(--primary-color)">notifications</span>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle notify-badge"></span>
                </button>
                <div class="vr mx-2 d-none d-sm-block dark:bg-outline-variant"></div>
                <div class="d-flex align-items-center gap-2">
                    <div class="text-end d-none d-sm-block">
                        <p class="mb-0 fw-bold small dark:text-on-surface"><?= htmlspecialchars($adminName) ?></p>
                        <p class="mb-0 text-secondary" style="font-size: 10px;">SYSTEM SUPER</p>
                    </div>
                    <img alt="Profile" class="rounded-circle border border-2 border-primary-container" id="userAvatar" src="https://sf-static.upanhlaylink.com/img/image_202606138a35a649241c443bd9508782adfbf1ba.jpg" style="width: 38px; height: 38px; object-fit: cover;" />
                    <a href="adminout.php" class="btn btn-light rounded-circle" title="Đăng xuất" style="line-height:1;">
                        <span class="material-symbols-outlined" style="color:#ffb4ab; font-size:20px; vertical-align:middle;">logout</span>
                    </a>
                </div>
            </div>
        </header>
        <!-- Dashboard Body -->
        <div class="container-fluid p-4">
            <!-- Title & Quick Actions -->
            <div class="row align-items-end mb-4 gy-3 animate-entrance stagger-1">
                <div class="col-lg-8">
                    <h1 class="fw-bold mb-1 dark:text-primary">Dashboard Overview</h1>
                    <p class="text-secondary mb-0">Welcome back<?= $adminName !== '' ? ', ' . htmlspecialchars($adminName) : '' ?>. Here's what's happening today at Tri Thức NP.</p>
                </div>
                <div class="col-lg-4 d-flex justify-content-lg-end gap-2">
                    <button class="btn btn-outline-primary-custom d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined fs-5">download</span> Report
                    </button>
                    <button class="btn btn-primary-custom d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined fs-5">add</span> Enrollment
                    </button>
                </div>
            </div>
            <!-- Stats Grid -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
                <div class="col animate-entrance stagger-2">
                    <div class="glass-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="stat-icon bg-info bg-opacity-10" style="color: #64b5f6">
                                <span class="material-symbols-outlined fs-2">group</span>
                            </div>
                            <span class="badge text-success bg-success bg-opacity-10">+12%</span>
                        </div>
                        <p class="text-uppercase text-secondary small fw-bold tracking-wider mb-1">Total Students</p>
                        <h2 class="fw-bold mb-0 dark:text-on-surface"><?= number_format($totalStudents) ?></h2>
                    </div>
                </div>
                <div class="col animate-entrance stagger-2" style="animation-delay: 0.25s">
                    <div class="glass-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="stat-icon bg-primary bg-opacity-10" style="color: var(--primary-color)">
                                <span class="material-symbols-outlined fs-2">menu_book</span>
                            </div>
                            <span class="badge text-secondary bg-light dark:bg-surface-container-highest">Active</span>
                        </div>
                        <p class="text-uppercase text-secondary small fw-bold tracking-wider mb-1">Active Courses</p>
                        <h2 class="fw-bold mb-0 dark:text-on-surface">42</h2>
                    </div>
                </div>
                <div class="col animate-entrance stagger-2" style="animation-delay: 0.3s">
                    <div class="glass-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <span class="material-symbols-outlined fs-2">payments</span>
                            </div>
                            <span class="badge text-success bg-success bg-opacity-10">+5.4%</span>
                        </div>
                        <p class="text-uppercase text-secondary small fw-bold tracking-wider mb-1">Monthly Revenue</p>
                        <h2 class="fw-bold mb-0 dark:text-on-surface">428.5M <span class="small fw-normal text-secondary">VND</span></h2>
                    </div>
                </div>
                <div class="col animate-entrance stagger-2" style="animation-delay: 0.35s">
                    <div class="glass-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <span class="material-symbols-outlined fs-2">person_add</span>
                            </div>
                            <span class="badge text-danger bg-danger bg-opacity-10">Today</span>
                        </div>
                        <p class="text-uppercase text-secondary small fw-bold tracking-wider mb-1">New Registrations</p>
                        <h2 class="fw-bold mb-0 dark:text-on-surface">18</h2>
                    </div>
                </div>
            </div>
            <!-- Middle Row: Chart & Schedule -->
            <div class="row g-4 mb-4">
                <div class="col-xl-8 animate-entrance stagger-3">
                    <div class="glass-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="h5 fw-bold mb-0 dark:text-primary">Student Enrollment Trends</h3>
                            <select class="form-select form-select-sm w-auto dark:bg-surface-container-low dark:border-outline-variant">
                                <option>Last 6 Months</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                        <div class="chart-container" style="height: 250px;">
                            <svg class="w-100 h-100" preserveaspectratio="none" viewbox="0 0 1000 300">
                                <defs>
                                    <lineargradient id="lineGradient" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#a2c9ff" stop-opacity="0.2"></stop>
                                        <stop offset="100%" stop-color="#a2c9ff" stop-opacity="0"></stop>
                                    </lineargradient>
                                </defs>
                                <path class="chart-area" d="M0,275 L100,220 L250,240 L400,140 L600,180 L800,80 L1000,100 L1000,275 L0,275 Z" fill="url(#lineGradient)"></path>
                                <path class="chart-line" d="M0,275 L100,220 L250,240 L400,140 L600,180 L800,80 L1000,100" fill="none" stroke="#a2c9ff" stroke-linecap="round" stroke-width="4"></path>
                                <circle class="chart-point" cx="100" cy="220" fill="#a2c9ff" r="5" style="animation-delay: 0.6s"></circle>
                                <circle class="chart-point" cx="400" cy="140" fill="#a2c9ff" r="5" style="animation-delay: 1.0s"></circle>
                                <circle class="chart-point" cx="800" cy="80" fill="#a2c9ff" r="5" style="animation-delay: 1.4s"></circle>
                            </svg>
                            <div class="d-flex justify-content-between mt-2 text-secondary small">
                                <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 animate-entrance stagger-4">
                    <div class="glass-card shadow-sm d-flex flex-column">
                        <h3 class="h5 fw-bold mb-4 dark:text-primary">Today's Schedule</h3>
                        <div class="flex-grow-1">
                            <div class="d-flex gap-3 p-3 mb-3 border-start border-4 bg-light rounded dark:bg-surface-container-low" style="border-color: var(--primary-color) !important;">
                                <div class="text-center" style="min-width: 50px;">
                                    <p class="mb-0 fw-bold dark:text-primary-fixed-dim">08:30</p>
                                    <p class="mb-0 small text-secondary">AM</p>
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold dark:text-on-surface">IELTS Intensive</p>
                                    <p class="mb-0 small text-secondary">Room 302 • Mr. Hoang</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3 p-3 mb-3 border-start border-4 bg-light rounded dark:bg-surface-container-low" style="border-color: var(--secondary) !important;">
                                <div class="text-center" style="min-width: 50px;">
                                    <p class="mb-0 fw-bold dark:text-primary-fixed-dim">10:15</p>
                                    <p class="mb-0 small text-secondary">AM</p>
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold dark:text-on-surface">Math Grade 12</p>
                                    <p class="mb-0 small text-secondary">Room 101 • Ms. Vy</p>
                                </div>
                            </div>
                        </div>
                        <a class="btn btn-link text-decoration-none d-flex align-items-center justify-content-center mt-3 gap-1 dark:text-primary" href="#">
                            View full calendar <span class="material-symbols-outlined fs-6">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Table Row: Activities -->
            <div class="glass-card shadow-sm p-0 overflow-hidden animate-entrance stagger-5">
                <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 dark:border-outline-variant">
                    <div>
                        <h3 class="h5 fw-bold mb-1 dark:text-primary">Recent Student Activities</h3>
                        <p class="small text-secondary mb-0">Real-time update on enrollment and results.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light border btn-sm p-2"><span class="material-symbols-outlined" style="color: var(--primary-color)">filter_list</span></button>
                        <button class="btn btn-light border btn-sm p-2"><span class="material-symbols-outlined" style="color: var(--primary-color)">refresh</span></button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="text-uppercase fw-bold" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3 border-0">Student</th>
                                <th class="py-3 border-0">Course</th>
                                <th class="py-3 border-0">Action</th>
                                <th class="py-3 border-0">Status</th>
                                <th class="py-3 border-0">Time</th>
                                <th class="px-4 py-3 border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr class="animate-entrance" style="animation-delay: 0.6s">
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 11px; background-color: rgba(162, 201, 255, 0.2); color: var(--primary-color)">NL</div>
                                        <div>
                                            <p class="mb-0 fw-bold dark:text-on-surface">Nguyen Linh</p>
                                            <p class="mb-0 text-secondary" style="font-size: 10px;">ID: #ST1042</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="dark:text-on-surface-variant">IELTS Intensive 7.5</td>
                                <td class="dark:text-on-surface-variant">Completed Mock Test</td>
                                <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success">PASSED</span></td>
                                <td class="text-secondary italic">2 mins ago</td>
                                <td class="px-4 text-end">
                                    <button class="btn btn-light btn-sm rounded-circle"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                            <tr class="animate-entrance" style="animation-delay: 0.7s">
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 11px; background-color: rgba(162, 201, 255, 0.2); color: var(--primary-color)">TQ</div>
                                        <div>
                                            <p class="mb-0 fw-bold dark:text-on-surface">Tran Quang</p>
                                            <p class="mb-0 text-secondary" style="font-size: 10px;">ID: #ST0981</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="dark:text-on-surface-variant">SAT Math Level 2</td>
                                <td class="dark:text-on-surface-variant">New Enrollment</td>
                                <td><span class="badge rounded-pill bg-opacity-10" style="background-color: var(--primary-color); color: var(--primary-color)">PENDING</span></td>
                                <td class="text-secondary italic">15 mins ago</td>
                                <td class="px-4 text-end">
                                    <button class="btn btn-light btn-sm rounded-circle"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                            <tr class="animate-entrance" style="animation-delay: 0.8s">
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 11px;">ML</div>
                                        <div>
                                            <p class="mb-0 fw-bold dark:text-on-surface">Minh Loc</p>
                                            <p class="mb-0 text-secondary" style="font-size: 10px;">ID: #ST0892</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="dark:text-on-surface-variant">Physics Grade 11</td>
                                <td class="dark:text-on-surface-variant">Absence Recorded</td>
                                <td><span class="badge rounded-pill bg-warning bg-opacity-10 text-warning">WARNING</span></td>
                                <td class="text-secondary italic">1 hour ago</td>
                                <td class="px-4 text-end">
                                    <button class="btn btn-light btn-sm rounded-circle"><span class="material-symbols-outlined">more_vert</span></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center dark:border-outline-variant">
                    <small class="text-secondary">Showing 3 of 258 entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#"><span class="material-symbols-outlined fs-6">chevron_left</span></a></li>
                            <li class="page-item active"><a class="page-link" href="#" style="background-color: var(--primary-color); border-color: var(--primary-color); color: #001c38;">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#"><span class="material-symbols-outlined fs-6" style="color: var(--primary-color)">chevron_right</span></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('toggleSidebar');
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const mainLogo = document.getElementById('mainLogo');
        const userAvatar = document.getElementById('userAvatar');
        const html = document.documentElement;

        const LOGO_DARK = 'https://sf-static.upanhlaylink.com/img/image_2026062459ce7705445a58722665145a97edb5e5.jpg';
        const LOGO_LIGHT = 'https://sf-static.upanhlaylink.com/img/image_202606138a35a649241c443bd9508782adfbf1ba.jpg';

        // Using assigned placeholders for user avatar
        const AVATAR_DARK = 'https://sf-static.upanhlaylink.com/img/image_2026062459ce7705445a58722665145a97edb5e5.jpg';
        const AVATAR_LIGHT = 'https://sf-static.upanhlaylink.com/img/image_202606138a35a649241c443bd9508782adfbf1ba.jpg';

        const updateVisuals = (isDark) => {
            mainLogo.src = isDark ? LOGO_DARK : LOGO_LIGHT;
            userAvatar.src = isDark ? AVATAR_DARK : AVATAR_LIGHT;
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
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                html.classList.add('light');
                themeIcon.textContent = 'dark_mode';
                localStorage.setItem('theme', 'light');
                updateVisuals(false);
            } else {
                html.classList.remove('light');
                html.classList.add('dark');
                themeIcon.textContent = 'light_mode';
                localStorage.setItem('theme', 'dark');
                updateVisuals(true);
            }
        });

        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'dark') {
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

        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('show');
            }
        });
    </script>
</body>

</html>