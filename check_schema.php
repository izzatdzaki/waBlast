<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=sik', 'root', '');
$result = $db->query("DESCRIBE blast_messages");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Blast Messages Table Structure:\n";
echo "================================\n";
foreach ($columns as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " | " . $col['Key'] . "\n";
}
