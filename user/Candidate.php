<?php 
include_once '../user/sidenav.php';
include_once("../connection/connection.php");

$conn = connection(); // Establish the database connection

$fullname = "";
$institute = "";

// Check if the user is logged in
if(isset($_SESSION['UserLogin'])) {
    $student_id = $_SESSION['UserLogin'];
    
    // Prepare and execute a query to get the user's fullname and institute
    $select_stmt = $conn->prepare("SELECT fullname, institute FROM registered_students WHERE id = ?");
    $select_stmt->bind_param("s", $student_id);
    $select_stmt->execute();
    $select_result = $select_stmt->get_result();
    
    // Fetch the result
    if ($select_result->num_rows > 0) {
        $row = $select_result->fetch_assoc();
        $fullname = $row['fullname'];
        $institute = $row['institute'];
    }
}

// Check if the user is logged in
if(isset($_SESSION['UserLogin'])) {
    $student_id = $_SESSION['UserLogin'];
    
    // Prepare and execute a query to get the user's student number
    $select_student_number_stmt = $conn->prepare("SELECT student_number FROM registered_students WHERE student_number = ?");
    $select_student_number_stmt->bind_param("s", $student_id);
    $select_student_number_stmt->execute();
    $student_number_result = $select_student_number_stmt->get_result();
    
    // Fetch the student number from the result
    if ($student_number_result->num_rows > 0) {
        $student_number_row = $student_number_result->fetch_assoc();
        $user_student_number = $student_number_row['student_number'];
    }
}

// SOLO CANDIDATE APPLICATION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['solosubmit'])) {
    // Check if the user has already submitted any application
    $fullname = $_POST['fullname'];
    $yearandsection = $_POST['yearandsection'];
    $institute = $_POST['institute'];

    // Prepare and execute a query to check for existing application in solo_candidate_application table
    $check_stmt_solo = $conn->prepare("SELECT id FROM solo_candidate_application WHERE fullname = ? AND yearandsection = ? AND institute = ?");
    $check_stmt_solo->bind_param("sss", $fullname, $yearandsection, $institute);
    $check_stmt_solo->execute();
    $check_result_solo = $check_stmt_solo->get_result();

    // Prepare and execute a query to check for existing application in partylist_candidate_application table
    $check_stmt_partylist = $conn->prepare("SELECT id FROM partylist_candidate_application WHERE fullname = ?");
    $check_stmt_partylist->bind_param("s", $fullname);
    $check_stmt_partylist->execute();
    $check_result_partylist = $check_stmt_partylist->get_result();

    if ($check_result_solo->num_rows > 0) {
        // Application already exists for the user in solo_candidate_application table
        echo '<script>alert("You have already submitted a solo application.");</script>';
    } elseif ($check_result_partylist->num_rows > 0) {
        // Application already exists for the user in partylist_candidate_application table
        echo '<script>alert("You have already submitted an application for the party list.");</script>';
    } else {
        // Proceed with the solo candidate application submission
        // Prepare and bind parameters
        $stmt = $conn->prepare("INSERT INTO solo_candidate_application (fullname, yearandsection, institute, position, image, platform, application_status, application_date) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("ssssss", $fullname, $yearandsection, $institute, $position, $image, $platform);
        
        // Set parameters and execute
        $position = $_POST['position'];
        $image = $_FILES['image']['name']; // Assuming you're storing the image filename
        $platform = $_POST['platform'];
        
        // Upload image file
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        
        // Ensure the directory exists before moving the file
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory recursively
        }
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Execute the prepared statement
            if ($stmt->execute()) {
                echo '<script>alert("Application submitted successfully!");</script>';
            } else {
                echo '<script>alert("Error submitting application.");</script>';
            }
        } else {
            echo '<script>alert("Error moving file.");</script>';
        }
        
        // Close statement
        $stmt->close();
    }

    // Close check statements
    $check_stmt_solo->close();
    $check_stmt_partylist->close();
}

