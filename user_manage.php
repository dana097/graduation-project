<?php
session_start();
include 'connection.php';

// التحقق أن المستخدم مسجل الدخول ومن حقه الوصول
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
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
// هنا يمكنك تحديد أن فقط ولي الأمر أو هيئة التدريس لديهم صلاحية الدخول لهذه الصفحة
if ($_SESSION['user_type'] == 0) {
    // إذا كان طالبًا، لا يسمح له بالدخول
    header("Location: profile.php");
    exit();
}

// معالجة الحذف إذا تم إرسال طلب حذف
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $stmt_del = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt_del->bind_param("i", $del_id);
    $stmt_del->execute();
    $stmt_del->close();
    // بعد الحذف أعد التوجيه بدون المعامل delete_id لتفادي حذف متكرر عند إعادة تحميل الصفحة
    header("Location: user_manage.php");
    exit();
}

// الحصول على قائمة المستخدمين
$result = $conn->query("SELECT id, first_name, last_name, email, user_type FROM users ORDER BY user_type, first_name");

$types = [
    0 => "Student",
    1 => "Parent",
    2 => "Faculty"
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users</title>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
body {
  margin: 0;
  padding: 0;
  font-family: Arial, sans-serif;
  background: url('./image.jpg') no-repeat center center fixed;
  background-size: cover;
}
.topbar {
  background-color: rgba(59, 59, 59, 0.9);
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
  display: flex;
  align-items: center;
}
.navbar ul li a:hover {
  text-decoration: underline;
}
.manage-box {
  background: rgba(255,255,255,0.95);
  width: 90%;
  max-width: 800px;
  margin: 60px auto;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0px 4px 15px rgba(0,0,0,0.5);
}
.manage-box h2 {
  margin-bottom: 20px;
  font-size: 20px;
  font-weight: bold;
  color: #333;
}
.table-users {
  width: 100%;
  border-collapse: collapse;
}
.table-users th, .table-users td {
  padding: 12px;
  border: 1px solid #ccc;
  text-align: left;
}
.table-users th {
  background-color: #3b3b3b;
  color: white;
}
.table-users tr:nth-child(even) {
  background-color: #f5f5f5;
}
.table-users tr:hover {
  background-color: #e1e1e1;
}
.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
  color: white;
}
.btn-edit {
  background-color: #f5b987;
}
.btn-edit:hover {
  background-color: #e0a36f;
}
.btn-delete {
  background-color: #d9534f;
}
.btn-delete:hover {
  background-color: #c9302c;
}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">Academic Organizer</div>
  <div class="auth-links">
    <a href="logout.php">Log Out</a>
  </div>
</div>

<div class="navbar">
    <ul>
        <!-- أيقونة البروفايل قبل زر AR -->
        <li>
            <a href="profile.php" title="Profile">
                <div class="profile-icon"></div>
            </a>
        </li>
        <li><a href="ar.php">AR</a></li>
        <li><a href="home.php">Home</a></li>
         <li><a href="user_manage.php">User Manage</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>
</div>

<div class="manage-box">
  <h2>All Users</h2>
  <table class="table-users">
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Type</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo $types[$row['user_type']] ?? 'Unknown'; ?></td>
        <td>
          <a class="btn btn-edit" href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a>
          <a class="btn btn-delete" href="user_manage.php?delete_id=<?php echo $row['id']; ?>"
             onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>
<script>
    const notifications = <?= json_encode($notifications); ?>;
</script>
<script src="notifications.js"></script>
</body>
</html>
