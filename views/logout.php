<?php
session_start();

// Xóa toàn bộ session
session_unset();
session_destroy();

// Chuyển về trang đăng nhập
header("Location: index.php");
exit();
