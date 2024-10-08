<?php
session_start();

// เช็คการล็อกอิน
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// รับข้อมูลจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // เก็บข้อมูลกระทู้ในรูปแบบ JSON
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tags = $_POST['tags'];
    $username = $_SESSION['username'];

    // เวลาปัจจุบัน
    $current_time = date('Y-m-d H:i:s');

    // สร้างกระทู้ใหม่
    $new_thread = [
        "id" => uniqid(), // สร้าง ID ที่ไม่ซ้ำกัน
        "title" => $title,
        "content" => $content,
        "tags" => $tags,
        "username" => $username,
        "created_at" => $current_time // เพิ่มเวลาปัจจุบัน
    ];    

    // สร้างเส้นทางไฟล์ JSON สำหรับ username นั้นๆ
    $userJsonFilePath = 'assets/threads/' . $username . '_threads.json';

    // ตรวจสอบว่ามีโฟลเดอร์อยู่หรือไม่ ถ้าไม่มีให้สร้าง
    if (!file_exists('assets/threads')) {
        mkdir('assets/threads', 0777, true);
    }

    // อ่านกระทู้เดิมจากไฟล์ JSON ถ้ามี
    $threads = [];
    if (file_exists($userJsonFilePath)) {
        $threads = json_decode(file_get_contents($userJsonFilePath), true);
    }

    // เพิ่มกระทู้ใหม่ลงในอาเรย์
    $threads[] = $new_thread;

    // บันทึกข้อมูลกลับไปยังไฟล์ JSON
    file_put_contents($userJsonFilePath, json_encode($threads, JSON_PRETTY_PRINT));

    // เปลี่ยนเส้นทางกลับไปที่หน้า home
    header("Location: /user/page/home.php");
    exit();
}
?>
