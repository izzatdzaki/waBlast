<?php

$db = new PDO('mysql:host=localhost;dbname=sik', 'root', '');
$result = $db->query('SHOW COLUMNS FROM pasien WHERE Field="no_rkm_medis"');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo json_encode($row, JSON_PRETTY_PRINT);
