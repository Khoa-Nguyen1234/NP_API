<?php
session_start();

// Xử lý đăng xuất
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: index.php');
  exit;
}
?>
<!doctype html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#1565c0" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta
    name="apple-mobile-web-app-status-bar-style"
    content="black-translucent" />
  <meta name="apple-mobile-web-app-title" content="NP Education" />
  <title>Trung Tâm Giáo Dục Tri Thức NP</title>
  <link href="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/Screenshot%202026-06-09%20221852-eFYcZs9Oh7Z8uLwh28PTSFXBYsw3v9.png" rel="icon" />
  <link rel="manifest" href="manifest.json" />
  <link
    rel="apple-touch-icon"
    href="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/Screenshot%202026-06-09%20221852-eFYcZs9Oh7Z8uLwh28PTSFXBYsw3v9.png" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@400;500;600;700&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
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

    /* Header Styles */
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
      white-space: nowrap;
      flex-shrink: 0;
    }

    .mobile-login .student-info {
      display: flex !important;
      align-items: center;
      gap: 8px;
      position: relative;
    }

    .mobile-login .student-name {
      font-size: 13px;
      padding: 8px 12px;
      white-space: normal;
    }

    /* Dropdown Menu */
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

    /* Added styles for student name display and logout button */
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

    /* Hero Section */
    .hero {
      padding: 80px 0;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: "";
      position: absolute;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle,
          rgba(33, 150, 243, 0.1) 0%,
          rgba(33, 150, 243, 0) 70%);
      border-radius: 50%;
      top: -150px;
      left: -150px;
      z-index: 0;
    }

    .hero::after {
      content: "";
      position: absolute;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle,
          rgba(33, 150, 243, 0.1) 0%,
          rgba(33, 150, 243, 0) 70%);
      border-radius: 50%;
      bottom: -200px;
      right: -200px;
      z-index: 0;
    }

    .hero-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      z-index: 1;
    }

    .hero-left {
      flex: 1;
      padding-right: 40px;
    }

    .hero-right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .hero-image {
      max-width: 100%;
      height: auto;
      max-height: 400px;
      filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.15));
      transition:
        transform 0.5s ease,
        filter 0.5s ease;
      border-radius: 10px;
    }

    .hero-image:hover {
      transform: scale(1.05) rotate(2deg);
      filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.2));
    }

    .slogan {
      font-family: "Montserrat", sans-serif;
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 30px;
      color: #1565c0;
      line-height: 1.2;
    }

    .slogan-line {
      display: block;
      margin-bottom: 10px;
      background: linear-gradient(90deg, #1565c0, #64b5f6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 2px 10px rgba(33, 150, 243, 0.2);
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.8s forwards;
    }

    .slogan-line:nth-child(2) {
      animation-delay: 0.3s;
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .quote-container {
      margin-top: 40px;
      padding: 20px;
      border-left: 4px solid #2196f3;
      background-color: rgba(255, 255, 255, 0.7);
      border-radius: 0 10px 10px 0;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
      backdrop-filter: blur(5px);
      opacity: 0;
      transform: translateX(-20px);
      animation: fadeInLeft 0.8s forwards 0.6s;
    }

    @keyframes fadeInLeft {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .quote-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .quote {
      font-size: 1.2rem;
      font-style: italic;
      color: #455a64;
      margin-bottom: 10px;
      line-height: 1.6;
    }

    .author {
      font-weight: 600;
      color: #1565c0;
      text-align: right;
      position: relative;
      display: inline-block;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .author::after {
      content: "";
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -2px;
      left: 0;
      background-color: #1565c0;
      transition: width 0.3s ease;
    }

    .author:hover {
      color: #0d47a1;
      letter-spacing: 1px;
    }

    .author:hover::after {
      width: 100%;
    }

    /* About Section */
    .about {
      padding: 80px 0;
      background-color: #fff;
      position: relative;
      overflow: hidden;
    }

    .about::before {
      content: "";
      position: absolute;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle,
          rgba(33, 150, 243, 0.05) 0%,
          rgba(33, 150, 243, 0) 70%);
      border-radius: 50%;
      top: 50px;
      right: 50px;
    }

    .section-title {
      font-family: "Montserrat", sans-serif;
      font-size: 2.5rem;
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
      background-color: #2196f3;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 2px;
    }

    .about-content {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .about-text {
      max-width: 800px;
      font-size: 1.1rem;
      line-height: 1.8;
      color: #455a64;
      text-align: justify;
      position: relative;
      z-index: 1;
    }

    .about-text p {
      margin-bottom: 20px;
      transform: translateY(20px);
      opacity: 0;
      transition: all 0.5s ease;
    }

    .about-text.animated p {
      transform: translateY(0);
      opacity: 1;
    }

    .about-text p:nth-child(1) {
      transition-delay: 0.1s;
    }

    .about-text p:nth-child(2) {
      transition-delay: 0.2s;
    }

    .about-text p:nth-child(3) {
      transition-delay: 0.3s;
    }

    .about-text ul {
      margin-left: 20px;
      margin-bottom: 20px;
      list-style: none;
      padding-left: 0;
      transform: translateY(20px);
      opacity: 0;
      transition: all 0.5s ease 0.4s;
    }

    .about-text.animated ul {
      transform: translateY(0);
      opacity: 1;
    }

    .about-text li {
      margin-bottom: 10px;
      position: relative;
      padding-left: 25px;
    }

    .about-text li::before {
      content: "\f00c";
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      color: #2196f3;
      position: absolute;
      left: 0;
      top: 0;
    }

    /* Activities Section */
    .activities {
      padding: 80px 0;
      background-color: #f5f7fa;
      position: relative;
      overflow: hidden;
    }

    .activities::after {
      content: "";
      position: absolute;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle,
          rgba(33, 150, 243, 0.05) 0%,
          rgba(33, 150, 243, 0) 70%);
      border-radius: 50%;
      bottom: -150px;
      left: -150px;
    }

    .gallery {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 40px;
      position: relative;
      z-index: 1;
    }

    .gallery-item {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      transform: translateY(50px);
      opacity: 0;
    }

    .gallery-item.animate {
      transform: translateY(0);
      opacity: 1;
    }

    .gallery-item:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      z-index: 2;
    }

    .gallery-item img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      display: block;
      transition:
        all 0.5s ease,
        opacity 0.3s ease;
    }

    .gallery-item:hover img {
      transform: scale(1.1);
    }

    .gallery-item::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom,
          rgba(0, 0, 0, 0) 70%,
          rgba(0, 0, 0, 0.7) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .gallery-item:hover::after {
      opacity: 1;
    }

    /* Gallery Controls */
    .gallery-controls {
      text-align: center;
      margin-top: 20px;
    }

    .gallery-control-btn {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      margin: 0 5px;
      transition: all 0.3s ease;
      font-family: "Roboto", sans-serif;
      font-weight: 500;
    }

    .gallery-control-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .gallery-control-btn:active {
      transform: translateY(0);
    }

    #pauseBtn {
      background: #2196f3;
      color: white;
    }

    #pauseBtn:hover {
      background: #1976d2;
    }

    #playBtn {
      background: #4caf50;
      color: white;
    }

    #playBtn:hover {
      background: #388e3c;
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
      content: "";
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
      height: 180px;
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
      content: "";
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

    /* Floating particles */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 0;
    }

    .particle {
      position: absolute;
      display: block;
      pointer-events: none;
      width: 6px;
      height: 6px;
      background-color: rgba(33, 150, 243, 0.2);
      border-radius: 50%;
      animation: float 15s infinite linear;
    }

    @keyframes float {
      0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
      }

      100% {
        transform: translateY(-1000px) rotate(720deg);
        opacity: 0;
      }
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .hero-content {
        flex-direction: column-reverse;
      }

      .hero-left,
      .hero-right {
        width: 100%;
        padding: 0;
        text-align: center;
      }

      .hero-right {
        margin-bottom: 40px;
      }

      .slogan {
        font-size: 2.5rem;
      }

      .quote-container {
        max-width: 600px;
        margin: 40px auto 0;
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

      /* Mobile styles for student info */
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
        /* Overrides the absolute positioning */
        left: 50%;
        /* Centers the menu */
        transform: translateX(-50%) translateY(10px);
        /* Centers and adds slight downward offset */
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
        margin-left: 0;
        padding: 10px 0;
      }

      .nav-links li:first-child {
        margin-left: 0;
      }

      /* Desktop login button - hidden */
      .desktop-login {
        display: none;
      }

      /* Mobile login button - shown */
      .mobile-login {
        display: block;
        width: 100%;
      }

      .mobile-login .student-name {
        width: 100%;
        display: block;
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
        font-size: 20px;
        padding: 12px 20px;
        text-align: center;
        /* Allow text to wrap instead of overflow */
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        font-size: 18px;
        padding: 10px 15px;
      }

      .dropdown-toggle i {
        transition: transform 0.3s ease;
        font-size: 14px;
      }

      .dropdown.active .dropdown-toggle i {
        transform: rotate(180deg);
      }

      .slogan {
        font-size: 2rem;
      }

      .gallery {
        grid-template-columns: repeat(auto-fill,
            minmax(250px, 1fr));
      }

      .chat-widgets {
        bottom: 30px;
        right: 30px;
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

      .slogan {
        font-size: 1.8rem;
      }

      .quote {
        font-size: 1rem;
      }

      .section-title {
        font-size: 2rem;
      }

      .about-text {
        font-size: 1.1rem;
      }

      .gallery {
        grid-template-columns: 1fr;
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

      .slogan {
        font-size: 1.5rem;
      }

      .quote {
        font-size: 0.9rem;
      }

      .section-title {
        font-size: 1.8rem;
      }

      .about-text {
        font-size: 1.1rem;
        line-height: 1.7;
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

      .gallery-item img {
        height: 200px;
      }
    }

    /* Extra small devices (below 360px) */
    @media (max-width: 360px) {
      .slogan {
        font-size: 1.3rem;
        margin-bottom: 15px;
      }

      .quote-container {
        margin-top: 20px;
        padding: 15px;
      }

      .section-title {
        font-size: 1.5rem;
        margin-bottom: 30px;
      }

      .chat-toggle {
        width: 55px;
        height: 55px;
      }

      .fb-toggle {
        font-size: 25px;
      }

      .zalo-icon {
        width: 30px;
        height: 30px;
      }
    }

    /* Landscape mode adjustments */
    @media (max-height: 600px) and (orientation: landscape) {
      .hero {
        padding: 30px 0;
      }

      .hero-image {
        max-height: 300px;
      }

      .slogan {
        font-size: 1.8rem;
        margin-bottom: 15px;
      }

      .about,
      .activities {
        padding: 40px 0;
      }

      .section-title {
        margin-bottom: 30px;
      }
    }

    /* Improved touch targets for mobile */
    @media (hover: none) and (pointer: coarse) {

      .nav-links a,
      .btn-login,
      .chat-toggle,
      .gallery-item {
        min-height: 44px;
        min-width: 44px;
      }

      .social-icons a {
        width: 48px;
        height: 48px;
      }
    }

    /* High DPI screens - improve sharpness */
    @media (-webkit-min-device-pixel-ratio: 2),
    (min-resolution: 192dpi) {

      .hamburger span,
      .dropdown-toggle i {
        will-change: transform;
      }
    }

    /* Accessibility - prefers reduced motion */
    @media (prefers-reduced-motion: reduce) {
      * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
      body {
        background-color: #1a1a1a;
        color: #e0e0e0;
      }

      header {
        background-color: #2a2a2a;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
      }

      .nav-links a {
        color: #e0e0e0;
      }

      .about {
        background-color: #2a2a2a;
      }

      .activities {
        background-color: #1a1a1a;
      }

      .dropdown-menu {
        background-color: #2a2a2a;
      }

      .dropdown-menu a {
        color: #e0e0e0;
      }

      .quote-container {
        background-color: rgba(0, 0, 0, 0.3);
      }

      .section-title {
        color: #64b5f6;
      }

      .about-text {
        color: #c0c0c0;
      }
    }
  </style>
</head>

<body>
  <header>
    <div class="container">
      <div class="logo">
        <img
          src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/Screenshot%202026-06-09%20221852-eFYcZs9Oh7Z8uLwh28PTSFXBYsw3v9.png"
          alt="Trung Tâm Giáo Dục Tri Thức NP" />
      </div>
      <nav>
        <ul class="nav-links">
          <li>
            <a href="index.php" class="active">Trang chủ</a>
          </li>
          <li><a href="./About.html">Giới thiệu</a></li>
          <li><a href="./news-student.html">Tin tức</a></li>
          <li><a href="./student_recite.html">Báo bài</a></li>
          <li><a href="#activities">Hoạt động học sinh</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle">Thời khóa biểu
              <i class="fas fa-chevron-down"></i>
            </a>

            <ul class="dropdown-menu">
              <li><a href="#">Lớp 6</a></li>
              <li><a href="#">Lớp 7</a></li>
              <li><a href="#">Lớp 8</a></li>
              <li><a href="#">Lớp 9</a></li>
              <li><a href="#">Lớp 10</a></li>
              <li><a href="#">Lớp 11</a></li>
              <li><a href="#">Lớp 12</a></li>
            </ul>
          </li>
          <li>
            <a href="attendance.php">Điểm danh</a>
          </li>
          <li>
            <a href="./TeacherNewsStudentView.html">Luyện đề</a>
          </li>
          <!-- Mobile login button or student info -->
          <li class="mobile-login" id="mobileLoginContainer">
            <?php if (!empty($_SESSION['student_ho_ten'])): ?>
              <div class="student-info" style="margin-left: 0; justify-content: center;">
                <div class="student-name">
                  <?= htmlspecialchars($_SESSION['student_ho_ten']) ?> (<?= htmlspecialchars($_SESSION['student_ma_hs']) ?>)
                </div>
                <div class="logout-menu" style="right: auto; left: 50%; transform: translateX(-50%); min-width: 180px;">
                  <a href="logout.php" style="text-align: center;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                  </a>
                </div>
              </div>
            <?php else: ?>
              <button class="btn-login" id="mobileLoginBtn">
                Đăng nhập
              </button>
            <?php endif; ?>
          </li>
        </ul>
        <!-- Desktop login button or student info -->
        <div
          class="login-btn desktop-login"
          id="desktopLoginContainer">
          <?php if (!empty($_SESSION['student_ho_ten'])): ?>
            <div class="student-info" style="margin-left: 0;">
              <div class="student-name">
                <?= htmlspecialchars($_SESSION['student_ho_ten']) ?> (<?= htmlspecialchars($_SESSION['student_ma_hs']) ?>)
              </div>
              <div class="logout-menu">
                <a href="?logout=1">
                  <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
              </div>
            </div>
          <?php else: ?>
            <button class="btn-login" id="desktopLoginBtn">
              Đăng nhập
            </button>
          <?php endif; ?>
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

  <section class="hero">
    <div class="particles"></div>
    <div class="container">
      <div class="hero-content">
        <div class="hero-left">
          <h1 class="slogan">
            <span class="slogan-line">Thầy dạy dễ hiểu</span>
            <span class="slogan-line">Trò học hiệu quả</span>
          </h1>
          <div class="quote-container">
            <p class="quote">
              "Thầy giáo bình thường giải quyết những rắc
              rối,<br />
              Thầy giáo có năng khiếu tiết lộ sự đơn giản."
            </p>
            <p class="author">Robert Braunt</p>
          </div>
        </div>
        <div class="hero-right">
          <img
            src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/banner1-7rQxLFlfijVkvCxzCXogJilKp5vnhD.png"
            alt="Trung Tâm Giáo Dục Tri Thức NP"
            class="hero-image" />
        </div>
      </div>
    </div>
  </section>

  <section id="about" class="about">
    <div class="container">
      <h2 class="section-title" data-aos="fade-up">Giới thiệu</h2>
      <div class="about-content">
        <div class="about-text">
          <p>
            Xuất phát điểm từ một trung tâm luyện thi nhỏ được
            thành lập năm 2018 với tên gọi ban đầu là Trung tâm
            Tri Thức NP. Ban đầu, Trung tâm chỉ gồm một vài lớp
            luyện thi Đại học môn Lý và Hóa. Sau 7 năm kể từ
            ngày thành lập, NP đã cố gắng rất nhiều để trở thành
            một Gia Đình xứng đáng với tình cảm của các Quí PH
            và các bạn HS.
          </p>
          <p>
            Hiện nay, bên cạnh các chương trình Bồi dưỡng văn
            hóa, Luyện thi Tuyển sinh 10 và Luyện thi TN THPT đã
            đạt được nhiều thành tích đáng kể, NP còn có các
            chương trình học thu hút được rất nhiều quan tâm của
            mọi người:
          </p>
          <ul>
            <li>Bồi dưỡng và Luyện thi Đánh giá năng lực</li>
            <li>Bồi dưỡng và Luyện thi IELTS</li>
            <li>
              Bồi dưỡng môn Khoa Học Tự Nhiên khối 6 7 8 9
            </li>
            <li>Bồi dưỡng học sinh giỏi cấp II, cấp III</li>
          </ul>
          <p>
            Trong thời gian sắp tới, NP sẽ cố gắng hoàn thiện
            mình hơn nữa để nâng cao chất lượng giảng dạy, hoàn
            thiện cơ sở vật chất và hệ thống làm việc để làm hài
            lòng tất cả Quí PH và các bạn HS đã yêu thương, tin
            tưởng NP ❤.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section id="activities" class="activities">
    <div class="container">
      <h2 class="section-title" data-aos="fade-up">
        Hoạt động học tập
      </h2>
      <div class="gallery" id="galleryContainer">
        <div class="gallery-item" data-index="0">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+1"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="1">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+2"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="2">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+3"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="3">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+4"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="4">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+5"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="5">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+6"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="6">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+7"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="7">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+8"
            alt="Hoạt động học tập" />
        </div>
        <div class="gallery-item" data-index="8">
          <img
            src="https://via.placeholder.com/400x300?text=Hình+ảnh+9"
            alt="Hoạt động học tập" />
        </div>
      </div>

      <!-- Navigation controls -->
      <div
        class="gallery-controls"
        style="text-align: center; margin-top: 20px">
        <button
          id="pauseBtn"
          class="gallery-control-btn"
          style="
                            padding: 10px 20px;
                            background: #2196f3;
                            color: white;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            font-size: 16px;
                            margin: 0 5px;
                        ">
          <i class="fas fa-pause"></i> Tạm dừng
        </button>
        <button
          id="playBtn"
          class="gallery-control-btn"
          style="
                            padding: 10px 20px;
                            background: #4caf50;
                            color: white;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            font-size: 16px;
                            margin: 0 5px;
                            display: none;
                        ">
          <i class="fas fa-play"></i> Tiếp tục
        </button>
      </div>
    </div>
  </section>

  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-logo">
          <img
            src="https://sf-static.upanhlaylink.com/img/image_2026061078329f1e7a5c58cae2c01b7f71db09a1.jpg"
            alt="Trung Tâm Giáo Dục Tri Thức NP" />
        </div>
        <div class="footer-info">
          <h3>Trung Tâm Giáo Dục Tri Thức NP</h3>
          <p>
            <i class="fas fa-map-marker-alt"></i> Địa chỉ: số
            7-11A-54-78-112 Đường số 2, Cư xá Đô Thành, P4, Q3
          </p>
          <p>
            <i class="fas fa-phone"></i> Điện thoại: 0976969028
            - 0906969028
          </p>
          <p>
            <i class="fas fa-envelope"></i> Email:
            trungtamtrithucnp@gmail.com
          </p>
        </div>
        <div class="footer-social">
          <h3>Kết nối với chúng tôi</h3>
          <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
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
        <img
          src="./img/icon/zaloicon.png"
          alt="Zalo"
          class="zalo-icon" />
      </a>
    </div>
  </div>

  <!-- AOS JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Initialize AOS
      AOS.init({
        duration: 800,
        easing: "ease-in-out",
        once: true,
      });

      // Create floating particles
      const particlesContainer = document.querySelector(".particles");
      if (particlesContainer) {
        for (let i = 0; i < 20; i++) {
          createParticle(particlesContainer);
        }
      }

      function createParticle(container) {
        const particle = document.createElement("span");
        particle.classList.add("particle");

        // Random position
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        particle.style.left = posX + "%";
        particle.style.top = posY + "%";

        // Random size
        const size = Math.random() * 5 + 3;
        particle.style.width = size + "px";
        particle.style.height = size + "px";

        // Random opacity
        particle.style.opacity = Math.random() * 0.5 + 0.3;

        // Random animation duration and delay
        const duration = Math.random() * 20 + 10;
        const delay = Math.random() * 5;
        particle.style.animation = `float ${duration}s ${delay}s infinite linear`;

        container.appendChild(particle);
      }

      // Header scroll effect
      const header = document.querySelector("header");
      window.addEventListener("scroll", function() {
        if (window.scrollY > 50) {
          header.classList.add("scrolled");
        } else {
          header.classList.remove("scrolled");
        }
      });

      // Animate about text when in viewport
      const aboutText = document.querySelector(".about-text");
      const aboutObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add("animated");
            }
          });
        }, {
          threshold: 0.2,
        },
      );

      if (aboutText) {
        aboutObserver.observe(aboutText);
      }

      // Animate gallery items when in viewport
      const galleryItems = document.querySelectorAll(".gallery-item");
      const galleryObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              const delay =
                entry.target.getAttribute("data-delay") ||
                0;
              setTimeout(() => {
                entry.target.classList.add("animate");
              }, delay);
            }
          });
        }, {
          threshold: 0.1,
        },
      );

      galleryItems.forEach((item) => {
        galleryObserver.observe(item);
      });

      // Gallery Auto-Slide Logic
      const totalImages = galleryItems.length;
      if (totalImages > 0) {
        let currentStartIndex = 0;
        let isPlaying = true;
        let slideInterval;

        function updateGallery() {
          const items =
            document.querySelectorAll(".gallery-item");
          items.forEach((item, index) => {
            const newIndex =
              (currentStartIndex + index) % totalImages;
            item.setAttribute("data-index", newIndex);
          });
          currentStartIndex =
            (currentStartIndex + 1) % totalImages;
        }

        function startSlideshow() {
          slideInterval = setInterval(updateGallery, 4000);
          isPlaying = true;
          document.getElementById("pauseBtn").style.display =
            "inline-block";
          document.getElementById("playBtn").style.display =
            "none";
        }

        function pauseSlideshow() {
          clearInterval(slideInterval);
          isPlaying = false;
          document.getElementById("pauseBtn").style.display =
            "none";
          document.getElementById("playBtn").style.display =
            "inline-block";
        }

        // Event listeners cho nút điều khiển
        const pauseBtn = document.getElementById("pauseBtn");
        const playBtn = document.getElementById("playBtn");

        if (pauseBtn) {
          pauseBtn.addEventListener("click", pauseSlideshow);
        }

        if (playBtn) {
          playBtn.addEventListener("click", startSlideshow);
        }

        // Bắt đầu slideshow tự động
        startSlideshow();

        // Tạm dừng khi rời khỏi tab
        document.addEventListener("visibilitychange", function() {
          if (document.hidden) {
            if (isPlaying) {
              pauseSlideshow();
            }
          }
        });
      }

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

      // Mobile Dropdown Menu
      const dropdowns = document.querySelectorAll(".dropdown");

      dropdowns.forEach((dropdown) => {
        const dropdownToggle =
          dropdown.querySelector(".dropdown-toggle");

        if (dropdownToggle) {
          dropdownToggle.addEventListener("click", function(e) {
            // Only for mobile view
            if (window.innerWidth <= 768) {
              e.preventDefault();
              e.stopPropagation();

              // Toggle active class for this dropdown
              dropdown.classList.toggle("active");

              // Close other dropdowns
              dropdowns.forEach((otherDropdown) => {
                if (
                  otherDropdown !== dropdown &&
                  otherDropdown.classList.contains(
                    "active",
                  )
                ) {
                  otherDropdown.classList.remove(
                    "active",
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

      // Author hover effect
      const author = document.querySelector(".author");

      if (author) {
        author.addEventListener("mouseenter", function() {
          this.style.letterSpacing = "1px";
          this.style.transition = "all 0.3s ease";
        });

        author.addEventListener("mouseleave", function() {
          this.style.letterSpacing = "normal";
        });
      }

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

            // Add active class to clicked link
            document
              .querySelectorAll(".nav-links a")
              .forEach((link) => {
                link.classList.remove("active");
              });
            this.classList.add("active");
          }
        });
      });

      // Add active class to nav links based on scroll position
      window.addEventListener("scroll", function() {
        const sections = document.querySelectorAll("section[id]");
        const scrollPosition = window.scrollY;

        sections.forEach((section) => {
          const sectionTop = section.offsetTop - 150;
          const sectionHeight = section.offsetHeight;
          const sectionId = section.getAttribute("id");

          if (
            scrollPosition >= sectionTop &&
            scrollPosition < sectionTop + sectionHeight
          ) {
            document
              .querySelector(
                `.nav-links a[href="#${sectionId}"]`,
              )
              ?.classList.add("active");
          } else {
            document
              .querySelector(
                `.nav-links a[href="#${sectionId}"]`,
              )
              ?.classList.remove("active");
          }
        });
      });

      // Image lazy loading
      if ("IntersectionObserver" in window) {
        const imgOptions = {
          threshold: 0.1,
          rootMargin: "0px 0px 100px 0px",
        };

        const imgObserver = new IntersectionObserver(
          (entries, observer) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.getAttribute("data-src");

                if (src) {
                  img.src = src;
                  img.removeAttribute("data-src");
                }

                observer.unobserve(img);
              }
            });
          },
          imgOptions,
        );

        document
          .querySelectorAll("img[data-src]")
          .forEach((img) => {
            imgObserver.observe(img);
          });
      }
    });

    function handleLogin() {
      window.location.href = "./student_login.php";
    }

    // Xử lý sự kiện click nút đăng nhập
    document.addEventListener('DOMContentLoaded', function() {
      const desktopLoginBtn = document.getElementById('desktopLoginBtn');
      const mobileLoginBtn = document.getElementById('mobileLoginBtn');

      if (desktopLoginBtn) {
        desktopLoginBtn.addEventListener('click', handleLogin);
      }

      if (mobileLoginBtn) {
        mobileLoginBtn.addEventListener('click', handleLogin);
      }
    });

    // Service Worker Registration for PWA
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker
        .register("sw.js")
        .then((registration) => {
          console.log("Service Worker registered:", registration);
        })
        .catch((error) => {
          console.log(
            "Service Worker registration failed:",
            error,
          );
        });
    }
  </script>
</body>

</html>