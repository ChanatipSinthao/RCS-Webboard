<?php
session_start();

// ตรวจสอบว่าเป็น admin หรือไม่
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$dbname = "forum_db";
$username_db = "root"; // ชื่อผู้ใช้ฐานข้อมูล
$password_db = "";     // รหัสผ่านฐานข้อมูล

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่าได้ส่ง username มาหรือไม่
if (isset($_POST['username'])) {
    $username_to_delete = $conn->real_escape_string($_POST['username']);
    
    // ลบข้อมูลผู้ใช้จากฐานข้อมูล
    $sql = "DELETE FROM users WHERE username='$username_to_delete'";
    
    if ($conn->query($sql) === TRUE) {
        // ลบคอมเมนต์จากไฟล์ JSON อื่น ๆ
        $thread_files = glob("C:\\xampp\\htdocs\\Project_webboard\\php\\assets\\threads\\*_threads.json");

        foreach ($thread_files as $thread_file) {
            if (file_exists($thread_file)) {
                // อ่านเนื้อหาของไฟล์ JSON
                $json_data = file_get_contents($thread_file);
                $data = json_decode($json_data, true);

                // เช็คว่ามีคอมเมนต์จากผู้ใช้ที่กำลังจะถูกลบหรือไม่
                $has_comments = false;
                foreach ($data as &$thread) {
                    // ตรวจสอบว่ามีคอมเมนต์จากผู้ใช้ที่ถูกลบ
                    if (isset($thread['comments'])) {
                        $original_comment_count = count($thread['comments']);
                        // ลบคอมเมนต์ของผู้ใช้ที่ถูกลบ
                        $thread['comments'] = array_filter($thread['comments'], function($comment) use ($username_to_delete) {
                            return $comment['username'] !== $username_to_delete;
                        });
                        // ตรวจสอบว่ามีการลบคอมเมนต์เกิดขึ้น
                        if (count($thread['comments']) < $original_comment_count) {
                            $has_comments = true;
                        }
                    }
                }

                // ถ้ามีการลบคอมเมนต์ จะทำการเขียนข้อมูลกลับไปยังไฟล์ JSON
                if ($has_comments) {
                    file_put_contents($thread_file, json_encode($data, JSON_PRETTY_PRINT));
                }
            }
        }

        // กำหนดเส้นทางของไฟล์ JSON ที่เกี่ยวข้อง
        $json_file = "C:\\xampp\\htdocs\\Project_webboard\\php\\assets\\threads\\{$username_to_delete}_threads.json";
        
        if (file_exists($json_file)) {
            if (unlink($json_file)) { // ลบไฟล์
                $response = ['success' => true, 'message' => 'ลบผู้ใช้และไฟล์ JSON ที่เกี่ยวข้องเรียบร้อยแล้ว!'];
            } else {
                $response = ['success' => false, 'message' => 'ไม่สามารถลบไฟล์ JSON ได้'];
            }
        } else {
            $response = ['success' => true, 'message' => 'ลบผู้ใช้เรียบร้อย แต่ไม่มีไฟล์ JSON ที่เกี่ยวข้อง'];
        }
    } else {
        $response = ['success' => false, 'message' => "เกิดข้อผิดพลาดในการลบผู้ใช้: " . $conn->error];
    }
} else {
    $response = ['success' => false, 'message' => "ไม่มีชื่อผู้ใช้ที่ส่งมา"];
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// คืนค่าผลลัพธ์เป็น JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
