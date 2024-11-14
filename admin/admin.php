<?php
include_once("../connection/connection.php");

$conn = connection(); // Establish the database connection

// Function to update application status
function updateApplicationStatus($application_id, $status) {
    global $conn;
    $update_stmt = $conn->prepare("UPDATE solo_candidate_application SET application_status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $application_id);
    return $update_stmt->execute();
}

// Handle accept or reject action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['application_id'])) {
    $action = $_POST['action'];
    $application_id = $_POST['application_id'];

    if ($action === 'accept') {
        if (updateApplicationStatus($application_id, 'accepted')) {
            header("Location: admin.php"); // Redirect back to admin panel
            exit();
        } else {
            echo "Error updating application status";
        }
    } elseif ($action === 'reject') {
        if (updateApplicationStatus($application_id, 'rejected')) {
            header("Location: admin.php"); // Redirect back to admin panel
            exit();
        } else {
            echo "Error updating application status";
        }
    } else {
        // Invalid action
        exit("Invalid action");
    }
}

// Fetch applications from the database based on their status
$select_accepted_stmt = $conn->prepare("SELECT id, fullname, yearandsection, institute, position, image, platform FROM solo_candidate_application WHERE application_status = 'accepted'");
$select_accepted_stmt->execute();
$accepted_applications = $select_accepted_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$select_accepted_stmt->close();

$select_rejected_stmt = $conn->prepare("SELECT id, fullname, yearandsection, institute, position, image, platform FROM solo_candidate_application WHERE application_status = 'rejected'");
$select_rejected_stmt->execute();
$rejected_applications = $select_rejected_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$select_rejected_stmt->close();

$select_pending_stmt = $conn->prepare("SELECT id, fullname, yearandsection, institute, position, image, platform FROM solo_candidate_application WHERE application_status = 'pending'");
$select_pending_stmt->execute();
$pending_applications = $select_pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$select_pending_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>

<h2>Pending Applications</h2>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Year & Section</th>
            <th>Institute</th>
            <th>Position</th>
            <th>Image</th>
            <th>Platform</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pending_applications as $application): ?>
            <tr>
                <td><?php echo $application['id']; ?></td>
                <td><?php echo $application['fullname']; ?></td>
                <td><?php echo $application['yearandsection']; ?></td>
                <td><?php echo $application['institute']; ?></td>
                <td><?php echo $application['position']; ?></td>
                <td><img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['image']; ?>" style="max-width: 100px; max-height: 100px;"></td>
                <td><?php echo $application['platform']; ?></td>
                <td>
                <form action="" method="post" onsubmit="updateApplicationStatus('accept', <?php echo $application['id']; ?>); return false;">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                    <button type="submit">Accept</button>
                </form>
                <form action="" method="post" onsubmit="updateApplicationStatus('reject', <?php echo $application['id']; ?>); return false;">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                    <button type="submit">Reject</button>
                </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Accepted Applications</h2>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Year & Section</th>
            <th>Institute</th>
            <th>Position</th>
            <th>Image</th>
            <th>Platform</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($accepted_applications as $application): ?>
            <tr>
                <td><?php echo $application['id']; ?></td>
                <td><?php echo $application['fullname']; ?></td>
                <td><?php echo $application['yearandsection']; ?></td>
                <td><?php echo $application['institute']; ?></td>
                <td><?php echo $application['position']; ?></td>
                <td><img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['image']; ?>" style="max-width: 100px; max-height: 100px;"></td>
                <td><?php echo $application['platform']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Rejected Applications</h2>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Year & Section</th>
            <th>Institute</th>
            <th>Position</th>
            <th>Image</th>
            <th>Platform</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rejected_applications as $application): ?>
            <tr>
                <td><?php echo $application['id']; ?></td>
                <td><?php echo $application['fullname']; ?></td>
                <td><?php echo $application['yearandsection']; ?></td>
                <td><?php echo $application['institute']; ?></td>
                <td><?php echo $application['position']; ?></td>
                <td><img src="../user/uploads/ echo $application['image']; ?>" alt="<?php echo $application['image']; ?>" style="max-width: 100px; max-height: 100px;"></td>
                <td><?php echo $application['platform']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
