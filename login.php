<?php
session_start();

//  if (isset($_SESSION['user_id'])) {
//     header("Location: profile.php");
//     exit();
// }
include 'connection.php'; 

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] == "student"  ) {
        header("Location: profile.php");
        exit();
    }  else if ($_SESSION['user_type'] == "parent"){
        header("Location: profileparent.php");
    }else if ($_SESSION['user_type'] == "faculty"){
        header("Location: teacher/profilefaculty.php");
    }
}
 
$error = "";

 if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // فلترة المدخلات
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password_input = trim($_POST['password']);

    if (!$email || empty($password_input)) {
        $error = "يرجى إدخال البريد الإلكتروني وكلمة المرور بشكل صحيح.";
    } else {
        // إعداد الاستعلام
        $stmt = $conn->prepare("SELECT id, first_name, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // التحقق من وجود المستخدم
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // التحقق من صحة كلمة المرور
            if (password_verify($password_input, $user['password'])) {
                // تسجيل الدخول بنجاح - تخزين البيانات في SESSION
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_type'] = $user['user_type'];

                // إعادة التوجيه للملف الشخصي
                if ($user['user_type'] == "student") {
                    header("Location: profile.php"); // الطالب
                } else if ($user['user_type'] == "parent") {

                    
                    header("Location: profileparent.php"); // ولي الأمر
                }else if($user['user_type'] == "faculty"){
                     header("Location: teacher/profilefaculty.php");// و عضو هيئة تدريس

                }
                else{
                     header("Location: login.php"); // ولي الأمر أو عضو هيئة تدريس

                }
                exit();

             } else {
                $error = "  كلمة المرور غير صحيحة.";
            }
        } else {
            $error = "  البريد الإلكتروني غير مسجل.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
/* ====== التنسيق الأصلي ====== */
body { margin:0; padding:0; font-family:Arial,sans-serif; background:url('./image.jpg') no-repeat center center fixed; background-size:cover; }
.topbar { background-color: rgba(59,59,59,0.9); color:white; padding:12px 30px; display:flex; justify-content:space-between; align-items:center; }
.topbar .logo { font-style:italic; font-weight:bold; font-size:18px; }
.navbar { background-color: rgba(85,85,85,0.9); padding:10px 30px; }
.navbar ul { list-style:none; display:flex; gap:20px; margin:0; padding:0; }
.navbar ul li a { color:white; text-decoration:none; font-size:14px; }
.navbar ul li a:hover { text-decoration:underline; }
.login-box { background: rgba(255, 255, 255, 0.95); width: 350px; margin: 60px auto; padding: 30px; border-radius: 10px; box-shadow: 0px 4px 15px rgba(0,0,0,0.5); text-align: center; }
.login-box h2 { margin-bottom:20px; font-size:20px; font-weight:bold; }
.login-box input { width:90%; padding:12px; margin:8px 0; border:none; border-radius:20px; background-color:#f5b987; font-size:14px; outline:none; }
.login-box button { width:95%; padding:12px; background-color:#3b3b3b; color:white; border:none; border-radius:20px; margin-top:15px; cursor:pointer; font-size:15px; }
.login-box button:hover { background-color:#555; }
.login-box p { margin-top:15px; font-size:14px; }
.login-box p a { color:blue; text-decoration:none; }
.login-box p a:hover { text-decoration:underline; }
.error { color:red; margin-bottom:10px; }
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

<div class="login-box">
  <h2>Login</h2>
  <?php if($error != "") { echo "<div class='error'>$error</div>"; } ?>
  <form method="POST">
    <input type="email" name="email" placeholder="Email address" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
  <p><a href="register.php">Create new account</a></p>
</div>

</body>
</html>