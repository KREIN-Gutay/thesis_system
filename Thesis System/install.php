<?php
// Installation script for Thesis Management System

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'thesis_management_system';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Thesis Management System - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
<div class='container'>
<h1>Thesis Management System Installation</h1>";

try {
    // Create connection
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='info'>Connected to MySQL server successfully.</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p class='success'>Database '$db_name' created or already exists.</p>";
    
    // Select database
    $pdo->exec("USE `$db_name`");
    
    // Read SQL file
    $sql = file_get_contents('thesis_system.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read thesis_system.sql file");
    }
    
    // Execute SQL
    $statements = explode(';', $sql);
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $success_count++;
            } catch (PDOException $e) {
                $error_count++;
                echo "<p class='error'>Error executing statement: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p class='success'>Installation completed! Successfully executed $success_count statements.</p>";
    echo "<p class='info'>Errors encountered: $error_count</p>";
    
    // Test data check
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();
    
    echo "<p class='info'>Found $user_count users in the database.</p>";
    
    echo "<h2>Next Steps</h2>
    <ol>
        <li>Delete this install.php file for security reasons</li>
        <li>Ensure the <code>assets/uploads/</code> directory is writable</li>
        <li>Access the application at <a href='index.php'>index.php</a></li>
        <li>Login with default credentials:<br>
        Admin: admin / password<br>
        Student: student1 / password<br>
        Adviser: adviser1 / password</li>
    </ol>
    
    <a href='index.php' class='btn'>Go to Application</a>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in config/db.php</p>";
} catch (Exception $e) {
    echo "<p class='error'>Installation error: " . $e->getMessage() . "</p>";
}

echo "</div></body></html>";
?>