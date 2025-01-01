<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('config.php');

// Fuzzy Membership Function
function getMembership($symptomLevel) {
    if ($symptomLevel <= 1) return "Low";
    if ($symptomLevel <= 3) return "Medium";
    return "High";
}

// Apply Fuzzy Rules
function applyFuzzyRules($symptoms) {
    $diagnosis = [];

    // Example rule: Diagnose Conjunctivitis if Red Eyes is High and Itchiness is Medium or High
    if (
        isset($symptoms['red_eyes']) && getMembership($symptoms['red_eyes']) == 'High' &&
        isset($symptoms['itchiness']) && getMembership($symptoms['itchiness']) !== 'Low'
    ) {
        $diagnosis[] = [
            "name" => "Conjunctivitis",
            "description" => "Inflammation or infection of the conjunctiva, usually caused by allergies or infection."
        ];
    }

    // Example rule: Diagnose Allergic Reaction if Itchiness is High and Red Eyes is Medium or High
    if (
        isset($symptoms['itchiness']) && getMembership($symptoms['itchiness']) == 'High' &&
        isset($symptoms['red_eyes']) && getMembership($symptoms['red_eyes']) !== 'Low'
    ) {
        $diagnosis[] = [
            "name" => "Allergic Reaction",
            "description" => "An allergic response causing symptoms such as itching and red eyes."
        ];
    }

    // Add more fuzzy rules here as needed

    return $diagnosis;
}

// Main Logic
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Check for valid JSON and presence of symptoms
if (!isset($data['symptoms']) || !is_array($data['symptoms'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input. Symptoms data is required and should be an array."]);
    exit();
}

// Apply fuzzy rules
$diagnosis = applyFuzzyRules($data['symptoms']);

header('Content-Type: application/json');
echo json_encode($diagnosis);

sqlsrv_close($conn);
?>
