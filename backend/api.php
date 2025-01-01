<?php
include 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['endpoint'])) {
    $endpoint = $_GET['endpoint'];

    switch ($endpoint) {
        case 'start-consultation':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_name = $_POST['user_name'] ?? null;
        
                if (!$user_name) {
                    echo json_encode(["error" => "User name is required"]);
                    exit;
                }
        
                // Use parameterized query for security
                $sql = "INSERT INTO consultations (user_name, created_at)
                        OUTPUT INSERTED.id
                        VALUES (?, GETDATE())";

                $params = [$user_name];
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    echo json_encode(["error" => "Database error: " . print_r(sqlsrv_errors(), true)]);
                    exit;
                }

                // Fetch the inserted ID directly
                $lastId = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['id'];

                if ($lastId) {
                    echo json_encode(["consultation_id" => $lastId]);
                } else {
                    echo json_encode(["error" => "Failed to retrieve consultation ID."]);
                }
            } else {
                echo json_encode(["error" => "Invalid request method"]);
            }
            break;

        case 'save-answer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultation_id = $_POST['consultation_id'];
                $symptom_id = $_POST['symptom_id'];
                $severity = $_POST['severity'];
        
                $sql = "INSERT INTO consultation_answers (consultation_id, symptom_id, severity)
                        VALUES (?, ?, ?)";
                $params = [$consultation_id, $symptom_id, $severity];
                $stmt = sqlsrv_query($conn, $sql, $params);
        
                if ($stmt === false) {
                    echo json_encode(["error" => "Database error: " . print_r(sqlsrv_errors(), true)]);
                } else {
                    echo json_encode(["message" => "Answer saved successfully"]);
                }
            }
            break;

        case 'calculate-fuzzy':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Initialize debugging array
                $debug = [];
        
                // Step 1: Get consultation_id
                $consultation_id = $_POST['consultation_id'] ?? null;
                $debug[] = "Received consultation_id: " . json_encode($consultation_id);
        
                if (!is_numeric($consultation_id)) {
                    $debug[] = "ERROR: consultation_id is invalid or missing.";
                    echo json_encode(["error" => "Invalid consultation ID.", "debug" => $debug]);
                    exit;
                }
        
                // Step 2: Fetch user name from consultations table
                $sql = "SELECT user_name FROM consultations WHERE id = ?";
                $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
                if ($stmt === false) {
                    $debug[] = "ERROR: Failed to query consultations table: " . print_r(sqlsrv_errors(), true);
                    echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
                    exit;
                }
        
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                if (!$row) {
                    $debug[] = "ERROR: No user found for consultation_id = $consultation_id";
                    echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
                    exit;
                }
                $user_name = $row['user_name'];
                $debug[] = "Retrieved user_name: " . json_encode($user_name);
        
                // Step 3: Fetch answers from consultation_answers table
                $sql = "SELECT symptom_id, severity FROM consultation_answers WHERE consultation_id = ?";
                $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
                if ($stmt === false) {
                    $debug[] = "ERROR: Failed to query consultation_answers table: " . print_r(sqlsrv_errors(), true);
                    echo json_encode(["error" => "Failed to retrieve answers.", "debug" => $debug]);
                    exit;
                }
        
                $answers = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $answers[] = $row;
                }
                $debug[] = "Retrieved answers: " . json_encode($answers);
        
                if (empty($answers)) {
                    $debug[] = "ERROR: No answers found for consultation_id = $consultation_id";
                    echo json_encode(["error" => "No answers found.", "debug" => $debug]);
                    exit;
                }
        
                // Step 4: Fetch fuzzy rules
                $sql = "SELECT fr.symptom_id, fr.severity_level, fr.disease_id, fr.impact,
                               s.low, s.medium, s.high, d.relevance
                        FROM fuzzy_rules fr
                        JOIN symptoms s ON fr.symptom_id = s.id
                        JOIN diseases d ON fr.disease_id = d.id";
                $stmt = sqlsrv_query($conn, $sql);
        
                if ($stmt === false) {
                    $debug[] = "ERROR: Failed to query fuzzy_rules: " . print_r(sqlsrv_errors(), true);
                    echo json_encode(["error" => "Failed to retrieve fuzzy rules.", "debug" => $debug]);
                    exit;
                }
        
                $rules = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $rules[] = $row;
                }
                $debug[] = "Retrieved fuzzy rules: " . json_encode($rules);
        
                if (empty($rules)) {
                    $debug[] = "ERROR: No fuzzy rules found.";
                    echo json_encode(["error" => "No fuzzy rules found.", "debug" => $debug]);
                    exit;
                }
        
                // Step 5: Fuzzification - Convert severity values to fuzzy membership values
                $fuzzyInputs = [];
                foreach ($answers as $answer) {
                    $severity = $answer['severity'];
                    $fuzzyInputs[$answer['symptom_id']] = [
                        'Low' => max(0, 1 - abs($severity - 1) / 2),
                        'Medium' => max(0, 1 - abs($severity - 3) / 2),
                        'High' => max(0, 1 - abs($severity - 5) / 2),
                    ];
                }
                $debug[] = "Fuzzified inputs: " . json_encode($fuzzyInputs);
        
                // Step 6: Rule Evaluation - Calculate disease scores based on fuzzy rules
                $diseaseScores = []; // This will store diseaseId => score mapping
                foreach ($rules as $rule) {
                    $symptomId = $rule['symptom_id'];
                    $severityLevel = $rule['severity_level'];
                    $diseaseId = $rule['disease_id'];
                    $impact = $rule['impact'];
                    $relevance = $rule['relevance'];

                    // Get the fuzzy membership for the severity level
                    $membership = $fuzzyInputs[$symptomId][$severityLevel] ?? 0;

                    // Calculate contribution to the disease score
                    $contribution = $membership * $impact * $relevance;

                    // Add or update the disease score in the array
                    if (!isset($diseaseScores[$diseaseId])) {
                        $diseaseScores[$diseaseId] = 0;
                    }
                    $diseaseScores[$diseaseId] += $contribution;
                }

                // Debugging: Check the disease scores array
                $debug[] = "Calculated disease scores: " . json_encode($diseaseScores);

                // Step 7: Normalize Scores (optional)
                $totalScore = array_sum($diseaseScores);
                foreach ($diseaseScores as $diseaseId => &$score) {
                    $score = $totalScore > 0 ? $score / $totalScore : 0;
                }

                // Debugging: Check normalized scores
                $debug[] = "Normalized disease scores: " . json_encode($diseaseScores);

                // Step 8: Get Disease Names and Descriptions, and Prepare Results
                $results = [];
                foreach ($diseaseScores as $diseaseId => $score) {
                    // Fetch disease details
                    $sql = "SELECT name, description FROM diseases WHERE id = ?";
                    $stmt = sqlsrv_query($conn, $sql, [$diseaseId]);
                    if ($stmt === false) {
                        $debug[] = "ERROR: Failed to fetch disease details for disease_id = $diseaseId: " . print_r(sqlsrv_errors(), true);
                        continue; // Skip if the query fails for this disease
                    }
                    
                    $disease = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    
                    if ($disease) {
                        // Add disease name, description, and fuzzy score to the results array
                        $results[] = [
                            "disease_name" => $disease['name'],
                            "description" => $disease['description'],
                            "fuzzy_score" => $score,
                        ];
                    }
                }

                // Debugging: Check final results before sending
                $debug[] = "Final results: " . json_encode($results);
                        
                // Step 9: Insert Results into consultation_results table
                foreach ($diseaseScores as $diseaseId => $score) {
                    $sql = "INSERT INTO consultation_results (consultation_id, disease_id, fuzzy_score)
                            VALUES (?, ?, ?)";
                    $stmt = sqlsrv_query($conn, $sql, [$consultation_id, $diseaseId, $score]);
        
                    if ($stmt === false) {
                        $debug[] = "ERROR: Failed to insert into consultation_results for disease_id $diseaseId: " . print_r(sqlsrv_errors(), true);
                    } else {
                        $debug[] = "Inserted result: consultation_id = $consultation_id, disease_id = $diseaseId, fuzzy_score = $score";
                    }
                }
        
                // Step 10: Return Results with Debug Data
                echo json_encode(["user_name" => $user_name, "consultation_id" => $consultation_id, "debug" => $debug]);
            }
            break;
        
        case 'get-symptoms':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $sql = "SELECT id, name FROM symptoms";
                $stmt = sqlsrv_query($conn, $sql);
        
                $symptoms = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $symptoms[] = $row;
                }
        
                echo json_encode(["symptoms" => $symptoms]);
            }
            break;

        case 'get-diagnosis':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultation_id = $_POST['consultation_id'];
        
                // Validate consultation ID
                if (!$consultation_id) {
                    echo json_encode(["error" => "Consultation ID is required"]);
                    exit;
                }
        
                // Fetch the diagnosis result from the database
                $sql = "SELECT d.name AS disease_name, d.description 
                        FROM diagnoses di
                        JOIN diseases d ON di.disease_id = d.id
                        WHERE di.consultation_id = $consultation_id";
        
                $result = $conn->query($sql);
        
                if ($result->num_rows > 0) {
                    $diagnosis = [];
                    while ($row = $result->fetch_assoc()) {
                        $diagnosis[] = [
                            "disease_name" => $row['disease_name'],
                            "description" => $row['description']
                        ];
                    }
                    echo json_encode(["diagnosis" => $diagnosis]);
                } else {
                    echo json_encode(["error" => "No diagnosis found for this consultation"]);
                }
            } else {
                echo json_encode(["error" => "Invalid request method"]);
            }
            break;

        default:
            echo json_encode(["error" => "Unknown endpoint"]);
            break;
        
        case 'get-consultation-results':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Get the consultation_id from the request
                $consultation_id = $_GET['consultation_id'] ?? null;
        
                // Validate the consultation_id
                if (!is_numeric($consultation_id)) {
                    echo json_encode(["error" => "Invalid consultation ID."]);
                    exit;
                }
        
                // Fetch the results from consultation_results table
                $sql = "SELECT cr.disease_id, cr.fuzzy_score, d.name AS disease_name, d.description
                        FROM consultation_results cr
                        JOIN diseases d ON cr.disease_id = d.id
                        WHERE cr.consultation_id = ?";
                $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
                if ($stmt === false) {
                    echo json_encode(["error" => "Failed to fetch consultation results: " . print_r(sqlsrv_errors(), true)]);
                    exit;
                }
        
                $results = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $name = htmlspecialchars($row['disease_name'], ENT_QUOTES, 'UTF-8');
                    $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');

                    $results[] = [
                        "disease_name" => $name,
                        "description" => $description,
                        "fuzzy_score" => $row['fuzzy_score'],
                    ];
                }
        
                // Log the results to the PHP error log for inspection
                error_log("DEBUG: Consultation Results: " . print_r($results, true));
        
                // Return the results as JSON
                echo json_encode([
                    "diagnosis" => $results,
                    "raw_results" => $results // Add raw results here for frontend inspection
                ], JSON_UNESCAPED_UNICODE);
            }
            break;
    }
} else {
    echo json_encode(["error" => "No endpoint specified"]);
}
?>