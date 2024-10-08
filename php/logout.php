<?php
session_start();
session_destroy(); // ทำลาย session
header("Location: /index.php"); // เปลี่ยนเส้นทางกลับไปหน้าหลัก
exit();
?>
