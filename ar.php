<?php
session_start();
include 'connection.php';
 

?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>النسخة العربية - الصفحة الرئيسية</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      direction: rtl;
      background: url("./image.jpg") no-repeat center center fixed;
      background-size: cover;
    }

    .topbar {
      background-color: rgba(59,59,59,0.9);
      color: white;
      padding: 12px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar .logo {
      font-style: italic;
      font-weight: bold;
      font-size: 18px;
    }

    .topbar .auth-links a {
      color: white;
      margin-right: 20px;
      text-decoration: none;
      font-weight: bold;
    }

    .topbar .auth-links a:hover {
      text-decoration: underline;
    }

    .navbar {
      background-color: rgba(85,85,85,0.9);
      padding: 10px 30px;
    }

    .navbar ul {
      list-style: none;
      display: flex;
      gap: 20px;
      margin: 0;
      padding: 0;
      justify-content: flex-start;
    }

    .navbar ul li a {
      color: white;
      text-decoration: none;
      font-size: 14px;
    }

    .navbar ul li a:hover {
      text-decoration: underline;
    }

    .ar-box {
      background: rgba(255, 255, 255, 0.85);
      width: 550px;
      margin: 60px auto;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
      text-align: right;
    }

    .ar-box h1 {
      margin-bottom: 20px;
      font-size: 24px;
      font-weight: bold;
      text-align: center;
    }

    .ar-box p {
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 15px;
    }

    .ar-box a {
      color: #f5b987;
      text-decoration: none;
    }

    .ar-box a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- الشريط العلوي -->
 
<div class="topbar">
        <div class="logo">المنظم الأكاديمي</div>
    <div class="auth-links">
        <?php if (isset($_SESSION['user_id'])): ?>
       <a href="logout.php">تسجيل خروج</a>
        <?php else: ?>
            <a href="login.php">تسجيل دخول</a>
            <a href="register.php">تسجيل </a>
        <?php endif; ?>
    </div>
</div>
 
  <!-- الشريط الثاني -->
  <div class="navbar">
    <ul>
      <li><a href="ar.php">AR</a></li>
      <li><a href="home.php">الرئيسية</a></li>
      <li><a href="about.php">من نحن</a></li>
      <li><a href="contact.php">تواصل معنا</a></li>
    </ul>
  </div>

  <!-- صندوق المحتوى -->
  <div class="ar-box">
    <h1>مرحباً بكم في النسخة العربية</h1>
    <p>نحن في "المنظم الأكاديمي" نسعى لتوفير منصة تساعد الطلاب على تنظيم جداولهم الدراسية وإدارة مهامهم الأكاديمية بسهولة.</p>
    <p>يمكنك تسجيل الدخول للوصول إلى جميع الأدوات المتقدمة، أو إنشاء حساب جديد إذا لم يكن لديك حساب بعد.</p>
    <p>استكشف الأقسام المختلفة للموقع عبر الشريط العلوي، وتواصل معنا في حال وجود أي استفسارات.</p>
    <p>ارجع إلى <a href="home.php">الصفحة الرئيسية</a> أو تصفح <a href="about.php">من نحن</a> لمعرفة المزيد عن خدماتنا.</p>
  </div>

</body>
</html>
