<?php
include('config.php');

$query = "SELECT * FROM diseases";
$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}

header('Content-Type: application/json');
echo json_encode($results);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>