<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "parent") {
    header("Location: login.php");
    exit();
}


if (isset($_GET['incomplete'])):
    echo '<p class="success-message" style="display:block; background-color:orange; color:#000;">
        يرجى تعبئة بيانات   المدينة، والحي قبل المتابعة.
    </p>';
endif;

$user_id = $_SESSION['user_id'];
// آخر 10 إشعارات
 
$stmt = $conn->prepare("SELECT id, message, timestamp, status FROM notification WHERE recipient=? ORDER BY timestamp DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
  }
// جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();
//user

 
// حفظ التعديلات
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateprofile'])) {
     
    $first_name  = $_POST['first_name'];
    $last_name   = $_POST['last_name'];
     $city        = $_POST['city'];
    $town        = $_POST['town'];

    // $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // تحديث البيانات العامة
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?,  city=?, town=? WHERE id=?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $city, $town, $user_id);
    $stmt->execute();
    $stmt->close();

    // تحديث كلمة المرور إذا تم تغييرها
    if (!empty($new_password) || !empty($confirm_password)) {
             if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
                $stmt->close();
                $success_message = "Profile and password updated successfully!";
            } else {
                $error_message = "New password and confirm password do not match!";
            }
        
    } else {
        $success_message = "Profile updated successfully!";
    }

    // إعادة جلب البيانات بعد التحديث
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();
}

 


 

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Organizer - Parent Profile</title>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: Arial, sans-serif;
        background: url("image.jpg") no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
    }
    .topbar {
        background-color: rgba(59,59,59,0.9);
        color:white;
        padding:12px 30px;
        display:flex;
        justify-content:space-between;
        align-items:center;
    }
    .topbar .logo { font-style:italic; font-weight:bold; font-size:18px; }
    .topbar .auth-links a { color:white; text-decoration:none; font-weight:bold; margin-left:20px; }
    .topbar .auth-links a:hover { text-decoration:underline; }

    .navbar {
        background-color: rgba(85,85,85,0.9);
        padding:10px 30px;
    }
    .navbar ul {
        list-style:none;
        display:flex;
        gap:20px;
        margin:0;
        padding:0;
        align-items:center;
    }
    .navbar ul li:first-child { margin-right:5px; } 
    .navbar ul li a { color:white; text-decoration:none; font-size:14px; display:flex; align-items:center; }
    .navbar ul li a:hover { text-decoration:underline; }

    .profile-icon {
        width:24px;
        height:24px;
        background-color:white;
        border-radius:50%;
        cursor:pointer;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
        background-size:16px 16px;
        background-repeat:no-repeat;
        background-position:center;
    }
    .profile-container {
        background: rgba(255,255,255,0.85);
        width:400px;
        margin:60px auto;
        padding:30px;
        border-radius:10px;
        box-shadow:0px 4px 15px rgba(0,0,0,0.5);
        text-align:center;
    }
    .profile-container h2 {
        margin-bottom:20px;
        font-size:18px;
        font-weight:bold;
        color:#333;
    }
    .form-group { margin-bottom:15px; text-align:left; }
    .form-label { display:block; font-weight:bold; font-size:14px; color:#333; margin-bottom:5px; }
    .form-input {
        width:100%;
        padding:12px;
        border:none;
        border-radius:20px;
        background-color:#f5b987;
        font-size:14px;
        outline:none;
    }
    .form-input::placeholder { color:#666; }
    .form-row { display:flex; gap:10px; } 
    .save-button {
        width:100%;
        padding:12px;
        background-color:#3b3b3b;
        color:white;
        border:none;
        border-radius:20px;
        margin-top:15px;
        cursor:pointer;
        font-size:15px;
    }
    .save-button:hover { background-color:#555; }
    .success-message {
        background-color:#4CAF50;
        color:white;
        padding:10px;
        border-radius:5px;
        margin-bottom:15px;
        display:none;
    }
    @media(max-width:768px){
        .topbar { padding:10px 15px; flex-direction:column; gap:10px; }
        .navbar { padding:10px 15px; }
        .navbar ul { flex-wrap:wrap; gap:10px; }
        .profile-container { width:90%; padding:20px; }
        .form-row { flex-direction:column; gap:0; }
    }

        
        /* Popup background */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Popup container */
        .popup-container {
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            font-family: 'Segoe UI', sans-serif;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .popup-container h3 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
            text-align: center;
        }

     

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .accept_button, .reject_button {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .accept_button {
            background-color: #28a745;
            color: white;
        }

        .reject_button {
            background-color: #dc3545;
            color: white;
        }
    
    
</style>
</head>
<body>
    
<div class="topbar">
    <div class="logo">Academic Organizer</div>
    <div class="auth-links">
        <a>Hello , <?php  echo $_SESSION['user_name'] ?></a>
        <a href="logout.php">Logout</a>

    </div>
</div>

  
<?php if ($_SESSION['user_type'] == "parent"): ?>
    <div class="navbar">
        <ul>
            <li>
                <a href="profileparent.php" title="Profile">
                    <div class="profile-icon"></div>
                </a>
            </li>
         
            <li><a href="home.php">Home</a></li>
            
            <li><a href="parent_invitation.php">Invites</a></li>
            <li><a href="linked_student.php">All Linked Students</a></li>
            <li><a href="student_performance.php">Track Student Performance</a></li>
            <li><a href="report_statistics.php">Report and Statistics</a></li>
                <li style="position:relative;">
      <a href="#" id="notifToggle" title="Notifications">
        <i class="fas fa-bell"></i>
        <span id="notifCount" class="notif-count"></span>
      </a>
      <div id="notifDropdown" class="notif-dropdown">
        <ul id="notifList"></ul>
      </div>
    </li>
         </ul> 
    </div>
 <?php endif; ?>



 


<div class="profile-container"> 
<h2>Parent Profile</h2>
<?php if(isset($success_message)) echo "<p class='success-message' style='display:block;'>{$success_message}</p>"; ?>
<?php if(isset($error_message)) echo "<p class='success-message' style='display:block; background-color:red;'>{$error_message}</p>"; ?>

<form method="POST">
    <div class="form-group">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required>
    </div>

    <div class="form-group">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($profile['last_name']); ?>" required>
    </div>
 

<div class="form-row">
    <div class="form-group">
        <label class="form-label">City <span style="color:red;">*</span></label>
        <input type="text" name="city" class="form-input" value="<?php echo htmlspecialchars($profile['city']); ?>" required>
    </div>
    <div class="form-group">
        <label class="form-label">Town <span style="color:red;">*</span></label>
        <input type="text" name="town" class="form-input" value="<?php echo htmlspecialchars($profile['town']); ?>" required>
    </div>
</div>


    <hr>

    <div class="form-group">
        
    </div>


    <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-input" placeholder="Enter new password">
    </div>

    <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password">
    </div>

    <button type="submit" class="save-button" name="updateprofile">Save</button>
</form>
</div>
 
<script>
document.querySelectorAll('.navbar a').forEach(link => {
    link.addEventListener('click', function(e) {
        const city = document.querySelector('input[name="city"]').value.trim();
        const town = document.querySelector('input[name="town"]').value.trim();

        // التحقق من أن الحقول ليست فارغة
        if (city === "" || town === "") {
            e.preventDefault(); // منع الانتقال
            alert("يرجى تعبئة بيانات المدينة والحي قبل المتابعة.");
        }
    });
});
</script>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>




