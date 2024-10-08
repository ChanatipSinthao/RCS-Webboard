<?php
// ตั้งค่าให้ cookie ใช้เฉพาะ HTTP และหมดอายุหลังจาก 30 นาที
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 1800);

// ตั้งค่า session cookie parameters
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax', // หรือ 'Strict' ถ้าต้องการให้ปลอดภัยยิ่งขึ้นในบางกรณี
    'lifetime' => 1800,  // 30 นาที
]);

session_start();

// ตรวจสอบว่ามี session username หรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: /index.php"); // เปลี่ยนเส้นทางไปยังหน้า index.php ถ้าไม่มี session
    exit();
}

// ตรวจสอบว่า token อยู่ใน session หรือไม่ (สำหรับการใช้งานในอนาคต)
if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
}

// ตรวจสอบว่า username ใน session ถูกต้องหรือไม่
if (isset($_SESSION['username'])) {
    // ป้องกัน session fixation
    session_regenerate_id(true);
    $username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
    $threads = [];

    // อ่านไฟล์ JSON ของกระทู้
    $files = glob($_SERVER['DOCUMENT_ROOT'] . '/php/assets/threads/*_threads.json');

    foreach ($files as $file) {
        // ตรวจสอบว่าชื่อไฟล์ถูกต้องตามรูปแบบที่ต้องการ
        if (!preg_match('/^[a-zA-Z0-9_\-]+_threads\.json$/', basename($file))) {
            continue;
        }

        // ตรวจสอบว่าชื่อไฟล์มาจากไดเรกทอรีที่ถูกต้องเพื่อป้องกัน Directory Traversal
        $allowedDirectory = $_SERVER['DOCUMENT_ROOT'] . '/php/assets/threads/';
        if (strpos(realpath($file), realpath($allowedDirectory)) !== 0) {
            continue;
        }

        // อ่านข้อมูล JSON  จากไฟล์
        $jsonData = file_get_contents($file);
        if ($jsonData !== false) {
            $userThreads = json_decode($jsonData, true);

            // ตรวจสอบความสมบูรณ์ของข้อมูล JSON
            if (json_last_error() === JSON_ERROR_NONE && is_array($userThreads)) {
                $threads = array_merge($threads, $userThreads);
            }
        } else {
            // บันทึก log ข้อผิดพลาดถ้าไม่สามารถอ่านไฟล์ได้
            error_log("ไม่สามารถอ่านไฟล์: " . $file);
        }
    }

    // ตรวจสอบความถูกต้องของ session username อีกครั้ง
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $_SESSION['username'])) {
        unset($_SESSION['username']);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="/css/t_threads.css">
</head>
<body>
    <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>
    <section class="section-container">

        <!-- Box สำหรับ Create a New Thread -->
        <div class="form-container">
            <h2>สร้างกระทู้</h2>
            <form action="/php/create_thread.php" method="POST">
                <input type="text" name="title" placeholder="หัวข้อ" required>
                <textarea name="content" placeholder="เนื้อหากระทู้" required oninput="autoResize(this)"></textarea>
                <input type="text" name="tags" placeholder="แท็ก">
                <button type="submit">บันทึกกระทู้</button>
            </form>
        </div>

        <!-- Box สำหรับ Recent Threads -->
        <div class="threads-container">
            <h2>กระทู้ในขณะนี้</h2>
            <ul class="threads-list">
                <?php 
                // กำหนดค่าพื้นฐานสำหรับการแบ่งหน้า
                $threadsPerPage = 20; // จำนวนกระทู้ต่อหน้า
                $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1; // หน้าปัจจุบัน
                $totalThreads = count($threads); // จำนวนกระทู้ทั้งหมด
                $totalPages = ceil($totalThreads / $threadsPerPage); // จำนวนหน้าทั้งหมด

                // จัดเรียง $threads ตาม created_at จากใหม่ไปเก่า
                usort($threads, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });

                // คำนวณตำแหน่งเริ่มต้นและตำแหน่งสิ้นสุดสำหรับกระทู้ที่จะแสดง
                $start = ($currentPage - 1) * $threadsPerPage;
                $end = min($start + $threadsPerPage, $totalThreads);

                // ตรวจสอบว่ามีกระทู้ที่จะแสดงหรือไม่
                if ($start < $totalThreads): 
                    for ($i = $start; $i < $end; $i++): 
                        $thread = $threads[$i]; ?>
                        <li>
                            <strong>
                                <a href="/php/view_thread.php?username=<?php echo urlencode($thread['username']); ?>&thrid=<?php echo urlencode($thread['id']); ?>" class="thread-link">
                                    <?php echo htmlspecialchars($thread['title']); ?>
                                </a>
                            </strong>
                            <small>แท็ก <?php echo htmlspecialchars($thread['tags']); ?></small>
                            <small>โพสต์โดย <?php echo htmlspecialchars($thread['username']); ?></small>
                            <small>เวลา <?php echo htmlspecialchars($thread['created_at']); ?></small>
                        </li>
                    <?php endfor; 
                else: ?>
                    <p>ไม่มีกระทู้ในขณะนี้</p>
                <?php endif; ?>
            </ul>

            <!-- แสดงปุ่ม pagination -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>" class="prev">ก่อนหน้า</a>
                <?php endif; ?>
                
                <!-- แสดงหมายเลขหน้าทั้งหมด -->
                <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                    <?php if ($page == $currentPage): ?>
                        <span class="current-page"><?php echo $page; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>" class="next">ถัดไป</a>
                <?php endif; ?>
            </div>
        </div>

    </section>
    <script>
        function autoResize(textarea) {
            textarea.style.height = 'auto'; 
            textarea.style.height = (textarea.scrollHeight) + 'px'; 
        }
    </script>
</body>
</html>