// ACCEPTED SOLO CANDIDATE
$select_accepted_stmt = $conn->prepare("SELECT id, fullname, yearandsection, institute, position, image, platform FROM solo_candidate_application WHERE application_status = 'accepted' ORDER BY position ASC");
$select_accepted_stmt->execute();
$accepted_applications = $select_accepted_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$select_accepted_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['partylistsubmit'])) {
    // Extract form data
    $fullname = $_POST['fullnameP'];
    $yearandsection = $_POST['yearandsectionP'];
    $institute = $_POST['instituteP'];
    $position = $_POST['positionparty'];
    $partylistname = strtoupper($_POST['partylistname']); // Convert to uppercase
    $image = $_FILES['imageP']['name']; // Assuming you're storing the image filename
    $platform = $_POST['platformparty'];
    $application_status = "Pending"; // Set default status
    $member = $_POST['officersmembername']; // Assuming this is a comma-separated list of member names

    // Check if the user has already submitted an application for the party list
    $checkUserApplicationStmt = $conn->prepare("SELECT * FROM partylist_candidate_application WHERE fullname = ?");
    $checkUserApplicationStmt->bind_param("s", $fullname);
    $checkUserApplicationStmt->execute();
    $result = $checkUserApplicationStmt->get_result();
    $userApplicationExists = $result->num_rows > 0;

    if ($userApplicationExists) {
        // If user has already submitted an application, display an error message and redirect to the candidate form
        echo '<script>alert("You have already submitted an application.");</script>';
        echo '<script>window.location.href = "Candidate.php";</script>';
        exit; // Stop further execution
    }

    // Check if the user has already submitted an application for solo candidacy or party list candidacy
    $check_stmt = $conn->prepare("SELECT id FROM solo_candidate_application WHERE fullname = ? UNION ALL SELECT id FROM partylist_candidate_application WHERE fullname = ?");
    $check_stmt->bind_param("ss", $fullname, $fullname);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Application already exists for the user
        echo '<script>alert("You have already submitted an application for solo candidate.");</script>';
    } else {
        // Proceed with the application submission

        // Check if the position is President
        if ($position !== "President") {
            $checkPartyListStmt = $conn->prepare("SELECT * FROM partylist_candidate_application WHERE partylistname = ?");
            $checkPartyListStmt->bind_param("s", $partylistname);
            $checkPartyListStmt->execute();
            $result = $checkPartyListStmt->get_result();
            $partyListExists = $result->num_rows > 0;

            if (!$partyListExists) {
                // If party list does not exist, display an error message and redirect to the candidate form
                echo '<script>alert("The party list does not exist.");</script>';
                echo '<script>window.location.href = "Candidate.php";</script>';
                exit; // Stop further execution
            }
            // Proceed with uploading image file
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["imageP"]["name"]);
            // Ensure the directory exists before moving the file
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create the directory recursively
            }

            if (move_uploaded_file($_FILES["imageP"]["tmp_name"], $target_file)) {
                // Prepare SQL statement for inserting party list candidate application
                $sql = "INSERT INTO partylist_candidate_application (partylistname, position, fullname, yearandsection, institute, image, platform, application_status, application_date, member) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                $stmt = $conn->prepare($sql);

                // Bind parameters
                $stmt->bind_param("sssssssss", $partylistname, $position, $fullname, $yearandsection, $institute, $image, $platform, $application_status, $member);

                // Execute the statement
                if ($stmt->execute()) {
                    echo '<script>alert("Application submitted successfully!");</script>';
                    echo '<script>window.location.href = "Candidate.php";</script>';
                } else {
                    echo '<script>alert("Error submitting application.");</script>';
                    echo '<script>window.location.href = "Candidate.php";</script>';
                }

                // Close statement
                $stmt->close();
            } else {
                echo '<script>alert("Error moving file.");</script>';
                echo '<script>window.location.href = "Candidate.php";</script>';
            }

            exit; // Stop further execution
        }

        $checkPresidentStmt = $conn->prepare("SELECT * FROM partylist_candidate_application WHERE partylistname = ? AND position = 'President'");
        $checkPresidentStmt->bind_param("s", $partylistname);
        $checkPresidentStmt->execute();
        $presidentResult = $checkPresidentStmt->get_result();
        $presidentExists = $presidentResult->num_rows > 0;

        if ($presidentExists) {
            // If party list already has a President, display an error message and redirect to the candidate form
            echo '<script>alert("This party list already has a President.");</script>';
            echo '<script>window.location.href = "Candidate.php";</script>';
            exit; // Stop further execution
        }
        // Proceed with uploading image file
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["imageP"]["name"]);
        // Ensure the directory exists before moving the file
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory recursively
        }

        if (move_uploaded_file($_FILES["imageP"]["tmp_name"], $target_file)) {
            // Prepare SQL statement for inserting party list candidate application
            $sql = "INSERT INTO partylist_candidate_application (partylistname, position, fullname, yearandsection, institute, image, platform, application_status, application_date, member) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bind_param("sssssssss", $partylistname, $position, $fullname, $yearandsection, $institute, $image, $platform, $application_status, $member);

            // Execute the statement
            if ($stmt->execute()) {
                echo '<script>alert("Application submitted successfully!");</script>';
                echo '<script>window.location.href = "Candidate.php";</script>';
            } else {
                echo '<script>alert("Error submitting application.");</script>';
                echo '<script>window.location.href = "Candidate.php";</script>';
            }

            // Close statement
            $stmt->close();
        } else {
            echo '<script>alert("Error moving file.");</script>';
            echo '<script>window.location.href = "Candidate.php";</script>';
        }
    }

    // Close connection
    // $conn->close();
}

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
        // Check if the position is Senator
        if ($row['position'] === 'Senator') {
            $partylistname = $row['partylistname'];
            if (!isset($accepted_applications_by_partylist[$partylistname])) {
                $accepted_applications_by_partylist[$partylistname] = [];
            }
            $accepted_applications_by_partylist[$partylistname][] = $row;
        } else {
            // For other positions, add them normally
            $accepted_applications_by_partylist[$row['partylistname']][] = $row;
        }
    }
}


