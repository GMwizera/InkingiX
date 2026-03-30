<?php
require_once 'config/database.php';

$admins = [
    [
        'name'   => 'System Admin',
        'email'  => 'admin@inkingi.rw',
        'pass'   => 'Admin1234',
        'role'   => 'system_admin',
        'school' => null
    ],
    [
        'name'   => 'GS Kigali Admin',
        'email'  => 'school@inkingi.rw',
        'pass'   => 'School1234',
        'role'   => 'school_admin',
        'school' => 'GS Kigali'
    ]
];

foreach ($admins as $admin) {
    $hash = password_hash($admin['pass'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users 
        (name, email, password_hash, school_name, role, language)
        VALUES (?, ?, ?, ?, ?, 'en')
    ");
    $stmt->execute([
        $admin['name'],
        $admin['email'],
        $hash,
        $admin['school'],
        $admin['role']
    ]);
    echo "Created: " . $admin['email'] . "<br>";
}

echo "<br><strong>Done. Delete this file now.</strong>";
