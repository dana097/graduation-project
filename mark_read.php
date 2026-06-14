<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $notif_id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE notification SET status='read' WHERE id=? AND recipient=?");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>
