<?php 
// Start session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the student_id is not set in the session, then redirect to the login page
if (!isset($_SESSION['UserLogin'])) {
    // Redirect to login page if not logged in
    header("Location: ../user/Landing.php");
    exit; // Ensure script execution stops here
}

// Include connection file and establish connection
include("../connection/connection.php");
$con = connection();

// Fetch student ID from session
$student_id = $_SESSION['UserLogin'];

// Ensure student_id is properly escaped to prevent SQL injection
$student_id = mysqli_real_escape_string($con, $student_id);

// Query to fetch the student data
$sql = "SELECT * FROM `registered_students` WHERE `id` = '$student_id'";
$result = mysqli_query($con, $sql);

// Get current page name
$currentPage = basename($_SERVER['PHP_SELF']);
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />      
    <link rel="shortcut icon" href="../Background/LOGOSSC.png" type="image/x-icon">
    <link rel="stylesheet" href="../design/sidebar.css">
    <link rel="stylesheet" href="../design/results.css">
    <link rel="stylesheet" href="../design/VOTED.css">
    <link rel="stylesheet" href="../design/application.css">
    <title>CDMSSCVMS</title>
</head>
<body>
    <nav class="sidebar">
        <div class="brand-container">
            <div class="logo">
                <img src="../Background/LOGOCDM.png" width="90px">
            </div>
            <div class="brand-info">
                <div class="brand-column">
                <?php 
                    // Fetch the user data
                    $row = mysqli_fetch_assoc($result);
                    // Check if $row is valid
                    if ($row) {
                ?>
                    <h4 class="studentnumber">Welcome</h4>
                    <h4 class="studentnumber"><?php echo htmlspecialchars($row['student_number']); ?></h4>
                <?php 
                    } else {
                        echo '<h4 class="studentnumber">Student not found.</h4>';
                    }
                ?>
                </div>
            </div>
        </div>

        <div class="LinkContainer">
            <a class="links <?php if($currentPage === 'Dashboard.php') echo 'active'; ?>" href="../user/Dashboard.php">Dashboard</a>
            <a class="links <?php if($currentPage === 'Candidate.php') echo 'active'; ?>" href="../user/Candidate.php">Candidate List</a>
            <a class="links <?php if($currentPage === 'Vote.php') echo 'active'; ?>" href="../user/Vote.php">Vote</a>
            <a class="links <?php if($currentPage === 'Result.php') echo 'active'; ?>" href="../user/Result.php">Result</a>
            <a class="btnlink" href="../user/logout.php">LOGOUT</a>
        </div>
    </nav>

