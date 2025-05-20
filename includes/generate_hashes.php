<?php
// Generowanie hashów dla wszystkich testowych kont
$users = [
    [
        'email' => 'admin@bania.pl',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'email' => 'pracownik1@bania.pl',
        'password' => 'pracownik123',
        'role' => 'employee'
    ],
    [
        'email' => 'jan.kowalski@example.com',
        'password' => 'klient123',
        'role' => 'client'
    ]
];

foreach ($users as $user) {
    $hash = password_hash($user['password'], PASSWORD_DEFAULT);
    echo "Email: {$user['email']}<br>";
    echo "Password: {$user['password']}<br>";
    echo "Hash: $hash<br><br>";
}
?>