?>


    
    <nav class="top-navbar">
        <a class="Candidates" data-content="candidateListContent">List of Candidates</a>
        <a class="Independent" data-content="independentContent">Independent Application</a>
        <a class="Partylist" data-content="partylistContent">Partylist Application</a>
    </nav>
    

    <div class="main-content">

        <div id="candidateListContent" style="display: none;">

            <div class="candidateList">
                <input type="radio" id="independentRadio" name="category" value="independent">
                <label for="independentRadio">Independent Candidates</label><br>
                <input type="radio" id="partylistRadio" name="category" value="partylist">
                <label for="partylistRadio">PartyList Candidates</label><br>
            </div>
    
            <div id="independentC" style="display: none;">
                <?php 
                    // Group accepted applications by position
                    $grouped_applications = [];
                    foreach ($accepted_applications as $application) {
                        $position = str_replace(' ', '_', $application['position']); // Replace spaces with underscores
                        if (!isset($grouped_applications[$position])) {
                            $grouped_applications[$position] = [];
                        }
                        $grouped_applications[$position][] = $application;
                    }

                    // Define the order of positions from president to senator
                    $positions_order = [
                        'President', 
                        'Vice_President', 
                        'Secretary', 
                        'Assistant_Secretary', 
                        'Auditor', 
                        'Treasurer', 
                        'Public_Officer', 
                        'Media_Adviser', 
                        'Business_Manager', 
                        'Event_Manager', 
                        'Senator'
                    ];

                    // Function to replace a single underscore with space
                    function replaceSingleUnderscoreWithSpace($text) {
                        if (substr_count($text, '_') == 1) {
                            return str_replace('_', ' ', $text);
                        }
                        return $text;
                    }

                    // Display applications for each position in the defined order
                    $positions_count = count($positions_order);
                    for ($i = 0; $i < $positions_count; $i += 2):
                        $position1 = $positions_order[$i];
                        $position1_key = str_replace(' ', '_', $position1); // Replace spaces with underscores
                        $position2 = ($i + 1 < $positions_count) ? $positions_order[$i + 1] : null;
                        $position2_key = ($position2) ? str_replace(' ', '_', $position2) : null; // Replace spaces with underscores
                    ?>
                        <div class="position-row">
                            <div class="position-section">
                                <h2><?php echo replaceSingleUnderscoreWithSpace($position1); ?></h2>
                                <div class="candidates-container">
                                    <?php if (isset($grouped_applications[$position1_key])): ?>
                                        <?php foreach (array_chunk($grouped_applications[$position1_key], 2) as $chunk): ?>
                                            <div class="candidate-row">
                                                <?php foreach ($chunk as $application): ?>
                                                    <div class="candidate-wrapper" onclick="togglePlatform(this)">
                                                        <div class="applicant-details">
                                                            <div class="applicant-image">
                                                                <img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['fullname']; ?>" class="applicant-image-circle" width="200" height="180">
                                                            </div>
                                                            <div class="applicant-name"><?php echo $application['fullname']; ?></div>
                                                            <div class="applicant-year"><?php echo $application['yearandsection']; ?></div>
                                                            <div class="applicant-institute"><?php echo $application['institute']; ?></div>
                                                            <div class="applicant-position"><?php echo replaceSingleUnderscoreWithSpace($position1); ?></div>
                                                            <h5>Click to see the platform.</h5>
                                                            <div class="applicant-platform" style="display: none;"><?php echo $application['platform']; ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>There are currently no candidates for this position.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($position2): ?>
                                <div class="position-section">
                                    <h2><?php echo replaceSingleUnderscoreWithSpace($position2); ?></h2>
                                    <div class="candidates-container">
                                        <?php if (isset($grouped_applications[$position2_key])): ?>
                                            <?php foreach (array_chunk($grouped_applications[$position2_key], 2) as $chunk): ?>
                                                <div class="candidate-row">
                                                    <?php foreach ($chunk as $application): ?>
                                                        <div class="candidate-wrapper" onclick="togglePlatform(this)">
                                                            <div class="applicant-details">
                                                                <div class="applicant-image">
                                                                    <img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['fullname']; ?>" class="partylist-applicant-image-circle" width="200" height="180">
                                                                </div>
                                                                <div class="applicant-name"><?php echo $application['fullname']; ?></div>
                                                                <div class="applicant-year"><?php echo $application['yearandsection']; ?></div>
                                                                <div class="applicant-institute"><?php echo $application['institute']; ?></div>
                                                                <div class="applicant-position"><?php echo replaceSingleUnderscoreWithSpace($position2); ?></div>
                                                                <h5>Click to see the platform.</h5>
                                                                <div class="applicant-platform" style="display: none;"><?php echo $application['platform']; ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>There are currently no candidates for this position.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
            </div>



            <div id="partylistC" style="display: none;">
                <?php if (empty($accepted_applications_by_partylist)): ?>
                    <div class="no-partylist-message">
                        <p>There are currently no party lists.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($accepted_applications_by_partylist as $partylistname => $accepted_applications): ?>
                        <div class="partylist-section">
                            <h2>Party List: <?php echo $partylistname; ?></h2>
                            <div class="partylist-positions">
                                <?php 
                                
                                // Define the order of positions from president to senator
                                $positions_order = [
                                    'President', 
                                    'Vice President', 
                                    'Secretary', 
                                    'Assistant Secretary', 
                                    'Auditor', 
                                    'Treasurer', 
                                    'Public Officer', 
                                    'Media Adviser', 
                                    'Business Manager', 
                                    'Event Manager', 
                                    'Senator'
                                ];
                                // Group accepted applications by position within each party list
                                $grouped_applications = [];
                                foreach ($accepted_applications as $application) {
                                    $position = $application['position'];
                                    // Replace underscores with spaces in position names
                                    $position = str_replace('_', ' ', $position);
                                    if (!isset($grouped_applications[$position])) {
                                        $grouped_applications[$position] = [];
                                    }
                                    $grouped_applications[$position][] = $application;
                                }
                                // Display applications for each position within the party list
                                foreach ($positions_order as $position): ?>
                                    <?php if ($position === 'Senator'): ?>
                                        <?php foreach ($grouped_applications[$position] as $application): ?>
                                            <div class="partylist-position-section">
                                                <h3><?php echo $position; ?></h3>
                                                <div class="partylist-candidates-container senator">
                                                    <div class="partylist-candidate-wrapper">
                                                        <div class="partylist-candidate" onclick="togglePlatform(this)">
                                                            <div class="partylist-applicant-details">
                                                                <div class="partylist-applicant-image">
                                                                    <img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['fullname']; ?>" class="partylist-applicant-image-circle">
                                                                </div>
                                                                <div class="partylist-applicant-name"><?php echo $application['fullname']; ?></div>
                                                                <div class="partylist-applicant-year"><?php echo $application['yearandsection']; ?></div>
                                                                <div class="partylist-applicant-institute"><?php echo $application['institute']; ?></div>
                                                                <div class="partylist-applicant-position"><?php echo $position; ?></div>
                                                                <h5>Click to see the platform.</h5>
                                                                <div class="applicant-platform" style="display: none;"><?php echo $application['platform']; ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="partylist-position-section">
                                            <h3><?php echo $position; ?></h3>
                                            <div class="partylist-candidates-container<?php echo ($position === 'Senator') ? ' senator' : ''; ?>">
                                                <?php if (isset($grouped_applications[$position])): ?>
                                                    <?php foreach ($grouped_applications[$position] as $application): ?>
                                                        <div class="partylist-candidate-wrapper">
                                                            <div class="partylist-candidate" onclick="togglePlatform(this)">
                                                                <div class="partylist-applicant-details">
                                                                    <div class="partylist-applicant-image">
                                                                        <img src="../user/uploads/<?php echo $application['image']; ?>" alt="<?php echo $application['fullname']; ?>" class="partylist-applicant-image-circle">
                                                                    </div>
                                                                    <div class="partylist-applicant-name"><?php echo $application['fullname']; ?></div>
                                                                    <div class="partylist-applicant-year"><?php echo $application['yearandsection']; ?></div>
                                                                    <div class="partylist-applicant-institute"><?php echo $application['institute']; ?></div>
                                                                    <div class="partylist-applicant-position"><?php echo $position; ?></div>
                                                                    <h5>Click to see the platform.</h5>
                                                                    <div class="applicant-platform" style="display: none;"><?php echo $application['platform']; ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="partylist-no-candidate">
                                                        <p>There are currently no candidates for this position.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>






        </div>

        <div id="independentContent" style="display: none;">
    <div class="independentform">
        <form id="independentForm" method="post" enctype="multipart/form-data">
            <h2>INDEPENDENT CANDIDATE FORM</h2><br>
            <div class="flex-container">
                <div class="flex-item">
                    <label for="name">Name:</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo $fullname; ?>" readonly>
                </div>
                <div class="flex-item">
                    <label for="yearSection">Year & Section:</label>
                    <input type="text" id="yearSection" name="yearandsection" required>
                </div>
            </div>
            
            <div class="flex-container">
                <div class="flex-item">
                    <label for="institute">Institute:</label>
                    <input type="text" id="institute" name="institute" value="<?php echo $institute; ?>" readonly>
                </div>
                <div class="flex-item">
                    <label for="picture">Upload Picture:</label>
                    <input type="file" id="picture" name="image" accept=".jpeg, .png, .jpg" required>
                </div>
            </div>
    
            <label for="note">Note: White background with school uniform attire. JPG & PNG file only.</label>
            
            <label for="position">Running for:</label>
            <select id="position" name="position" required>
                <option value="" disabled selected>Select a position</option>
                <option value="President">President</option>
                <option value="Vice_President">Vice President</option>
                <option value="Secretary">Secretary</option>
                <option value="Assistant_Secretary">Assistant Secretary</option>
                <option value="Auditor">Auditor</option>
                <option value="Treasurer">Treasurer</option>
                <option value="Public_Officer">Public Officer</option>
                <option value="Media_Adviser">Media Adviser</option>
                <option value="Business_Manager">Business Manager</option>
                <option value="Event_Manager">Event Manager</option>
                <option value="Senator">Senator</option>
            </select><br>
            
            <label for="objectives">Objectives/Platform:</label>
            <textarea id="objectives" name="platform" rows="4" cols="30" required></textarea><br>
    
            <input type="submit" name="solosubmit" class="solosubmit" value="Submit">
        </form>
        <div id="error-message-solo" style="display: none; color: red; text-align: center; margin-top: 10px;">
             Application period has ended.
        </div>
    </div>
