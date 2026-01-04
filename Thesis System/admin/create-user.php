<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../includes/Security.php';
require_once '../models/User.php';

Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Security::sanitizeInput($_POST['username'] ?? '');
    $email    = Security::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } else {
        $userModel = new User($pdo);

        if ($userModel->findByUsername($username)) {
            $error = 'Username already exists.';
        } elseif ($userModel->findByEmail($email)) {
            $error = 'Email already exists.';
        } else {
            $data = [
                'username'    => $username,
                'email'       => $email,
                'password'    => Security::hashPassword($password),
                'role'        => $role,
                'first_name'  => '',
                'last_name'   => '',
                'middle_name' => null,
                'student_id'  => null,
                'program_id'  => null
            ];

            $userModel->createUser($data);
            $success = ucfirst($role) . ' account created successfully.';
        }
    }
}

ob_start();
?>

<div class="page-header">
    <h2>Create User</h2>
    <p>Create a student, adviser, or admin account.</p>
</div>

<div class="card fade-in" style="max-width: 600px;">
    <div class="card-body">
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="adviser">Adviser</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div style="margin-top:20px;">
                <button class="btn btn-primary">Create User</button>
                <a href="users.php" class="btn btn-outline" style="margin-left:10px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
