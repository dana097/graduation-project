<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Academic Organizer</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
    }

    body {
      height: 100vh;
      background: url('image.jpg') no-repeat center center / cover;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .overlay {
      background: rgba(0, 0, 0, 0.55);
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .content {
      text-align: center;
      color: #fff;
      max-width: 800px;
      padding: 20px;
    }

    .content h1 {
      font-size: 48px;
      margin-bottom: 20px;
    }

    .tagline {
      font-size: 22px;
      margin-bottom: 15px;
      line-height: 1.6;
    }

    .subtitle {
      font-size: 16px;
      margin-bottom: 35px;
      opacity: 0.9;
    }

    .buttons {
      display: flex;
      gap: 20px;
      justify-content: center;
    }

    .buttons a {
      text-decoration: none;
      padding: 12px 34px;
      border-radius: 8px;
      font-size: 18px;
      color: #fff;
      background: #7a7a7a;
      transition: 0.3s;
    }

    .buttons a:hover {
      opacity: 0.85;
    }
  </style>
</head>

<body>
  <div class="overlay">
    <div class="content">
      <h1>Welcome to Academic Organizer</h1>

      <p class="tagline">
        Organize your studies, manage your time smarter,<br>
        and achieve academic success with confidence.
      </p>

      <p class="subtitle">
        A smart platform designed to help students plan tasks, track progress,
        and stay focused throughout their academic journey.
      </p>

      <div class="buttons">
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      </div>
    </div>
  </div>
</body>
</html>