</div>

<div id="partylistContent" style="display: none;">
    <div class="partylistform">
        <form id="partylistForm" method="post" enctype="multipart/form-data">
            <h2>PARTYLIST CANDIDATE FORM</h2><br>

            <div class="flex-container">
                <div class="flex-item">
                    <label for="partyName">Name:</label>
                    <input type="text" id="userNameInput" name="fullnameP" value="<?php echo $fullname; ?>" readonly>
                </div>
                <div class="flex-item">
                    <label for="yearSection">Year & Section:</label>
                    <input type="text" id="yearSection" name="yearandsectionP" required>
                </div>
            </div>

            <div class="flex-container">
                <div class="flex-item">
                    <label for="institute">Institute:</label>
                    <input type="text" id="institutePartylist" name="instituteP" value="<?php echo $institute; ?>" readonly>
                </div>
                <div class="flex-item">
                    <label for="picture">Upload Picture:</label>
                    <input type="file" id="picture" name="imageP" accept="image/jpeg, image/png, image/jpg" required>
                </div>
            </div>

            <label for="note">Note: White background with school uniform attire. JPG & PNG file only.</label>
            <label for="position">Running for:</label>
            <select id="positionPartylist" name="positionparty" required>
                <option value="" disabled selected>Select a position</option>
                <option value="President">President</option>
                <option value="Vice_President">Vice President</option>
                <option value="Secretary">Secretary</option>
                <option value="Assistant_Secretary">Assistant Secretary</option>
                <option value="Auditor">Auditor</option>
                <option value="Treasurer">Treasurer</option>
                <option value="Public_Officer">Public Officer</option>
                <option value="Media_Adviser">Media Adviser</option>
                <option value="Business_Manager">Business Manager</option>
                <option value="Event_Manager">Event Manager</option>
                <option value="Senator">Senator</option>
            </select>
            
            <label id="partyNameLabelP" for="partyName">Partylist Name:</label>
            <input type="text" id="partyNameInputP" name="partylistname" required oninput="this.value = this.value.toUpperCase()">
                                    
 
            <label for="objectives">Objectives/Platform:</label>
            <textarea id="objectives" name="platformparty" rows="4" cols="30" required></textarea><br>

            <div id="membersOfficerContainer" style="display: none;"> 
                <label for="membersOfficer">Members Officer:(Name of your member)</label>
                <div id="membersOfficers" style="display: none;">
                    <textarea id="objectives" name="officersmembername" rows="4" cols="30" placeholder="Name of your members and its position"></textarea><br>
                </div>
            </div>

            <input type="submit" name="partylistsubmit" class="partylistsubmit" value="Submit">
        </form>
        <div id="error-message-partylist" style="display: none; color: red; text-align: center; margin-top: 10px;">
            Application period has ended.
        </div>
    </div>
