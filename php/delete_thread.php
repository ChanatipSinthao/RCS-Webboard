<?php
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลผ่าน GET หรือไม่
if (isset($_GET['username']) && isset($_GET['thrid'])) {
    $username = htmlspecialchars($_GET['username'], ENT_QUOTES, 'UTF-8');
    $thrid = htmlspecialchars($_GET['thrid'], ENT_QUOTES, 'UTF-8');

    // ตรวจสอบว่าผู้ใช้ปัจจุบันเป็นเจ้าของกระทู้หรือไม่ หรือเป็น admin
    if ($_SESSION['username'] !== $username && $_SESSION['role'] !== 'admin') {
        header("Location: /user/page/home.php");
        exit();
    }

    // สร้างเส้นทางไฟล์ JSON สำหรับ username ที่เกี่ยวข้อง
    $userJsonFilePath = 'assets/threads/' . $username . '_threads.json';

    // อ่านข้อมูลกระทู้จากไฟล์ JSON
    if (file_exists($userJsonFilePath)) {
        $threads = json_decode(file_get_contents($userJsonFilePath), true);

        // ค้นหากระทู้ที่ตรงกับ thrid ที่ส่งมา
        $threadIndex = null;
        foreach ($threads as $index => $thread) {
            if ($thread['id'] === $thrid) {
                $threadIndex = $index;
                break;
            }
        }

        // ถ้าพบกระทู้ที่เลือก
        if ($threadIndex !== null) {
            // ลบกระทู้
            array_splice($threads, $threadIndex, 1);

            // อัพเดตไฟล์ JSON
            file_put_contents($userJsonFilePath, json_encode($threads, JSON_PRETTY_PRINT));

            // หลังจากลบเสร็จ เปลี่ยนเส้นทางไปหน้า home.php
            header("Location: /user/page/home.php");
            exit();
        } else {
            // ถ้าไม่พบกระทู้ที่เลือก
            header("Location: /user/page/home.php");
            exit();
        }
    } else {
        // ถ้าไม่พบไฟล์ JSON
        header("Location: /user/page/home.php");
        exit();
    }
} else {
    // ถ้าไม่มีข้อมูลใน GET
    header("Location: /user/page/home.php");
    exit();
}
?>
