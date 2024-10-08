<?php
// ตั้งค่าความปลอดภัยและการจัดการ session
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'lifetime' => 1800]);
session_start();

// ตรวจสอบว่า session username และ role มีอยู่และเป็น user หรือไม่
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
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

// ดึงข้อมูล username, email, และ role จากตาราง users
$username = $_SESSION['username'];  // อ้างอิง username จาก session
$sql = "SELECT username, email, role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $username = htmlspecialchars($user_data['username'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($user_data['email'], ENT_QUOTES, 'UTF-8');
    $role = htmlspecialchars($user_data['role'], ENT_QUOTES, 'UTF-8');
} else {
    echo "No user found.";
    exit();
}

// ปิดการเชื่อมต่อฐานข้อมูล
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCS - ข้อมูลของคุณ</title>
    <link rel="stylesheet" href="/css/t_threads.css">
<style>
    h3 {
        color: white;
    }

    .user-status {
        color: white;
        margin: 10px;
    }

    .user-sub-status {
        color: #007bff;
        margin-left: 10px;
    }

    .dark-mode .user-status {
        color: #3f3f3f;
    }

    .dark-mode h3 {
        color: #3f3f3f;
    }
</style>
</head>
<body>
    <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>
    <section class="section-container">
        <div class="form-container">
            <h3>ข้อมูลของคุณ</h3>
            <p class="user-status"><strong>ชื่อสมาชิก</strong><a class="user-sub-status"> <?php echo $username; ?></a> </p>
            <p class="user-status"><strong>อีเมล</strong><a class="user-sub-status"> <?php echo $email; ?></a></p>
            <p class="user-status"><strong>สถานะ</strong><a class="user-sub-status"> <?php echo $role; ?></a></p>
        </div>
    </section>
</body>
</html>
