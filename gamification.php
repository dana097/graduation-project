<?php
session_start();
include 'connection.php';

// التحقق من وجود الجلسة
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_type = $_SESSION['user_type'];
// آخر 10 إشعارات
 
$stmt = $conn->prepare("SELECT id, message, timestamp, status FROM notification WHERE recipient=? ORDER BY timestamp DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
  }
// 1. النقاط من التسليم في الوقت (2 نقطة للوقت، 1 نقطة للتأخير)
$ontime_points_query = $conn->query("
    SELECT 
        SUM(CASE 
            WHEN t.complete = 1 AND tu.current_date IS NOT NULL THEN 2
            ELSE 0 
        END) as ontime_points
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id AND t.complete = 1
");
$ontime_points = $ontime_points_query->fetch_assoc()['ontime_points'] ?? 0;

// 2. النقاط من الكورسات (نقطة لكل كورس)
$course_points_query = $conn->query("
    SELECT COUNT(DISTINCT c.id) as course_points
    FROM course c
    WHERE c.user_id = $user_id
");
$course_points = $course_points_query->fetch_assoc()['course_points'] ?? 0;

// 3. النقاط من نوع المهام
$task_type_points_query = $conn->query("
    SELECT 
        SUM(CASE 
            WHEN tu.shared = 1 AND tu.accept = 1 THEN 3
            WHEN tu.shared = 0 THEN 1
            ELSE 0 
        END) as task_type_points
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id AND t.complete = 1
");
$task_type_points = $task_type_points_query->fetch_assoc()['task_type_points'] ?? 0;

// حساب إجمالي النقاط
$total_points = $ontime_points + $course_points + $task_type_points;

 
// ========== حساب التحديات الفردية ==========

// 1. هل سلم أي مهمة اليوم؟ (الكارد الأول)
$today_tasks = $conn->query("
    SELECT COUNT(*) as c
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND t.complete = 1 
    AND DATE(tu.current_date) = CURDATE()
")->fetch_assoc()['c'] ?? 0;

// 2. هل تخلف عن تسليم أي مهمة في الترم الحالي؟ (الكارد الثاني)
// نفترض أن الترم يبدأ من تاريخ محدد
$semester_start = date('Y-m-d', strtotime('-3 months')); // الترم الحالي: آخر 3 شهور
$semester_end = date('Y-m-d'); // تاريخ اليوم

// هل هناك أي مهام في الترم الحالي؟
$total_semester_tasks = $conn->query("
    SELECT COUNT(*) as c
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND t.due_date >= '$semester_start' 
    AND t.due_date <= '$semester_end'
")->fetch_assoc()['c'] ?? 0;

// هل تخلف عن تسليم أي مهمة في الترم؟
$missed_deadlines = $conn->query("
    SELECT COUNT(*) as c
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND t.complete = 0  -- لم تكتمل
    AND t.due_date >= '$semester_start' 
    AND t.due_date < CURDATE()  -- انتهى وقتها ولم تكتمل
")->fetch_assoc()['c'] ?? 0;

// 3. هل أكمل 5 مهام في الأسبوع الحالي؟
$week_completed = $conn->query("
    SELECT COUNT(*) as c
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND t.complete = 1 
    AND YEARWEEK(tu.current_date, 1) = YEARWEEK(CURDATE(), 1)
")->fetch_assoc()['c'] ?? 0;

// هل هناك أي مهام في الأسبوع الحالي؟
$total_week_tasks = $conn->query("
    SELECT COUNT(*) as c
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND YEARWEEK(t.due_date, 1) = YEARWEEK(CURDATE(), 1)
")->fetch_assoc()['c'] ?? 0;

// هل تخلف عن تسليم أي مهمة في الأسبوع الحالي؟
$week_missed = $conn->query("
    SELECT COUNT(*) as c
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND t.complete = 0
    AND YEARWEEK(t.due_date, 1) = YEARWEEK(CURDATE(), 1)
    AND t.due_date < CURDATE()
")->fetch_assoc()['c'] ?? 0;

// 4. هل أكمل مهام لـ 3 أيام متتالية؟
$streak_query = $conn->query("
    SELECT DATE(tu.current_date) as task_day
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id 
    AND t.complete = 1
    AND tu.current_date IS NOT NULL
    GROUP BY DATE(tu.current_date)
    ORDER BY DATE(tu.current_date) DESC
");

$dates = [];
while($row = $streak_query->fetch_assoc()){
    $dates[] = $row['task_day'];
}

$streak = 0;
$prev = null;
foreach($dates as $d){
    if(!$prev) $streak = 1;
    else {
        $diff = (strtotime($prev) - strtotime($d)) / 86400;
        if($diff == 1) $streak++;
        else break;
    }
    $prev = $d;
}

// حساب التقدم العام
$total_query = $conn->query("
    SELECT COUNT(*) as total 
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id AND tu.shared = 1
");
$completed_query = $conn->query("
    SELECT COUNT(*) as completed 
    FROM task t
    JOIN taskuser tu ON t.task_id = tu.task_id
    WHERE tu.user_id = $user_id AND t.complete = 1 
");
$total_tasks = $total_query->fetch_assoc()['total'] ?? 0;
$completed_tasks = $completed_query->fetch_assoc()['completed'] ?? 0;
$progress = ($total_tasks > 0) ? round(($completed_tasks / $total_tasks) * 100) : 0;

// تحديد حالة الكارد الثاني (الترم)
$semester_status = 'neutral'; // رمادي افتراضي
if ($total_semester_tasks > 0) {
    if ($missed_deadlines > 0) {
        $semester_status = 'failed'; // أحمر
    } else {
        $semester_status = 'success'; // أخضر
    }
}

// تحديد حالة الكارد الثالث (الأسبوع)
$week_status = 'neutral'; // رمادي افتراضي
if ($total_week_tasks > 0) {
    if ($week_missed > 0) {
        $week_status = 'failed'; // أحمر
    } elseif ($week_completed >= 5) {
        $week_status = 'success'; // أخضر
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Organizer - Gamification</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
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

    /* قسم النقاط */
    .points-section { 
        background: #3b3b3b; 
        color: white; 
        padding: 20px; 
        border-radius: 10px; 
        margin-bottom: 30px; 
        text-align: center; 
    }
    .points-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 15px; 
    }
    .points-card { 
        background: rgba(255,255,255,0.1); 
        padding: 15px; 
        border-radius: 8px; 
    }
    .points-card h4 { 
        margin-bottom: 10px; 
        font-size: 14px; 
    }
    .total-points-card { 
        background: #f5b987; 
        color: #333; 
        padding: 15px; 
        border-radius: 8px; 
        grid-column: span 2; 
    }

    .progress-section { margin-bottom: 30px; text-align: center; }
    .progress-bar { background: #ddd; border-radius: 20px; overflow: hidden; height: 25px; width: 80%; margin: 0 auto; }
    .progress-bar-inner { background: #f5b987; width: 0%; height: 100%; text-align: right; padding-right: 10px; color: #333; font-weight: bold; line-height: 25px; transition: width 0.5s ease-in-out; }

    .challenges { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    
    /* تحديات */
    .challenge-box { 
        padding: 20px; 
        border-radius: 12px; 
        text-align: center; 
        color: #333; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        background: #cccccc; /* رمادي افتراضي */
    }
    
    /* الكارد 1: إذا سلم مهمة اليوم */
    .challenge-today-success {
        background: #4CAF50 !important; /* أخضر */
        color: white !important;
    }
    
    /* الكارد 2: إذا لم يتخلف عن تسليم في الترم */
    .challenge-semester-success {
        background: #4CAF50 !important; /* أخضر */
        color: white !important;
    }
    
    /* الكارد 2: إذا تخلف عن تسليم في الترم */
    .challenge-semester-failed {
        background: #ff4444 !important; /* أحمر */
        color: white !important;
    }
    
    /* الكارد 2: إذا لا يوجد مهام في الترم */
    .challenge-semester-neutral {
        background: #cccccc !important; /* رمادي */
        color: #333 !important;
    }
    
    /* الكارد 3: إذا أكمل 5 مهام في الأسبوع */
    .challenge-week-success {
        background: #4CAF50 !important; /* أخضر */
        color: white !important;
    }
    
    /* الكارد 3: إذا لم يكمل 5 مهام في الأسبوع */
    .challenge-week-failed {
        background: #ff4444 !important; /* أحمر */
        color: white !important;
    }
    
    /* الكارد 3: إذا لا يوجد مهام في الأسبوع */
    .challenge-week-neutral {
        background: #cccccc !important; /* رمادي */
        color: #333 !important;
    }
    
    /* الكارد 4: إذا أكمل 3 أيام متتالية */
    .challenge-streak-success {
        background: #4CAF50 !important; /* أخضر */
        color: white !important;
    }
    
    /* الكارد 4: إذا لم يكمل 3 أيام متتالية */
    .challenge-streak-neutral {
        background: #cccccc !important; /* رمادي */
        color: #333 !important;
    }
    
    .challenge-box h3 { margin-bottom: 10px; font-size: 16px; }
    .challenge-box p { margin-bottom: 10px; font-size: 14px; }
    
    .challenge-success-icon {
        color: white;
        font-size: 24px;
        margin-bottom: 10px;
    }
    .challenge-failed-icon {
        color: white;
        font-size: 24px;
        margin-bottom: 10px;
    }
    .challenge-neutral-icon {
        color: #333;
        font-size: 24px;
        margin-bottom: 10px;
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

    @media (max-width: 768px) {
        .points-grid { grid-template-columns: 1fr; }
        .total-points-card { grid-column: span 1; }
        .challenges { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>
    <div class="topbar">
        <div class="logo">Academic Organizer</div>
        <div class="auth-links">
            <a>Hello , <?php echo $user_name; ?></a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($user_type == "student"): ?>
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

    <?php elseif ($user_type == "parent"): ?>
    <div class="navbar">
        <ul>
            <li>
                <a href="profile.php" title="Profile">
                    <div class="profile-icon"></div>
                </a>
            </li>
            <li><a href="home.php">Home</a></li>
        </ul>
    </div>
    <?php endif; ?>

    <div class="container">
        <h2>Gamification Challenges</h2>

        <!-- قسم النقاط -->
        <div class="points-section">
            <h3 style="color: #f5b987; margin-bottom: 15px;">🎯 Points System</h3>
            <div class="points-grid">
                <div class="points-card">
                    <h4>⏰ On-time Submission</h4>
                    <p style="font-size: 24px; font-weight: bold; color: #f5b987;"><?php echo $ontime_points; ?> pts</p>
                    <small>2 pts per submitted task</small>
                </div>
                <div class="points-card">
                    <h4>📚 Total Courses</h4>
                    <p style="font-size: 24px; font-weight: bold; color: #f5b987;"><?php echo $course_points; ?> pts</p>
                    <small>1 pt per course</small>
                </div>
               <div class="points-card">
                    <h4>👑 Task Type</h4>
                    <p style="font-size: 24px; font-weight: bold; color: #f5b987;"><?php echo $task_type_points; ?> pts</p>
                    <small>3 pts (individual) / 1 pt (shared)</small>
                </div>
                <div class="points-card total-points-card">
                    <h4>🏆 Total Points</h4>
                    <p style="font-size: 32px; font-weight: bold; color: #3b3b3b;"><?php echo $total_points; ?> pts</p>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-section">
            <h3>Level Progress</h3>
            <div class="progress-bar">
                <div class="progress-bar-inner" style="width:<?php echo $progress; ?>%"><?php echo $progress; ?>%</div>
            </div>
        </div>

        <!-- Challenge Cards -->
        <div class="challenges">
            <!-- الكارد 1: Submit Today's Task -->
            <div class="challenge-box <?php echo ($today_tasks>0) ? 'challenge-today-success' : ''; ?>">
                <?php if($today_tasks>0): ?>
                    <div class="challenge-success-icon">✅</div>
                <?php else: ?>
                    <div class="challenge-neutral-icon">📅</div>
                <?php endif; ?>
                <h3>Submit Today's Task</h3>
                <p>Submit at least one task today.</p>
                <p><b>Status:</b> 
                    <?php 
                    if($today_tasks>0) {
                        echo "Submitted! ($today_tasks tasks)";
                    } else {
                        echo "No submissions today";
                    }
                    ?>
                </p>
            </div>

            <!-- الكارد 2: Semester Commitment -->
            <div class="challenge-box <?php echo 'challenge-semester-' . $semester_status; ?>">
                <?php if($semester_status == 'success'): ?>
                    <div class="challenge-success-icon">✅</div>
                <?php elseif($semester_status == 'failed'): ?>
                    <div class="challenge-failed-icon">⚠️</div>
                <?php else: ?>
                    <div class="challenge-neutral-icon">📚</div>
                <?php endif; ?>
                <h3>Semester Commitment</h3>
                <p>Don't miss any deadlines this semester.</p>
                <p><b>Status:</b> 
                    <?php 
                    if($total_semester_tasks == 0) {
                        echo "No tasks this semester";
                    } elseif($missed_deadlines > 0) {
                        echo "Missed $missed_deadlines deadlines";
                    } else {
                        echo "Perfect record!";
                    }
                    ?>
                </p>
            </div>

            <!-- الكارد 3: Weekly Challenge -->
            <div class="challenge-box <?php echo 'challenge-week-' . $week_status; ?>">
                <?php if($week_status == 'success'): ?>
                    <div class="challenge-success-icon">🏅</div>
                <?php elseif($week_status == 'failed'): ?>
                    <div class="challenge-failed-icon">❌</div>
                <?php else: ?>
                    <div class="challenge-neutral-icon">📅</div>
                <?php endif; ?>
                <h3>Weekly Challenge</h3>
                <p>Complete 5 tasks without missing any.</p>
                <p><b>Status:</b> 
                    <?php 
                    if($total_week_tasks == 0) {
                        echo "No tasks this week";
                    } elseif($week_missed > 0) {
                        echo "Missed $week_missed tasks";
                    } elseif($week_completed >= 5) {
                        echo "Perfect week! ($week_completed tasks)";
                    } else {
                        echo "$week_completed / 5 tasks completed";
                    }
                    ?>
                </p>
            </div>

            <!-- الكارد 4: Consistency -->
            <div class="challenge-box <?php echo ($streak>=3) ? 'challenge-streak-success' : 'challenge-streak-neutral'; ?>">
                <?php if($streak>=3): ?>
                    <div class="challenge-success-icon">🔥</div>
                <?php else: ?>
                    <div class="challenge-neutral-icon">📊</div>
                <?php endif; ?>
                <h3>Consistency Streak</h3>
                <p>Submit tasks 3 days in a row.</p>
                <p><b>Status:</b> <?php echo ($streak>=3) ? "🔥 $streak days streak!" : "$streak / 3 days"; ?></p>
            </div>
        </div>
    </div>

    <script>
        const notifications = <?= json_encode($notifications); ?>;
    
        // كود الإشعارات
      
    </script>
    <script src="notifications.js"></script>
</body>
</html>
