<?php
session_start();

if (isset($_SESSION['username'])) {
    // ถ้ามีการ Login แล้ว ให้เปลี่ยนเส้นทางไปที่หน้า home
    header("Location: /user/page/home.php");
    exit(); // หยุดการทำงานของสคริปต์หลังจากเปลี่ยนเส้นทาง
} 

$threads = [];

// อ่านกระทู้จากไฟล์ JSON ของทุก username
$files = glob($_SERVER['DOCUMENT_ROOT'] . '/php/assets/threads/*_threads.json');

foreach ($files as $file) {
    // ใช้ @ เพื่อไม่ให้เกิดข้อผิดพลาดที่แสดงในกรณีที่ไฟล์ไม่สามารถอ่านได้
    $jsonData = @file_get_contents($file); 
    if ($jsonData !== false) {
        $userThreads = json_decode($jsonData, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $threads = array_merge($threads, $userThreads);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCS - รับชมกระทู้</title>
    <link rel="stylesheet" href="/css/t_threads.css">
</head>
<body>
    <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>
    <section class="section-container">
        <!-- Box สำหรับ Recent Threads -->
        <div class="threads-container">
            <h2>กระทู้ในขณะนี้</h2>
            <ul class="threads-list">
                <?php 
                // กำหนดค่าพื้นฐานสำหรับการแบ่งหน้า
                $threadsPerPage = 20; // จำนวนกระทู้ต่อหน้า
                $currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1; // หน้าปัจจุบัน
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
</body>
</html>
