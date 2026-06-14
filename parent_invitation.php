<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'parent') {
    header("Location: login.php");
    exit();
}
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
// معالجة القبول أو الرفض
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $invite_id = intval($_POST['invite_id'] ?? 0);

    if ($invite_id > 0) {
        if ($action === 'accept') {
            $stmt = $conn->prepare("UPDATE student_guardian SET status='accepted', updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->bind_param("i", $invite_id);
            $stmt->execute();
            $stmt->close();
            $message = "Invitation accepted.";
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE student_guardian SET status='rejected', updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->bind_param("i", $invite_id);
            $stmt->execute();
            $stmt->close();
            $message = "Invitation rejected.";
        }
    }
}

// جلب الدعوات المعلقة لهذا الولي
$parent_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT sg.id as invite_id, u.first_name, u.last_name, u.email, sg.created_at
    FROM student_guardian sg
    JOIN users u ON sg.student_id = u.id
    WHERE sg.guardian_id = ? AND sg.status='pending'
    ORDER BY sg.created_at DESC
");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$invites = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Parent Invites - Academic Organizer</title>
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
 

.invite-container { background: rgba(255, 255, 255, 0.88); width: 60%; max-width: 600px; margin: 60px auto; padding: 25px 30px; border-radius: 10px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.4); }
.invite-container h2 { color: #222; margin-bottom: 15px; }
.invite-box { background-color: #e2c0a8; padding: 20px 25px; border-radius: 10px; box-shadow: 0 0 8px rgba(0,0,0,0.2); margin-bottom: 20px; }
.invite-box p { margin: 8px 0; color: #333; line-height: 1.6; }
.invite-box strong { color: #111; }
.invite-buttons { display: flex; justify-content: center; gap: 20px; margin-top: 15px; }
.invite-buttons button { background-color: #b89078; border: none; color: white; border-radius: 6px; padding: 10px 25px; cursor: pointer; font-size: 15px; font-weight: bold; transition: 0.3s; }
.invite-buttons button:hover { background-color: #9d7863; }
.message-box { text-align:center; color:#fff; background:#3b3b3b; padding:10px; border-radius:10px; margin-bottom:15px; }
@media (max-width: 768px) { .invite-container { width: 90%; padding: 15px; } }
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

<div class="invite-container">
    <h2>Pending Invitations</h2>

    <?php if (!empty($message)): ?>
        <p class="message-box"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (count($invites) > 0): ?>
        <?php foreach ($invites as $invite): ?>
            <div class="invite-box">
                <p>👤 You have a new invitation from your son to join his account and track his study schedule.</p>
                <p><strong>Son's name:</strong> <?= htmlspecialchars($invite['first_name'] . ' ' . $invite['last_name']) ?></p>
                <p>📧 <strong>Email:</strong> <?= htmlspecialchars($invite['email']) ?></p>
                <p>📅 <strong>Invitation sent on:</strong> <?= date("F j, Y", strtotime($invite['created_at'])) ?></p>
                <p>💠 Would you like to accept this invitation?</p>
                <div class="invite-buttons">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="invite_id" value="<?= $invite['invite_id'] ?>">
                        <button type="submit" name="action" value="accept">Accept</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="invite_id" value="<?= $invite['invite_id'] ?>">
                        <button type="submit" name="action" value="reject">Reject</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; color:#333;">No pending invitations at the moment.</p>
    <?php endif; ?>
</div>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>
