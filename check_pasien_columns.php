<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='pasien' AND TABLE_SCHEMA='sik'");

echo "=== Pasien Table Columns ===\n\n";
foreach ($columns as $col) {
    echo $col->COLUMN_NAME . " (" . $col->COLUMN_TYPE . ")\n";
    if (strpos(strtolower($col->COLUMN_NAME), 'lahir') !== false || strpos(strtolower($col->COLUMN_NAME), 'birth') !== false) {
        echo "   ✓ Birthday field found!\n";
    }
}

echo "\n\n";

// Check if tgl_lahir exists
$hasDateOfBirth = collect($columns)->contains(fn($col) => 
    strpos(strtolower($col->COLUMN_NAME), 'lahir') !== false || 
    strpos(strtolower($col->COLUMN_NAME), 'birth') !== false
);

if ($hasDateOfBirth) {
    echo "✅ Database sudah punya field tanggal lahir\n";
} else {
    echo "⚠️  Database tidak punya field tanggal lahir\n";
    echo "Kita perlu membuat migration untuk menambahkan field ini\n";
}
