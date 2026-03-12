<?php

$db = new PDO('mysql:host=localhost;dbname=sik', 'root', '');
$result = $db->query('SHOW CREATE TABLE pasien');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
