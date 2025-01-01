// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //     // Initialize debugging array
            //     $debug = [];
        
            //     // Step 1: Get consultation_id
            //     $consultation_id = $_POST['consultation_id'] ?? null;
            //     $debug[] = "Received consultation_id: " . json_encode($consultation_id);
        
            //     if (!is_numeric($consultation_id)) {
            //         $debug[] = "ERROR: consultation_id is invalid or missing.";
            //         echo json_encode(["error" => "Invalid consultation ID.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 2: Fetch user name from consultations table
            //     $sql = "SELECT user_name FROM consultations WHERE id = ?";
            //     $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query consultations table: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            //     if (!$row) {
            //         $debug[] = "ERROR: No user found for consultation_id = $consultation_id";
            //         echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
            //         exit;
            //     }
            //     $user_name = $row['user_name'];
            //     $debug[] = "Retrieved user_name: " . json_encode($user_name);
        
            //     // Step 3: Fetch answers from consultation_answers table
            //     $sql = "SELECT symptom_id, severity FROM consultation_answers WHERE consultation_id = ?";
            //     $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query consultation_answers table: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Failed to retrieve answers.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $answers = [];
            //     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //         $answers[] = $row;
            //     }
            //     $debug[] = "Retrieved answers: " . json_encode($answers);
        
            //     if (empty($answers)) {
            //         $debug[] = "ERROR: No answers found for consultation_id = $consultation_id";
            //         echo json_encode(["error" => "No answers found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 4: Fetch fuzzy rules
            //     $sql = "SELECT fr.symptom_id, fr.severity_level, fr.disease_id, fr.impact,
            //                    s.low, s.medium, s.high, d.relevance
            //             FROM fuzzy_rules fr
            //             JOIN symptoms s ON fr.symptom_id = s.id
            //             JOIN diseases d ON fr.disease_id = d.id";
            //     $stmt = sqlsrv_query($conn, $sql);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query fuzzy_rules: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Failed to retrieve fuzzy rules.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $rules = [];
            //     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //         $rules[] = $row;
            //     }
            //     $debug[] = "Retrieved fuzzy rules: " . json_encode($rules);
        
            //     if (empty($rules)) {
            //         $debug[] = "ERROR: No fuzzy rules found.";
            //         echo json_encode(["error" => "No fuzzy rules found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 5: Fuzzification
            //     $fuzzyInputs = [];
            //     foreach ($answers as $answer) {
            //         $severity = $answer['severity'];
            //         $fuzzyInputs[$answer['symptom_id']] = [
            //             'Low' => max(0, 1 - abs($severity - 1) / 2),
            //             'Medium' => max(0, 1 - abs($severity - 3) / 2),
            //             'High' => max(0, 1 - abs($severity - 5) / 2),
            //         ];
            //     }
            //     $debug[] = "Fuzzified inputs: " . json_encode($fuzzyInputs);
        
            //     // Step 6: Rule Evaluation
            //     $diseaseScores = [];
            //     foreach ($rules as $rule) {
            //         $symptomId = $rule['symptom_id'];
            //         $severityLevel = $rule['severity_level'];
            //         $diseaseId = $rule['disease_id'];
            //         $impact = $rule['impact'];
            //         $relevance = $rule['relevance'];
        
            //         $membership = $fuzzyInputs[$symptomId][$severityLevel] ?? 0;
            //         $contribution = $membership * $impact * $relevance;
        
            //         if (!isset($diseaseScores[$diseaseId])) {
            //             $diseaseScores[$diseaseId] = 0;
            //         }
            //         $diseaseScores[$diseaseId] += $contribution;
            //     }
            //     $debug[] = "Disease scores: " . json_encode($diseaseScores);
        
            //     // Step 7: Normalize Scores
            //     $totalScore = array_sum($diseaseScores);
            //     foreach ($diseaseScores as $diseaseId => &$score) {
            //         $score = $totalScore > 0 ? $score / $totalScore : 0;
            //     }
        
            //     // Step 8: Insert Results into consultation_results
            //     foreach ($diseaseScores as $diseaseId => $score) {
            //         $sql = "INSERT INTO consultation_results (consultation_id, disease_id, fuzzy_score)
            //                 VALUES (?, ?, ?)";
            //         $stmt = sqlsrv_query($conn, $sql, [$consultation_id, $diseaseId, $score]);
        
            //         if ($stmt === false) {
            //             $debug[] = "ERROR: Failed to insert into consultation_results for disease_id $diseaseId: " . print_r(sqlsrv_errors(), true);
            //         } else {
            //             $debug[] = "Inserted result: consultation_id = $consultation_id, disease_id = $diseaseId, fuzzy_score = $score";
            //         }
            //     }
        
            //     // Step 9: Format Results for Frontend
            //     $results = [];
            //     foreach ($diseaseScores as $diseaseId => $score) {
            //         $sql = "SELECT name, description FROM diseases WHERE id = ?";
            //         $stmt = sqlsrv_query($conn, $sql, [$diseaseId]);
            //         $disease = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
            //         $results[] = [
            //             "disease_name" => $disease['name'],
            //             "description" => $disease['description'],
            //             "fuzzy_score" => $score,
            //         ];
            //     }
            //     $debug[] = "Final results: " . json_encode($results);
        
            //     // Step 10: Return Results with Debug Data
            //     echo json_encode(["user_name" => $user_name, "diagnosis" => $results, "debug" => $debug]);
            // }
            // break;

            // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //     // Initialize debugging array
            //     $debug = [];
        
            //     // Step 1: Get consultation_id
            //     $consultation_id = $_POST['consultation_id'] ?? null;
            //     $debug[] = "Received consultation_id: " . json_encode($consultation_id);
        
            //     if (!is_numeric($consultation_id)) {
            //         $debug[] = "ERROR: consultation_id is invalid or missing.";
            //         echo json_encode(["error" => "Invalid consultation ID.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 2: Fetch user name from consultations table
            //     $sql = "SELECT user_name FROM consultations WHERE id = ?";
            //     $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query consultations table: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            //     if (!$row) {
            //         $debug[] = "ERROR: No user found for consultation_id = $consultation_id";
            //         echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
            //         exit;
            //     }
            //     $user_name = $row['user_name'];
            //     $debug[] = "Retrieved user_name: " . json_encode($user_name);
        
            //     // Step 3: Fetch answers from consultation_answers table
            //     $sql = "SELECT symptom_id, severity FROM consultation_answers WHERE consultation_id = ?";
            //     $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query consultation_answers table: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Failed to retrieve answers.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $answers = [];
            //     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //         $answers[] = $row;
            //     }
            //     $debug[] = "Retrieved answers: " . json_encode($answers);
        
            //     if (empty($answers)) {
            //         $debug[] = "ERROR: No answers found for consultation_id = $consultation_id";
            //         echo json_encode(["error" => "No answers found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 4: Fetch fuzzy rules
            //     $sql = "SELECT fr.symptom_id, fr.severity_level, fr.disease_id, fr.impact,
            //                    s.low, s.medium, s.high, d.relevance
            //             FROM fuzzy_rules fr
            //             JOIN symptoms s ON fr.symptom_id = s.id
            //             JOIN diseases d ON fr.disease_id = d.id";
            //     $stmt = sqlsrv_query($conn, $sql);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query fuzzy_rules: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Failed to retrieve fuzzy rules.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $rules = [];
            //     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //         $rules[] = $row;
            //     }
            //     $debug[] = "Retrieved fuzzy rules: " . json_encode($rules);
        
            //     if (empty($rules)) {
            //         $debug[] = "ERROR: No fuzzy rules found.";
            //         echo json_encode(["error" => "No fuzzy rules found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 5: Fuzzification - Convert severity values to fuzzy membership values
            //     $fuzzyInputs = [];
            //     foreach ($answers as $answer) {
            //         $severity = $answer['severity'];
            //         $fuzzyInputs[$answer['symptom_id']] = [
            //             'Low' => max(0, 1 - abs($severity - 1) / 2),
            //             'Medium' => max(0, 1 - abs($severity - 3) / 2),
            //             'High' => max(0, 1 - abs($severity - 5) / 2),
            //         ];
            //     }
            //     $debug[] = "Fuzzified inputs: " . json_encode($fuzzyInputs);
        
            //     // Step 6: Rule Evaluation - Calculate disease scores based on fuzzy rules
            //     $diseaseScores = [];
            //     foreach ($rules as $rule) {
            //         $symptomId = $rule['symptom_id'];
            //         $severityLevel = $rule['severity_level'];
            //         $diseaseId = $rule['disease_id'];
            //         $impact = $rule['impact'];
            //         $relevance = $rule['relevance'];
        
            //         $membership = $fuzzyInputs[$symptomId][$severityLevel] ?? 0;
            //         $contribution = $membership * $impact * $relevance;
        
            //         if (!isset($diseaseScores[$diseaseId])) {
            //             $diseaseScores[$diseaseId] = 0;
            //         }
            //         $diseaseScores[$diseaseId] += $contribution;
            //     }
            //     $debug[] = "Disease scores: " . json_encode($diseaseScores);
        
            //     // Step 7: Normalize Scores
            //     $totalScore = array_sum($diseaseScores);
            //     foreach ($diseaseScores as $diseaseId => &$score) {
            //         $score = $totalScore > 0 ? $score / $totalScore : 0;
            //     }
        
            //     // Step 8: Insert Results into consultation_results table
            //     foreach ($diseaseScores as $diseaseId => $score) {
            //         $sql = "INSERT INTO consultation_results (consultation_id, disease_id, fuzzy_score)
            //                 VALUES (?, ?, ?)";
            //         $stmt = sqlsrv_query($conn, $sql, [$consultation_id, $diseaseId, $score]);
        
            //         if ($stmt === false) {
            //             $debug[] = "ERROR: Failed to insert into consultation_results for disease_id $diseaseId: " . print_r(sqlsrv_errors(), true);
            //         } else {
            //             $debug[] = "Inserted result: consultation_id = $consultation_id, disease_id = $diseaseId, fuzzy_score = $score";
            //         }
            //     }
        
            //     // Step 9: Format Results for Frontend
            //     $results = [];
            //     foreach ($diseaseScores as $diseaseId => $score) {
            //         $sql = "SELECT name, description FROM diseases WHERE id = ?";
            //         $stmt = sqlsrv_query($conn, $sql, [$diseaseId]);
            //         $disease = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
            //         $results[] = [
            //             "disease_name" => $disease['name'],
            //             "description" => $disease['description'],
            //             "fuzzy_score" => $score,
            //         ];
            //     }
            //     $debug[] = "Final results: " . json_encode($results);
        
            //     // Step 10: Return Results with Debug Data
            //     echo json_encode(["user_name" => $user_name, "diagnosis" => $results, "debug" => $debug]);
            // }

            // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //     // Initialize debugging array
            //     $debug = [];
        
            //     // Step 1: Get consultation_id
            //     $consultation_id = $_POST['consultation_id'] ?? null;
            //     $debug[] = "Received consultation_id: " . json_encode($consultation_id);
        
            //     if (!is_numeric($consultation_id)) {
            //         $debug[] = "ERROR: consultation_id is invalid or missing.";
            //         echo json_encode(["error" => "Invalid consultation ID.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 2: Fetch user name from consultations table
            //     $sql = "SELECT user_name FROM consultations WHERE id = ?";
            //     $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query consultations table: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            //     if (!$row) {
            //         $debug[] = "ERROR: No user found for consultation_id = $consultation_id";
            //         echo json_encode(["error" => "Consultation not found.", "debug" => $debug]);
            //         exit;
            //     }
            //     $user_name = $row['user_name'];
            //     $debug[] = "Retrieved user_name: " . json_encode($user_name);
        
            //     // Step 3: Fetch answers from consultation_answers table
            //     $sql = "SELECT symptom_id, severity FROM consultation_answers WHERE consultation_id = ?";
            //     $stmt = sqlsrv_query($conn, $sql, [$consultation_id]);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query consultation_answers table: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Failed to retrieve answers.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $answers = [];
            //     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //         $answers[] = $row;
            //     }
            //     $debug[] = "Retrieved answers: " . json_encode($answers);
        
            //     if (empty($answers)) {
            //         $debug[] = "ERROR: No answers found for consultation_id = $consultation_id";
            //         echo json_encode(["error" => "No answers found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 4: Fetch fuzzy rules
            //     $sql = "SELECT fr.symptom_id, fr.severity_level, fr.disease_id, fr.impact,
            //                    s.low, s.medium, s.high, d.relevance
            //             FROM fuzzy_rules fr
            //             JOIN symptoms s ON fr.symptom_id = s.id
            //             JOIN diseases d ON fr.disease_id = d.id";
            //     $stmt = sqlsrv_query($conn, $sql);
        
            //     if ($stmt === false) {
            //         $debug[] = "ERROR: Failed to query fuzzy_rules: " . print_r(sqlsrv_errors(), true);
            //         echo json_encode(["error" => "Failed to retrieve fuzzy rules.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     $rules = [];
            //     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //         $rules[] = $row;
            //     }
            //     $debug[] = "Retrieved fuzzy rules: " . json_encode($rules);
        
            //     if (empty($rules)) {
            //         $debug[] = "ERROR: No fuzzy rules found.";
            //         echo json_encode(["error" => "No fuzzy rules found.", "debug" => $debug]);
            //         exit;
            //     }
        
            //     // Step 5: Fuzzification - Convert severity values to fuzzy membership values
            //     $fuzzyInputs = [];
            //     foreach ($answers as $answer) {
            //         $severity = $answer['severity'];
            //         $fuzzyInputs[$answer['symptom_id']] = [
            //             'Low' => max(0, 1 - abs($severity - 1) / 2),
            //             'Medium' => max(0, 1 - abs($severity - 3) / 2),
            //             'High' => max(0, 1 - abs($severity - 5) / 2),
            //         ];
            //     }
            //     $debug[] = "Fuzzified inputs: " . json_encode($fuzzyInputs);
        
            //     // Step 6: Rule Evaluation - Calculate disease scores based on fuzzy rules
            //     $diseaseScores = [];
            //     foreach ($rules as $rule) {
            //         $symptomId = $rule['symptom_id'];
            //         $severityLevel = $rule['severity_level'];
            //         $diseaseId = $rule['disease_id'];
            //         $impact = $rule['impact'];
            //         $relevance = $rule['relevance'];
        
            //         $membership = $fuzzyInputs[$symptomId][$severityLevel] ?? 0;
            //         $contribution = $membership * $impact * $relevance;
        
            //         if (!isset($diseaseScores[$diseaseId])) {
            //             $diseaseScores[$diseaseId] = 0;
            //         }
            //         $diseaseScores[$diseaseId] += $contribution;
            //     }
            //     $debug[] = "Disease scores: " . json_encode($diseaseScores);
        
            //     // Step 7: Normalize Scores
            //     $totalScore = array_sum($diseaseScores);
            //     foreach ($diseaseScores as $diseaseId => &$score) {
            //         $score = $totalScore > 0 ? $score / $totalScore : 0;
            //     }
        
            //     // Step 8: Insert Results into consultation_results table
            //     foreach ($diseaseScores as $diseaseId => $score) {
            //         $sql = "INSERT INTO consultation_results (consultation_id, disease_id, fuzzy_score)
            //                 VALUES (?, ?, ?)";
            //         $stmt = sqlsrv_query($conn, $sql, [$consultation_id, $diseaseId, $score]);
        
            //         if ($stmt === false) {
            //             $debug[] = "ERROR: Failed to insert into consultation_results for disease_id $diseaseId: " . print_r(sqlsrv_errors(), true);
            //         } else {
            //             $debug[] = "Inserted result: consultation_id = $consultation_id, disease_id = $diseaseId, fuzzy_score = $score";
            //         }
            //     }

            //     // Step 9: Format Results for Frontend
            //     $results = [];
            //     foreach ($diseaseScores as $diseaseId => $score) {
            //         $sql = "SELECT name, description FROM diseases WHERE id = ?";
            //         $stmt = sqlsrv_query($conn, $sql, [$diseaseId]);
            //         $disease = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
            //         $results[] = [
            //             "disease_name" => $disease['name'],
            //             "description" => $disease['description'],
            //             "fuzzy_score" => $score,
            //         ];
            //     }
            //     $debug[] = "Final results: " . json_encode($results);
        
            //     // Step 10: Return Results with Debug Data
            //     echo json_encode(["user_name" => $user_name, "results" => $results, "debug" => $debug]);
            // }