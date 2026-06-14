<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

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
// جلب جامعة الطالب الحالي
$student_university_id = null;
$stmt_student = $conn->prepare("SELECT university_id FROM users WHERE id = ?");
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();
if ($row_student = $result_student->fetch_assoc()) {
    $student_university_id = $row_student['university_id'];
}
$stmt_student->close();

// جلب الجامعات المتاحة (فقط جامعة الطالب)
$universities = [];
if ($student_university_id) {
    $stmt = $conn->prepare("
        SELECT DISTINCT u.university_id, un.name as university_name
        FROM users u 
        JOIN university un ON u.university_id = un.id
        WHERE u.user_type = 'student' AND u.university_id = ?
        ORDER BY un.name ASC
    ");
    $stmt->bind_param("i", $student_university_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $universities[$row['university_id']] = $row['university_name'];
    }
    $stmt->close();
}

// جلب الكليات المتاحة
$colleges = [];
$stmt_colleges = $conn->prepare("SELECT id, name FROM college ORDER BY name ASC");
$stmt_colleges->execute();
$result_colleges = $stmt_colleges->get_result();
while ($row = $result_colleges->fetch_assoc()) {
    $colleges[] = $row;
}
$stmt_colleges->close();

// جلب الكورسات حسب الجامعة والكلية المختارة
$selected_university = isset($_GET['university']) ? $_GET['university'] : ($student_university_id ? $student_university_id : '');
$selected_college = isset($_GET['college']) ? $_GET['college'] : '';
$courses = [];

$where_conditions = ["u.user_type = 'faculty'"];
$params = [];
$types = "";

if (!empty($selected_university)) {
    $where_conditions[] = "u.university_id = ?";
    $params[] = $selected_university;
    $types .= "s";
}

if (!empty($selected_college)) {
    $where_conditions[] = "c.college_id = ?";
    $params[] = $selected_college;
    $types .= "i";
}

// إذا لم يتم تحديد جامعة، استخدام جامعة الطالب كافتراضي
if (empty($selected_university) && $student_university_id) {
    $where_conditions[] = "u.university_id = ?";
    $params[] = $student_university_id;
    $types .= "s";
    $selected_university = $student_university_id;
}

$where_clause = implode(" AND ", $where_conditions);

if(isset($_GET['college'])){
    $stmt = $conn->prepare("
        SELECT c.id, c.name AS course_name, c.course AS course, 
            u.first_name AS teacher_name, u.university_id,
            un.name as university_name, c.year, c.semester, c.teacher_name as original_teacher_name,
            c.college_id, cl.name as college_name
        FROM course c
        JOIN users u ON c.user_id = u.id
        JOIN university un ON u.university_id = un.id
        LEFT JOIN college cl ON c.college_id = cl.id
        WHERE $where_clause
        ORDER BY un.name ASC, c.name ASC
    ");

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
}
// معالجة اختيار الطالب للكورسات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_ids'])) {
    $course_ids = $_POST['course_ids'];
    $enrolled_courses = [];
    $failed_courses = [];

    foreach ($course_ids as $course_id) {
        $course_id = intval($course_id);

        // التحقق إذا تم التسجيل مسبقًا في هذا الكورس (التحقق من الجدولين)
        $stmt_check = $conn->prepare("
            SELECT 1 FROM course WHERE user_id = ? AND course = (
                SELECT course FROM course WHERE id = ?
            )
            UNION
            SELECT 1 FROM schedule WHERE user_id = ? AND course_id = ?
        ");
        $stmt_check->bind_param("iiii", $student_id, $course_id, $student_id, $course_id);
        $stmt_check->execute();
        $exists = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if ($exists) {
            $failed_courses[] = $course_id;
            continue;
        }

        // جلب بيانات الكورس الأصلية
        $stmt_course = $conn->prepare("
            SELECT course, name, year, semester, teacher_name, college_id 
            FROM course 
            WHERE id = ?
        ");
        $stmt_course->bind_param("i", $course_id);
        $stmt_course->execute();
        $course_data = $stmt_course->get_result()->fetch_assoc();
        $stmt_course->close();

        if (!$course_data) {
            $failed_courses[] = $course_id;
            continue;
        }

        // التحقق من عدم وجود نفس الكورس مسبقاً للطالب
        $stmt_check_duplicate = $conn->prepare("
            SELECT id FROM course 
            WHERE user_id = ? AND course = ? AND name = ?
        ");
        $stmt_check_duplicate->bind_param("iss", $student_id, $course_data['course'], $course_data['name']);
        $stmt_check_duplicate->execute();
        $duplicate = $stmt_check_duplicate->get_result()->fetch_assoc();
        $stmt_check_duplicate->close();

        if ($duplicate) {
            $failed_courses[] = $course_id;
            continue;
        }

        // إضافة الكورس للطالب في جدول course
        $stmt_insert_course = $conn->prepare("
            INSERT INTO course (course, name, year, semester, teacher_name, user_id, college_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_insert_course->bind_param(
            "ssissii",
            $course_data['course'],
            $course_data['name'],
            $course_data['year'],
            $course_data['semester'],
            $course_data['teacher_name'],
            $student_id,
            $course_data['college_id']
        );

        if (!$stmt_insert_course->execute()) {
            $failed_courses[] = $course_id;
            $stmt_insert_course->close();
            continue;
        }
        
        $new_course_id = $stmt_insert_course->insert_id;
        $stmt_insert_course->close();

        // جلب مواعيد الكورس من جدول schedule الأصلي حسب المعلم الذي أنشأ الكورس
        $stmt_schedule = $conn->prepare("
            SELECT s.day, s.start_time, s.end_time
            FROM schedule s
            JOIN course c ON s.course_id = c.id
            WHERE s.course_id = ? AND c.user_id = (
                SELECT user_id FROM course WHERE id = ?
            )
        ");
        $stmt_schedule->bind_param("ii", $course_id, $course_id);
        $stmt_schedule->execute();
        $result_schedule = $stmt_schedule->get_result();

        $insert_success = true;
        while($row_schedule = $result_schedule->fetch_assoc()) {
            $stmt_insert = $conn->prepare("
                INSERT INTO schedule (course_id, user_id, day, start_time, end_time)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_insert->bind_param(
                "issss",
                $new_course_id,
                $student_id,
                $row_schedule['day'],
                $row_schedule['start_time'],
                $row_schedule['end_time']
            );
            if(!$stmt_insert->execute()) {
                $insert_success = false;
                $failed_courses[] = $course_id;
                break;
            }
            $stmt_insert->close();
        }
        $stmt_schedule->close();

        if($insert_success) {
            // جلب المهام المرتبطة بالكورس أولاً
            $stmt_tasks = $conn->prepare("SELECT task_id FROM task WHERE course_id = ?");
            $stmt_tasks->bind_param("i", $course_id);
            $stmt_tasks->execute();
            $result_tasks = $stmt_tasks->get_result();
            $tasks = [];
            while($row_task = $result_tasks->fetch_assoc()){
                $tasks[] = $row_task['task_id'];
            }
            $stmt_tasks->close();

            // إدخال المهام في جدول taskuser للطالب
            foreach($tasks as $task_id){
                $stmt_insert_task = $conn->prepare("
                    INSERT INTO taskuser (task_id, user_id, shared, accept)
                    VALUES (?, ?, 1, 1)
                ");
                $stmt_insert_task->bind_param("ii", $task_id, $student_id);
                $stmt_insert_task->execute();
                $stmt_insert_task->close();
            }

            $enrolled_courses[] = $course_id;
        }
    }

    if (count($enrolled_courses) > 0) {
        $success_message = "You have successfully enrolled in " . count($enrolled_courses) . " course(s)!";
    }
    
    if (count($failed_courses) > 0) {
        $error_message = "Failed to enroll in " . count($failed_courses) . " course(s). You may have already enrolled in them or there are duplicates.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Enroll Course</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url("https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image.jpg-ri2jUSpScDXjsulXIHz0LMu56qvFYI.jpeg") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        .topbar {
            background-color: rgba(59,59,59,0.9);
            color: white;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar .logo { font-style: italic; font-weight: bold; font-size: 18px; }
        .topbar .auth-links a { color:white; margin-left:20px; text-decoration:none; font-weight:bold; }
        .topbar .auth-links a:hover { text-decoration:underline; }

        .navbar {
            background-color: rgba(85,85,85,0.9);
            padding: 10px 30px;
        }
        .navbar ul { list-style:none; display:flex; gap:20px; margin:0; padding:0; align-items:center; }
        .navbar ul li a { color:white; text-decoration:none; font-size:14px; }
        .navbar ul li a:hover { text-decoration:underline; }

        .container {
            background: rgba(255,255,255,0.85);
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
        }
        .container h2 { margin-bottom:25px; font-size:22px; font-weight:bold; color:#333; text-align: center; }

        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-section label {
            font-weight: bold;
            margin-right: 5px;
            white-space: nowrap;
        }
        .filter-section select {
            padding: 8px 12px;
            border: 1px solid #aaa;
            border-radius: 5px;
            font-size: 14px;
            min-width: 200px;
        }
        .filter-section button {
            background-color: #f5b987;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .filter-section button:hover {
            background-color: #e3a86f;
        }

        .courses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .courses-table th, .courses-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .courses-table th {
            background-color: #f5b987;
            color: #333;
            font-weight: bold;
        }
        .courses-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .courses-table tr:hover {
            background-color: #f1f1f1;
        }
        .courses-table input[type="checkbox"] {
            transform: scale(1.2);
        }

        .submit-section {
            margin-top: 20px;
            text-align: center;
        }
        .submit-section button {
            background-color: #f5b987;
            color: #333;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
        }
        .submit-section button:hover {
            background-color: #e3a86f;
        }

        .success-message { 
            color: green; 
            margin-bottom: 15px; 
            padding: 10px;
            background-color: #e6ffe6;
            border-radius: 5px;
            border: 1px solid #b3ffb3;
        }
        .error-message { 
            color: red; 
            margin-bottom: 15px; 
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 5px;
            border: 1px solid #ffb3b3;
        }
        
        .no-courses {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
        
        .profile-icon {
            width: 24px;
            height: 24px;
            background-color: white;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
            background-size: 16px 16px;
            background-repeat: no-repeat;
            background-position: center;
            margin-right: 20px;
        }

        .profile-icon:hover {
            background-color: #f5b987;
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
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-section select {
                min-width: auto;
                width: 100%;
            }
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

<?php if ($_SESSION['user_type'] == "student"): ?>
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

<?php elseif ($_SESSION['user_type'] == "parent"): ?>
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
    <h2>Enroll in University Courses</h2>

    <?php if($success_message): ?>
        <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>
    <?php if($error_message): ?>
        <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- فلترة حسب الجامعة والكلية -->
    <div class="filter-section">
        <form method="GET">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="university">University:</label>
                    <select name="university" id="university"  disabled >
                        <?php if(empty($universities)): ?>
                            <option value="">No universities available</option>
                        <?php else: ?>
                            <option value="">-- All Universities --</option>
                            <?php foreach($universities as $id => $name): ?>
                                <option value="<?= htmlspecialchars($id) ?>" 
                                    <?= ($selected_university == $id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="college">College:</label>
                    <select name="college" id="college">
                        <option value="">-- All Colleges --</option>
                        <?php foreach($colleges as $college): ?>
                            <option value="<?= $college['id'] ?>" 
                                <?= ($selected_college == $college['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($college['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- عرض الكورسات في جدول -->
    <form method="POST">
        <?php if(count($courses) > 0): ?>
            <table class="courses-table">
                <thead>
                    <tr>
                        <th width="5%">Select</th>
                        <th width="20%">Course Name</th>
                        <th width="15%">Course Code</th>
                        <th width="15%">Teacher</th>
                        <th width="15%">University</th>
                        <th width="15%">College</th>
                        <th width="15%">Year/Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $course): ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="course_ids[]" value="<?= $course['id'] ?>">
                            </td>
                            <td><?= htmlspecialchars($course['course_name']) ?></td>
                            <td><?= htmlspecialchars($course['course']) ?></td>
                            <td><?= htmlspecialchars($course['teacher_name']) ?></td>
                            <td><?= htmlspecialchars($course['university_name']) ?></td>
                            <td><?= htmlspecialchars($course['college_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($course['year']) ?>/<?= htmlspecialchars($course['semester']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="submit-section">
                <button type="submit">Enroll in Selected Courses</button>
            </div>
        <?php else: ?>
            <div class="no-courses">
                <?php if(!empty($selected_university) || !empty($selected_college)): ?>
                    No courses available for the selected filters.
                <?php else: ?>
                    No courses available.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </form>
</div>

    <script>
        const notifications = <?= json_encode($notifications); ?>;
    </script>
    <script src="notifications.js"></script>
</body>
</html>