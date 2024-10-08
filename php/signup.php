<?php
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

// เริ่ม Session
session_start();
session_regenerate_id(true); // ป้องกัน Session fixation

// รับข้อมูลจากฟอร์ม
$user = $_POST['username'];
$pass = $_POST['password'];
$email = $_POST['email'];

// ตรวจสอบอีเมล
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('รูปแบบอีเมลไม่ถูกต้อง'); window.history.back();</script>";
    exit();
}

// ใช้ Prepared Statements เพื่อตรวจสอบ username และ email
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $user, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['username'] === $user) {
            echo "<script>alert('Username นี้ถูกใช้ไปแล้ว กรุณาเลือกชื่อผู้ใช้อื่น'); window.history.back();</script>";
        }
        if ($row['email'] === $email) {
            echo "<script>alert('Email นี้ถูกใช้ไปแล้ว กรุณาเลือก Email อื่น'); window.history.back();</script>";
        }
    }
} else {
    $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $user, $hashed_password, $email);
    
    if ($stmt->execute()) {
        // ดึงข้อมูล role ของผู้ใช้ที่เพิ่งสมัครใหม่
        $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['role'] = $row['role']; // เก็บ role ในเซสชัน
        }

        $_SESSION['token'] = bin2hex(random_bytes(32));
        $_SESSION['username'] = $user;
        echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='/user/page/home.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
