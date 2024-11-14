<?php
include_once("../connection/connection.php");
$conn = connection();

$sql = "SELECT VotingStartDateTime, VotingEndDateTime FROM votingschedule ORDER BY VotingStartDateTime DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Voting schedule not found.']);
}

$conn->close();
?>
