<?php
session_start();
if (!isset($_SESSION['UserLogin'])) {
    echo json_encode(['error' => 'Error: User ID not found in session.']);
    exit;
}

include_once("../connection/connection.php");
$conn = connection();

$user_id = $_SESSION['UserLogin'];
$response = [];

// Check if the user has submitted a solo candidate application
$sql_solo_check = "SELECT COUNT(*) as count FROM solo_candidate_application WHERE id = ?";
$stmt_solo_check = $conn->prepare($sql_solo_check);
$stmt_solo_check->bind_param("i", $user_id);
$stmt_solo_check->execute();
$result_solo_check = $stmt_solo_check->get_result();
$row_solo_check = $result_solo_check->fetch_assoc();
$solo_count = $row_solo_check['count'];

// Check if the user has submitted a partylist candidate application
$sql_partylist_check = "SELECT COUNT(*) as count FROM partylist_candidate_application WHERE id = ?";
$stmt_partylist_check = $conn->prepare($sql_partylist_check);
$stmt_partylist_check->bind_param("i", $user_id);
$stmt_partylist_check->execute();
$result_partylist_check = $stmt_partylist_check->get_result();
$row_partylist_check = $result_partylist_check->fetch_assoc();
$partylist_count = $row_partylist_check['count'];

if ($solo_count > 0) {
    // Fetch notifications for solo candidate applications
    $sql_solo = "SELECT application_status, reason FROM solo_candidate_application WHERE id = ?";
    $stmt_solo = $conn->prepare($sql_solo);
    $stmt_solo->bind_param("i", $user_id);
    $stmt_solo->execute();
    $result_solo = $stmt_solo->get_result();

    if ($result_solo->num_rows > 0) {
        while ($row = $result_solo->fetch_assoc()) {
            $response[] = [
                'type' => 'solo',
                'status' => $row['application_status'],
                'reason' => $row['reason']
            ];
        }
    }
} elseif ($partylist_count > 0) {
    // Fetch notifications for partylist candidate applications
    $sql_partylist = "SELECT application_status, reason FROM partylist_candidate_application WHERE id = ?";
    $stmt_partylist = $conn->prepare($sql_partylist);
    $stmt_partylist->bind_param("i", $user_id);
    $stmt_partylist->execute();
    $result_partylist = $stmt_partylist->get_result();

    if ($result_partylist->num_rows > 0) {
        while ($row = $result_partylist->fetch_assoc()) {
            $response[] = [
                'type' => 'partylist',
                'status' => $row['application_status'],
                'reason' => $row['reason']
            ];
        }
    }
}

echo json_encode($response);
$conn->close();
?>
