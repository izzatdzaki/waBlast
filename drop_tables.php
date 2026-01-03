<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=sik', 'root', '');
$db->exec('SET FOREIGN_KEY_CHECKS=0');
$db->exec('DROP TABLE IF EXISTS jobs');
$db->exec('SET FOREIGN_KEY_CHECKS=1');
echo "✓ Jobs table dropped\n";
