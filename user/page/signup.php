<?php
session_start(); // เริ่ม Session

// ตรวจสอบว่า Session ของผู้ใช้มีอยู่แล้วหรือไม่
if (isset($_SESSION['username'])) {
    // ถ้ามีการ Login แล้ว ให้เปลี่ยนเส้นทางไปที่หน้า home
    header("Location: /user/page/home.php");
    exit(); // หยุดการทำงานของสคริปต์หลังจากเปลี่ยนเส้นทาง
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RCS - สมัครสมาชิก </title>

  <!-- CSS -->
  <link rel="stylesheet" href="/css/login.css">

  <!-- Boxicons CSS -->
  <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <?php include '/xampp/htdocs/Project_webboard/assets/template/header.php'; ?>
  <section class="container forms">
    <div class="form login">
      <div class="form-content">
        <header class="header-btn">สมัครสมาชิก</header>
        <form action="/php/signup.php" method="POST">
          <div class="field input-field">
            <input type="text" name="username" placeholder="ชื่อสมาชิก" id="username" class="input" required>
          </div>

          <div class="field input-field">
            <input type="text" name="email" placeholder="อีเมล" id="email" class="input" required>
          </div>

          <div class="field input-field">
            <input type="password" name="password" placeholder="รหัสผ่าน" class="password" required>
            <i class='bx bx-hide eye-icon'></i>
          </div>

          <div class="field button-field">
            <button type="submit">สมัครสมาชิก</button>
          </div>
        </form>
      </div>

      <div class="line"></div>

    </div>
  </section>
  <script>
    const forms = document.querySelector(".forms"),
    pwShowHide = document.querySelectorAll(".eye-icon"),
    links = document.querySelectorAll(".link");

    // Add click event listener to each eye icon for toggling password visibility
    pwShowHide.forEach(eyeIcon => {
        eyeIcon.addEventListener("click", () => {
            let pwFields = eyeIcon.parentElement.parentElement.querySelectorAll(".password");

            pwFields.forEach(password => {
            if (password.type === "password") { // If password is hidden
                password.type = "text"; // Show password
                eyeIcon.classList.replace("bx-hide", "bx-show"); // Change icon to show state
                return;
            }
            password.type = "password"; // Hide password
            eyeIcon.classList.replace("bx-show", "bx-hide"); // Change icon to hide state
            });
        });
    });

    // Add click event listener to each link to toggle between forms
    links.forEach(link => {
        link.addEventListener("click", e => {
            e.preventDefault(); // Prevent default link behavior
            forms.classList.toggle("show-signup");
        });
    });
  </script>
</body>
</html>