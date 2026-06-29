<?php
session_start();
require_once __DIR__ . '/../model/GradeLevelModel.php';

// --- PHẦN CHỈNH SỬA LẠI SESSION ---

$isLoggedIn = isset($_SESSION['student']);

// Lấy thông tin từ mảng $_SESSION['student'] dựa trên các cột trong bảng student
$studentName = $isLoggedIn ? $_SESSION['student']['full_name'] : '';
$studentId   = $isLoggedIn ? $_SESSION['student']['student_id'] : '';
$studentCode = $isLoggedIn ? $_SESSION['student']['student_code'] : '';
// ----------------------------------

$gradeModel = new GradeLevelModel();
$grades = $gradeModel->getAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giới thiệu - Trung Tâm Giáo Dục Tri Thức NP</title>
    <!-- Đồng bộ icon với index.php -->
    <link href="./img/icon/icon.jpg" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Roboto", sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            overflow-x: hidden;
            font-size: 16px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles - Đồng bộ hoàn toàn từ index.php */
        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            flex-wrap: nowrap;
        }

        .logo img {
            height: 60px;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        nav {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
        }

        .nav-links {
            display: flex;
            list-style: none;
            flex-wrap: nowrap;
        }

        .nav-links li {
            margin-left: 25px;
            position: relative;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 17px;
            transition: all 0.3s ease;
            position: relative;
            padding: 5px 0;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #2196f3;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #2196f3;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        /* Mobile login button - hidden by default */
        .mobile-login {
            display: none;
        }

        /* Dropdown Menu - Đồng bộ width 300px từ index.php */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
        }

        .dropdown-toggle i {
            margin-left: 5px;
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .dropdown:hover .dropdown-toggle i {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 300px;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1000;
            list-style-type: none;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu li {
            margin: 0;
            padding: 0;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px 20px;
            color: #333;
            transition: all 0.3s ease;
            white-space: normal;
            word-wrap: break-word;
        }

        .dropdown-menu a:hover {
            background-color: #f5f7fa;
            color: #2196f3;
            padding-left: 25px;
        }

        .dropdown-menu a::after {
            display: none;
        }

        .login-btn {
            margin-left: 30px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-login {
            background: linear-gradient(45deg, #1565c0, #64b5f6);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 17px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
            animation: pulse 2s infinite;
            white-space: nowrap;
            display: inline-block;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #0d47a1, #1976d2);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(33, 150, 243, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(33, 150, 243, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(33, 150, 243, 0);
            }
        }

        /* Added styles for student name display and logout button - từ index.php */
        .student-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: 30px;
            position: relative;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .student-name {
            background: linear-gradient(45deg, #1565c0, #64b5f6);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 17px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
            white-space: nowrap;
            display: inline-block;
        }

        .student-name:hover {
            background: linear-gradient(45deg, #0d47a1, #1976d2);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
        }

        .logout-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1000;
            min-width: 150px;
            margin-top: 10px;
        }

        .student-info:hover .logout-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .logout-menu a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .logout-menu a:hover {
            background-color: #f5f7fa;
            color: #d32f2f;
            padding-left: 25px;
        }

        .logout-menu a i {
            margin-right: 8px;
        }

        /* Hamburger Menu Icon */
        .menu-toggle {
            display: none;
            cursor: pointer;
            width: 40px;
            height: 40px;
            position: relative;
            z-index: 1001;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: auto;
        }

        .hamburger {
            width: 30px;
            height: 24px;
            position: relative;
            transition: all 0.3s ease;
        }

        .hamburger span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: #2196f3;
            border-radius: 3px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: all 0.3s ease;
        }

        .hamburger span:nth-child(1) {
            top: 0;
        }

        .hamburger span:nth-child(2) {
            top: 10px;
        }

        .hamburger span:nth-child(3) {
            top: 20px;
        }

        .hamburger.active span:nth-child(1) {
            top: 10px;
            transform: rotate(135deg);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
            left: -60px;
        }

        .hamburger.active span:nth-child(3) {
            top: 10px;
            transform: rotate(-135deg);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #1565c0 0%, #64b5f6 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" fill-opacity="1" d="m0,96l48,112c96,128,192,160,288,186.7c384,213,480,235,576,213.3c672,192,768,128,864,128c960,128,1056,192,1152,213.3c1248,235,1344,213,1392,202.7l1440,192l1440,320l1392,320c1344,320,1248,320,1152,320c1056,320,960,320,864,320c768,320,672,320,576,320c480,320,384,320,288,320c192,320,96,320,48,320l0,320z"></path></svg>');
            background-size: cover;
            background-position: center bottom;
            opacity: 0.6;
            z-index: 1;
        }

        .page-header .container {
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-family: "Montserrat", sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeInDown 1s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .breadcrumb {
            display: flex;
            justify-content: center;
            list-style: none;
            margin-top: 20px;
            animation: fadeIn 1.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .breadcrumb li {
            margin: 0 10px;
            position: relative;
        }

        .breadcrumb li:not(:last-child)::after {
            content: "›";
            position: absolute;
            right: -15px;
            top: 0;
            color: rgba(255, 255, 255, 0.7);
        }

        .breadcrumb a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 18px;
        }

        .breadcrumb a:hover {
            color: white;
        }

        .breadcrumb li:last-child a {
            color: white;
            font-weight: 600;
        }

        /* About Section */
        .about-section {
            padding: 100px 0;
            background-color: #fff;
            position: relative;
            overflow: hidden;
        }

        .about-section::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle,
                    rgba(33, 150, 243, 0.1) 0%,
                    rgba(33, 150, 243, 0) 70%);
            border-radius: 50%;
            z-index: 1;
        }

        .about-section::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle,
                    rgba(33, 150, 243, 0.1) 0%,
                    rgba(33, 150, 243, 0) 70%);
            border-radius: 50%;
            z-index: 1;
        }

        .section-title {
            font-family: "Montserrat", sans-serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: #1565c0;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .section-title::after {
            content: "";
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #1565c0, #64b5f6);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .about-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 40px;
            position: relative;
            z-index: 2;
        }

        .about-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .about-image::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                    rgba(21, 101, 192, 0.3) 0%,
                    rgba(100, 181, 246, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .about-image:hover::before {
            opacity: 1;
        }

        .about-image img {
            max-width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }

        .about-image:hover img {
            transform: scale(1.05);
        }

        .about-text {
            flex: 1;
            min-width: 300px;
        }

        .about-text p {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #455a64;
            margin-bottom: 20px;
            text-align: justify;
        }

        .about-text h3 {
            font-family: "Montserrat", sans-serif;
            font-size: 2rem;
            color: #1565c0;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
        }

        .about-text h3::after {
            content: "";
            position: absolute;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1565c0, #64b5f6);
            bottom: 0;
            left: 0;
            border-radius: 1.5px;
        }

        .about-text ul {
            margin-bottom: 20px;
            list-style-type: none;
        }

        .about-text li {
            margin-bottom: 15px;
            position: relative;
            padding-left: 30px;
            font-size: 1.2rem;
            color: #455a64;
            transition: transform 0.3s ease;
        }

        .about-text li:hover {
            transform: translateX(5px);
        }

        .about-text li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #2196f3;
            position: absolute;
            left: 0;
            top: 2px;
        }

        /* Online Teaching Section */
        .online-teaching {
            padding: 100px 0;
            background-color: #f5f7fa;
            position: relative;
            overflow: hidden;
        }

        .online-teaching::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle,
                    rgba(33, 150, 243, 0.1) 0%,
                    rgba(33, 150, 243, 0) 70%);
            border-radius: 50%;
        }

        .online-teaching-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 40px;
            position: relative;
            z-index: 2;
        }

        .online-teaching-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .online-teaching-image::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                    rgba(21, 101, 192, 0.3) 0%,
                    rgba(100, 181, 246, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .online-teaching-image:hover::before {
            opacity: 1;
        }

        .online-teaching-image img {
            max-width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }

        .online-teaching-image:hover img {
            transform: scale(1.05);
        }

        .online-teaching-text {
            flex: 1;
            min-width: 300px;
        }

        .online-teaching-text p {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #455a64;
            margin-bottom: 20px;
            text-align: justify;
        }

        .online-teaching-text h3 {
            font-family: "Montserrat", sans-serif;
            font-size: 2rem;
            color: #1565c0;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
        }

        .online-teaching-text h3::after {
            content: "";
            position: absolute;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1565c0, #64b5f6);
            bottom: 0;
            left: 0;
            border-radius: 1.5px;
        }

        .online-teaching-text ul {
            margin-bottom: 20px;
            list-style-type: none;
        }

        .online-teaching-text li {
            margin-bottom: 15px;
            position: relative;
            padding-left: 30px;
            font-size: 1.2rem;
            color: #455a64;
            transition: transform 0.3s ease;
        }

        .online-teaching-text li:hover {
            transform: translateX(5px);
        }

        .online-teaching-text li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #2196f3;
            position: absolute;
            left: 0;
            top: 2px;
        }

        /* Recorded Lessons Section */
        .recorded-lessons {
            padding: 100px 0;
            background-color: #fff;
            position: relative;
            overflow: hidden;
        }

        .recorded-lessons::before {
            content: "";
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle,
                    rgba(33, 150, 243, 0.1) 0%,
                    rgba(33, 150, 243, 0) 70%);
            border-radius: 50%;
        }

        .recorded-lessons-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 40px;
            position: relative;
            z-index: 2;
        }

        .recorded-lessons-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .recorded-lessons-image::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                    rgba(21, 101, 192, 0.3) 0%,
                    rgba(100, 181, 246, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .recorded-lessons-image:hover::before {
            opacity: 1;
        }

        .recorded-lessons-image img {
            max-width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }

        .recorded-lessons-image:hover img {
            transform: scale(1.05);
        }

        .recorded-lessons-text {
            flex: 1;
            min-width: 300px;
        }

        .recorded-lessons-text p {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #455a64;
            margin-bottom: 20px;
            text-align: justify;
        }

        .recorded-lessons-text h3 {
            font-family: "Montserrat", sans-serif;
            font-size: 2rem;
            color: #1565c0;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
        }

        .recorded-lessons-text h3::after {
            content: "";
            position: absolute;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1565c0, #64b5f6);
            bottom: 0;
            left: 0;
            border-radius: 1.5px;
        }

        .recorded-lessons-text ul {
            margin-bottom: 20px;
            list-style-type: none;
        }

        .recorded-lessons-text li {
            margin-bottom: 15px;
            position: relative;
            padding-left: 30px;
            font-size: 1.2rem;
            color: #455a64;
            transition: transform 0.3s ease;
        }

        .recorded-lessons-text li:hover {
            transform: translateX(5px);
        }

        .recorded-lessons-text li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #2196f3;
            position: absolute;
            left: 0;
            top: 2px;
        }

        /* Mission & Vision Section */
        .mission-vision {
            padding: 100px 0;
            background-color: #f5f7fa;
            position: relative;
            overflow: hidden;
        }

        .mission-vision::before {
            content: "";
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle,
                    rgba(33, 150, 243, 0.1) 0%,
                    rgba(33, 150, 243, 0) 70%);
            border-radius: 50%;
        }

        .mission-vision-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .mission-box,
        .vision-box {
            flex: 1;
            min-width: 300px;
            background-color: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .mission-box::before,
        .vision-box::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #1565c0, #64b5f6);
            top: 0;
            left: 0;
            transition: height 0.3s ease;
        }

        .mission-box:hover,
        .vision-box:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .mission-box:hover::before,
        .vision-box:hover::before {
            height: 10px;
        }

        .mission-box h3,
        .vision-box h3 {
            font-family: "Montserrat", sans-serif;
            font-size: 2rem;
            color: #1565c0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .mission-box h3 i,
        .vision-box h3 i {
            margin-right: 15px;
            font-size: 2.2rem;
            color: #2196f3;
            transition: transform 0.3s ease;
        }

        .mission-box:hover h3 i,
        .vision-box:hover h3 i {
            transform: scale(1.2);
        }

        .mission-box p,
        .vision-box p {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #455a64;
        }

        /* Programs Section */
        .programs-section {
            padding: 100px 0;
            background-color: #f5f7fa;
            position: relative;
            overflow: hidden;
        }

        .programs-section::before {
            content: "";
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle,
                    rgba(33, 150, 243, 0.1) 0%,
                    rgba(33, 150, 243, 0) 70%);
            border-radius: 50%;
        }

        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .program-card {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .program-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .program-icon {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1565c0, #64b5f6);
            color: #fff;
            font-size: 4.5rem;
            transition: all 0.3s ease;
        }

        .program-card:hover .program-icon {
            background: linear-gradient(135deg, #0d47a1, #1976d2);
        }

        .program-icon i {
            transition: transform 0.3s ease;
        }

        .program-card:hover .program-icon i {
            transform: scale(1.2);
        }

        .program-content {
            padding: 25px;
        }

        .program-title {
            font-family: "Montserrat", sans-serif;
            font-size: 1.5rem;
            color: #1565c0;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .program-title::after {
            content: "";
            position: absolute;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #1565c0, #64b5f6);
            bottom: 0;
            left: 0;
            border-radius: 1.5px;
            transition: width 0.3s ease;
        }

        .program-card:hover .program-title::after {
            width: 80px;
        }

        .program-description {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #455a64;
            margin-bottom: 20px;
        }

        .program-link {
            display: inline-block;
            color: #1565c0;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            position: relative;
            padding-right: 25px;
            transition: all 0.3s ease;
        }

        .program-link::after {
            content: "\f061";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            transition: right 0.3s ease;
        }

        .program-link:hover {
            color: #0d47a1;
            padding-right: 30px;
        }

        .program-link:hover::after {
            right: -5px;
        }

        /* Team Section */
        .team-section {
            padding: 100px 0;
            background-color: #fff;
            position: relative;
        }

        .team-section::before {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 200px;
            background: linear-gradient(to top,
                    rgba(245, 247, 250, 0.5),
                    transparent);
            z-index: 1;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .team-member {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .team-member::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 0;
            background: linear-gradient(to top,
                    rgba(21, 101, 192, 0.8),
                    transparent);
            bottom: 0;
            left: 0;
            z-index: 1;
            transition: height 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .team-member:hover::before {
            height: 50%;
        }

        .team-photo {
            width: 100%;
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .team-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .team-member:hover .team-photo img {
            transform: scale(1.1);
        }

        .team-info {
            padding: 25px;
            text-align: center;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .team-member:hover .team-info {
            transform: translateY(-10px);
        }

        .team-info h3 {
            font-family: "Montserrat", sans-serif;
            font-size: 1.5rem;
            color: #1565c0;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }

        .team-member:hover .team-info h3 {
            color: #fff;
        }

        .team-info p {
            color: #455a64;
            font-size: 1.1rem;
            margin-bottom: 15px;
            transition: color 0.3s ease;
        }

        .team-member:hover .team-info p {
            color: rgba(255, 255, 255, 0.9);
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .team-member:hover .team-social {
            opacity: 1;
            transform: translateY(0);
        }

        .team-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: #fff;
            transition: all 0.3s ease;
        }

        .team-social a:hover {
            background-color: #fff;
            color: #1565c0;
            transform: translateY(-5px) rotate(360deg);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #0d47a1, #1976d2);
            color: #fff;
            padding: 60px 0 20px;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, #64b5f6, #1565c0, #64b5f6);
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .footer-logo img {
            height: 80px;
            margin-bottom: 20px;
            background-color: white;
            padding: 10px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .footer-logo img:hover {
            transform: scale(1.05);
        }

        .footer-info,
        .footer-social {
            margin-bottom: 30px;
        }

        .footer-info h3,
        .footer-social h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-info h3::after,
        .footer-social h3::after {
            content: "";
            position: absolute;
            width: 50px;
            height: 3px;
            background-color: #64b5f6;
            bottom: 0;
            left: 0;
            border-radius: 1.5px;
            transition: width 0.3s ease;
        }

        .footer-info:hover h3::after,
        .footer-social:hover h3::after {
            width: 70px;
        }

        .footer-info p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
            font-size: 1.1rem;
        }

        .footer-info p:hover {
            transform: translateX(5px);
        }

        .footer-info i {
            margin-right: 10px;
            color: #90caf9;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #fff;
            font-size: 18px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .social-icons a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #fff;
            transform: scale(0);
            transition: transform 0.3s ease;
            border-radius: 50%;
            z-index: -1;
        }

        .social-icons a:hover {
            color: #1565c0;
            transform: translateY(-5px);
        }

        .social-icons a:hover::before {
            transform: scale(1);
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            position: relative;
            z-index: 1;
        }

        /* Chat Widgets */
        .chat-widgets {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .chat-widget {
            position: relative;
        }

        .chat-toggle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            text-decoration: none;
        }

        .chat-toggle:hover {
            transform: scale(1.1) rotate(10deg);
        }

        .fb-toggle {
            background: linear-gradient(135deg, #0084ff, #0066cc);
            color: white;
            font-size: 35px;
        }

        .zalo-toggle {
            background: linear-gradient(135deg, #0068ff, #0055cc);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .zalo-icon {
            width: 40px;
            height: 40px;
            transition: transform 0.3s ease;
        }

        .chat-toggle:hover .zalo-icon {
            transform: scale(1.1);
        }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Animation for chat widgets */
        @keyframes pulse-chat {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 132, 255, 0.7);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(0, 132, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(0, 132, 255, 0);
            }
        }

        .fb-toggle {
            animation: pulse-chat 2s infinite;
        }

        .zalo-toggle {
            animation: pulse-chat 2s infinite;
            animation-delay: 1s;
        }

        /* Responsive Design */
        @media (max-width: 992px) {

            .about-content,
            .online-teaching-content,
            .recorded-lessons-content {
                flex-direction: column;
            }

            .about-image,
            .online-teaching-image,
            .recorded-lessons-image {
                order: -1;
                margin-bottom: 30px;
            }

            .footer-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .footer-info h3::after,
            .footer-social h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .social-icons {
                justify-content: center;
            }

            .footer-info p:hover {
                transform: none;
            }

            .programs-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                font-size: 18px;
            }

            /* Hide desktop login button and show mobile login button */
            .desktop-login {
                display: none;
            }

            .mobile-login {
                display: flex;
                justify-content: center;
                margin: 20px 0;
            }

            .mobile-login .btn-login {
                width: 80%;
                font-size: 20px;
                padding: 12px 25px;
                text-align: center;
            }

            /* Mobile styles for student info - từ index.php */
            .student-info {
                margin-left: 0;
                margin-top: 15px;
                width: 100%;
                justify-content: center;
            }

            .student-name {
                width: 80%;
                text-align: center;
                font-size: 18px;
            }

            .logout-menu {
                position: absolute;
                top: 100%;
                right: auto;
                left: 50%;
                transform: translateX(-50%) translateY(10px);
                background-color: #fff;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                border-radius: 5px;
                padding: 10px 0;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 1000;
                min-width: 150px;
                margin-top: 10px;
            }

            .student-info:hover .logout-menu {
                transform: translateX(-50%) translateY(0);
            }

            .menu-toggle {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: 90px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 90px);
                background-color: #fff;
                flex-direction: column;
                align-items: center;
                padding: 20px 0;
                transition: left 0.3s ease;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                overflow-y: auto;
                z-index: 999;
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links li {
                margin: 12px 0;
                width: 90%;
                text-align: center;
            }

            .nav-links a {
                font-size: 22px;
                font-weight: 600;
                padding: 10px 0;
                display: block;
            }

            .dropdown-menu {
                position: static;
                width: 100%;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                max-height: 0;
                overflow: hidden;
                padding: 0;
                transition: max-height 0.5s ease;
                background-color: #f5f7fa;
                border-radius: 8px;
                margin-top: 10px;
                overflow-y: auto;
            }

            .dropdown.active .dropdown-menu {
                max-height: 500px;
                padding: 10px 0;
            }

            .dropdown-menu a {
                font-size: 18px;
                padding: 10px 15px;
                text-align: center;
                white-space: normal;
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-word;
            }

            .dropdown-toggle i {
                transition: transform 0.3s ease;
                font-size: 14px;
            }

            .dropdown.active .dropdown-toggle i {
                transform: rotate(180deg);
            }

            .page-title {
                font-size: 2.5rem;
            }

            .team-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .programs-grid {
                grid-template-columns: 1fr;
            }

            .chat-toggle {
                width: 80px;
                height: 80px;
            }

            .fb-toggle {
                font-size: 40px;
            }

            .zalo-icon {
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 576px) {
            .logo img {
                height: 50px;
            }

            .nav-links a {
                font-size: 24px;
            }

            .dropdown-menu a {
                font-size: 22px;
            }

            .mobile-login .btn-login {
                font-size: 22px;
            }

            .page-title {
                font-size: 2.2rem;
            }

            .section-title {
                font-size: 2.3rem;
            }

            .about-text h3,
            .mission-box h3,
            .vision-box h3,
            .online-teaching-text h3,
            .recorded-lessons-text h3 {
                font-size: 1.8rem;
            }

            .about-text p,
            .mission-box p,
            .vision-box p,
            .about-text li,
            .online-teaching-text p,
            .online-teaching-text li,
            .recorded-lessons-text p,
            .recorded-lessons-text li {
                font-size: 1.2rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .chat-widgets {
                bottom: 20px;
                right: 20px;
                gap: 15px;
            }

            .chat-toggle {
                width: 70px;
                height: 70px;
            }

            .fb-toggle {
                font-size: 35px;
            }

            .zalo-icon {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .logo img {
                height: 40px;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .footer-info h3,
            .footer-social h3 {
                font-size: 1.3rem;
            }

            .chat-widgets {
                bottom: 15px;
                right: 15px;
                gap: 10px;
            }

            .chat-toggle {
                width: 60px;
                height: 60px;
            }

            .fb-toggle {
                font-size: 30px;
            }

            .zalo-icon {
                width: 35px;
                height: 35px;
            }
        }

        /* Animation classes */
        .fade-in {
            animation: fadeIn 1s ease forwards;
        }

        .fade-in-up {
            animation: fadeInUp 1s ease forwards;
        }

        .fade-in-left {
            animation: fadeInLeft 1s ease forwards;
        }

        .fade-in-right {
            animation: fadeInRight 1s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Floating animation */
        .float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <!-- Đồng bộ logo path với index.php -->
                <img src="./img/logo/logo.png" alt="Logo" />
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="./index.php">Trang chủ</a></li>
                    <li><a href="./About.php" class="active">Giới thiệu</a></li>
                    <li><a href="./TeacherNewsStudentView.php">Giáo viên</a></li>
                    <li><a href="#">Tin tức</a></li>
                    <li><a href="./student_recite.php">Báo bài</a></li>
                    <li><a href="#activities">Hoạt động học sinh</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Thời khóa biểu
                            <i class="fas fa-chevron-down"></i>
                        </a>

                        <ul class="dropdown-menu">
                            <?php foreach ($grades as $g): ?>
                                <li>
                                    <a href="timetable_view.php?grade_level_id=<?= $g['grade_level_id'] ?>">
                                        <?= htmlspecialchars($g['grade_level_name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="./student-scores.php">Xem điểm</a></li>
                    <li><a href="./student-attendance.php">Điểm danh</a></li>
                    <!-- Mobile login button or student info -->
                    <li class="mobile-login" id="mobileLoginContainer">
                        <button class="btn-login" id="mobileLoginBtn">Đăng nhập</button>
                    </li>
                </ul>
                <!-- Desktop login button or student info -->
                <div class="login-btn desktop-login" id="desktopLoginContainer">
                    <button class="btn-login" id="desktopLoginBtn">Đăng nhập</button>
                </div>
                <div class="menu-toggle">
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Giới thiệu trung tâm</h1>
            <ul class="breadcrumb">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="./About.php">Giới thiệu</a></li>
            </ul>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Về chúng tôi</h2>
            <div class="about-content">
                <div
                    class="about-image"
                    data-aos="fade-right"
                    data-aos-delay="200">
                    <img
                        src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/6.jpg-GGLH1aGvHT7QknHMrffLRp0H6iPPDi.jpeg"
                        alt="trung tâm giáo dục tri thức np" />
                </div>
                <div
                    class="about-text"
                    data-aos="fade-left"
                    data-aos-delay="300">
                    <h3>Lịch sử phát triển</h3>
                    <p>
                        Xuất phát điểm từ một trung tâm luyện thi nhỏ được
                        thành lập năm 2018 với tên gọi ban đầu là Trung tâm
                        Tri Thức NP. Ban đầu, trung tâm chỉ gồm một vài lớp
                        LTĐH môn Lý và Hóa. Sau 7 năm kể từ ngày thành lập,
                        NP đã cố gắng rất nhiều để trở thành một gia đình
                        xứng đáng với tình cảm mà các Quí PH và các
                        bạn HS đã dành cho chúng tôi.
                    </p>
                    <p>
                        Hiện nay, bên cạnh các chương trình bồi dưỡng văn
                        hóa, luyện thi tuyển sinh 10 và luyện thi TN THPT đã
                        đạt được nhiều thành tích đáng kể, NP còn có các
                        chương trình học thu hút được rất nhiều sự quan tâm
                        của mọi người:
                    </p>
                    <ul>
                        <li>Bồi dưỡng và luyện thi Đánh giá Năng lực.</li>
                        <li>Bồi dưỡng và luyện thi IELTS.</li>
                        <li>
                            Bồi dưỡng môn Khoa Học Tự Nhiên khối 6, 7, 8, 9.
                        </li>
                        <li>Bồi dưỡng học sinh giỏi cấp II, cấp III.</li>
                    </ul>
                    <p>
                        Tới tháng 01/2025, Trung tâm Tri Thức NP đổi tên thành Trung tâm Giáo dục Tri Thức NP. NP sẽ cố gắng hoàn thiện
                        mình hơn nữa để nâng cao chất lượng giảng dạy, hoàn
                        thiện cơ sở vật chất và hệ thống làm việc để làm hài
                        lòng tất cả Quí PH và các bạn HS đã yêu thương, tin
                        tưởng NP ❤.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Phần giảng dạy qua online -->
    <section class="online-teaching" id="online-teaching">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                Giảng dạy qua online
            </h2>
            <div class="online-teaching-content">
                <div
                    class="online-teaching-image"
                    data-aos="fade-right"
                    data-aos-delay="200">
                    <img
                        src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/18.jpg-wg2lCe6ApH1KXVynCH5dfKWpuwZdjm.jpeg"
                        alt="giảng dạy trực tuyến tại trung tâm giáo dục tri thức np" />
                </div>
                <div
                    class="online-teaching-text"
                    data-aos="fade-left"
                    data-aos-delay="300">
                    <h3>Học tập không giới hạn</h3>
                    <p>
                        Trung tâm Giáo Dục Tri Thức NP tự hào giới thiệu hệ
                        thống giảng dạy trực tuyến hiện đại, giúp học sinh
                        có thể tiếp cận kiến thức mọi lúc, mọi nơi. Với công
                        nghệ livestream chất lượng cao, chúng tôi đảm bảo
                        trải nghiệm học tập trực tuyến không khác gì học
                        trực tiếp tại lớp.
                    </p>
                    <p>
                        Hệ thống giảng dạy online của chúng tôi được thiết
                        kế để đáp ứng nhu cầu học tập trong thời đại số, đặc
                        biệt phù hợp trong các tình huống học sinh không thể
                        đến trung tâm trực tiếp.
                    </p>
                    <ul>
                        <li>
                            Lớp học trực tuyến với giáo viên giảng dạy trực
                            tiếp.
                        </li>
                        <li>
                            Tương tác hai chiều giữa giáo viên và học sinh.
                        </li>
                        <li>Hệ thống quản lý học tập hiện đại.</li>
                        <li>Tài liệu học tập số hóa dễ dàng truy cập.</li>
                        <li>
                            Ghi hình bài giảng để học sinh có thể xem lại.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Phần bài giảng được lưu lại và chia sẻ đến học sinh -->
    <section class="recorded-lessons" id="recorded-lessons">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                Bài giảng được lưu lại và chia sẻ
            </h2>
            <div class="recorded-lessons-content">
                <div
                    class="recorded-lessons-text"
                    data-aos="fade-right"
                    data-aos-delay="200">
                    <h3>Học tập mọi lúc mọi nơi</h3>
                    <p>
                        Với mục tiêu tạo điều kiện tốt nhất cho việc học tập
                        của học sinh, NP đã xây
                        dựng hệ thống lưu trữ và chia sẻ bài giảng chất
                        lượng cao. Tất cả các buổi học trực tuyến đều được
                        ghi lại và tổ chức thành thư viện bài giảng phong
                        phú.
                    </p>
                    <p>
                        Học sinh có thể truy cập vào kho tài liệu này bất cứ
                        lúc nào để ôn tập, củng cố kiến thức hoặc bù đắp
                        những buổi học đã bỏ lỡ. Đây là một trong những ưu
                        điểm vượt trội của phương pháp học tập hiện đại tại
                        trung tâm chúng tôi.
                    </p>
                    <ul>
                        <li>
                            Bài giảng được lưu trữ có hệ thống theo chủ đề
                            và cấp độ.
                        </li>
                        <li>Chất lượng video và âm thanh rõ nét.</li>
                        <li>
                            Dễ dàng tìm kiếm và truy cập qua cổng thông tin
                            học sinh.
                        </li>
                        <li>
                            Có thể tải về để học tập khi không có kết nối
                            Internet.
                        </li>
                        <li>
                            Bổ sung thêm tài liệu và bài tập đi kèm mỗi bài
                            giảng.
                        </li>
                    </ul>
                </div>
                <div
                    class="recorded-lessons-image"
                    data-aos="fade-left"
                    data-aos-delay="300">
                    <img
                        src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/thay1.jpg-RPFRzX5bAvabKyZuPnx0vHEU1u1M5i.jpeg"
                        alt="Bài giảng được lưu lại tại trung tâm giáo dục tri thức np" />
                </div>
            </div>
        </div>
    </section>

    <section class="mission-vision" id="mission">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                Sứ mệnh & Tầm nhìn
            </h2>
            <div class="mission-vision-content">
                <div
                    class="mission-box"
                    data-aos="fade-up"
                    data-aos-delay="200">
                    <h3><i class="fas fa-bullseye"></i> Sứ mệnh</h3>
                    <p>
                        Sứ mệnh của Trung Tâm Giáo Dục Tri Thức NP là cung
                        cấp một môi trường học tập chất lượng cao, nơi học
                        sinh được phát triển toàn diện cả về kiến thức, kỹ
                        năng và phẩm chất. Chúng tôi cố gắng nhất để đồng hành cùng
                        học sinh trên con đường chinh phục mục tiêu học tập
                        và xây dựng nền tảng vững chắc cho tương lai.
                    </p>
                </div>
                <div
                    class="vision-box"
                    data-aos="fade-up"
                    data-aos-delay="300">
                    <h3><i class="fas fa-eye"></i> Tầm nhìn</h3>
                    <p>
                        Trở thành trung tâm giáo dục chất lượng, nơi mỗi học
                        sinh đều được tôn trọng, được khơi dậy niềm đam mê
                        học tập và được trang bị đầy đủ kiến thức, kỹ năng
                        cần thiết để thành công trong cuộc sống. Chúng tôi
                        hướng tới việc xây dựng một cộng đồng học tập năng
                        động, sáng tạo và nhân văn.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="programs-section" id="programs">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                Chương trình đào tạo
            </h2>
            <div class="programs-grid">
                <div
                    class="program-card"
                    data-aos="fade-up"
                    data-aos-delay="100">
                    <div class="program-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="program-content">
                        <h3 class="program-title">
                            Bồi dưỡng và luyện thi Đánh giá Năng lực
                        </h3>
                        <p class="program-description">
                            Chương trình đào tạo chuyên sâu giúp học sinh
                            phát triển các kỹ năng tư duy, phân tích và giải
                            quyết vấn đề, chuẩn bị tốt nhất cho kỳ thi đánh
                            giá năng lực.
                        </p>
                        <a href="https://www.facebook.com/trungtamtrithucnp" class="program-link">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div
                    class="program-card"
                    data-aos="fade-up"
                    data-aos-delay="200">
                    <div class="program-icon">
                        <i class="fas fa-language"></i>
                    </div>
                    <div class="program-content">
                        <h3 class="program-title">
                            Bồi dưỡng và luyện thi IELTS
                        </h3>
                        <p class="program-description">
                            Khóa học IELTS với phương pháp giảng dạy hiệu
                            quả, giúp học viên nâng cao 4 kỹ năng nghe, nói,
                            đọc, viết và đạt điểm số IELTS như mong muốn.
                        </p>
                        <a href="https://www.facebook.com/trungtamtrithucnp" class="program-link">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div
                    class="program-card"
                    data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="program-icon">
                        <i class="fas fa-atom"></i>
                    </div>
                    <div class="program-content">
                        <h3 class="program-title">
                            Bồi dưỡng môn Khoa học Tự nhiên
                        </h3>
                        <p class="program-description">
                            Chương trình bồi dưỡng chuyên sâu các môn Khoa
                            học Tự nhiên dành cho học sinh khối 6, 7, 8, 9,
                            giúp học sinh nắm vững kiến thức và phát triển
                            tư duy khoa học.
                        </p>
                        <a href="https://www.facebook.com/trungtamtrithucnp" class="program-link">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div
                    class="program-card"
                    data-aos="fade-up"
                    data-aos-delay="400">
                    <div class="program-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="program-content">
                        <h3 class="program-title">
                            Bồi dưỡng học sinh giỏi
                        </h3>
                        <p class="program-description">
                            Chương trình đặc biệt dành cho học sinh có năng
                            khiếu, giúp các em phát huy tối đa tiềm năng và
                            đạt thành tích cao trong các kỳ thi học sinh
                            giỏi cấp II, cấp III.
                        </p>
                        <a href="https://www.facebook.com/trungtamtrithucnp" class="program-link">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div
                    class="program-card"
                    data-aos="fade-up"
                    data-aos-delay="500">
                    <div class="program-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="program-content">
                        <h3 class="program-title">
                            Luyện thi tuyển sinh 10
                        </h3>
                        <p class="program-description">
                            Chương trình luyện thi chuyên biệt giúp học sinh
                            lớp 9 chuẩn bị tốt nhất cho kỳ thi tuyển sinh
                            vào lớp 10, với đội ngũ giáo viên giàu kinh
                            nghiệm và phương pháp giảng dạy hiệu quả.
                        </p>
                        <a href="https://www.facebook.com/trungtamtrithucnp" class="program-link">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div
                    class="program-card"
                    data-aos="fade-up"
                    data-aos-delay="600">
                    <div class="program-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="program-content">
                        <h3 class="program-title">Luyện thi TN THPT</h3>
                        <p class="program-description">
                            Chương trình luyện thi toàn diện giúp học sinh
                            lớp 12 ôn tập và củng cố kiến thức, sẵn sàng cho
                            kỳ thi tốt nghiệp THPT và xét tuyển Đại học với
                            kết quả cao nhất.
                        </p>
                        <a href="https://www.facebook.com/trungtamtrithucnp" class="program-link">Tìm hiểu thêm</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section" id="team">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                Đội ngũ Giáo Viên
            </h2>
            <div class="team-grid">
                <div
                    class="team-member"
                    data-aos="fade-up"
                    data-aos-delay="100">
                    <div class="team-photo">
                        <img
                            src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/thay3.jpg-DPzEF9jZiFSzF6l7JQOrC2YeJgI3Wm.jpeg"
                            alt="giáo viên" />
                    </div>
                    <div class="team-info">
                        <h3>Ngô Diệu Thạch</h3>
                        <p>Giáo viên Lý</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div
                    class="team-member"
                    data-aos="fade-up"
                    data-aos-delay="200">
                    <div class="team-photo">
                        <img
                            src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/thay5.jpg-k5KUUW3x1cK0eMrOrTOobG6alWov8R.jpeg"
                            alt="giáo viên" />
                    </div>
                    <div class="team-info">
                        <h3>Trần Nguyễn Hạ Quyên</h3>
                        <p>Giáo viên Sinh học</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div
                    class="team-member"
                    data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="team-photo">
                        <img
                            src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/thay1.jpg-RPFRzX5bAvabKyZuPnx0vHEU1u1M5i.jpeg"
                            alt="giáo viên" />
                    </div>
                    <div class="team-info">
                        <h3>Lê Minh Xuân Nhị</h3>
                        <p>Giáo viên Hóa học</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div
                    class="team-member"
                    data-aos="fade-up"
                    data-aos-delay="400">
                    <div class="team-photo">
                        <img
                            src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/465857952_2054407054995220_2428996733991711865_n.jpg-LtpGsMGkIW5T48gfZTvZn5Sx2fdRaZ.jpeg"
                            alt="giáo viên" />
                    </div>
                    <div class="team-info">
                        <h3>Tư Đô Nguyên</h3>
                        <p>Giáo viên Toán</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo" data-aos="fade-up">
                    <img
                        src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/banner1-7rQxLFlfijVkvCxzCXogJilKp5vnhD.png"
                        alt="Trung Tâm Giáo Dục Tri Thức NP" />
                </div>
                <div
                    class="footer-info"
                    data-aos="fade-up"
                    data-aos-delay="100">
                    <h3>Trung Tâm Giáo Dục Tri Thức NP</h3>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> Địa chỉ: số 7-11A-54-78-112 Đường số 2, Cư xá Đô Thành, P4, Q3
                    </p>
                    <p>
                        <i class="fas fa-phone"></i> Điện thoại: 0976969028 - 0906969028
                    </p>
                    <p>
                        <i class="fas fa-envelope"></i> Email: trungtamtrithucnp@gmail.com
                    </p>
                </div>
                <div
                    class="footer-social"
                    data-aos="fade-up"
                    data-aos-delay="200">
                    <h3>Kết nối với chúng tôi</h3>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/trungtamtrithucnp"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>
                    &copy; 2025 Trung Tâm Giáo Dục Tri Thức NP. Tất cả quyền
                    được bảo lưu.
                </p>
            </div>
        </div>
    </footer>

    <!-- Chat Widgets -->
    <div class="chat-widgets">
        <!-- Facebook Messenger Chat Widget -->
        <div class="chat-widget facebook-chat">
            <a
                href="https://www.facebook.com/TrungtamTriThucNP"
                target="_blank"
                class="chat-toggle fb-toggle">
                <i class="fab fa-facebook-messenger"></i>
            </a>
        </div>

        <!-- Zalo Chat Widget -->
        <div class="chat-widget zalo-chat">
            <a
                href="https://zalo.me/0976969028"
                target="_blank"
                class="chat-toggle zalo-toggle">
                <!-- Đồng bộ path zalo icon với index.php -->
                <img src="./img/icon/zaloicon.png" alt="Zalo" class="zalo-icon" />
            </a>
        </div>
    </div>

    <!-- AOS JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Check if student is logged in
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
            const studentName = <?php echo json_encode($studentName); ?>;
            const studentId = <?php echo json_encode($studentId); ?>;
            const studentCode = <?php echo json_encode($studentCode); ?>;

            // Function to render login button or student info
            function renderAuthUI() {
                const desktopContainer = document.getElementById('desktopLoginContainer');
                const mobileContainer = document.getElementById('mobileLoginContainer');

                if (isLoggedIn && studentName) {
                    // Create student info HTML
                    const studentInfoHTML = `
                        <div class="student-info">
                            <div class="student-name" data-user-id="${studentId}">
                                <i class="fas fa-user-circle"></i> 
                                 <span class="student-fullname">${studentName}</span>
                                <span class="student-code">(${studentCode})</span>
                            </div>
                            <div class="logout-menu">
                                <a href="#" onclick="handleLogout(event)">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </a>
                            </div>
                        </div>
                    `;

                    // Update desktop container
                    desktopContainer.innerHTML = studentInfoHTML;
                    desktopContainer.classList.add('student-info-container');

                    // Update mobile container
                    mobileContainer.innerHTML = studentInfoHTML;
                    mobileContainer.classList.add('student-info-container');
                } else {
                    // Show login buttons
                    const loginHTML = `<button class="btn-login" onclick="handleLogin()">Đăng nhập</button>`;
                    desktopContainer.innerHTML = loginHTML;
                    mobileContainer.innerHTML = loginHTML;
                }
            }

            // Render auth UI on page load
            renderAuthUI();

            // Initialize AOS
            AOS.init({
                duration: 800,
                easing: "ease-in-out",
                once: true,
                mirror: false,
            });

            // Mobile Menu Toggle
            const menuToggle = document.querySelector(".menu-toggle");
            const navLinks = document.querySelector(".nav-links");
            const hamburger = document.querySelector(".hamburger");

            if (menuToggle) {
                menuToggle.addEventListener("click", function() {
                    navLinks.classList.toggle("active");
                    hamburger.classList.toggle("active");
                });
            }

            // Close mobile menu when clicking outside
            document.addEventListener("click", function(event) {
                if (
                    !event.target.closest("nav") &&
                    navLinks.classList.contains("active")
                ) {
                    navLinks.classList.remove("active");
                    hamburger.classList.remove("active");
                }
            });

            // Mobile dropdown menu - improved for better UX
            const dropdowns = document.querySelectorAll(".dropdown");

            dropdowns.forEach((dropdown) => {
                const dropdownToggle =
                    dropdown.querySelector(".dropdown-toggle");

                if (dropdownToggle) {
                    dropdownToggle.addEventListener("click", function(e) {
                        // Only for mobile view
                        if (window.innerWidth <= 768) {
                            e.preventDefault();
                            e.stopPropagation(); // Prevent event bubbling

                            // Toggle active class for this dropdown
                            dropdown.classList.toggle("active");

                            // Close other dropdowns
                            dropdowns.forEach((otherDropdown) => {
                                if (
                                    otherDropdown !== dropdown &&
                                    otherDropdown.classList.contains(
                                        "active"
                                    )
                                ) {
                                    otherDropdown.classList.remove(
                                        "active"
                                    );
                                }
                            });
                        }
                    });
                }
            });

            // Add ripple effect to chat toggles
            const chatToggles = document.querySelectorAll(".chat-toggle");

            chatToggles.forEach((toggle) => {
                toggle.addEventListener("click", function(e) {
                    // Add ripple effect
                    const x =
                        e.clientX - e.target.getBoundingClientRect().left;
                    const y =
                        e.clientY - e.target.getBoundingClientRect().top;

                    const ripple = document.createElement("span");
                    ripple.classList.add("ripple");
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;

                    this.appendChild(ripple);

                    setTimeout(function() {
                        ripple.remove();
                    }, 600);
                });
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
                anchor.addEventListener("click", function(e) {
                    // Skip for dropdown toggles on mobile
                    if (
                        this.classList.contains("dropdown-toggle") &&
                        window.innerWidth <= 768
                    ) {
                        return;
                    }

                    e.preventDefault();

                    const targetId = this.getAttribute("href");
                    if (targetId === "#") return;

                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: "smooth",
                        });

                        // Close mobile menu after clicking a link
                        if (navLinks.classList.contains("active")) {
                            navLinks.classList.remove("active");
                            hamburger.classList.remove("active");
                        }
                    }
                });
            });

            // Add hover effects to program cards
            const programCards = document.querySelectorAll(".program-card");

            programCards.forEach((card) => {
                card.addEventListener("mouseenter", function() {
                    this.querySelector(".program-icon i").classList.add(
                        "float"
                    );
                });

                card.addEventListener("mouseleave", function() {
                    this.querySelector(".program-icon i").classList.remove(
                        "float"
                    );
                });
            });

            // Add parallax effect to page header
            window.addEventListener("scroll", function() {
                const header = document.querySelector(".page-header");
                if (header) {
                    const scrollPosition = window.pageYOffset;
                    header.style.backgroundPosition = `center ${
                            scrollPosition * 0.5
                        }px`;
                }
            });

            // Add animation to team members on hover
            const teamMembers = document.querySelectorAll(".team-member");

            teamMembers.forEach((member) => {
                member.addEventListener("mouseenter", function() {
                    this.querySelector(".team-photo img").style.transform =
                        "scale(1.1)";
                });

                member.addEventListener("mouseleave", function() {
                    this.querySelector(".team-photo img").style.transform =
                        "scale(1)";
                });
            });
        });

        function handleLogin() {
            window.location.href = "./Login/Student.php";
        }

        function handleLogout(event) {
            event.preventDefault();
            // Create a form to submit logout request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = './logout.php';
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>