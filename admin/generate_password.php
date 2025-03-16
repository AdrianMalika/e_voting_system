<?php
require_once '../config/database.php';

function generateSimplePassword() {
    // Define character sets
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Excluding I and O to avoid confusion
    $lowercase = 'abcdefghijkmnpqrstuvwxyz'; // Excluding l and o to avoid confusion
    $numbers = '23456789'; // Excluding 0 and 1 to avoid confusion
    
    $password = '';
    
    // Add one uppercase letter
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    
    // Add two numbers
    for ($i = 0; $i < 2; $i++) {
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
    }
    
    // Add 5 lowercase letters to reach 8 characters total
    for ($i = 0; $i < 5; $i++) {
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    }
    
    // Shuffle the password
    $password = str_shuffle($password);
    
    return $password;
}

// Generate password and return as JSON
header('Content-Type: application/json');
echo json_encode(['password' => generateSimplePassword()]);
