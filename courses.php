<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name']; // اسم المستخدم الحالي

// جلب قائمة الكليات من قاعدة البيانات
$colleges = [];
$stmt_colleges = $conn->prepare("SELECT id, name FROM college ORDER BY name ASC");
$stmt_colleges->execute();
$result_colleges = $stmt_colleges->get_result();
while ($row = $result_colleges->fetch_assoc()) {
    $colleges[] = $row;
}
$stmt_colleges->close();

// آخر 10 إشعارات
$stmt = $conn->prepare("SELECT id, message, timestamp, status FROM notification WHERE recipient=? ORDER BY timestamp DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while($row = $result->fetch_assoc()){
    $notifications[] = $row;
}

// إضافة كورس جديد
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['editIndex'])) {
    $course = $_POST['course'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $name = $_POST['name'];
    $teacher_name = $user_name; // اسم المدرّس يأتي من الجلسة
    $college_id = $_POST['college_id'];

    if (!empty($course) && !empty($year) && !empty($semester) && !empty($name) && !empty($college_id)) {
        $stmt = $conn->prepare("INSERT INTO course (course, year, semester, name, teacher_name, user_id, college_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssii", $course, $year, $semester, $name, $teacher_name, $user_id, $college_id);

        if ($stmt->execute()) {
            $success_message = "Course added successfully.";
        } else {
            $error_message = "Error adding course.";
        }

        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// تعديل كورس
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editIndex'])) {
    $id = $_POST['editIndex'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $name = $_POST['name'];
    $teacher_name = $user_name; // اسم المدرّس يأتي من الجلسة
    $college_id = $_POST['college_id'];

    if (!empty($course) && !empty($year) && !empty($semester) && !empty($name) && !empty($college_id)) {
        $stmt = $conn->prepare("UPDATE course SET course = ?, year = ?, semester = ?, name = ?, teacher_name = ?, college_id = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sisssiii", $course, $year, $semester, $name, $teacher_name, $college_id, $id, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Course updated successfully.'); window.location.href='courses.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error updating course.'); window.location.href='courses.php';</script>";
            exit();
        }

        $stmt->close();
    }
}

// حذف كورس
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM course WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('Course deleted successfully.'); window.location.href='courses.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to delete course.'); window.location.href='courses.php';</script>";
        exit();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Academic Organizer - Courses</title>
    <!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .topbar .logo {
            font-style: italic;
            font-weight: bold;
            font-size: 18px;
        }

        .topbar .auth-links a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .topbar .auth-links a:hover {
            text-decoration: underline;
        }

        .navbar {
            background-color: rgba(85,85,85,0.9);
            padding: 10px 30px;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .navbar ul li a:hover {
            text-decoration: underline;
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

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 150px;
            z-index: 1000;
            display: none;
            margin-top: 5px;
        }

        .profile-dropdown.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        /* Main container for schedule management */
        .schedule-container {
            background: rgba(255, 255, 255, 0.85);
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
        }

        .schedule-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .schedule-header h2 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .schedule-header p {
            color: #666;
            font-size: 14px;
        }

        /* Form for adding new courses */
        .add-course-form {
            background: rgba(245, 185, 135, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .add-course-form h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        .form-input, .form-select {
            padding: 12px;
            border: none;
            border-radius: 20px;
            background-color: #f5b987;
            font-size: 14px;
            outline: none;
            width: 100%;
        }

        .form-input::placeholder {
            color: #666;
        }

        .time-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
            align-items: end;
        }

        .time-separator {
            padding: 12px 0;
            text-align: center;
            font-weight: bold;
            color: #333;
        }

        .add-button {
            padding: 12px 30px;
            background-color: #3b3b3b;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 15px;
            margin-top: 10px;
        }

        .add-button:hover {
            background-color: #555;
        }

        /* Schedule table styling */
        .schedule-table-container {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            overflow: hidden;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            background-color: rgba(245, 185, 135, 0.9);
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            font-size: 16px;
        }

        .schedule-table td {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .schedule-table tr:last-child td {
            border-bottom: none;
        }

        .schedule-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Added edit and delete button styles */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }

        .edit-btn {
            background-color: #f5b987;
            color: #333;
        }

        .edit-btn:hover {
            background-color: #e6a876;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Added edit form styles */
        .edit-form {
            background: rgba(245, 185, 135, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: none;
        }

        .edit-form h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .cancel-btn {
            padding: 12px 30px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 15px;
            margin-top: 10px;
            margin-left: 10px;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
            text-align: center;
        }

        .empty-schedule {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .topbar {
                padding: 10px 15px;
                flex-direction: column;
                gap: 10px;
            }
            
            .navbar {
                padding: 10px 15px;
            }
            
            .navbar ul {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .schedule-container {
                width: 95%;
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .time-row {
                grid-template-columns: 1fr;
            }

            .schedule-table {
                font-size: 12px;
            }

            .schedule-table th,
            .schedule-table td {
                padding: 10px 8px;
            }
        }
        
 
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
.topbar .auth-links a {
    color: white; margin-left: 20px; text-decoration: none; font-weight: bold;
}
.topbar .auth-links a:hover { text-decoration: underline; }

.navbar {
    background-color: rgba(85,85,85,0.9);
    padding: 10px 30px;
}
.navbar ul {
    list-style: none;
    display: flex;
    gap: 20px;
    margin: 0;
    padding: 0;
    align-items: center;
}
.navbar ul li a {
    color: white;
    text-decoration: none;
    font-size: 14px;
}
.navbar ul li a:hover { text-decoration: underline; }
.profile-icon {
    width: 24px; height: 24px; background-color: white; border-radius: 50%;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555' viewBox='0 0 24 24'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
    background-size: 16px 16px;
    background-repeat: no-repeat;
    background-position: center;
    margin-right: 20px;
}
.profile-icon:hover { background-color: #f5b987; }

/* ===== محتوى Courses ===== */
.container {
    background: rgba(255, 255, 255, 0.85);
    width: 90%;
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
    text-align: center;
}
.container h2 {
    margin-bottom: 25px;
    font-size: 22px;
    font-weight: bold;
    color: #333;
}

/* ===== حقول الإدخال ===== */
.form-group {
    margin-bottom: 20px;
    text-align: left;
}
label {
    font-weight: bold;
    color: #333;
    display: block;
    margin-bottom: 5px;
}
input, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #aaa;
    border-radius: 8px;
    font-size: 14px;
}

/* ===== الأزرار ===== */
.buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}
button {
    background-color: #f5b987;
    color: #333;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
button:hover {
    background-color: #e3a86f;
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

  
<?php if ($_SESSION['user_type'] == "faculty"): ?>
        <div class="navbar">
        <ul>
            <li>
                <a href="profilefaculty.php" title="Profile">
                    <div class="profile-icon"></div>
                </a>
            </li>
            <li><a href="../home.php">Home</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="schedule.php">Schedule</a></li>  
            <li><a href="tasks-assignments.php">Tasks & Assignments</a></li>
 
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




<div class="schedule-container">

    <div class="schedule-header">
        <h2>Courses</h2>
    </div>

    <!-- رسالة نجاح أو خطأ -->
    <?php if (isset($success_message)): ?>
        <p style="color: green; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <p style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- نموذج إضافة كورس جديد -->
    <form id="add-form" method="POST" action="courses.php">
        <div class="form-group">
            <label for="course">Course Code</label>
            <input type="text" name="course" id="course" placeholder="Enter course" required />
        </div>

        <div class="form-group">
            <label for="name">Course Name</label>
            <input type="text" name="name" id="name" placeholder="Enter course name" required />
        </div>

        <div class="form-group">
            <label for="college_id">College</label>
            <select name="college_id" id="college_id" class="form-select" required>
                <option value="">Select College</option>
                <?php foreach($colleges as $college): ?>
                    <option value="<?= $college['id'] ?>"><?= htmlspecialchars($college['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
     
        <div class="form-group">
            <label for="year" class="form-label">Year</label>
            <select id="year" name="year" class="form-input" required>
                <option value="">Select Year</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option> 
                <option value="2026">2026</option>
            </select>
        </div>

        <div class="form-group">
            <label for="semester" class="form-label">Semester</label>
            <select id="semester" name="semester" class="form-input" required>
                <option value="">Select Semester</option>
                <option value="1">1</option>
                <option value="2">2</option> 
            </select>
        </div>

        <div class="form-group">
            <label for="teacher_name">Teacher Name</label>
            <input type="text" name="teacher_name" id="teacher_name" value="<?= htmlspecialchars($user_name) ?>" readonly />
        </div>
        <button type="submit" class="add-button">Add Course</button>
    </form>

    <!-- نموذج تعديل كورس (مخفي بشكل افتراضي) -->
    <form id="edit-form" method="POST" action="courses.php" style="display:none;  padding: 20px; border-radius: 10px; margin-bottom: 30px;">
        <input type="hidden" name="editIndex" id="editIndex" />
        <div class="form-group">
            <label for="edit_course">Course Code</label>
            <input type="text" name="course" id="edit_course" placeholder="Enter course" required />
        </div>
       
        <div class="form-group">
            <label for="edit_name">Course Name</label>
            <input type="text" name="name" id="edit_name" placeholder="Enter course name" required />
        </div>

        <div class="form-group">
            <label for="edit_college_id">College</label>
            <select name="college_id" id="edit_college_id" class="form-select" required>
                <option value="">Select College</option>
                <?php foreach($colleges as $college): ?>
                    <option value="<?= $college['id'] ?>"><?= htmlspecialchars($college['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="edit_year" class="form-label">Year</label>
            <select id="edit_year" name="year" class="form-input" required>
                <option value="">Select Year</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option> 
                <option value="2026">2026</option>
            </select>
        </div>

        <div class="form-group">
            <label for="edit_semester" class="form-label">Semester</label>
            <select id="edit_semester" name="semester" class="form-input" required>
                <option value="">Select Semester</option>
                <option value="1">1</option>
                <option value="2">2</option> 
            </select>
        </div>
 
        <div class="form-group">
            <label for="edit_teacher_name">Teacher Name</label>
            <input type="text" name="teacher_name" id="edit_teacher_name" value="<?= htmlspecialchars($user_name) ?>" readonly />
        </div>
        <button type="submit" class="add-button">Update Course</button>
        <button type="button" class="cancel-btn" onclick="cancelEdit()">Cancel</button>
    </form>

    <!-- جدول عرض الكورسات -->
    <div class="schedule-table-container">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>College</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Teacher Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $conn->prepare("
                SELECT c.id, c.course, c.year, c.semester, c.name, c.teacher_name, c.college_id, cl.name as college_name 
                FROM course c 
                LEFT JOIN college cl ON c.college_id = cl.id 
                WHERE c.user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['course']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['college_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['year']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td> 
                    <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="edit-btn" onclick='editCourse(<?= json_encode($row) ?>)'>Edit</button>
                            <a href="courses.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this course?');" class="delete-btn">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; $stmt->close(); ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function editCourse(course) {
        document.getElementById('edit-form').style.display = 'block';
        document.getElementById('add-form').style.display = 'none';

        document.getElementById('editIndex').value = course.id;
        document.getElementById('edit_course').value = course.course;
        document.getElementById('edit_name').value = course.name;
        document.getElementById('edit_college_id').value = course.college_id;
        document.getElementById('edit_year').value = course.year;
        document.getElementById('edit_semester').value = course.semester;
        document.getElementById('edit_teacher_name').value = course.teacher_name;

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function cancelEdit() {
        document.getElementById('edit-form').style.display = 'none';
        document.getElementById('add-form').style.display = 'block';

        // إعادة تعيين حقول التعديل
        document.getElementById('edit-form').reset();
    }
</script>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>