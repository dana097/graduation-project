<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id'])) {
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
$showTasks = isset($_GET['show']) && $_GET['show'] === 'tasks';

// =====================
// الحصول على الكورسات
// =====================
$courses = [];
$stmt = $conn->prepare("SELECT id, name FROM course WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();

// ========== حذف مهمة ==========
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("
        DELETE task 
        FROM task
        JOIN course ON task.course_id = course.id
        WHERE task.task_id = ? AND course.user_id = ?
    ");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('Task deleted successfully!'); window.location.href='tasks-assignments.php?show=tasks';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting task: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
}

// ========== الحصول على بيانات مهمة للتعديل ==========
$editTaskData = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("
        SELECT task.* 
        FROM task 
        JOIN course ON task.course_id = course.id
        WHERE task.task_id = ? AND course.user_id = ?
    ");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $editTaskData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ========== تعديل مهمة ==========
if (isset($_POST['update_task'])) {
    $id = intval($_POST['task_id']);
    $semester = $_POST['editSemester'];
    $course = $_POST['editCourse'];
    $description = $_POST['editTask'];
    $assignmentType = $_POST['editAssignmentType'];
    $dueDate = $_POST['editDueDate'];
    $submissionTime = $_POST['editSubmissionTime'];

    // تعديل المهمة فقط إذا الكورس تابع للمستخدم
    $stmt = $conn->prepare("
        UPDATE task 
        JOIN course ON task.course_id = course.id
        SET task.semester=?, task.course_id=?, task.description=?, task.assigment_type=?, task.due_date=?, task.submission_time=?
        WHERE task.task_id=? AND course.user_id=?
    ");
    $stmt->bind_param("sissssii", $semester, $course, $description, $assignmentType, $dueDate, $submissionTime, $id, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('Task updated successfully!'); window.location.href='tasks-assignments.php?show=tasks';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating task: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
}

// ========== إضافة مهمة جديدة ==========
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['semester'])) {
    $semester = $_POST['semester'];
    $course = $_POST['course'];
    $description = $_POST['task'];
    $assignmentType = $_POST['assignmentType'];
    $dueDate = $_POST['dueDate'];
    $submissionTime = $_POST['submissionTime'];

    $stmt = $conn->prepare("INSERT INTO task (semester, course_id, description, assigment_type, due_date, submission_time, complete) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sissss", $semester, $course, $description, $assignmentType, $dueDate, $submissionTime);
    
    if ($stmt->execute()) {
        echo "<script>alert('Task added successfully!'); window.location.href='tasks-assignments.php?show=tasks';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding task: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
}

// ========== عرض المهام ==========
$tasks = [];
$stmt = $conn->prepare("
    SELECT task.*, course.name AS course_name 
    FROM task 
    JOIN course ON task.course_id = course.id 
    WHERE course.user_id = ? 
    ORDER BY task.due_date ASC, task.submission_time ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
$stmt->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Organizer - Tasks & Assignments</title>
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

        /* Main container */
        .main-container {
            background: rgba(255, 255, 255, 0.85);
            width: 95%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
        }

        /* Professional navigation section with main action buttons */
        .navigation-section {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(139, 115, 85, 0.8);
            border-radius: 10px;
        }

        .nav-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .nav-btn-primary {
            background-color: #f5b987;
            color: #333;
        }

        .nav-btn-secondary {
            background-color: #3b3b3b;
            color: white;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .nav-btn.active {
            background-color: #e6a876;
            transform: translateY(-1px);
        }

        /* Content sections */
        /* .content-section {
            display: none;
        } */

        .content-section.active {
            display: block;
        }

        /* New Task form styling */
        .new-task-section {
            background: rgba(245, 185, 135, 0.3);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
        }

        .form-input {
            padding: 12px 15px;
            border: 2px solid transparent;
            border-radius: 8px;
            background-color: #f5b987;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #3b3b3b;
            box-shadow: 0 0 0 3px rgba(59, 59, 59, 0.1);
        }

        .form-input::placeholder {
            color: #666;
        }

        /* Action buttons section */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            transition: all 0.3s ease;
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

        .btn-type {
            background-color: #2196F3;
            color: white;  transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }

        /* Tasks display section */
        .tasks-display-section {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }

        .tasks-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tasks-table th {
            background-color: rgba(245, 185, 135, 0.9);
            color: #333;
            padding: 15px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }

        .tasks-table td {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
        }

        .tasks-table tr:last-child td {
            border-bottom: none;
        }

        .tasks-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .status-complete {
            background-color: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-incomplete {
            background-color: #f44336;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-overdue {
            background-color: #ff9800;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .btn-share{
            color: #f5f5f5;
        background-color: #ff9800;

        }
            .status-pending {
                background-color: #2196F3;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: bold;
            }

            .empty-tasks {
                text-align: center;
                color: #666;
                padding: 40px;
                font-style: italic;
                font-size: 16px;
            }

            /* Action buttons in table */
            .task-actions {
                display: flex;
                gap: 8px;
                align-items: center;
            }

            .task-action-btn {
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                font-weight: bold;
                transition: all 0.2s ease;
            }

            .btn-edit {
                background-color: #2196F3;
                color: white;
            }

            .btn-delete {
                background-color: #f44336;
                color: white;
            }

            .task-action-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }

            /* Edit form modal */
            .edit-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
            }

            .edit-modal.show {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .edit-modal-content {
                background: rgba(255, 255, 255, 0.95);
                padding: 30px;
                border-radius: 10px;
                width: 90%;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
            }

            .modal-title {
                color: #333;
                margin-bottom: 20px;
                font-size: 20px;
                font-weight: bold;
                text-align: center;
            }

            .modal-buttons {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 20px;
            }

            .btn-save {
                background-color: #4CAF50;
                color: white;
            }

            .btn-cancel {
                background-color: #666;
                color: white;
            }
            /* تحسين مظهر الـ checkbox */
            .tasks-table input[type="checkbox"] {
                width: 18px;
                height: 18px;
                accent-color: #4CAF50;
            }

            .tasks-table input[type="checkbox"]:checked {
                background-color: #4CAF50;
            }

            @media (max-width: 768px) {
                .navigation-section {
                    flex-direction: column;
                    align-items: center;
                }
                
                .form-grid {
                    grid-template-columns: 1fr;
                }
                
                .action-buttons {
                    flex-direction: column;
                    align-items: center;
                }
                
                .tasks-table {
                    font-size: 11px;
                }
                
                .tasks-table th,
                .tasks-table td {
                    padding: 8px 6px;
                }

                /* Added responsive design for task actions */
                .task-actions {
                    flex-direction: column;
                    gap: 4px;
                }

                .task-action-btn {
                    font-size: 10px;
                    padding: 4px 8px;
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


    <div class="main-container">
        <!-- Professional navigation with main buttons -->
       

        <!-- New Task Section -->
        <div class="content-section"  id="newTaskSection">
            <div class="new-task-section">
                <h2 class="section-title">Add New Task/Assignment</h2>
                <form id="taskForm" method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="semester" class="form-label">Semester</label>
                            <select 
                                id="semester" 
                                name="semester" 
                                class="form-input" 
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="1">1</option>
                                <option value="2">2</option> 
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editCourse" class="form-label">Course</label>
                                <select id="course" name="course" class="form-input" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= htmlspecialchars($course['id']) ?>">
                                            <?= htmlspecialchars($course['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                        </div>
                        <div class="form-group">
                            <label for="task" class="form-label">Task Description</label>
                            <input 
                                type="text" 
                                id="task" 
                                name="task" 
                                class="form-input" 
                                placeholder="e.g., Develop Security Policy"
                                 
                            >
                        </div>
                        <div class="form-group">
                            <label for="assignmentType" class="form-label">Assignment Type</label>
                            <select 
                                id="assignmentType" 
                                name="assignmentType" 
                                class="form-input" 
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="Individual">Individual</option>
                                <option value="Group">Group</option>
                                <option value="Project">Project</option>
                                <option value="Exam">Exam</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Research">Research</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dueDate" class="form-label">Due Date</label>
                                              
                        <input type="date" id="dueDate" name="dueDate"  class="form-input" 
                                required>
                              
                        </div>
                        <div class="form-group">
                            <label for="submissionTime" class="form-label">Submission Time</label>
                            <select 
                                id="submissionTime" 
                                name="submissionTime" 
                                class="form-input" 
                                required
                            >
                                <option value="">Select Time</option>
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
                            </select>
                        </div>
                    </div>

                    <!-- Action buttons as requested -->
                    <!-- <div class="action-buttons"> -->
                        <button type="submit" class="action-btn  add-button">Add</button>
                    <!-- </div> -->
                </form>
            </div>
        </div>

        <!-- Tasks Display Section -->
        <!-- Tasks Display Section -->
<div class="content-section"  id="tasksSection">
    <div class="tasks-display-section">
        <table class="tasks-table">
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Task</th>
                    <th>Type</th>
                    <th>Due Date</th>
                    <th>Time</th>
                     <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tasksTableBody">
                <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="8" class="empty-tasks">
                            No tasks added yet. Use "New Task" to add your first task.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <?php
                        $dueDate = new DateTime($task['due_date']);
                        $today = new DateTime();
                        $status = '';
                        $statusClass = '';
                        
                        if ($task['complete'] == 1) {
                            $status = 'Complete';
                            $statusClass = 'status-complete';
                        } else if ($dueDate < $today) {
                            $status = 'Overdue';
                            $statusClass = 'status-overdue';
                        } else {
                            $status = 'Pending';
                            $statusClass = 'status-pending';
                        }
                        
                        $formattedDate = $dueDate->format('M j, Y');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['semester']); ?></td>
                            <td><?php echo htmlspecialchars($task['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <td><?php echo htmlspecialchars($task['assigment_type']); ?></td>
                            <td><?php echo $formattedDate; ?></td>
                            <td><?php echo htmlspecialchars($task['submission_time']); ?></td>
                          
                            <td>
                                <div class="task-actions">
 
                                    <button class="task-action-btn btn-edit" onclick="editTask(<?php echo $task['task_id']; ?>)">Edit</button>
                                    <button class="task-action-btn btn-delete" onclick="deleteTask(<?php echo $task['task_id']; ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
    </div>

     <!-- Edit Task Modal -->
<div class="edit-modal <?php echo (isset($_GET['edit']) && $editTaskData) ? 'show' : ''; ?>" id="editModal">
    <div class="edit-modal-content">
        <h2 class="modal-title">Edit Task</h2>
        <form id="editTaskForm" method="POST" action="">
            <input type="hidden" id="editTaskId" name="task_id" value="<?php echo isset($editTaskData['task_id']) ? $editTaskData['task_id'] : ''; ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="editSemester" class="form-label">Semester</label>
                    <input 
                        type="text" 
                        id="editSemester" 
                        name="editSemester" 
                        class="form-input" 
                        value="<?php echo isset($editTaskData['semester']) ? htmlspecialchars($editTaskData['semester']) : ''; ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="editCourse" class="form-label">Course</label>
                  <select id="editCourse" name="editCourse" class="form-input" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>" 
                            <?= (isset($editTaskData['course_id']) && $editTaskData['course_id'] == $course['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </div>
                <div class="form-group">
                    <label for="editTask" class="form-label">Task Description</label>
                    <input 
                        type="text" 
                        id="editTask" 
                        name="editTask" 
                        class="form-input" 
                        value="<?php echo isset($editTaskData['description']) ? htmlspecialchars($editTaskData['description']) : ''; ?>"
                         
                    >
                </div>
                <div class="form-group">
                    <label for="editAssignmentType" class="form-label">Assignment Type</label>
                    <select 
                        id="editAssignmentType" 
                        name="editAssignmentType" 
                        class="form-input" 
                        required
                    >
                        <option value="Individual" <?php echo (isset($editTaskData['assigment_type']) && $editTaskData['assigment_type'] == 'Individual') ? 'selected' : ''; ?>>Individual</option>
                        <option value="Group" <?php echo (isset($editTaskData['assigment_type']) && $editTaskData['assigment_type'] == 'Group') ? 'selected' : ''; ?>>Group</option>
                        <option value="Project" <?php echo (isset($editTaskData['assigment_type']) && $editTaskData['assigment_type'] == 'Project') ? 'selected' : ''; ?>>Project</option>
                        <option value="Exam" <?php echo (isset($editTaskData['assigment_type']) && $editTaskData['assigment_type'] == 'Exam') ? 'selected' : ''; ?>>Exam</option>
                        <option value="Quiz" <?php echo (isset($editTaskData['assigment_type']) && $editTaskData['assigment_type'] == 'Quiz') ? 'selected' : ''; ?>>Quiz</option>
                        <option value="Research" <?php echo (isset($editTaskData['assigment_type']) && $editTaskData['assigment_type'] == 'Research') ? 'selected' : ''; ?>>Research</option>
                    </select>
                </div>
            <div class="form-group">
                <label for="editDueDate" class="form-label"><? echo $editTaskData['due_date']; ?>Due Date</label>
                <input 
                    type="date" 
                    id="editDueDate" 
                    name="editDueDate"  
                    class="form-input" 
                    required
                    value="<?php echo isset($editTaskData['due_date']) ? htmlspecialchars($editTaskData['due_date']) : ''; ?>"
                >
            </div>

                <div class="form-group">
                    <label for="editSubmissionTime" class="form-label">Submission Time</label>
                    <select 
                        id="editSubmissionTime" 
                        name="editSubmissionTime" 
                        class="form-input" 
                        required
                    >
                        <option value="07:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '07:00') ? 'selected' : ''; ?>>7:00 AM</option>
                        <option value="07:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '07:30') ? 'selected' : ''; ?>>7:30 AM</option>
                        <option value="08:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '08:00') ? 'selected' : ''; ?>>8:00 AM</option>
                        <option value="08:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '08:30') ? 'selected' : ''; ?>>8:30 AM</option>
                        <option value="09:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '09:00') ? 'selected' : ''; ?>>9:00 AM</option>
                        <option value="09:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '09:30') ? 'selected' : ''; ?>>9:30 AM</option>
                        <option value="10:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '10:00') ? 'selected' : ''; ?>>10:00 AM</option>
                        <option value="10:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '10:30') ? 'selected' : ''; ?>>10:30 AM</option>
                        <option value="11:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '11:00') ? 'selected' : ''; ?>>11:00 AM</option>
                        <option value="11:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '11:30') ? 'selected' : ''; ?>>11:30 AM</option>
                        <option value="12:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '12:00') ? 'selected' : ''; ?>>12:00 PM</option>
                        <option value="12:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '12:30') ? 'selected' : ''; ?>>12:30 PM</option>
                        <option value="13:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '13:00') ? 'selected' : ''; ?>>1:00 PM</option>
                        <option value="13:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '13:30') ? 'selected' : ''; ?>>1:30 PM</option>
                        <option value="14:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '14:00') ? 'selected' : ''; ?>>2:00 PM</option>
                        <option value="14:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '14:30') ? 'selected' : ''; ?>>2:30 PM</option>
                        <option value="15:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '15:00') ? 'selected' : ''; ?>>3:00 PM</option>
                        <option value="15:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '15:30') ? 'selected' : ''; ?>>3:30 PM</option>
                        <option value="16:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '16:00') ? 'selected' : ''; ?>>4:00 PM</option>
                        <option value="16:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '16:30') ? 'selected' : ''; ?>>4:30 PM</option>
                        <option value="17:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '17:00') ? 'selected' : ''; ?>>5:00 PM</option>
                        <option value="17:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '17:30') ? 'selected' : ''; ?>>5:30 PM</option>
                        <option value="18:00" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '18:00') ? 'selected' : ''; ?>>6:00 PM</option>
                        <option value="18:30" <?php echo (isset($editTaskData['submission_time']) && $editTaskData['submission_time'] == '18:30') ? 'selected' : ''; ?>>6:30 PM</option>
                    </select>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="submit" name="update_task" class="action-btn btn-save">Save Changes</button>
                <button type="button" class="action-btn btn-cancel" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Share Task Modal -->
<div class="edit-modal" id="shareModal" style="display: none;">
    <div class="edit-modal-content">
        <h2 class="modal-title">Share Task</h2>
        <form method="POST" action="">
            <input type="hidden" name="shared_task_id" id="sharedTaskId">
            <div class="form-group">
                <label for="studentEmail" class="form-label">Student Email</label>
                <input type="email" name="student_email" id="studentEmail" class="form-input" required>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="share_task" class="action-btn btn-save">Share</button>
                <button type="button" class="action-btn btn-cancel" onclick="closeShareModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

 <script>
  

    function setActiveButton(activeId) {
        // Remove active class from all buttons
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Add active class to clicked button
        document.getElementById(activeId).classList.add('active');
    }

    function toggleTaskStatus(taskId) {
        // تحديث الحالة في قاعدة البيانات
        window.location.href = 'tasks-assignments.php?toggle_status=' + taskId + '&show=tasks';
    }

    function editTask(taskId) {
        // Redirect to edit mode with task ID
        window.location.href = 'tasks-assignments.php?edit=' + taskId + '&show=tasks';
    }

    function deleteTask(taskId) {
        if (confirm('Are you sure you want to delete this task?')) {
            window.location.href = 'tasks-assignments.php?delete=' + taskId + '&show=tasks';
        }
    }

    function closeEditModal() {
        window.location.href = 'tasks-assignments.php?show=tasks';
    }

    
    // function sheardTask(taskId) {
    //     document.getElementById('sharedTaskId').value = taskId;
    //     document.getElementById('shareModal').style.display = 'block';
    // }

    function closeShareModal() {
        document.getElementById('shareModal').style.display = 'none';
    }

document.getElementById('shareModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeShareModal();
    }
});



    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    // Auto-open edit modal if in edit mode
    <?php if (isset($_GET['edit']) && $editTaskData): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('editModal').classList.add('show');
        });
    <?php endif; ?>

    // Initialize navigation based on current state
    <?php if ($showTasks): ?>
        setActiveButton('tasksBtn');
    <?php else: ?>
        setActiveButton('newTaskBtn');
    <?php endif; ?>
</script>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>