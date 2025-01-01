<?php
$serverName = "LAPTOP-FS0SR2G3\GERRY";
$connectionOptions = array(
    "Database" => "EyeDiagnosticSystem_2",
    "Uid" => "user_gerry_23091397164",
    "PWD" => "gerry164"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
?>