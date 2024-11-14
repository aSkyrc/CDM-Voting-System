<?php
include_once("../connection/connection.php");

$conn = connection(); // Establish the database connection

function getLeadingCandidates($conn) {
    $positions = ['President', 'Vice_President', 'Secretary', 'Assistant_Secretary', 'Auditor', 'Treasurer', 'Public_Officer', 'Media_Adviser', 'Business_Manager', 'Event_Manager', 'Senator'];
    $results = [];

    foreach ($positions as $position) {
        $query = $conn->prepare("SELECT candidate_id, candidate_type, COUNT(*) AS vote_count FROM votes WHERE position = ? GROUP BY candidate_id, candidate_type ORDER BY vote_count DESC LIMIT 3");
        $query->bind_param("s", $position);
        $query->execute();
        $result = $query->get_result();

        $position_result = [
            'position' => str_replace("_", " ", $position),
            'candidates' => []
        ];

        while ($row = $result->fetch_assoc()) {
            $candidate_id = $row['candidate_id'];
            $candidate_query = $conn->prepare("SELECT fullname, yearandsection, institute, image FROM solo_candidate_application WHERE id = ? UNION SELECT fullname, yearandsection, institute, image FROM partylist_candidate_application WHERE id = ?");
            $candidate_query->bind_param("ii", $candidate_id, $candidate_id);
            $candidate_query->execute();
            $candidate_result = $candidate_query->get_result();
            if ($candidate_info = $candidate_result->fetch_assoc()) {
                $position_result['candidates'][] = array_merge($candidate_info, ['vote_count' => $row['vote_count']]);
            }
            $candidate_query->close();
        }
        
        $results[] = $position_result;
        $query->close();
    }
    
    return $results;
}

header('Content-Type: application/json');
echo json_encode(getLeadingCandidates($conn));
?>
