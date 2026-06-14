<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id'])) {
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
$success_message = "";
$error_message = "";

// جلب الكورسات الخاصة بالمستخدم
$courses = [];
$sql = "SELECT id, name FROM course WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[] = $row; // يحتوي على [id => ..., name => ...]
}
 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editIndex'])) {
    $schedule_id = $_POST['editIndex'];
    $course_name = $_POST['editCourseName']; // Optional: not used in DB
    $day = $_POST['editCourseDay'];
    $start = $_POST['editStartTime'];
    $end = $_POST['editEndTime'];

    if (strtotime($end) <= strtotime($start)) {
          echo "<script>alert('End time must be after start time'); window.location.href='schedule.php?show=course';</script>";

       
    } else {
        $stmt = $conn->prepare("UPDATE schedule SET day = ?, start_time = ?, end_time = ? WHERE schedule_id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $day, $start, $end, $schedule_id, $user_id);
        if ($stmt->execute()) {
        echo "<script>alert('Schedule updated successfully!'); window.location.href='schedule.php?show=course';</script>";

             
        } else {
        echo "<script>alert('Error updating schedule:'); window.location.href='schedule.php?show=course';</script>";
 
        }
        $stmt->close();
    }
}

// معالجة النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addrecord'])) {
    $course_id = $_POST['course_id'];
    $courseDay = $_POST['courseDay'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
   
    if (!empty($course_id) && !empty($courseDay) && !empty($startTime) && !empty($endTime)) {
        if (strtotime($endTime) <= strtotime($startTime)) {
            $error_message = "End time must be after start time.";
        } else {
            // استخراج اسم الكورس بناءً على ID لتخزينه في عمود name أيضًا (إن لزم الأمر)
            $stmt = $conn->prepare("SELECT name FROM course WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $course_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($course_name);
            $stmt->fetch();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO schedule (course_id, start_time, end_time, day, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $course_id, $startTime, $endTime, $courseDay, $user_id);

            if ($stmt->execute()) {
                           echo "<script>alert('Course schedule added successfully!'); window.location.href='schedule.php?show=course';</script>";

             } else {
              echo "<script>alert('Database error:'); window.location.href='schedule.php?show=course';</script>";

            }

            $stmt->close();
        }


    } else {
       echo "<script>alert('Please fill in all fields.'); window.location.href='schedule.php?show=course';</script>";
 
    }
}
if (isset($_GET['delete'])) {
    $schedule_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $schedule_id, $user_id);
    if ($stmt->execute()) {
            echo "<script>alert('Course deleted successfully!'); window.location.href='schedule.php?show=course';</script>";

    } else {
    echo "<script>alert('Failed to delete course'); window.location.href='schedule.php?show=course';</script>";

    }
    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Organizer - Student Schedule</title>
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
            max-width: 1000px;
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

        .form-input {
            padding: 12px;
            border: none;
            border-radius: 20px;
            background-color: #f5b987;
            font-size: 14px;
            outline: none;
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
            <h2>Add New Schedules</h2>
            <p>Add and manage your weekly class schedule</p>
        </div>

   
        <!-- Form to add new courses -->
        <div class="add-course-form">
            <h3></h3>
            <form id="courseForm" method="POST" action="">  
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_id" class="form-label">Select Course</label>
                        <select id="course_id" name="course_id" class="form-input" required>
                            <option value="">Choose a course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['id']) ?>">
                                    <?= htmlspecialchars($course['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="courseDay" class="form-label">Day</label>
                        <select 
                            id="courseDay" 
                            name="courseDay" 
                            class="form-input" 
                            required
                        >
                            <option value="">Select Day</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                </div>
                
                <div class="time-row">
                    <div class="form-group">
                        <label for="startTime" class="form-label">Start Time</label>
                        <select 
                            id="startTime" 
                            name="startTime" 
                            class="form-input" 
                            required
                        >
                            <option value="">Select Start Time</option>
                            <option value="07:00">7:00 AM</option>
                            <option value="07:30">7:30 AM</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                        </select>
                    </div>
                    <div class="time-separator">to</div>
                    <div class="form-group">
                        <label for="endTime" class="form-label">End Time</label>
                        <select 
                            id="endTime" 
                            name="endTime" 
                            class="form-input" 
                            required
                        >
                            <option value="">Select End Time</option>
                            <option value="07:30">7:30 AM</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                            <option value="20:30">8:30 PM</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="add-button" name="addrecord">
                    Add Course
                </button>
            </form>
        </div>

        <!-- Edit course form -->
        <div class="edit-form" id="editCourseForm">
            <h3>Edit Course</h3>
       <form id="editForm" method="POST" action="">  
                <input type="hidden" id="editIndex" name="editIndex">
                <div class="form-row">
                    <div class="form-group">
                        <label for="editCourseName" class="form-label">Course Name</label>
                        <input 
                            type="text" 
                            id="editCourseName" 
                            name="editCourseName" 
                            class="form-input" 
                            placeholder="e.g., Information Security"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="editCourseDay" class="form-label">Day</label>
                        <select 
                            id="editCourseDay" 
                            name="editCourseDay" 
                            class="form-input" 
                            required
                        >
                            <option value="">Select Day</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                </div>
                
                <div class="time-row">
                    <div class="form-group">
                        <label for="editStartTime" class="form-label">Start Time</label>
                        <select 
                            id="editStartTime" 
                            name="editStartTime" 
                            class="form-input" 
                            required
                        >
                            <option value="">Select Start Time</option>
                            <option value="07:00">7:00 AM</option>
                            <option value="07:30">7:30 AM</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                        </select>
                    </div>
                    <div class="time-separator">to</div>
                    <div class="form-group">
                        <label for="editEndTime" class="form-label">End Time</label>
                        <select 
                            id="editEndTime" 
                            name="editEndTime" 
                            class="form-input" 
                            required
                        >
                            <option value="">Select End Time</option>
                            <option value="07:30">7:30 AM</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                            <option value="20:30">8:30 PM</option>
                        </select>
                    </div>
                </div>
                <input type="submit" class="add-button"> </input>

                <button type="button" class="cancel-btn" onclick="cancelEdit()">
                    Cancel
                </button>
            </form>
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>


        </div>

<!-- Schedule table display -->
<div class="schedule-table-container">
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Course</th>
                <th>Day</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="scheduleTableBody">
            <?php
            // جلب الجدول للمستخدم الحالي
            $sql = "SELECT s.schedule_id, s.course_id, s.start_time, s.end_time, s.day, c.name as course_name
                    FROM schedule s
                    JOIN course c ON s.course_id = c.id
                    WHERE s.user_id = ?
                    ORDER BY FIELD(s.day, 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), s.start_time";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['day']) ?></td>
                    <td>
                        <button class="edit-btn" onclick='editCourse(<?= json_encode($row) ?>)'>Edit</button>
                        <a href="?delete=<?= $row['schedule_id'] ?>" onclick="return confirm('Are you sure you want to delete this schedule?');">🗑️</a>
                    </td>
                </tr>
            <?php
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="4" class="empty-schedule">
                        No courses added yet. Use the form above to add your first course.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


    <script>
    function editCourse(data) {
        document.getElementById("editCourseForm").style.display = "block";
        document.getElementById("editForm").scrollIntoView({ behavior: "smooth" });

        document.getElementById("editIndex").value = data.schedule_id;
        document.getElementById("editCourseDay").value = data.day;
        document.getElementById("editStartTime").value = data.start_time;
        document.getElementById("editEndTime").value = data.end_time;
        
        // اختياري: عرض اسم الدورة
        document.getElementById("editCourseName").value = data.course_name;
    }

    function cancelEdit() {
        document.getElementById("editCourseForm").reset();
        document.getElementById("editCourseForm").style.display = "none";
    }
    </script>


 
        
      
          
   

        
        
 <script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>         
</body>
</html>
