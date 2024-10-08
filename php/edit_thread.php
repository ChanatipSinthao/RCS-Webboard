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
        header("Location: /php/view_thread.php?username=" . urlencode($username) . "&thrid=" . urlencode($thrid));
        exit();
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

// ตรวจสอบการอัปเดตข้อมูลกระทู้
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTitle = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $newContent = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    $newTags = htmlspecialchars($_POST['tags'], ENT_QUOTES, 'UTF-8');

    // อัปเดตข้อมูลใน selectedThread
    $selectedThread['title'] = $newTitle;
    $selectedThread['content'] = $newContent;
    $selectedThread['tags'] = $newTags;

    // อัปเดตไฟล์ JSON
    $threads[array_search($selectedThread, $threads)] = $selectedThread;
    file_put_contents($userJsonFilePath, json_encode($threads, JSON_PRETTY_PRINT));

    // เปลี่ยนเส้นทางกลับไปยังหน้า view_thread หลังจากบันทึกเสร็จ
    header("Location: /php/view_thread.php?username=" . urlencode($username) . "&thrid=" . urlencode($thrid));
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Thread</title>
    <link rel="stylesheet" href="/css/v_threads.css"> <!-- ใช้สไตล์เดียวกับ view_thread -->
    <style>
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #000000;
            color: #ccc;
            border: 1px solid #585858;
            border-radius: 20px;
        }

        .edit-title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #ccc;
        }

        .edit-thread-form input,
        .edit-thread-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #585858;
            border-radius: 10px;
            background-color: #080808;
            color: #ccc;
            font-size: 16px;
        }

        .edit-thread-form textarea {
            height: 150px;
            resize: vertical;
        }

        .edit-thread-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        .edit-thread-form button:hover {
            background-color: #0056b3;
            transition: background-color 0.3s ease;
        }

        .dark-mode .edit-container {
            background-color: #f9f9f9;
            color: #2e2e2e;
        }

        .dark-mode .edit-title {
            color: #2e2e2e;
        }

        .dark-mode .edit-thread-form input {
            background-color: #f9f9f9;
            color: #2e2e2e;
        }

        .dark-mode .edit-thread-form textarea {
            background-color: #f9f9f9;
            color: #2e2e2e;
        }

    </style>
</head>
<body>
    <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>

    <section class="section-container">
        <div class="edit-container">
            <h2 class="edit-title">แก้ไขกระทู้</h2>
            <form action="" method="POST" class="edit-thread-form">
                <label for="title">หัวข้อ</label>
                <input type="text" id="title" name="title" value="<?php echo $title; ?>" required>

                <label for="content">เนื้อหา</label>
                <textarea id="content" name="content" required><?php echo $content; ?></textarea>

                <label for="tags">แท็ก</label>
                <input type="text" id="tags" name="tags" value="<?php echo $tags; ?>">

                <button type="submit">บันทึกการแก้ไข</button>
            </form>
        </div>
    </section>
</body>
</html>
