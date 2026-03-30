<?php
$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$db   = getenv('DB_NAME') ?: 'railway';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'ZZULabmvLrpVEMPCofdjvIaOynTPhihX';
$port = getenv('DB_PORT') ?: '3306';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}



$admins = [
    [
        'first_name' => 'System',
        'last_name'  => 'Admin',
        'email'      => 'admin@inkingi.rw',
        'pass'       => 'Admin1234',
        'role'       => 'system_admin',
        'school'     => null
    ],
    [
        'first_name' => 'GS Kigali',
        'last_name'  => 'Admin',
        'email'      => 'school@inkingi.rw',
        'pass'       => 'School1234',
        'role'       => 'school_admin',
        'school'     => 'GS Kigali'
    ]
];

foreach ($admins as $admin) {
    $hash = password_hash($admin['pass'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users 
        (first_name, last_name, email, password, school_name, role, preferred_language)
        VALUES (?, ?, ?, ?, ?, ?, 'en')
    ");
    $stmt->execute([
        $admin['first_name'],
        $admin['last_name'],
        $admin['email'],
        $hash,
        $admin['school'],
        $admin['role']
    ]);
    echo "✅ Created: " . $admin['email'] . "<br>";
}

echo "<br><strong>Done. Delete this file now.</strong>";