</div>


    
</body>

<script>
    function togglePlatform(element) {
        var platformDiv = element.querySelector('.applicant-platform');
        if (platformDiv.style.display === 'none') {
            platformDiv.style.display = 'block';
        } else {
            platformDiv.style.display = 'none';
        }
    }

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
            const voteButtonsS = document.querySelectorAll('.solosubmit');
            const voteButtonsP = document.querySelectorAll('.partylistsubmit');
            const errorMessageS = document.getElementById('error-message-solo');
            const errorMessageP = document.getElementById('error-message-partylist');
            const errorMessageEnded = document.getElementById('error-message-ended');

            const updateState = () => {
                const now = new Date().getTime();
                if (now >= start.getTime()) {
                    voteButtonsS.forEach(button => button.disabled = true);
                    voteButtonsP.forEach(button => button.disabled = true);
                    errorMessageS.style.display = 'block';
                    errorMessageP.style.display = 'block';
                    errorMessageEnded.style.display = 'block';
                } else {
                    voteButtonsS.forEach(button => button.disabled = false);
                    voteButtonsP.forEach(button => button.disabled = false);
                    errorMessageS.style.display = 'none';
                    errorMessageP.style.display = 'none';
                    errorMessageEnded.style.display = 'none';
                }
            };

            // Initial state update
            updateState();

            // Periodically update the state
            setInterval(updateState, 1000);
        }

        document.addEventListener('DOMContentLoaded', fetchVotingSchedule);

        document.addEventListener('DOMContentLoaded', function () {
        // Select all submit buttons
        const submitButtonsS = document.querySelectorAll('.solosubmit');
        const submitButtonsP = document.querySelectorAll('.partylistsubmit');
        const errorMessageSolo = document.getElementById('error-message-solo');
        const errorMessagePartylist = document.getElementById('error-message-partylist');

        // Function to disable submit buttons and display error message
        function disableSubmitButtons() {
            submitButtonsS.forEach(button => button.disabled = true);
            submitButtonsP.forEach(button => button.disabled = true);
            errorMessageSolo.innerText = 'Application period hasn\'t started or ended.';
            errorMessageSolo.style.display = 'block';
            errorMessagePartylist.innerText = 'Application period hasn\'t started or ended.';
            errorMessagePartylist.style.display = 'block';
        }

        // Call the function to disable submit buttons and display error message
        disableSubmitButtons();
    });

        
</script>
<script src="../design/USERS.js"></script>

</html>