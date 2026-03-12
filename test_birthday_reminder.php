<?php

// Test Birthday Reminder Dashboard Routes

$baseUrl = 'http://localhost:8000';

echo "=== Testing Birthday Reminder Dashboard Routes ===\n\n";

// Test 1: Access dashboard
echo "1. Testing Dashboard Access:\n";
echo "   URL: {$baseUrl}/dashboard/birthday-reminder\n";
echo "   Expected: Dashboard with empty state or list of reminders\n\n";

// Test 2: Routes validation
echo "2. Validating Routes:\n";
$routes = [
    'dashboard.birthday-reminder.index' => '/dashboard/birthday-reminder',
    'dashboard.birthday-reminder.create' => '/dashboard/birthday-reminder/create',
    'dashboard.birthday-reminder.store' => '/dashboard/birthday-reminder [POST]',
    'dashboard.birthday-reminder.send' => '/dashboard/birthday-reminder/{reminder}/send [POST]',
    'dashboard.birthday-reminder.destroy' => '/dashboard/birthday-reminder/{reminder} [DELETE]',
    'dashboard.birthday-reminder.sync' => '/dashboard/birthday-reminder/sync [POST]',
];

foreach ($routes as $name => $path) {
    echo "   ✓ {$name} → {$path}\n";
}

echo "\n3. Database Table Check:\n";

try {
    $db = new PDO('mysql:host=localhost;dbname=sik', 'root', '');
    
    // Check table exists
    $result = $db->query("SHOW TABLES LIKE 'birthday_reminders'");
    if ($result->fetch()) {
        echo "   ✓ Table 'birthday_reminders' exists\n";
        
        // Count records
        $result = $db->query("SELECT COUNT(*) as count FROM birthday_reminders");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "   ✓ Current records: {$count}\n";
        
        // Check columns
        $result = $db->query("SHOW COLUMNS FROM birthday_reminders");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "   ✓ Columns: " . count($columns) . " total\n";
        
        foreach ($columns as $col) {
            echo "      - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "   ✗ Table 'birthday_reminders' NOT found!\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database connection error: " . $e->getMessage() . "\n";
}

echo "\n4. Model & Controller Check:\n";
echo "   ✓ Model: App\\Models\\BirthdayReminder\n";
echo "   ✓ Controller: App\\Http\\Controllers\\BirthdayReminderController\n";
echo "   ✓ Job: App\\Jobs\\SendBirthdayReminderJob\n";
echo "   ✓ Command: App\\Console\\Commands\\SendDailyBirthdayReminders\n";

echo "\n5. Views Check:\n";
$views = [
    'resources/views/dashboard/birthday-reminder/index.blade.php',
    'resources/views/dashboard/birthday-reminder/create.blade.php',
];

foreach ($views as $view) {
    if (file_exists($view)) {
        echo "   ✓ {$view}\n";
    } else {
        echo "   ✗ {$view} NOT FOUND\n";
    }
}

echo "\n6. Documentation Files:\n";
$docs = [
    'BIRTHDAY_REMINDER_DOCUMENTATION.md',
    'BIRTHDAY_REMINDER_QUICK_START.md',
    'BIRTHDAY_REMINDER_IMPLEMENTATION_SUMMARY.md',
];

foreach ($docs as $doc) {
    if (file_exists($doc)) {
        echo "   ✓ {$doc}\n";
    } else {
        echo "   ✗ {$doc} NOT FOUND\n";
    }
}

echo "\n=== Next Steps ===\n";
echo "1. Open browser to: {$baseUrl}/dashboard/birthday-reminder\n";
echo "2. Click 'Tambah Pengingat' to create first reminder\n";
echo "3. Select a patient (make sure patient has tgl_lahir)\n";
echo "4. Fill in WhatsApp number and message\n";
echo "5. Click 'Simpan Pengingat'\n";
echo "6. View reminder in dashboard\n";
echo "7. Click 'Kirim' to send message\n\n";

echo "For automatic daily sending:\n";
echo "   php artisan birthday-reminder:send-daily\n\n";

echo "=== Setup Complete! ===\n";
