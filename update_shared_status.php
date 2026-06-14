<?php
session_start();
header('Content-Type: application/json'); // نُعلم المتصفح أن الناتج JSON فقط
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? 0;
$response = ["success" => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = intval($_POST['schedule_id'] ?? 0);

    if ($schedule_id && $user_id) {
        $stmt = $conn->prepare("UPDATE scheduleuser SET shared = 1 WHERE schedule_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $schedule_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = ["success" => true];
        } else {
            $response = ["success" => false, "message" => "No rows updated."];
        }

        $stmt->close();
    } else {
        $response = ["success" => false, "message" => "Invalid data."];
    }
}

echo json_encode($response);
exit;
