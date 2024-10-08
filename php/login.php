<?php
session_start();

// ตั้งค่าการเชื่อมต่อกับ MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forum_db";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// รับข้อมูลจากฟอร์ม
$email_input = $_POST['email'];
$pass_input = $_POST['password'];

// ตรวจสอบอีเมล
if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('รูปแบบอีเมลไม่ถูกต้อง'); window.location.href='/user/page/login.php';</script>";
    exit();
}

// ใช้ Prepared Statements เพื่อตรวจสอบ email
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];

    // ตรวจสอบรหัสผ่าน
    if (password_verify($pass_input, $hashed_password)) {
        session_regenerate_id(true); // ป้องกัน Session fixation
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];

        // เปลี่ยนเส้นทางไปยังหน้าหลักหลังจากล็อกอินสำเร็จ
        header("Location: /user/page/home.php"); // ใช้ / แทน \
        exit();
    } else {
        echo "<script>alert('รหัสผ่านไม่ถูกต้อง!'); window.location.href='/user/page/login.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ไม่ค้นพบผู้ใช้งานนี้!'); window.location.href='/user/page/login.php';</script>";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
