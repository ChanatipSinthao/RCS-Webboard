<?php
// ตั้งค่าความปลอดภัยและการจัดการ session
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'lifetime' => 1800]);
session_start();

// ตรวจสอบว่า session username และ role มีอยู่และเป็น admin หรือไม่
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

// ดึงข้อมูล username, email, และ role ของทุกคนจากตาราง users
$sql = "SELECT username, email, role FROM users";
$result = $conn->query($sql);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $users_data = [];
    while ($row = $result->fetch_assoc()) {
        $users_data[] = [
            'username' => htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'),
            'role' => htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8')
        ];
    }
} else {
    echo "No users found.";
    exit();
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCS - ผู้ดูแลระบบ</title>
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/t_threads.css">
    <link rel="stylesheet" href="/css/admin_account.css">
    <style>
        /* การตกแต่ง CSS เช่นเดิม */
        /* ... */
    </style>
</head>
<body>
    <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>
    <section class="section-container">
        <div class="form-container">
            <h3>ตั้งค่าผู้ใช้งาน</h3>
            <?php if (!empty($users_data)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อผู้ใช้งาน</th>
                            <th>อีเมล</th>
                            <th>ตำแหน่ง</th>
                            <th>จัดการ</th> <!-- คอลัมน์สำหรับปุ่มลบ -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_data as $user): ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td>
                                    <?php if ($user['username'] !== $_SESSION['username']): // ตรวจสอบว่าไม่ใช่ผู้ใช้ตัวเอง ?>
                                        <button class="delete-button" data-username="<?php echo $user['username']; ?>">ลบ</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </section>

    <script>
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function() {
                const username = this.getAttribute('data-username');
                if (confirm(`คุณแน่ใจหรือว่าต้องการลบผู้ใช้ ${username}?`)) {
                    fetch('/php/delete_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'username': username,
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('ลบผู้ใช้และไฟล์ JSON ที่เกี่ยวข้องเรียบร้อยแล้ว!');
                            location.reload(); // โหลดหน้าต่อไปเพื่อให้เห็นการเปลี่ยนแปลง
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
</body>
</html>
