<?php
session_start();
include 'connection.php';

if (!(isset($_SESSION['user_id']) && isset($_SESSION['user_type']))) {
    // if (($_SESSION['user_type'] != "student") || ($_SESSION['user_type'] != "parent")) {
        header("Location: login.php");
        exit();
    // }  
} 
 

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home</title>
<style>
body { margin:0; padding:0; font-family:Arial,sans-serif; background:url('./image.jpg') no-repeat center center fixed; background-size:cover; }
.topbar { background-color: rgba(59,59,59,0.9); color:white; padding:12px 30px; display:flex; justify-content:space-between; align-items:center; }
.topbar .logo { font-style:italic; font-weight:bold; font-size:18px; }
.topbar .auth-links a { color:white; margin-left:20px; text-decoration:none; font-weight:bold; }
.topbar .auth-links a:hover { text-decoration:underline; }

.navbar { background-color: rgba(85,85,85,0.9); padding:10px 30px; }
.navbar ul { list-style:none; display:flex; gap:20px; margin:0; padding:0; align-items:center; }
.navbar ul li a { color:white; text-decoration:none; font-size:14px; display:flex; align-items:center; }
.navbar ul li a:hover { text-decoration:underline; }

/* أيقونة البروفايل */
.profile-icon {
    width: 24px;
    height: 24px;
    background-color: white;
    border-radius: 50%;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
    background-size: 16px 16px;
    background-repeat: no-repeat;
    background-position: center;
    margin-right: 5px;
    cursor: pointer;
}
.profile-icon:hover { background-color: #f5b987; }

.home-box { background: rgba(255,255,255,0.85); width:500px; margin:80px auto; padding:40px; border-radius:10px; box-shadow:0px 4px 15px rgba(0,0,0,0.5); text-align:center; }
.home-box h1 { margin-bottom:20px; font-size:24px; font-weight:bold; }
.home-box p { font-size:16px; line-height:1.5; }
.home-box a { color:#f5b987; text-decoration:none; font-weight:bold; }
.home-box a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="topbar">
    <div class="logo">Academic Organizer</div>
    <div class="auth-links">
        <?php if (isset($_SESSION['user_id'])): ?>
             <a>Hello , <?php  echo $_SESSION['user_name']; ?></a>
            <a href="logout.php">Log Out</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

 


<div class="navbar">
    <ul>
        <!-- أيقونة البروفايل قبل زر AR -->
        <li>
            <?php if ($_SESSION['user_type'] == "student"): ?>
                <a href="profile.php" title="Profile">
                    <div class="profile-icon"></div>
                </a> 
                <?php elseif ($_SESSION['user_type'] == "parent"):?>
                    <a href="profileparent.php" title="Profile">
                        <div class="profile-icon"></div>
                    </a> 
                <?php elseif ($_SESSION['user_type'] == "faculty"):?>
                    <a href="teacher/profilefaculty.php" title="Profile">
                        <div class="profile-icon"></div>
                    </a> 
                <?php endif?>
            
        </li>
        <li><a href="ar.php">AR</a></li>
        <li><a href="home.php">Home</a></li>
         <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>
</div>

<div class="home-box">
    <h1>Welcome <?php echo $_SESSION['user_name']; ?>!</h1>
    <p>This is your personal platform to manage your academic schedule, track your tasks, and stay organized.</p>
</div>

</body>
</html>