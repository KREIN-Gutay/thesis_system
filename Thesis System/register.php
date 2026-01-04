<?php
require_once 'config/db.php';
require_once 'models/User.php';
require_once 'includes/Auth.php';
require_once 'includes/Security.php';

Auth::redirectIfAuthenticated();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        $userModel = new User($pdo);

        if ($userModel->findByUsername($username)) {
            $error = 'Username already exists.';
        } elseif ($userModel->findByEmail($email)) {
            $error = 'Email already exists.';
        } else {
           $data = [
    'username'    => Security::sanitizeInput($username),
    'email'       => Security::sanitizeInput($email),
    'password'    => Security::hashPassword($password),
    'role'        => 'student',
    'first_name'  => 'Pending',
    'last_name'   => 'User',
    'middle_name' => null,
    'student_id'  => null,
    'program_id'  => null
];


            $userModel->createUser($data);
            $success = 'Account created successfully. You may now login.';
        }
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Thesis Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>

<body style="font-family:Poppins; background:#f4f6f8; display:flex; justify-content:center; align-items:center; height:100vh;">

<form method="POST" style="background:white; padding:40px; border-radius:15px; width:100%; max-width:420px;">
    <h2 style="text-align:center;">Create Account</h2>

    <?php if ($error): ?>
        <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Username" required
           style="width:100%; padding:12px; margin:10px 0;">

    <input type="email" name="email" placeholder="Email address" required
           style="width:100%; padding:12px; margin:10px 0;">

    <input type="password" name="password" placeholder="Password" required
           style="width:100%; padding:12px; margin:10px 0;">

    <button type="submit"
            style="width:100%; padding:12px; background:#667eea; color:white; border:none; border-radius:10px;">
        Register
    </button>

    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="login.php">Login</a>
    </p>
</form>

</body>
</html>
