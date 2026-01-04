<?php
require_once 'config/db.php';
require_once 'models/User.php';
require_once 'includes/Auth.php';
require_once 'includes/Security.php';

// Redirect if already logged in
Auth::redirectIfAuthenticated();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limit
    $identifier = $_SERVER['REMOTE_ADDR'];
    if (!Security::checkRateLimit($identifier)) {
        $error = 'Too many login attempts. Please try again later.';
    } else {
        $username = Security::sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $userModel = new User($pdo);
            $user = $userModel->findByUsername($username);
            
            if ($user && Security::verifyPassword($password, $user['password'])) {
                // Reset rate limit on successful login
                Security::resetRateLimit($identifier);
                
                // Login successful
                Auth::login($user);
                
                // Log activity
                require_once 'models/ActivityLog.php';
                $activityLog = new ActivityLog($pdo);
                $activityLog->logActivity($user['id'], 'LOGIN', 'User logged in');
                
                // Update last login time
                $userModel->updateLastLogin($user['id']);
                
                switch ($user['role']) {
    case 'admin':
        header('Location: /Thesis_Archive_System-main/Thesis%20System/admin/dashboard.php');
        break;

    case 'student':
        header('Location: /Thesis_Archive_System-main/Thesis%20System/student/dashboard.php');
        break;

    case 'adviser':
        header('Location: /Thesis_Archive_System-main/Thesis%20System/adviser/dashboard.php');
        break;

    default:
        header('Location: /Thesis_Archive_System-main/Thesis%20System/login.php');
}
exit();

            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Thesis Management System</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }

        /* LEFT SIDE */
        .auth-info {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-info h1 {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .auth-info p {
            opacity: 0.9;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .auth-info ul {
            list-style: none;
        }

        .auth-info li {
            margin-bottom: 12px;
            font-size: 15px;
        }

        /* RIGHT SIDE */
        .auth-form {
            padding: 50px;
        }

        .auth-form h2 {
            font-size: 26px;
            margin-bottom: 10px;
            color: #222;
        }

        .auth-form p {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1.5px solid #ddd;
            font-size: 15px;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #2a5298;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            background: #2a5298;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #1e3c72;
        }

        .alert {
            background: #ffe5e5;
            color: #b00020;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .auth-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
        }

        .auth-footer a {
            color: #2a5298;
            font-weight: 500;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
            }
            .auth-info {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <!-- LEFT INFO -->
    <div class="auth-info">
        <h1>Thesis Archive System</h1>
        <p>
            Securely manage thesis submissions, approvals, and academic records
            using a centralized system built for students and faculty.
        </p>

        <ul>
            <li>✔ Secure user authentication</li>
            <li>✔ Adviser approval workflow</li>
            <li>✔ Organized thesis archive</li>
            <li>✔ Role-based access</li>
        </ul>
    </div>

    <!-- RIGHT FORM -->
    <div class="auth-form">
        <h2>Account Login</h2>
        <p>Please sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn-login">Login</button>
        </form>

        <div class="auth-footer">
            <p>
                Don’t have an account?
                <a href="register.php">Create one</a>
            </p>
        </div>
    </div>

</div>

</body>
</html>
