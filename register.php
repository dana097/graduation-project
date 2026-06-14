<?php
session_start();
include 'connection.php';

 
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password_input = $_POST['password'];
    $user_type = $_POST['user_type']  ;
      echo "<div>  '$user_type'</div>";
    if (!$email) {
        $error = "بريد إلكتروني غير صالح.";
    } else {
       

        if (empty($error)) {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "البريد الإلكتروني مسجل مسبقًا.";
            } else {
                $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, user_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $user_type);

                if ($stmt->execute()) { 

                  if ($user_type == "student" || $user_type == "parent" || $user_type == "faculty") {
                      header("Location: login.php");
                  } 
                  else {
                      header("Location: register.php");
                  }
                  exit();
              }


                $stmt->close();
            }

            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
<style>
      body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background: url('./image.jpg') no-repeat center center fixed;
        background-size: cover;
      }
      .topbar {
        background-color: rgba(59, 59, 59, 0.9);
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
      .navbar {
        background-color: rgba(85, 85, 85, 0.9);
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
      .register-box {
        background: rgba(255, 255, 255, 0.85);
        width: 350px;
        margin: 60px auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.5);
        text-align: center;
      }
      .register-box h2 {
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: bold;
      }
      .register-box input[type="text"],
      .register-box input[type="email"],
      .register-box input[type="password"] {
        width: 90%;
        padding: 12px;
        margin: 8px 0;
        border: none;
        border-radius: 20px;
        background-color: #f5b987;
        font-size: 14px;
        outline: none;
      }
      .role-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        justify-content: center;
      }
      .role-buttons input[type="radio"] {
        display: none;
      }
      .role-buttons label {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        background-color: #3b3b3b;
        color: white;
        cursor: pointer;
        font-size: 14px;
        text-align: center;
      }
      .role-buttons input[type="radio"]:checked + label {
        background-color: #f5b987;
        color: #000;
        font-weight: bold;
      }
      .role-buttons label:hover {
        background-color: #555;
      }
      .register-box button {
        width: 95%;
        padding: 12px;
        background-color: #3b3b3b;
        color: white;
        border: none;
        border-radius: 20px;
        margin-top: 15px;
        cursor: pointer;
        font-size: 15px;
      }
      .register-box button:hover {
        background-color: #555;
      }
      .register-box p {
        margin-top: 15px;
        font-size: 14px;
      }
      .register-box p a {
        color: #f5b987;
        text-decoration: none;
      }
      .register-box p a:hover {
        text-decoration: underline;
      }
      .error {
        color: red;
        margin-bottom: 10px;
      }
  </style>
</head>
<body>

<div class="topbar">
  <div class="logo">Academic Organizer</div>
</div>

<div class="navbar">
  <ul>
    <li><a href="ar.php">AR</a></li>
    <li><a href="about.php">About</a></li>
    <li><a href="contact.php">Contact Us</a></li>
  </ul>
</div>

<div class="register-box">
  <h2>CREATE NEW ACCOUNT</h2>
  <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>
  <form method="POST" action="">
    <input type="text" name="first_name" placeholder="Enter first name" required>
    <input type="text" name="last_name" placeholder="Enter last name" required>
    <input type="email" name="email" placeholder="Enter your email" required>
    <input type="password" name="password" placeholder="Enter the password" required>

    <div class="role-buttons">
      <input type="radio" id="student" name="user_type" value="student" required>
      <label for="student">Student</label>

      <input type="radio" id="parent" name="user_type" value="parent">
      <label for="parent">Parent</label>

      <input type="radio" id="faculty" name="user_type" value="faculty">
      <label for="faculty">Faculty</label>
    </div>

    <button type="submit">Register</button>
  </form>

  <p>You have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>
