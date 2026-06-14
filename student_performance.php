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
// 1️⃣ جلب الطلاب المرتبطين بولي الأمر
$stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.last_name 
    FROM student_guardian sg
    JOIN users u ON sg.student_id = u.id
    WHERE sg.guardian_id = ? 
");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$selected_student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : ($students[0]['id'] ?? 0);
$selected_student = null;

foreach ($students as $s) {
    if ($s['id'] == $selected_student_id) {
        $selected_student = $s;
        break;
    }
}

$subjects_data = [];
if ($selected_student) {
    $stmt = $conn->prepare("
        SELECT 
            c.name AS subject,
            COUNT(t.task_id) AS total_assignments,
            SUM(CASE WHEN t.complete = 1 THEN 1 ELSE 0 END) AS completed_assignments,
            ROUND(SUM(CASE WHEN t.complete = 1 THEN 1 ELSE 0 END) / COUNT(t.task_id) * 100, 1) AS completion_rate,
            GROUP_CONCAT(DISTINCT t.assigment_type SEPARATOR ', ') AS assignment_types
        FROM course c
        JOIN task t ON t.course_id = c.id
        JOIN taskuser tu ON tu.task_id = t.task_id
        WHERE tu.user_id = ?
        GROUP BY c.name
    ");
    $stmt->bind_param("i", $selected_student_id);
    $stmt->execute();
    $subjects_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Student Performance Tracking</title>
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


.profile-icon, .notification-icon {
  width: 24px; height: 24px; border-radius: 50%;
  background-color: white; background-repeat: no-repeat;
  background-position: center; background-size: 16px 16px;
  cursor: pointer;
}
.profile-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
}
.notification-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 2C10.9 2 10 2.9 10 4v1.09c-3.39.49-6 3.39-6 6.91v4l-2 2v1h20v-1l-2-2v-4c0-3.52-2.61-6.42-6-6.91V4c0-1.1-.9-2-2-2zM12 22c1.1 0 2-.9 2-2H10c0 1.1.9 2 2 2z'/%3E%3C/svg%3E");
}
 

.table-container {
  background: rgba(255, 255, 255, 0.88);
  width: 80%; margin: 50px auto; padding: 25px;
  border-radius: 10px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.4);
}
.student-info {
  background-color: #e9d3c2; padding: 15px 20px; border-radius: 8px;
  display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;
  margin-bottom: 20px;
}
.student-info .details {color: #333; font-size: 15px; line-height: 1.8;}
.student-info .actions a, .student-info button {
  background-color: #b89078; color: white; border: none;
  border-radius: 6px; padding: 8px 14px; margin-left: 10px;
  cursor: pointer; font-size: 14px; font-weight: bold;
  text-decoration: none; display: inline-block; text-align: center;
}
.student-info .actions a:hover, .student-info button:hover {background-color: #9d7863;}
table {
  width: 100%; border-collapse: collapse; text-align: left;
  font-size: 15px; border-radius: 10px; overflow: hidden;
}
thead {background-color: #222; color: #fff;}
thead th {padding: 12px 15px; font-weight: bold;}
tbody tr {background-color: #2b2b2b; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2);}
tbody tr:nth-child(even) {background-color: #1f1f1f;}
tbody td {padding: 10px 15px;}
tbody tr:hover {background-color: #3a3a3a;}
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

<div class="table-container">
  <?php if ($selected_student): ?>
  <div class="student-info">
    <div class="details">
      <p><strong>Student Name:</strong> <?= htmlspecialchars($selected_student['first_name'].' '.$selected_student['last_name']); ?></p>
      <!-- <p><strong>Level:</strong> High School</p> -->
      <p><strong>Performance:</strong> <?= count($subjects_data) > 0 ? "Active" : "No data yet"; ?></p>
    </div>
    <div class="actions">
      <form method="get" style="display:inline;">
        <select name="student_id" onchange="this.form.submit()">
          <?php foreach ($students as $student): ?>
          <option value="<?= $student['id']; ?>" <?= ($student['id']==$selected_student_id)?'selected':''; ?>>
            <?= htmlspecialchars($student['first_name'].' '.$student['last_name']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </form>
      <a href="report_statistics.php">View Report and Statistics</a>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Subject</th>
        <th>Total Task</th>
        <th>Completed</th>
        <th>Completion %</th>
        <th>Task Types</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($subjects_data) > 0): ?>
      <?php foreach ($subjects_data as $subject): ?>
      <tr>
        <td><?= htmlspecialchars($subject['subject']); ?></td>
        <td><?= $subject['total_assignments']; ?></td>
        <td><?= $subject['completed_assignments']; ?></td>
        <td><?= $subject['completion_rate']; ?>%</td>
        <td><?= htmlspecialchars($subject['assignment_types'] ?? '-'); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php else: ?>
      <tr><td colspan="5">No performance data found for this student.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="color:white;text-align:center;">No linked students found.</p>
  <?php endif; ?>
</div>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>
