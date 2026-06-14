<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "student") {
    header("Location: login.php");
    exit();
}

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
// ================================
//  1. مراجعة بيانات الطالب
// ================================

$stmt = $conn->prepare("SELECT university_id, city, town FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (empty($user['university_id']) || empty($user['city']) || empty($user['town'])) {
    // إذا لم تُكمل البيانات، أرجع للمستخدم إلى صفحة البروفايل
    header("Location: profile.php?incomplete=1");
    exit();
}


// ================================
//  1. عدد المهام الكلي
// ================================
$total_query = "SELECT COUNT(*) AS total 
FROM task 
JOIN taskuser ON task.task_id = taskuser.task_id 
WHERE taskuser.user_id = ?
";
$stmt = $conn->prepare($total_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_tasks = $total_result['total'] ?? 0;

// ================================
//  2. المهام المكتملة
// ================================
$completed_query = "SELECT COUNT(*) AS completed 
FROM task 
JOIN taskuser ON task.task_id = taskuser.task_id 
WHERE taskuser.user_id = ? AND task.complete = 1
";
$stmt = $conn->prepare($completed_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_result = $stmt->get_result()->fetch_assoc();
$completed_tasks = $completed_result['completed'] ?? 0;

// ================================
// 3. المهام المتأخرة
// ================================
// المهمة تعتبر متأخرة إذا submission_time > due_date أو لم تُسلم بعد (NULL) وتاريخ اليوم تجاوز due_date


$delayed_query = "SELECT COUNT(*) AS delayed2 
FROM task 
JOIN taskuser ON task.task_id = taskuser.task_id 
WHERE taskuser.user_id = ? 
AND task.complete = 0
AND CONCAT(task.due_date, ' ', task.submission_time) < NOW()
";

$stmt = $conn->prepare($delayed_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$delayed_result = $stmt->get_result()->fetch_assoc();
$delayed_tasks = $delayed_result['delayed2'] ?? 0;


// ================================
// 3. تمت مشاركتها
// ================================
 

$shared = "SELECT COUNT(*) AS shared
FROM task 
JOIN taskuser ON task.task_id = taskuser.task_id 
WHERE taskuser.user_id = ? 
AND taskuser.shared = 0 
AND taskuser.accept = 1
 
";

$stmt = $conn->prepare($shared);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$shared_result = $stmt->get_result()->fetch_assoc();
$shared_tasks = $shared_result['shared'] ?? 0;

// ================================
//  4. نسبة الإنجاز (Overall Progress)
// ================================
if ($total_tasks > 0) {
    $progress = round(($completed_tasks / $total_tasks) * 100, 1);
} else {
    $progress = 0;
}


// ================================
//  5. مقارنة الأداء حسب السنة
// ================================

$progress_by_year_query = "
SELECT 
    COALESCE(c.year, YEAR(t.due_date)) AS year,
    COUNT(t.task_id) AS total_tasks,
    SUM(CASE WHEN t.complete = 1 THEN 1 ELSE 0 END) AS completed_tasks
FROM taskuser tu
INNER JOIN task t ON tu.task_id = t.task_id
LEFT JOIN course c ON t.course_id = c.id
WHERE tu.user_id = ?
    AND (c.year IS NOT NULL OR t.due_date IS NOT NULL)
GROUP BY COALESCE(c.year, YEAR(t.due_date))
HAVING COUNT(t.task_id) > 0
ORDER BY year ASC
";

$stmt = $conn->prepare($progress_by_year_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$years = [];
$progress_values = [];

while ($row = $result->fetch_assoc()) {
    $year = $row['year'];
    $total = $row['total_tasks'];
    $completed = $row['completed_tasks'];
    $progress_percent = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

    $years[] = $year;
    $progress_values[] = $progress_percent;
}



$due_query = "
SELECT COUNT(*) AS due_count
FROM task 
JOIN taskuser ON task.task_id = taskuser.task_id 
WHERE taskuser.user_id = ?
  AND task.complete = 0
  AND CONCAT(task.due_date, ' ', task.submission_time) >= NOW()
";


$stmt = $conn->prepare($due_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$due_task_count = $row['due_count'] ?? 0;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Organizer - Performance Analysis</title>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, sans-serif;
        background: url("https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image.jpg-ri2jUSpScDXjsulXIHz0LMu56qvFYI.jpeg") no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
    }
    .topbar { background-color: rgba(59,59,59,0.9); color: white; padding: 12px 30px; display: flex; justify-content: space-between; align-items: center; }
    .topbar .logo { font-style: italic; font-weight: bold; font-size: 18px; }
    .topbar .auth-links a { color: white; margin-left: 20px; text-decoration: none; font-weight: bold; }
    .topbar .auth-links a:hover { text-decoration: underline; }
    .navbar { background-color: rgba(85,85,85,0.9); padding: 10px 30px; }
    .navbar ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; align-items: center; }
    .navbar ul li a { color: white; text-decoration: none; font-size: 14px; }
    .navbar ul li a:hover { text-decoration: underline; }
    .profile-icon { width: 24px; height: 24px; background-color: white; border-radius: 50%; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E"); background-size: 16px 16px; background-repeat: no-repeat; background-position: center; margin-right: 20px; }
    .profile-icon:hover { background-color: #f5b987; }

    .container {
        background: rgba(255, 255, 255, 0.85);
        width: 90%;
        max-width: 1100px;
        margin: 40px auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
    }
    .container h2 {
        margin-bottom: 25px;
        font-size: 20px;
        font-weight: bold;
        color: #333;
        text-align: center;
    }
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .card {
        background: #f5b987;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        font-weight: bold;
        color: #333;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        font-size: 18px;
    }
    .card span {
        display: block;
        font-size: 30px;
        margin-top: 10px;
        color: #222;
    }
    /* Charts */
    .charts {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    .chart-box {
        background: white;
        height: 250px;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        display: flex;
        justify-content: center;
        align-items: center;
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
</style>
</head>
<body>
<div class="topbar">
    <div class="logo">Academic Organizer</div>
    <div class="auth-links">
<a>Hello , <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></a>
    <a href="logout.php">Logout</a>
</div>
</div>

<div class="navbar">
    <ul>
        <li>
            <a href="profile.php" title="Profile">
                <div class="profile-icon"></div>
            </a>
        </li>
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
    <h2>Performance Analysis</h2>


    
    <!-- بطاقات الإحصائيات -->
    <div class="cards">
        <div class="card">Due Tasks<span><?php echo $due_task_count; ?></span></div>
        <div class="card">Overall Progress<span><?php echo $progress; ?>%</span></div>
        <div class="card">Completed Tasks<span><?php echo $completed_tasks; ?></span></div>
        <div class="card">Delayed Tasks<span><?php echo $delayed_tasks; ?></span></div>
        <div class="card">Shared Tasks<span><?php echo $shared_tasks; ?></span></div>
    </div>
    <!-- Charts -->
    <div class="charts">
        <div class="chart-box">
            <canvas id="tasksDistributionChart"></canvas>
        </div>
        <div class="chart-box">
            <canvas id="tasksTrendChart"></canvas>
        </div>
    </div>
</div>
<!-- Chart.js library -->
 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// تمرير بيانات من PHP إلى JavaScript
const years = <?php echo json_encode($years); ?>;
const progressValues = <?php echo json_encode($progress_values); ?>;

//  تعريف الرسومات
const tasksDistributionCtx = document.getElementById('tasksDistributionChart').getContext('2d');
const tasksTrendCtx = document.getElementById('tasksTrendChart').getContext('2d');

//  الرسم البياني الأول (placeholder مؤقت)
const tasksDistributionChart = new Chart(tasksDistributionCtx, {
    type: 'bar',
    data: {
        labels: ['Completed', 'Delayed'],
        datasets: [{
            label: 'Task Summary',
            data: [<?php echo $completed_tasks; ?>, <?php echo $delayed_tasks; ?>],
            backgroundColor: ['#f5b987', '#ff7777']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

//  الرسم البياني الثاني (مقارنة الأداء حسب السنوات)
const tasksTrendChart = new Chart(tasksTrendCtx, {
    type: 'line',
    data: {
        labels: years,
        datasets: [{
            label: 'Performance by Year (%)',
            data: progressValues,
            borderColor: '#f5b987',
            backgroundColor: 'rgba(245,185,135,0.3)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, labels: { color: '#333' } }
        },
        scales: {
            x: { title: { display: true, text: 'Year' } },
            y: { title: { display: true, text: 'Completion (%)' }, beginAtZero: true, max: 100 }
        }
    }
});
</script>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
    <script src="notifications.js"></script>
</body>
</html>

 