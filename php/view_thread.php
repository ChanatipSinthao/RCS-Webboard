<?php
session_start();

// ตรวจสอบว่ามีการส่งข้อมูลผ่าน GET หรือไม่
if (isset($_GET['username']) && isset($_GET['thrid'])) {
    $username = htmlspecialchars($_GET['username'], ENT_QUOTES, 'UTF-8');
    $thrid = htmlspecialchars($_GET['thrid'], ENT_QUOTES, 'UTF-8');

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $isAdmin = true;
    } else {
        $isAdmin = false;
    }

    // สร้างเส้นทางไฟล์ JSON สำหรับ username ที่เกี่ยวข้อง
    $userJsonFilePath = 'assets/threads/' . $username . '_threads.json';

    // อ่านข้อมูลกระทู้จากไฟล์ JSON
    if (file_exists($userJsonFilePath)) {
        $threads = json_decode(file_get_contents($userJsonFilePath), true);
        $selectedThread = null;

        // ค้นหากระทู้ที่ตรงกับ thrid ที่ส่งมา
        foreach ($threads as $thread) {
            if ($thread['id'] === $thrid) {
                $selectedThread = $thread;
                break;
            }
        }

        // ถ้าพบกระทู้ที่เลือก
        if ($selectedThread) {
            $title = htmlspecialchars($selectedThread['title'], ENT_QUOTES, 'UTF-8');
            $content = htmlspecialchars($selectedThread['content'], ENT_QUOTES, 'UTF-8');
            $tags = htmlspecialchars($selectedThread['tags'], ENT_QUOTES, 'UTF-8');
            $createdAt = htmlspecialchars($selectedThread['created_at'], ENT_QUOTES, 'UTF-8');
            $comments = $selectedThread['comments'] ?? []; // อ่านคอมเม้นที่มีอยู่
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

// ตรวจสอบการส่งคอมเม้น เฉพาะผู้ที่ล็อกอิน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    if (isset($_SESSION['username'])) {
        $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
        $commentData = [
            "username" => $_SESSION['username'],
            "content" => $comment,
            "created_at" => date('Y-m-d H:i:s')
        ];

        // เพิ่มคอมเม้นลงในกระทู้ที่เลือก
        if ($selectedThread) {
            $selectedThread['comments'][] = $commentData;

            // อัพเดตไฟล์ JSON โดยอัพเดตเฉพาะกระทู้ที่ถูกเลือก
            foreach ($threads as &$thread) {
                if ($thread['id'] === $selectedThread['id']) {
                    $thread = $selectedThread; // Update selected thread
                    break;
                }
            }
            file_put_contents($userJsonFilePath, json_encode($threads, JSON_PRETTY_PRINT));
        }

        // รีเฟรชหน้าเพื่อแสดงคอมเม้นใหม่
        header("Location: /php/view_thread.php?username=" . urlencode($username) . "&thrid=" . urlencode($thrid));
        exit();
    } else {
        // ถ้าไม่ได้ล็อกอินให้รีไดเรกไปที่หน้าเข้าสู่ระบบ
        header("Location: /user/login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/css/v_threads.css">
</head>
<body>
    <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>
    <section class="section-container">
    <div class="thread-box">
        <h2 class="thread-title"><?php echo $title; ?></h2>
        <div class="thread-content">
            <p><?php echo nl2br($content); ?></p>
        </div>
        <hr class="thread-divider">
        <div class="thread-meta">
            <small>แท็ก <?php echo $tags; ?></small> |
            <small>โพสต์โดย <?php echo $username; ?></small> |
            <small>เวลา <?php echo $createdAt; ?></small>
            <?php if (isset($_SESSION['username']) && ($_SESSION['username'] === $username || $isAdmin)) : ?>
                <!-- แสดงปุ่มแก้ไขและลบกระทู้ถ้าผู้ใช้งานเป็นเจ้าของกระทู้หรือ admin -->
                <div class="thread-actions">
                    <a href="edit_thread.php?username=<?php echo $username; ?>&thrid=<?php echo $thrid; ?>" class="edit">แก้ไขกระทู้</a>
                    <a href="delete_thread.php?username=<?php echo $username; ?>&thrid=<?php echo $thrid; ?>" class="delete" onclick="return confirm('คุณต้องการลบกระทู้นี้หรือไม่ ?');">ลบกระทู้</a>
                </div>
            <?php endif; ?>
        </div>
            <a class="back-button" href="<?php echo isset($_SESSION['username']) ? '/user/page/home.php' : '/index.php'; ?>">กลับหน้าหลัก</a>
        </div>

        <!-- ฟอร์มคอมเม้น -->
        <div class="comment-section">
            <h3>แสดงความคิดเห็น</h3>
            <?php if (isset($_SESSION['username'])) : ?>
                <form action="" method="POST">
                    <textarea name="comment" placeholder="คุณกำลังคิดอะไรอยู่..." required oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"></textarea>
                    <button type="submit">บันทึกความคิดเห็น</button>
                </form>
            <?php else : ?>
                <p>โปรด <a href="/user/page/login.php" class="signup-btn">เข้าสู่ระบบ</a> เพื่อแสดงความคิดเห็น</p>
            <?php endif; ?>
        </div>

        <!-- แสดงคอมเม้น -->
        <div class="comments-list">
        <h3 class="comment-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="20" height="20" fill="currentColor">
                <path d="M208 352c114.9 0 208-78.8 208-176S322.9 0 208 0S0 78.8 0 176c0 38.6 14.7 74.3 39.6 103.4c-3.5 9.4-8.7 17.7-14.2 24.7c-4.8 6.2-9.7 11-13.3 14.3c-1.8 1.6-3.3 2.9-4.3 3.7c-.5 .4-.9 .7-1.1 .8l-.2 .2s0 0 0 0s0 0 0 0C1 327.2-1.4 334.4 .8 340.9S9.1 352 16 352c21.8 0 43.8-5.6 62.1-12.5c9.2-3.5 17.8-7.4 25.2-11.4C134.1 343.3 169.8 352 208 352zM448 176c0 112.3-99.1 196.9-216.5 207C255.8 457.4 336.4 512 432 512c38.2 0 73.9-8.7 104.7-23.9c7.5 4 16 7.9 25.2 11.4c18.3 6.9 40.3 12.5 62.1 12.5c6.9 0 13.1-4.5 15.2-11.1c2.1-6.6-.2-13.8-5.8-17.9c0 0 0 0 0 0s0 0 0 0l-.2-.2c-.2-.2-.6-.4-1.1-.8c-1-.8-2.5-2-4.3-3.7c-3.6-3.3-8.5-8.1-13.3-14.3c-5.5-7-10.7-15.4-14.2-24.7c24.9-29 39.6-64.7 39.6-103.4c0-92.8-84.9-168.9-192.6-175.5c.4 5.1 .6 10.3 .6 15.5z"/>
            </svg>
            <?php echo count($comments); ?> ความคิดเห็น
        </h3>
            <?php if (!empty($comments)) : ?>
                <?php foreach ($comments as $index => $comment) : ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span>ความคิดเห็นที่ <?php echo $index + 1; ?></span>
                        </div>
                        <div class="comment-content">
                            <p><?php echo nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')); ?></p>
                        </div>
                        <hr class="comment-divider">
                        <div class="comment-meta">
                            <strong><?php echo htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <small><?php echo htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php if (isset($_SESSION['username']) && ($_SESSION['role'] === 'admin' || $_SESSION['username'] === $comment['username'])) : ?>
                                <a href="delete_comment.php?username=<?php echo $username; ?>&thrid=<?php echo $thrid; ?>&comment_id=<?php echo $index; ?>" class="delete-btn" onclick="return confirm('คุณต้องการลบคอมเม้นนี้หรือไม่ ?');">ลบความคิดเห็น</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>ยังไม่มีความคิดเห็น...</p>
            <?php endif; ?>
        </div>
    </section>
    <script>
        // ทำให้ textarea ขยายขึ้นได้ตามเนื้อหาที่พิมพ์
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function () {
                this.style.height = 'auto'; // reset height
                this.style.height = (this.scrollHeight) + 'px'; // set new height
            });
        });
    </script>
</body>
</html>

