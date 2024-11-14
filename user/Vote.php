<?php
    include_once '../user/sidebar.php';
    include_once("../connection/connection.php");

    $conn = connection(); // Establish the database connection

    // Function to fetch candidates for a specific position
    function getCandidates($conn, $position) {
        $candidates = [];
    
        // Fetch solo candidates with accepted status
        $solo_query = $conn->prepare("SELECT id, fullname, image, 'solo' as type FROM solo_candidate_application WHERE position = ? AND application_status = 'accepted'");
        $solo_query->bind_param("s", $position); // Ensure the position is correctly bound
        $solo_query->execute();
        $solo_result = $solo_query->get_result();
        while ($row = $solo_result->fetch_assoc()) {
            $candidates[] = $row;
        }
        $solo_query->close();
    
        // Fetch partylist candidates with accepted status
        $partylist_query = $conn->prepare("SELECT id, fullname, image, partylistname, 'partylist' as type FROM partylist_candidate_application WHERE position = ? AND application_status = 'accepted'");
        $partylist_query->bind_param("s", $position); // Ensure the position is correctly bound
        $partylist_query->execute();
        $partylist_result = $partylist_query->get_result();
        while ($row = $partylist_result->fetch_assoc()) {
            $candidates[] = $row;
        }
        $partylist_query->close();
    
        return $candidates;
    }
    

    // Function to get total votes for a position
    function getTotalVotesForPosition($conn, $position) {
        $total_query = $conn->prepare("SELECT COUNT(*) AS total_votes FROM votes WHERE position = ?");
        $total_query->bind_param("s", $position); // Ensure the position is correctly bound
        $total_query->execute();
        $total_result = $total_query->get_result();
        $total_row = $total_result->fetch_assoc();
        $total_votes = $total_row['total_votes'];
        $total_query->close();
        return $total_votes;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vote'])) {
        // Extract student ID from session if it exists
        $student_id = isset($_SESSION['UserLogin']) ? $_SESSION['UserLogin'] : null;

        if ($student_id === null) {
            echo '<script>alert("You must be logged in to vote.");</script>';
            exit;
        }

        // Check if the student has already voted
        $stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->bind_result($vote_count);
        $stmt->fetch();
        $stmt->close();

        if ($vote_count > 0) {
            echo '<script>alert("You have already voted."); window.location.href = "vote.php";</script>';
            exit;
        }

        // Iterate through the POST data to extract the selected candidate for each position
        foreach ($_POST as $key => $value) {
            if ($key === 'vote' || empty($value)) {
                continue;
            }

            // Handle the senator positions separately
            if (strpos($key, 'senator_') === 0) {
                $position = 'Senator'; // Set the position to Senator
                list($candidate_id, $candidate_type) = explode(':', $value);

                // Insert the vote into the database
                $stmt = $conn->prepare("INSERT INTO votes (student_id, candidate_id, candidate_type, position, vote_date) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiss", $student_id, $candidate_id, $candidate_type, $position);

                if (!$stmt->execute()) {
                    echo '<script>alert("Error submitting vote.");</script>';
                    $stmt->close();
                    exit;
                }
                $stmt->close();
            } else {
                // Handle votes for other positions with two-word names
                $position = ucwords(str_replace('_', ' ', $key)); // Convert the key to a proper position name
                $position = str_replace(' ', '_', $position); // Replace spaces with underscores
                list($candidate_id, $candidate_type) = explode(':', $value);

                // Insert the vote into the database
                $stmt = $conn->prepare("INSERT INTO votes (student_id, candidate_id, candidate_type, position, vote_date) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiss", $student_id, $candidate_id, $candidate_type, $position);

                if (!$stmt->execute()) {
                    echo '<script>alert("Error submitting vote.");</script>';
                    $stmt->close();
                    exit;
                }
                $stmt->close();
            }
        }

        echo '<script>alert("Vote submitted successfully!"); window.location.href = "vote.php";</script>';
    }

    $positions = ['President', 'Vice_President', 'Secretary', 'Assistant_Secretary', 'Auditor', 'Treasurer', 'Public_Officer', 'Media_Adviser', 'Business_Manager', 'Event_Manager', 'Senator'];
    ?>

        <body>
            <div class="main-content">
                <div class="votebtn-container">
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                    <button class="votebtn" onclick="showVoteModal()">Vote Here</button> <!-- Add more instances of the button as needed -->
                </div>

                <div id="error-message-ended" style="display: none; color: red; text-align: center; margin-top: 10px;">
                    Voting period hasn't started or ended.
                </div>

                <div class="Progress-Bar"> <!-- Renamed class to "Progress-Bar" -->
                    <div class="row">
                        <!-- Iterate through positions array -->
                        <?php foreach ($positions as $position): ?>
                            
                            <?php if (strtolower($position) === 'senator'): ?>
                                <!-- Senator Position -->
                                <div class="senator-position">
                                <div class="position"><?php echo ucwords(strtolower(str_replace('_', ' ', $position))); ?></div>
                                    <?php
                                    // Get candidates for this position
                                    $candidates = getCandidates($conn, $position);
                                    // Get total votes for this position
                                    $total_votes = getTotalVotesForPosition($conn, $position);

                                    // Check if there are candidates for this position
                                    if (empty($candidates)) {
                                        echo '<p>There are currently no candidates for this position.</p>';
                                    } else {
                                        // Iterate through candidates
                                        $count = 0;
                                        foreach ($candidates as $candidate):
                                            if ($count % 2 == 0) {
                                                echo '<div class="row">';
                                            }
                                            // Get candidate ID and type
                                            $candidate_id = $candidate['id'];
                                            $candidate_type = $candidate['type'];

                                            // Get candidate name and image
                                            $candidate_name = $candidate['fullname'];
                                            $candidate_image = $candidate['image'];

                                            // Get vote count for this candidate and position
                                            $vote_count_query = $conn->prepare("SELECT COUNT(*) AS vote_count FROM votes WHERE candidate_id = ? AND candidate_type = ? AND position = ?");
                                            $vote_count_query->bind_param("iss", $candidate_id, $candidate_type, $position);
                                            $vote_count_query->execute();
                                            $vote_count_result = $vote_count_query->get_result();
                                            $vote_count_row = $vote_count_result->fetch_assoc();

                                            // Check if vote count is null, indicating no votes
                                            if ($vote_count_row['vote_count'] === null) {
                                                $vote_count = 0;
                                            } else {
                                                $vote_count = $vote_count_row['vote_count'];
                                            }

                                            // Calculate percentage of votes for the progress bar
                                            $vote_percentage = $total_votes > 0 ? ($vote_count / $total_votes) * 100 : 0;
                                            ?>
                                            <div class="candidate">
                                                <div class="candidate-info">
                                                    <div class="candidate-image">
                                                        <img src="uploads/<?php echo $candidate_image; ?>" alt="<?php echo $candidate_name; ?>" class="rounded-circle" width="50" height="50">
                                                    </div>
                                                    <div class="candidate-details">
                                                        <span class="candidate-name"><?php echo $candidate_name; ?></span>
                                                    </div>
                                                </div>
                                                <div class="progress-container">
                                                    <?php if ($vote_count === 0): ?>
                                                        <!-- Display "0 Votes" inside the progress-container div -->
                                                        0 
                                                    <?php endif; ?>
                                                    <?php if ($vote_count !== 0): ?>
                                                        <!-- Display progress bar when there are votes -->
                                                        <div class="progress-bar" style="width: <?php echo $vote_percentage; ?>%;">
                                                            <?php echo $vote_count; ?> 
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php
                                            $count++;
                                            if ($count % 2 == 0 || $count == count($candidates)) {
                                                echo '</div>';
                                            }
                                        endforeach;
                                    }
                                    ?>
                                </div>
                            <?php else: ?>
                                <!-- Other Positions -->
                                <div class="position-column <?php echo strtolower($position); ?>">
                                    <div class="position"><?php echo ucwords(strtolower(str_replace('_', ' ', $position))); ?></div>
                                    <?php
                                    // Get candidates for this position
                                    $candidates = getCandidates($conn, $position);
                                    // Get total votes for this position
                                    $total_votes = getTotalVotesForPosition($conn, $position);

                                    // Check if there are candidates for this position
                                    if (empty($candidates)) {
                                        echo '<div style="text-align: center;"><p>There are currently no candidates for this position.</p></div>';
                                    } else {
                                        // Iterate through candidates
                                        foreach ($candidates as $candidate):
                                            // Get candidate ID and type
                                            $candidate_id = $candidate['id'];
                                            $candidate_type = $candidate['type'];

                                            // Get candidate name and image
                                            $candidate_name = $candidate['fullname'];
                                            $candidate_image = $candidate['image'];

                                            // Get vote count for this candidate and position
                                            $vote_count_query = $conn->prepare("SELECT COUNT(*) AS vote_count FROM votes WHERE candidate_id = ? AND candidate_type = ? AND position = ?");
                                            $vote_count_query->bind_param("iss", $candidate_id, $candidate_type, $position);
                                            $vote_count_query->execute();
                                            $vote_count_result = $vote_count_query->get_result();
                                            $vote_count_row = $vote_count_result->fetch_assoc();

                                            // Check if vote count is null, indicating no votes
                                            if ($vote_count_row['vote_count'] === null) {
                                                $vote_count = 0;
                                            } else {
                                                $vote_count = $vote_count_row['vote_count'];
                                            }

                                            // Calculate percentage of votes for the progress bar
                                            $vote_percentage = $total_votes > 0 ? ($vote_count / $total_votes) * 100 : 0;
                                            ?>

                                            <div class="candidate">
                                                <div class="candidate-info">
                                                    <div class="candidate-image">
                                                        <img src="uploads/<?php echo $candidate_image; ?>" alt="<?php echo $candidate_name; ?>" class="rounded-circle" width="50" height="50">
                                                    </div>
                                                    <div class="candidate-details">
                                                        <span class="candidate-name"><?php echo $candidate_name; ?></span>
                                                    </div>
                                                </div>
                                                <div class="progress-container">
                                                    <?php if ($vote_count === 0): ?>
                                                        <!-- Display "0 Votes" inside the progress-container div -->
                                                        0 
                                                    <?php endif; ?>
                                                    <?php if ($vote_count !== 0): ?>
                                                        <!-- Display progress bar when there are votes -->
                                                        <div class="progress-bar" style="width: <?php echo $vote_percentage; ?>%;">
                                                            <?php echo $vote_count; ?> 
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach;
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>




                    <div class="votebtn-container">
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button>
                        <button class="votebtn" onclick="showVoteModal()">Vote Here</button> 
                    </div>


                        
                    <div id="voteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeVoteModal()">&times;</span>
        <h2>Ballot Form</h2>
        <form id="voteForm" method="post" class="vote-form">
            <?php 
            $positionsCount = count($positions);
            for ($i = 0; $i < $positionsCount; $i++):
                if ($positions[$i] === 'Senator'):
            ?>
            <div class="position-group">
                <?php for ($j = 1; $j <= 3; $j++): ?>
                    <div class="position-dropdown">
                        <label for="senator"><?php echo str_replace('_', ' ', $positions[$i]); ?> <?php echo $j; ?>:</label>
                        <select class="senator-dropdown" id="senator_<?php echo $j; ?>" name="senator_<?php echo $j; ?>" onchange="senatorSelected(this.id)">
                            <option value="">Select a candidate</option>
                            <?php 
                            $candidates = getCandidates($conn, $positions[$i]);
                            foreach ($candidates as $candidate):
                                $candidatePosition = strtolower(str_replace(' ', '_', $positions[$i]));
                            ?>
                            <option value="<?php echo $candidate['id'] . ':' . $candidate['type']; ?>">
                                <?php echo $candidate['fullname']; ?><?php echo ($candidate['type'] === 'partylist') ? ' (' . ucfirst($candidate['partylistname']) . ')' : ''; ?><?php echo ($candidate['type'] === 'solo') ? ' (solo)' : ''; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <!-- For positions other than Senator -->
            <?php if ($i % 2 == 0): ?>
            <div class="position-group">
            <?php endif; ?>
            <div class="position-dropdown">
                <label for="<?php echo strtolower(str_replace(' ', '', ucwords($positions[$i]))); ?>"><?php echo ucwords(strtolower(str_replace('_', ' ', $positions[$i]))); ?>:</label>
                <select id="<?php echo strtolower(str_replace(' ', '_', ucwords($positions[$i]))); ?>" name="<?php echo strtolower(str_replace(' ', '_', $positions[$i])); ?>">
                    <option value="">Select a candidate</option>
                    <?php 
                    $candidates = getCandidates($conn, $positions[$i]);
                    foreach ($candidates as $candidate):
                        $candidatePosition = strtolower(str_replace(' ', '_', $positions[$i]));
                    ?>
                    <option value="<?php echo $candidate['id'] . ':' . $candidate['type']; ?>">
                        <?php echo $candidate['fullname']; ?><?php echo ($candidate['type'] === 'partylist') ? ' (' . ucfirst($candidate['partylistname']) . ')' : ''; ?><?php echo ($candidate['type'] === 'solo') ? ' (solo)' : ''; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($i % 2 != 0 || $i == $positionsCount - 1): ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php endfor; ?>
            <button class="submit" type="submit" name="vote">Submit Vote</button>
        </form>
    </div>
</div>
            </div>

        </body>

        <scrip>
           <script>
function fetchVotingSchedule() {
    fetch('fetch_voting_schedule.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('timer').innerText = 'Voting schedule not found.';
            } else {
                const startDateTime = new Date(data.VotingStartDateTime);
                const endDateTime = new Date(data.VotingEndDateTime);
                updateCountdown(startDateTime, endDateTime);
                updateVoteButtonState(startDateTime, endDateTime);
            }
        })
        .catch(error => {
            console.error('Error fetching voting schedule:', error);
            document.getElementById('timer').innerText = 'Error loading schedule.';
        });
}

