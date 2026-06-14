<?php
session_start();
include 'connection.php';

// افتراض guardian_id (للاختبار فقط)
$guardian_id = $_SESSION['user_id'] ?? 1;
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
// جلب الطلاب المرتبطين بولي الأمر
$studentsQuery = $conn->prepare("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS full_name
    FROM student_guardian sg
    JOIN users u ON sg.student_id = u.id
    WHERE sg.guardian_id = ? AND  sg.status='accepted'
");
$studentsQuery->bind_param("i", $guardian_id);
$studentsQuery->execute();
$studentsResult = $studentsQuery->get_result();
$students = $studentsResult->fetch_all(MYSQLI_ASSOC);

$selectedStudent = $_GET['student_id'] ?? ($students[0]['id'] ?? null);

// جلب أداء الطالب
$performanceData = [];
if ($selectedStudent) {
    $stmt = $conn->prepare("
        SELECT c.name AS course_name,
               SUM(CASE WHEN t.complete = 1 THEN 1 ELSE 0 END) AS completed_tasks,
               SUM(CASE WHEN t.complete = 0 THEN 1 ELSE 0 END) AS incomplete_tasks
        FROM task t
        JOIN course c ON c.id = t.course_id
        JOIN taskuser tu ON tu.task_id = t.task_id
        WHERE tu.user_id = ?
        GROUP BY c.name
    ");
    $stmt->bind_param("i", $selectedStudent);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $performanceData[] = $row;
    }
}

$chartData = json_encode($performanceData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Organizer - Report</title>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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


.report-container {
    background: rgba(255, 255, 255, 0.93);
    width: 600px;
    margin: 40px auto;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0px 6px 20px rgba(0,0,0,0.2);
}
.report-title {
    font-size: 20px;
    font-weight: bold;
    color: #222;
    margin-bottom: 15px;
    text-align: center;
}
.student-select {
    margin-bottom: 15px;
    padding: 6px;
    border-radius: 6px;
    font-size: 13px;
    width: 100%;
}
canvas {
    background: white;
    border-radius: 8px;
    margin-bottom: 20px;
}
p {
    text-align: center;
    font-size: 13px;
    color: #555;
}
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

<div class="report-container">
    <div class="report-title">Reports and Statistics</div>

    <form method="get">
        <label for="student_id">Select Student:</label>
        <select name="student_id" id="student_id" class="student-select" onchange="this.form.submit()">
            <?php foreach ($students as $stu): ?>
                <option value="<?= $stu['id'] ?>" <?= ($stu['id'] == $selectedStudent) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($stu['full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selectedStudent && count($performanceData) > 0): ?>
        <canvas id="barChart" height="150"></canvas>
        <canvas id="pieChart" height="150"></canvas>
    <?php else: ?>
        <p>No data available for this student.</p>
    <?php endif; ?>
</div>

<script>
const chartData = <?= $chartData ?>;

if (chartData.length > 0) {
    const ctxBar = document.getElementById('barChart').getContext('2d');
    const ctxPie = document.getElementById('pieChart').getContext('2d');

    const labels = chartData.map(item => item.course_name);
    const completed = chartData.map(item => item.completed_tasks);
    const incomplete = chartData.map(item => item.incomplete_tasks);

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Completed', data: completed, backgroundColor: '#52c41a' },
                { label: 'Incomplete', data: incomplete, backgroundColor: '#f5222d' }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: { x: { ticks: { font: { size: 11 } } }, y: { ticks: { font: { size: 11 } } } }
        }
    });

    const totalCompleted = completed.reduce((a, b) => a + parseInt(b), 0);
    const totalIncomplete = incomplete.reduce((a, b) => a + parseInt(b), 0);

    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Completed', 'Incomplete'],
            datasets: [{ data: [totalCompleted, totalIncomplete], backgroundColor: ['#2ecc71', '#e74c3c'] }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
        }
    });
}
</script>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>
