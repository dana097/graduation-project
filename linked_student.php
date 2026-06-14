<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'parent') {
    header("Location: login.php");
    exit();
}

$parent_id = $_SESSION['user_id'];
// آخر 10 إشعارات
$user_id=$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, message, timestamp, status FROM notification WHERE recipient=? ORDER BY timestamp DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
  }
// جلب جميع الطلاب المرتبطين بالولي
$stmt = $conn->prepare("
    SELECT u.id ,u.first_name, u.last_name, u.email, sg.status
    FROM student_guardian sg
    JOIN users u ON sg.student_id = u.id
    WHERE sg.guardian_id = ? 
    ORDER BY u.first_name ASC
");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>All Linked Students - Academic Organizer</title>
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

.profile-icon, .notification-icon { width: 24px; height: 24px; border-radius: 50%; background-color: white; background-repeat: no-repeat; background-position: center; background-size: 16px 16px; cursor: pointer; }
.profile-icon { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E"); }
.notification-icon { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 2C10.9 2 10 2.9 10 4v1.09c-3.39.49-6 3.39-6 6.91v4l-2 2v1h20v-1l-2-2v-4c0-3.52-2.61-6.42-6-6.91V4c0-1.1-.9-2-2-2zM12 22c1.1 0 2-.9 2-2H10c0 1.1.9 2 2 2z'/%3E%3C/svg%3E"); }
 

.students-container { background: rgba(255, 255, 255, 0.88); width: 60%; max-width: 650px; margin: 60px auto; padding: 25px 30px; border-radius: 10px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.4); }
.students-container h2 { color: #222; margin-bottom: 20px; text-align: center; }
.students-box { background-color: #e2c0a8; padding: 20px 25px; border-radius: 10px; box-shadow: 0 0 8px rgba(0,0,0,0.2); }
.students-box p { margin: 8px 0; color: #333; line-height: 1.6; }
.students-box strong { color: #111; }
.students-list { margin-top: 10px; margin-bottom: 15px; }
.student-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.student-row span { font-size: 15px; }
button ,.select{ background-color: #b89078; border: none; color: white; border-radius: 6px; padding: 8px 20px; cursor: pointer; font-size: 15px; font-weight: bold; transition: 0.3s; }
button:hover { background-color: #9d7863; }
.ok-btn { display: block; margin: 20px auto 0; padding: 8px 25px; }
.status-accepted { color: green; font-weight: bold; }
.status-pending { color: orange; font-weight: bold; }
.status-rejected { color: red; font-weight: bold; }
@media (max-width: 768px) { .students-container { width: 90%; padding: 15px; } }
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

<div class="students-container">
  <h2>All Linked Students</h2>
  <div class="students-box">
    <p><strong>Your linked students:</strong></p>
    <div class="students-list">
      <?php if(count($students) > 0): ?>
        <?php foreach($students as $student): ?>
            <div class="student-row">
              <span>
                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?> 
                <?php if($student['status'] == 'accepted'): ?>
                  <span class="status-accepted">✅</span>
                <?php elseif($student['status'] == 'pending'): ?>
                  <span class="status-pending">⏳</span>
                <?php else: ?>
                  <span class="status-rejected">❌</span>
                <?php endif; ?>
              </span>

              <?php if($student['status'] == 'accepted'): ?>
                <a class="select" href="student_performance.php?student_id=<?= urlencode($student['id']) ?>">Select</a>
              <?php endif; ?>
            </div>
        <?php endforeach; ?>

      <?php else: ?>
        <p>No linked students found.</p>
      <?php endif; ?>
    </div>
    <!-- <button class="ok-btn">Ok</button> -->
  </div>
</div>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>