function updateVoteButtonState(start, end) {
    const voteButtons = document.querySelectorAll('.votebtn');
    const errorMessageP = document.getElementById('error-message-ended');
    const now = new Date().getTime();

    const updateState = () => {
        const now = new Date().getTime();
        if (now < start.getTime() || now > end.getTime()) {
            voteButtons.forEach(button => button.disabled = true);
            errorMessageP.style.display = 'block';
        } else {
            voteButtons.forEach(button => button.disabled = false);
            errorMessageP.style.display = 'none';
        }
    };

    updateState(); // Initial call to set the state based on current time

    setInterval(updateState, 1000); // Repeatedly update the state every second
}

document.addEventListener('DOMContentLoaded', fetchVotingSchedule);

function senatorSelected(selectId) {
    var dropdowns = document.getElementsByClassName('senator-dropdown');

    // Collect selected senator value
    var selectedValue = document.getElementById(selectId).value;

    // Iterate through all dropdowns
    for (var i = 0; i < dropdowns.length; i++) {
        var options = dropdowns[i].options;
        
        // Iterate through options in each dropdown
        for (var j = 0; j < options.length; j++) {
            // Check if the option value matches the selected value
            if (options[j].value === selectedValue) {
                // Disable the option in all other dropdowns
                for (var k = 0; k < dropdowns.length; k++) {
                    if (dropdowns[k].id !== selectId) {
                        var otherOptions = dropdowns[k].options;
                        for (var m = 0; m < otherOptions.length; m++) {
                            if (otherOptions[m].value === selectedValue) {
                                otherOptions[m].disabled = true;
                            }
                        }
                    }
                }
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Select all vote buttons
    const voteButtons = document.querySelectorAll('.votebtn');
    const errorMessageP = document.getElementById('error-message-ended');

    // Function to disable vote buttons and display error message
    function disableVoteButtons() {
        voteButtons.forEach(button => button.disabled = true);
        errorMessageP.innerText = 'Voting period hasn\'t started or ended.';
        errorMessageP.style.display = 'block';
    }

    // Call the function to disable vote buttons and display error message
    disableVoteButtons();
});


</script>
   
        <script src="../design/USERS.js"></script>
        </html>
