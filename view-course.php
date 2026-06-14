<?php
session_start();
include 'connection.php';

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
$success_message = "";
$error_message = "";

  
 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editIndex'])) {
             
    $id = $_POST['editIndex'];
     
    $course = $_POST['course'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $name = $_POST['name'];
    $teacher_name = $_POST['teacher_name'];


    if (!empty($course) && !empty($year) && !empty($semester) && !empty($name) && !empty($teacher_name)) {

        // تجهيز الاستعلام
        $stmt = $conn->prepare("UPDATE  course  SET course= ?, year= ?, semester= ?, name= ?, teacher_name= ? WHERE id = ? ");
        $stmt->bind_param("sisssi", $course, $year, $semester, $name, $teacher_name, $id);

        if ($stmt->execute()) {
         echo "<script>alert('Course Updated successfully.'); window.location.href='view-course.php?show=course';</script>";

        } else {
       echo "<script>alert('Error adding course:'); window.location.href='view-course.php?show=course';</script>";

        }
    
        $stmt->close();
    }
}

 
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM course WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    if ($stmt->execute()) {
        
   echo "<script>alert('Course deleted successfully.'); window.location.href='view-course.php?show=course';</script>";

    } else {
   echo "<script>alert('Failed to delete course.'); window.location.href='view-course.php?show=course';</script>";

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
        <a>Hello , <?php  echo $_SESSION['user_name']; ?></a>
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
            <li><a href="courses.php">Courses</a></li> <!-- تمت إضافتها هنا -->
            <li><a href="schedule.php">Schedule</a></li>
            <li><a href="tasks-assignments.php">Tasks & Assignments</a></li>
            <li><a href="#">Performance Analysis</a></li>
            <li><a href="shared.php">Shared Calendars</a></li>
            <li><a href="add_guardian.php">Add Guardian</a></li>
              <li><a href="gamification.php">Gamification</a></li>
      <li><a href="courseassgin.php">university course</a></li>
            </ul>
    </div>

     <div class="schedule-container">
                <!-- Edit course form -->
        <div class="edit-form" id="editCourseForm">
            <h3>Edit Course</h3>
       <form id="editForm" method="POST" action="">  

        <input type="hidden" id="editIndex" name="editIndex">
                
              
        <div class="form-group">
            <label for="course">Course Code</label>
            <input type="text" id="course" name="course"  class="form-input"  placeholder="Enter course code...">
        </div>

        <div class="form-group">
            <label for="name">Course Name</label>
            <input type="text" id="name" name="name"  class="form-input"  placeholder="Enter course name...">
        </div>

        <div class="form-group">
            <label for="year">Year</label>
            <input type="text" id="year" name="year" class="form-input" placeholder="Enter year...">
        </div>

        <div class="form-group">
            <label for="semester">Semester</label>
            <input type="text" id="semester" name="semester" class="form-input" placeholder="Enter semester...">
        </div>

        <div class="form-group">
            <label for="teacher_name">Teacher Name</label>
            <input type="text" id="teacher_name" name="teacher_name" class="form-input" placeholder="Enter teacher name...">
        </div>

    

            
 
                

                <input type="submit" class="add-button"> </input>

                <button type="button" class="cancel-btn" onclick="cancelEdit()">
                    Cancel
                </button>
            </form>
         


        </div>

        <div class="schedule-header">
            <h2>My Courses </h2>
         </div>
        <div class="schedule-table-container">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Semester</th>
                        <th>Name</th>
                        <th>Teacher Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="scheduleTableBody">
                    
        
                    <?php
                    // جلب الجدول للمستخدم الحالي
                    $sql = "SELECT  id,course, year, semester, name, teacher_name  
                            FROM course                   
                            WHERE user_id = ?";                    
        
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?> </td>
                            <td><?= htmlspecialchars($row['course']) ?> </td>
                            <td><?= htmlspecialchars($row['year']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>

                            <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                            <td>
                                <button class="edit-btn" onclick='editCourse(<?= json_encode($row) ?>)'>Edit</button>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this schedule?');">🗑️</a>
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
     </div>
    <script>
    function editCourse(data) {
        document.getElementById("editCourseForm").style.display = "block";
        document.getElementById("editForm").scrollIntoView({ behavior: "smooth" });

document.getElementById("editIndex").value = data.id;
        document.getElementById("course").value = data.course;
        document.getElementById("name").value = data.name;
        document.getElementById("year").value = data.year;
        document.getElementById("semester").value = data.semester;
        document.getElementById("teacher_name").value = data.teacher_name;

         
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
