<?php
include_once("../connection/connection.php");

$conn = connection(); // Establish the database connection

// Function to update application status
function updateApplicationStatus($application_id, $status) {
    global $conn;
    $update_stmt = $conn->prepare("UPDATE partylist_candidate_application SET application_status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $application_id);
    return $update_stmt->execute();
}

// Handle accept or reject action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['application_id'])) {
    $action = $_POST['action'];
    $application_id = $_POST['application_id'];

    if ($action === 'accept') {
        if (updateApplicationStatus($application_id, 'accepted')) {
            header("Location: partylist.php"); // Redirect back to party list panel
            exit();
        } else {
            echo "Error updating application status";
        }
    } elseif ($action === 'reject') {
        if (updateApplicationStatus($application_id, 'rejected')) {
            header("Location: partylist.php"); // Redirect back to party list panel
            exit();
        } else {
            echo "Error updating application status";
        }
    } else {
        // Invalid action
        exit("Invalid action");
    }
}

// Fetch pending and accepted applications from the database grouped by party list name
$select_applications_stmt = $conn->prepare("SELECT id, partylistname, fullname, yearandsection, institute, position, image, platform, application_status FROM partylist_candidate_application WHERE application_status IN ('Pending', 'Accepted') ORDER BY partylistname");
$select_applications_stmt->execute();
$applications_result = $select_applications_stmt->get_result();
$select_applications_stmt->close();

// Initialize arrays to store applications grouped by party list name
$pending_applications_by_partylist = [];
$accepted_applications_by_partylist = [];

// Iterate through the applications and group them by party list name
while ($row = $applications_result->fetch_assoc()) {
    if ($row['application_status'] === 'Pending') {
        $pending_applications_by_partylist[$row['partylistname']][] = $row;
    } else {
        $accepted_applications_by_partylist[$row['partylistname']][] = $row;
    }
}

// Function to post a party list to candidate.php
function postPartyList($partylistname) {
    // Redirect to candidate.php with the party list name as a parameter
    header("Location: ../user/Candidate.php$partylistname");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>

<h2>Party List Applications</h2>

<?php foreach ($pending_applications_by_partylist as $partylistname => $pending_applications): ?>
    <h3>Pending Applications for <?php echo $partylistname; ?></h3>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Party List Name</th>
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
                    <td><?php echo $application['partylistname']; ?></td>
                    <td><?php echo $application['fullname']; ?></td>
                    <td><?php echo $application['yearandsection']; ?></td>
                    <td><?php echo $application['institute']; ?></td>
                    <td><?php echo $application['position']; ?></td>
                    <td><img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['image']; ?>" style="max-width: 100px; max-height: 100px;"></td>
                    <td><?php echo $application['platform']; ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                            <button type="submit">Accept</button>
                        </form>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                            <button type="submit">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

<?php foreach ($accepted_applications_by_partylist as $partylistname => $accepted_applications): ?>
    <h3>Accepted Applications for <?php echo $partylistname; ?></h3>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Party List Name</th>
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
                    <td><?php echo $application['partylistname']; ?></td>
                    <td><?php echo $application['fullname']; ?></td>
                    <td><?php echo $application['yearandsection']; ?></td>
                    <td><?php echo $application['institute']; ?></td>
                    <td><?php echo $application['position']; ?></td>
                    <td><img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['image']; ?>" style="max-width: 100px; max-height: 100px;"></td>
                    <td><?php echo $application['platform']; ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="8">
                    <form action="" method="post">
                        <input type="hidden" name="action" value="post">
                        <input type="hidden" name="partylistname" value="<?php echo $partylistname; ?>">
                        <button type="submit">Post Party List</button>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
<?php endforeach; ?>

</body>
</html>
