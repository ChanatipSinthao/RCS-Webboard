<?php
session_start();

// ตรวจสอบว่ามีการส่งข้อมูลผ่าน GET หรือไม่
if (isset($_GET['username']) && isset($_GET['thrid']) && isset($_GET['comment_id'])) {
    $username = htmlspecialchars($_GET['username'], ENT_QUOTES, 'UTF-8');
    $thrid = htmlspecialchars($_GET['thrid'], ENT_QUOTES, 'UTF-8');
    $commentId = intval($_GET['comment_id']);

    // ตรวจสอบว่าเป็น admin หรือไม่
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

    // สร้างเส้นทางไฟล์ JSON สำหรับ username ที่เกี่ยวข้อง
    $userJsonFilePath = 'assets/threads/' . $username . '_threads.json';

    // อ่านข้อมูลกระทู้จากไฟล์ JSON
    if (file_exists($userJsonFilePath)) {
        $threads = json_decode(file_get_contents($userJsonFilePath), true);
        $selectedThread = null;

        // ค้นหากระทู้ที่ตรงกับ thrid ที่ส่งมา
        foreach ($threads as &$thread) {
            if ($thread['id'] === $thrid) {
                $selectedThread = &$thread;
                break;
            }
        }

        // ถ้าพบกระทู้ที่เลือก
        if ($selectedThread) {
            // ตรวจสอบว่า comment_id มีอยู่ในคอมเม้นต์หรือไม่
            if (isset($selectedThread['comments'][$commentId])) {
                // ตรวจสอบว่าเป็นเจ้าของคอมเม้นต์หรือ admin
                if ($_SESSION['username'] === $selectedThread['comments'][$commentId]['username'] || $isAdmin) {
                    // ลบคอมเมนต์
                    array_splice($selectedThread['comments'], $commentId, 1);

                    // อัปเดตไฟล์ JSON
                    file_put_contents($userJsonFilePath, json_encode($threads, JSON_PRETTY_PRINT));

                    // เปลี่ยนเส้นทางกลับไปยังหน้า view_thread
                    header("Location: /php/view_thread.php?username=" . urlencode($username) . "&thrid=" . urlencode($thrid));
                    exit();
                } else {
                    // ถ้าไม่ใช่เจ้าของคอมเมนต์และไม่ใช่ admin
                    header("Location: /user/page/home.php");
                    exit();
                }
            } else {
                // ถ้าไม่พบคอมเมนต์ที่ต้องการลบ
                header("Location: /user/page/home.php");
                exit();
            }
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
}

?>
