<head>
    <!-- เพิ่มลิงก์สำหรับ Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<header class="navbar">
    <div class="menu-toggle" id="menu-toggle" aria-expanded="false" aria-controls="navbar-menu">
        &#9776; <!-- ปุ่ม 3 ขีด -->
    </div>
    
    <ul class="menu" id="navbar-menu">
        <div class="left-items" id="left-items">
            <li><a href="/index.php">หน้าแรก</a></li>
            <li><a href="/user/page/threads.php">เว็บบอร์ด</a></li>
            <li>
                <a href="#" id="mode-toggle" class="mode-btn"><i class="fas fa-moon"></i></a>
            </li>
        </div>

        <?php if(isset($_SESSION['username'])): ?>
            <li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/admin/myaccount.php">สวัสดี, <?php echo $_SESSION['username']; ?></a>
                <?php else: ?>
                    <a href="/user/account/myaccount.php">สวัสดี, <?php echo $_SESSION['username']; ?></a>
                <?php endif; ?>
            </li>
            <li><a href="/php/logout.php">ออกจากระบบ</a></li>
        <?php else: ?>
            <li><a href="/user/page/signup.php" class="btn">สมัครสมาชิก</a></li>
            <li><a href="/user/page/login.php" class="btn">เข้าสู่ระบบ</a></li>
        <?php endif; ?>
    </ul>
</header>

<style>
    @import url('https://fonts.googleapis.com/css?family=Sarabun');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Sarabun', sans-serif;
    }

    .navbar {
        height: 70px;
        line-height: 70px;
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: black;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #585858;
    }

    .navbar ul {
    list-style: none;
    padding: 0; /* ลบการเว้นระยะที่ไม่จำเป็น */
    }

    .navbar li {
        list-style: none; /* ลบจุดกลมจาก <li> */
    }

    .menu {
        display: flex;
        gap: 20px;
        width: 100%;
        justify-content: flex-start;
    }

    .menu-toggle {
        display: none;
        font-size: 30px;
        cursor: pointer;
        color: white;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        font-size: 16px;
        transition: color 0.3s;
    }

    .navbar a.btn {
        padding: 10px 20px;
        border: 2px solid #585858;
        border-radius: 20px;
        font-weight: 600;
        transition: background-color 0.3s, color 0.3s;
    }

    .navbar a.btn:hover {
        background-color: white;
        color: black;
    }

    .left-items {
        display: flex;
        gap: 20px;
        margin-right: auto;
    }

    .mode-btn {
        background-color: #333; /* สีพื้นหลังสำหรับ Light Mode */
        color: white; /* สีตัวอักษรสำหรับ Light Mode */
        padding: 10px 15px;
        border-radius: 5px;
        transition: background-color 0.3s, transform 0.3s; /* เพิ่มการเปลี่ยนแปลง */
        display: inline-flex;
        align-items: center;
    }

    .mode-btn:hover {
        background-color: white; /* สีพื้นหลังเมื่อ Hover */
        color: black;
        transition: background-color 0.3s, color 0.3s;
    }

    body.dark-mode .mode-btn {
        background-color: #d4d4d4; /* สีพื้นหลังสำหรับ Dark Mode */
        color: #333; /* สีตัวอักษรสำหรับ Dark Mode */
    }

    body.dark-mode .mode-btn:hover {
        background-color: #000000; /* สีพื้นหลังเมื่อ Hover ใน Dark Mode */
        color: white;
    }

    /* Dark Mode styles */
    body.dark-mode {
        background-color: #f9f9f9;
        color: white;
    }

    .dark-mode .navbar {
        background-color: #f9f9f9;
    }

    .dark-mode .navbar a {
        color: black;
    }

    .dark-mode .navbar a.btn {
        border-color: #2e2c2c;
    }

    .dark-mode .navbar a.btn:hover {
        background-color: #2e2c2c;
        color: white;
    }

    .dark-mode .menu-toggle {
        color: black;
    }

    .dark-mode .navbar {
        color: white;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .menu-toggle {
            display: block;
        }

        .navbar a.btn {
        border: 2px solid black;
        }

        .menu {
            display: none;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            background-color: black;
            border-bottom: 1px solid #585858;
            position: absolute;
            top: 70px;
            left: 0;
            z-index: 999;
        }

        .mode-btn {
            text-align: center;
        }

        .dark-mode .menu {
            background-color: #f9f9f9;
        }

        .dark-mode .navbar a.btn {
        border: 2px solid #f9f9f9;
        }

        .menu.show {
            display: flex;
        }

        .menu li {
            width: 100%;
            text-align: left;
        }

        .menu li a {
            margin-left: 20px;
            display: block;
        }

        .left-items {
            display: none;
        }

        .left-items.show {
            display: flex;
            flex-direction: column;
        }
    }
</style>

<script>
    // ฟังก์ชันสลับไอคอนระหว่าง Dark Mode และ Light Mode
    function updateModeIcon() {
        const modeToggle = document.getElementById("mode-toggle");
        if (document.body.classList.contains("dark-mode")) {
            modeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            modeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    }

    // โหลดสถานะ Dark Mode จาก Local Storage เมื่อเปิดหน้า
    document.addEventListener("DOMContentLoaded", function() {
        // ตรวจสอบสถานะ Dark Mode จาก Local Storage
        const isDarkMode = localStorage.getItem("darkMode");
        if (isDarkMode === "enabled") {
            document.body.classList.add("dark-mode");
        }

        updateModeIcon();

        // ฟังก์ชันสลับโหมด Dark Mode
        const modeToggle = document.getElementById("mode-toggle");
        modeToggle.addEventListener("click", function(event) {
            event.preventDefault();
            document.body.classList.toggle("dark-mode");

            // เก็บสถานะใน Local Storage
            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "enabled");
            } else {
                localStorage.setItem("darkMode", "disabled");
            }

            updateModeIcon();
        });

        // ฟังก์ชัน toggle สำหรับปุ่ม 3 ขีด
        document.getElementById("menu-toggle").addEventListener("click", function() {
            var menu = document.getElementById("navbar-menu");
            var leftItems = document.getElementById("left-items");

            menu.classList.toggle("show");
            leftItems.classList.toggle("show");

            var expanded = this.getAttribute("aria-expanded") === "true" || false;
            this.setAttribute("aria-expanded", !expanded);
        });
    });
</script>


