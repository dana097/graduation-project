<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ; // مؤقتًا للتجربة

// جلب عدد الدعوات المعلقة لهذا الطالب
$pending_count_query = $conn->query("
    SELECT COUNT(*) as count
    FROM scheduleuser
    WHERE user_id = $user_id AND shared = 0
");
$pending_count = $pending_count_query->fetch_assoc()['count'] ?? 0;

// جلب تفاصيل الإشعارات (أو نأخذ فقط الاسم المرسل)
$notifications = $conn->query("
    SELECT su.schedule_id, su.sender_id, u.first_name, u.email
    FROM scheduleuser su
    JOIN users u ON u.id = su.sender_id
    WHERE su.user_id = $user_id AND su.shared = 0
    GROUP BY su.schedule_id, su.sender_id
    ORDER BY su.id DESC
");



?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications - Academic Organizer</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial,sans-serif; background: url('https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image.jpg-ri2jUSpScDXjsulXIHz0LMu56qvFYI.jpeg') no-repeat center center fixed; background-size: cover; min-height:100vh; }
.topbar { background-color: rgba(59,59,59,0.9); color:white; padding:12px 30px; display:flex; justify-content:space-between; align-items:center; }
.topbar .logo { font-style:italic; font-weight:bold; font-size:18px; }
.topbar .auth-links a { color:white; margin-left:20px; text-decoration:none; font-weight:bold; }
.topbar .auth-links a:hover { text-decoration:underline; }
.navbar { background-color: rgba(85,85,85,0.9); padding:10px 30px; }
.navbar ul { list-style:none; display:flex; gap:20px; margin:0; padding:0; align-items:center; }
.navbar ul li a { color:white; text-decoration:none; font-size:14px; }
.navbar ul li a:hover { text-decoration:underline; }
.profile-icon { width:24px; height:24px; background-color:white; border-radius:50%; cursor:pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E"); background-size:16px 16px; background-repeat:no-repeat; background-position:center; margin-right:20px; }
.profile-icon:hover { background-color:#f5b987; }
.container { background: rgba(255,255,255,0.85); width:90%; max-width:800px; margin:40px auto; padding:30px; border-radius:10px; box-shadow:0px 4px 15px rgba(0,0,0,0.5); }
.container h2 { margin-bottom:25px; font-size:22px; font-weight:bold; color:#333; text-align:center; }
.notification-list { display:flex; flex-direction:column; gap:15px; }
.notification-item { background:#f5b987; padding:15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.2); cursor:pointer; transition: background 0.2s; text-decoration:none; color:#333; }
.notification-item:hover { background:#e6a876; }
.badge { background:red; color:white; padding:4px 8px; border-radius:12px; font-size:12px; margin-left:10px; }
</style>
</head>
<body>

<div class="topbar">
    <div class="logo">Academic Organizer</div>
    <div class="auth-links"><a href="logout.php">Logout</a></div>
</div>

<div class="navbar">
    <ul>
        <li><a href="profile.html"><div class="profile-icon"></div></a></li>
        <li><a href="home.html">Home</a></li>
        <li><a href="schedule.html">Schedule</a></li>
        <li><a href="tasks-assignments.html">Tasks & Assignments</a></li>
        <li><a href="performance.html">Performance Analysis</a></li>
        <li><a href="shared-calendar.html">Shared Calendars</a></li>
        <li><a href="Add Guardian.html">Add Guardian</a></li>
        <li><a href="Gamification.html">Gamification</a></li>
    </ul>
</div>

<div class="container">
    <h2>Notifications 
        <?php if($pending_count > 0): ?>
            <span class="badge"><?= $pending_count ?></span>
        <?php endif; ?>
    </h2>

    <div class="notification-list">
        <?php if($notifications->num_rows > 0): ?>
                  <?php while($row = $notifications->fetch_assoc()): ?>
                    <a href="pending_shares_detail.php?schedule_id=<?= $row['schedule_id'] ?>&sender_id=<?= $row['sender_id'] ?>" class="notification-item">
                        <div>
                            <p><?= $row['first_name'] ?> wants to share Schedule ID: <?= $row['schedule_id'] ?> with you</p>
                        </div>
                    </a>
                <?php endwhile; ?> 
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
