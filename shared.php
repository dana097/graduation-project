<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? 15; // مؤقتًا للتجربة
$message = '';
$status = '';

// آخر 10 إشعارات
$stmt = $conn->prepare("SELECT id, message, timestamp, status FROM notification WHERE recipient=? ORDER BY timestamp DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['recipientEmail'] ?? '';
    $msg   = $_POST['message'] ?? '';

    if (empty($email)) {
        $message = "Enter the recipient email";
        $status = 'error';
    } else {
        $recipient = $conn->query("SELECT id FROM users WHERE email = '$email'")->fetch_assoc();

        if (!$recipient) {
            $message = "No user found with this email";
            $status = 'error';
        } else {
            $recipient_id = $recipient['id'];
            $user_name = $_SESSION['user_name'];

            $schedules = $conn->query("SELECT schedule_id FROM schedule WHERE user_id = $user_id");
            $shared_any = false;

            while ($row = $schedules->fetch_assoc()) {
                $schedule_id = $row['schedule_id'];

                $check = $conn->prepare("SELECT id FROM scheduleuser WHERE schedule_id = ? AND user_id = ?");
                $check->bind_param("ii", $schedule_id, $recipient_id);
                $check->execute();
                $result_check = $check->get_result();

                if ($result_check->num_rows == 0) {
                    $stmt_insert = $conn->prepare("INSERT INTO scheduleuser (schedule_id, user_id, sender_id, shared) VALUES (?, ?, ?, 0)");
                    $stmt_insert->bind_param("iii", $schedule_id, $recipient_id, $user_id);
                    $stmt_insert->execute();
                    $stmt_insert->close();

                    $notif_message = "$user_name shared a schedule with you";
                    $current_time = date('Y-m-d H:i:s');
                    $status_notif = 'unread';

                    $stmt_notif = $conn->prepare("INSERT INTO notification (recipient, message, timestamp, status) VALUES (?, ?, ?, ?)");
                    $stmt_notif->bind_param("isss", $recipient_id, $notif_message, $current_time, $status_notif);
                    $stmt_notif->execute();
                    $stmt_notif->close();

                    $shared_any = true;
                }
            }

            if ($shared_any) {
                $message = "Shared successfully with $email";
                $status = 'success';
            } else {
                $message = "You already shared all your schedules with this user";
                $status = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Organizer - Share Calendar</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">

<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family: Arial,sans-serif;
  background: url('https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image.jpg-ri2jUSpScDXjsulXIHz0LMu56qvFYI.jpeg') no-repeat center center fixed;
  background-size: cover;
  min-height:100vh;
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
.topbar .auth-links a { color:white; margin-left:20px; text-decoration:none; font-weight:bold; }
.topbar .auth-links a:hover { text-decoration:underline; }

.navbar {
  background-color: rgba(85,85,85,0.9);
  padding:10px 30px;
}
.navbar ul { list-style:none; display:flex; gap:20px; margin:0; padding:0; align-items:center; }
.navbar ul li a { color:white; text-decoration:none; font-size:14px; position:relative; }
.navbar ul li a:hover { text-decoration:underline; }

.profile-icon {
  width:24px; height:24px; border-radius:50%; background-color:white;
  cursor:pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
  background-size:16px 16px; background-repeat:no-repeat; background-position:center; margin-right:20px;
}
.profile-icon:hover { background-color:#f5b987; }

.container {
  background: rgba(255, 255, 255, 0.85);
  width:90%; max-width:800px; margin:40px auto;
  padding:30px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.5);
}
.container h2 { margin-bottom:25px; font-size:22px; font-weight:bold; color:#333; text-align:center; }
.form-group { display:flex; flex-direction:column; margin-bottom:15px; }
.form-group label { font-weight:bold; margin-bottom:5px; color:#333; }
.form-group input, .form-group textarea { padding:12px; border:none; border-radius:10px; font-size:14px; outline:none; background-color:#f5b987; }
.form-group textarea { resize: vertical; height:80px; }
.share-btn { width:100%; padding:15px; background-color:#3b3b3b; color:white; font-weight:bold; border:none; border-radius:10px; cursor:pointer; font-size:16px; }
.share-btn:hover { background-color:#555; }
.message { text-align:center; margin-bottom:20px; font-weight:bold; font-size:16px; }
.message.success { color: green; }
.message.error { color: red; }
.share-btn{
    display: block;
 margin: 5px;
 text-align: center;
}
    /* الإشعارات */
    .notif-count {
        background: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        position: absolute;
        top: -8px;
        right: -8px;
    }
    .notif-dropdown {
        display: none;
        position: absolute;
        background: white;
        border: 1px solid #ffaa00ff;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 250px;
        z-index: 1000;
        right: 0;
        margin-top: 5px;
    }
    .notif-dropdown ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
    }
    .notif-dropdown li {
        padding: 10px 15px;
        border-bottom: 1px solid #ff9100ff;
        font-size: 14px;
    }
    .notif-dropdown li:last-child {
        border-bottom: none;
    }
    .notif-dropdown li:hover {
        background: #f8aa00ff;
    }
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">Academic Organizer</div>
  <div class="auth-links">
    <a>Hello , <?= $_SESSION['user_name']; ?></a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="navbar">
  <ul>
    <li><a href="profile.php"><div class="profile-icon"></div></a></li>
    <li><a href="home.php">Home</a></li>
    <li><a href="courses.php">Courses</a></li>
    <li><a href="schedule.php">Schedule</a></li>
    <li><a href="tasks-assignments.php">Tasks & Assignments</a></li>
    <li><a href="performance.php">Performance Analysis</a></li>
    <li><a href="shared.php">Shared Calendars</a></li>
    <li><a href="add_guardian.php">Add Guardian</a></li>
    <li><a href="gamification.php">Gamification</a></li>
   <li><a href="courseassgin.php">university course</a></li>
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

<div class="container">
  <h2>Share Your Calendar</h2>
  <?php if ($message): ?>
    <div class="message <?= $status ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label for="recipientEmail">Recipient Email</label>
      <input type="email" id="recipientEmail" name="recipientEmail" placeholder="Enter recipient's email" required>
    </div>
    <div class="form-group">
      <label for="message">Message</label>
      <textarea id="message" name="message" placeholder="Add a short message (optional)"></textarea>
    </div>
    <button type="submit" class="share-btn">Share</button>
  </form>
  <a class="share-btn  share-btn2"  href="pending_shares_detail.php"> Shared Schedules </a>
</div>

<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>

</body>
</html>
