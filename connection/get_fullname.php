<?php

include("../connection/connection.php");
$con = connection();

if (isset($_POST['registerStudentNumber'])) {
    $registerStudentNumber = $_POST['registerStudentNumber'];
    // Fetch full name based on the provided student number
    $sql = "SELECT fullname FROM enrolled_students WHERE student_number = '$registerStudentNumber'";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo $row['fullname'];
    } else {
        echo ""; // Return empty string if student number not found or error occurs
    }
    exit; // Exit after echoing the full name to prevent further output
}

?>