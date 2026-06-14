
<?php

 
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? 0;


 
 
// 🟢 جلب الإشعارات
$stmt = $conn->prepare("SELECT id, message, timestamp, status FROM notification WHERE recipient=? ORDER BY timestamp DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
}

// 🟢 جلب الجداول المشتركة معي
$query = "
SELECT 
    s.schedule_id,
    c.name as course_id,
    s.start_time,
    s.end_time,
    s.day,
    u.first_name AS sender_name,
    su.shared
FROM scheduleuser su
JOIN schedule s ON su.schedule_id = s.schedule_id
JOIN users u ON su.sender_id = u.id
JOIN course c ON s.course_id = c.id
WHERE su.user_id = ?
ORDER BY s.start_time DESC
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$shared_schedules = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Organizer - Shared Calendars</title>
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
    .navbar ul li a:hover, .navbar ul li a.active { text-decoration:underline; }

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

    .shared-container {
        background: rgba(255,255,255,0.9);
        width:90%;
        max-width:900px;
        margin:60px auto;
        padding:30px;
        border-radius:10px;
        box-shadow:0px 4px 15px rgba(0,0,0,0.5);
        text-align:center;
    }

    .shared-container h2 {
        font-size:20px;
        font-weight:bold;
        color:#333;
        margin-bottom:20px;
    }

    table {
        width:100%;
        border-collapse:collapse;
        margin-top:10px;
    }

    th, td {
        padding:10px 15px;
        border:1px solid #ddd;
        text-align:center;
    }

    th {
        background-color:#f5b987;
        color:white;
    }

    tr:nth-child(even) { background-color:#f9f9f9; }
     tr{
 background-color:#333; 
 color: #fff;

     }
    .status-badge {
        padding:6px 12px;
        border-radius:8px;
        font-weight:bold;
        color:#fff;
    }
    
    .status-0 { background-color:orange; }
    .status-1 { background-color:green; }
    .status-2 { background-color:red; }
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
        border: 1px solid #ddd;
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
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    .notif-dropdown li:last-child {
        border-bottom: none;
    }
    .notif-dropdown li:hover {
        background: #f5f5f5;
    }
    @media(max-width:768px){
        .topbar { flex-direction:column; gap:10px; padding:10px 15px; }
        .navbar { padding:10px 15px; }
        .navbar ul { flex-wrap:wrap; gap:10px; }
        .shared-container { width:90%; padding:20px; }
        table { font-size:13px; }
    }
</style>
</head>
<body>
 
<div class="topbar">
  <div class="logo">Academic Organizer</div>
  <div class="auth-links">
    <a>Hello , <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<!--  Navbar -->
<?php if ($_SESSION['user_type'] == "student"): ?>
<div class="navbar">
  <ul>
    <li><a href="profile.php" title="Profile"><div class="profile-icon"></div></a></li>
    <li><a href="home.php">Home</a></li>
    <li><a href="courses.php">Courses</a></li>
    <li><a href="schedule.php">Schedule</a></li>
    <li><a href="tasks-assignments.php">Tasks & Assignments</a></li>
    <li><a href="performance.php">Performance Analysis</a></li>
    <li><a href="shared.php" class="active">Shared Calendars</a></li>
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
<?php endif; ?>

<!--   المحتوى الرئيسي -->
<div class="shared-container">
  <h2>Schedules Shared With Me</h2>

  <?php if ($shared_schedules->num_rows === 0): ?>
      <p style="font-weight:bold;">No schedules have been shared with you yet.</p>
  <?php else: ?>
      <table>
          <thead>
              <tr>
                  <th>Course ID</th>
                  <th>Day</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Sender</th>
                  <th>Status</th>
              </tr>
          </thead>
          <tbody>
              <?php while($row = $shared_schedules->fetch_assoc()): ?>
                  <tr data-schedule-id="<?= htmlspecialchars($row['schedule_id']) ?>">

                      <td><?= htmlspecialchars($row['course_id']) ?></td>
                      <td><?= htmlspecialchars(ucfirst($row['day'])) ?></td>
                      <td><?= htmlspecialchars($row['start_time']) ?></td>
                      <td><?= htmlspecialchars($row['end_time']) ?></td>
                      <td><?= htmlspecialchars($row['sender_name']) ?></td>
                      <td>
                          <?php
                          switch($row['shared']) {
                              case 0: $status_text = 'Pending'; $status_class = 'status-0'; break;
                              case 1: $status_text = 'Approved'; $status_class = 'status-1'; break;
                              case 2: $status_text = 'Rejected'; $status_class = 'status-2'; break;
                          }
                          ?>
                          <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                      </td>
                  </tr>
              <?php endwhile; ?>
          </tbody>
      </table>
  <?php endif; ?>
</div>
<script>
document.querySelectorAll('.status-badge').forEach(badge => {
    badge.addEventListener('click', function() {
        const row = this.closest('tr');
        const scheduleId = row.dataset.scheduleId;

        if (this.classList.contains('status-1')) {
            alert("This schedule is already approved.");
            return;
        }

        if (confirm("Do you want to approve this shared schedule?")) {
            fetch('update_shared_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'schedule_id=' + encodeURIComponent(scheduleId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.textContent = "Approved";
                    this.classList.remove('status-0', 'status-2');
                    this.classList.add('status-1');
                } else {
                    alert("Failed to update status: " + (data.message || 'Unknown error'));
                }
            })
            .catch(err => alert("Error: " + err));
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
