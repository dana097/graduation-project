<?php
// يمكنك إضافة أي معالجة PHP لاحقًا إذا احتجت
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
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
      margin-left: 20px;
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
    }

    .navbar ul li a {
      color: white;
      text-decoration: none;
      font-size: 14px;
    }

    .navbar ul li a:hover {
      text-decoration: underline;
    }

    .contact-box {
      background: rgba(255, 255, 255, 0.85);
      width: 400px;
      margin: 80px auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
      text-align: center;
    }

    .contact-box h2 {
      margin-bottom: 20px;
      font-size: 20px;
      font-weight: bold;
    }

    .contact-box p {
      font-size: 16px;
      line-height: 1.6;
    }

    .contact-box a {
      color: #f5b987;
      text-decoration: none;
      font-weight: bold;
    }

    .contact-box a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- الشريط العلوي -->
<div class="topbar">
    <div class="logo">Academic Organizer</div>
    <div class="auth-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Log Out</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>


  <!-- الشريط الثاني / القائمة -->
  <div class="navbar">
    <ul>
      <li><a href="ar.php">AR</a></li>
      <li><a href="home.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="contact.php">Contact Us</a></li>
    </ul>
  </div>

  <!-- صندوق التواصل -->
  <div class="contact-box">
    <h2>Contact Us</h2>
    <p>If you have any questions, suggestions, or need support, feel free to contact us via email:</p>
    <p>Email: <a href="mailto:academic.organizer@gmail.com">academic.organizer@gmail.com</a></p>
  </div>

</body>
</html>
