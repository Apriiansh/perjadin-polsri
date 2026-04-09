<?php
require 'vendor/autoload.php';
// Boot CI4
$app = \Config\Services::app();
$db = \Config\Database::connect();

echo "Repairing Student Travel Categories...\n";

// Update category for any travel request that has student members
$sql = "UPDATE travel_requests 
        SET category = 'mahasiswa' 
        WHERE id IN (SELECT travel_request_id FROM travel_student_members)";

$db->query($sql);
$affectedRows = $db->affectedRows();

echo "Fixed $affectedRows records.\n";
