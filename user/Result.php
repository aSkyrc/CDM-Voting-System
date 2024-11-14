<?php
date_default_timezone_set('Asia/Manila');
// Include sidebar and database connection
include_once '../user/Sidebar.php';
include_once("../connection/connection.php");

// Establish the database connection
$conn = connection();

// Define the path to the uploads folder
$image_path = "../user/uploads/";

// Function to fetch candidates for a specific position
function getCandidates($conn, $position) {
    $candidates = [];
    $query = $conn->prepare("SELECT id, fullname, yearandsection, institute, image FROM solo_candidate_application WHERE position = ?");
    $query->bind_param("s", $position);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'solo';
        $candidates[] = $row;
    }
    $query->close();

    // For partylist candidates
    $query = $conn->prepare("SELECT id, fullname, yearandsection, institute, image FROM partylist_candidate_application WHERE position = ?");
    $query->bind_param("s", $position);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'partylist';
        $candidates[] = $row;
    }
    $query->close();
    return $candidates;
}

// Function to get the leading candidates for a position
function getLeadingCandidate($conn, $position) {
    $leading_candidates = [];
    // For positions other than Senator, get the leading candidate
    $query = $conn->prepare("SELECT candidate_id, candidate_type, COUNT(*) AS vote_count FROM votes WHERE position = ? GROUP BY candidate_id, candidate_type ORDER BY vote_count DESC LIMIT 3");
    $query->bind_param("s", $position);
    $query->execute();
    $result = $query->get_result();

    // Check if there are results
    if ($result->num_rows > 0) {
        // Fetch the leading candidates
        while ($row = $result->fetch_assoc()) {
            $leading_candidates[] = $row;
        }
    }
    $query->close();
    return $leading_candidates;
}

// Define the positions
$positions = ['President', 'Vice_President', 'Secretary', 'Assistant_Secretary', 'Auditor', 'Treasurer', 'Public_Officer', 'Media_Adviser', 'Business_Manager', 'Event_Manager', 'Senator'];

// Function to check if voting schedule is set and if election has ended
function checkVotingSchedule($conn) {
    $sql = "SELECT VotingStartDateTime, VotingEndDateTime FROM votingschedule ORDER BY VotingStartDateTime DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $voting_schedule = $result->fetch_assoc();
        $current_time = time();
        $voting_end_time = strtotime($voting_schedule['VotingEndDateTime']);
        $election_ended = ($current_time > $voting_end_time);
        return [
            'schedule_set' => true,
            'election_ended' => $election_ended,
        ];
    } else {
        return [
            'schedule_set' => false,
            'election_ended' => false,
        ];
    }
}

// Check the voting schedule
$voting_status = checkVotingSchedule($conn);
?>

<div class="main-content">
    <h5>OFFICIAL RESULT OF THE ELECTION.</h5>

    <?php
    if (!$voting_status['schedule_set']) {
        echo "<div class='centered-message'><p>Voting schedule not set. Please wait for the announcement.</p></div>";
    } elseif (!$voting_status['election_ended']) {
        // Display message if the election hasn't ended yet
        echo "<div class='centered-message'><p>The election hasn't ended yet.</p></div>";
    } else {
        // Initialize position counter
        $position_counter = 0;

        // Loop through each position
        foreach ($positions as $position) {
            // Replace underscores with spaces in the position name
            $formatted_position = str_replace("_", " ", $position);

            // Retrieve leading candidates
            $leading_candidates = getLeadingCandidate($conn, $position);

            // Determine the alignment of the candidate info container
            $container_alignment = ($position_counter % 2 == 0) ? 'left' : 'right';

            // Start position container
            echo "<div class='position'>";

            // Output position name
            echo "<h6 class='$container_alignment'>$formatted_position</h6>";

            // Start candidate info container
            echo "<div class='candidate-info-container $container_alignment'>";

            // Loop through the leading candidates
            foreach ($leading_candidates as $leading_candidate) {
                // Retrieve candidate information
                $candidate_id = $leading_candidate['candidate_id'];
                $candidate_type = $leading_candidate['candidate_type'];
                $query = $conn->prepare("SELECT fullname, yearandsection, institute, image FROM solo_candidate_application WHERE id = ? AND position = ? UNION SELECT fullname, yearandsection, institute, image FROM partylist_candidate_application WHERE id = ? AND position = ?");
                $query->bind_param("issi", $candidate_id, $position, $candidate_id, $position);
                $query->execute();
                $result = $query->get_result();

                // Display candidate information
                if ($result->num_rows > 0) {
                    $candidate_info = $result->fetch_assoc();
                    ?>
                    <div class='leading-candidate'>
                        <div class='candidate-lead-image <?php echo ($container_alignment == 'left') ? 'left' : 'right'; ?>'>
                            <img src='<?php echo $image_path . $candidate_info['image']; ?>' alt='Candidate Image' width='200' height='200'>
                        </div>
                        <div class='candidate-lead-details <?php echo ($container_alignment == 'left') ? 'right' : 'left'; ?>'>
                            <p><strong>Name:</strong> <?php echo $candidate_info['fullname']; ?></p>
                            <p><strong>Year and Section:</strong> <?php echo $candidate_info['yearandsection']; ?></p>
                            <p><strong>Institute:</strong> <?php echo $candidate_info['institute']; ?></p>
                        </div>
                    </div>
                    <?php
                }
                $query->close();
            }

            // Close candidate info container
            echo "</div>"; // End candidate-info-container

            // End position container
            echo "</div>"; // End position

            // Increment position counter
            $position_counter++;
        }
    }
    ?>

</div>
</body>
<script src="../design/USERS.js"></script>
</html>
