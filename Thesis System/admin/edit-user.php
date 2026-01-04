<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/User.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Edit User';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users', 'active' => true],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get user ID from URL parameter
$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: users.php');
    exit;
}

$userModel = new User($pdo);
$user = $userModel->getUserWithDetails($userId);

if (!$user) {
    header('Location: users.php');
    exit;
}

$programs = $userModel->getAllPrograms();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'username' => $_POST['username'],
        'role' => $_POST['role'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Only update program if provided and user is a student
    if (!empty($_POST['program_id']) && $_POST['role'] === 'student') {
        $data['program_id'] = $_POST['program_id'];
    } elseif ($_POST['role'] !== 'student') {
        $data['program_id'] = null;
    }
    
    if ($userModel->updateUserFull($userId, $data)) {
        $message = 'User updated successfully!';
        // Refresh user data
        $user = $userModel->getUserWithDetails($userId);
    } else {
        $message = 'Error updating user. Please try again.';
    }
}

ob_start();
?>

<div class="page-header">
    <h2>Edit User</h2>
    <p>Update user information and settings.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-info" style="padding: 15px; border-radius: 10px; margin-bottom: 25px; background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
    <div class="col-md-8" style="flex: 0 0 66.666%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>User Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="username" style="display: block; margin-bottom: 8px; font-weight: 500;">Username</label>
                        <input type="text" id="username" name="username" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                        <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="first_name" style="display: block; margin-bottom: 8px; font-weight: 500;">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="last_name" style="display: block; margin-bottom: 8px; font-weight: 500;">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="role" style="display: block; margin-bottom: 8px; font-weight: 500;">Role</label>
                        <select id="role" name="role" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required>
                            <option value="student" <?php echo ($user['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="adviser" <?php echo ($user['role'] === 'adviser') ? 'selected' : ''; ?>>Adviser</option>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="program_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Program</label>
                        <select id="program_id" name="program_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                            <option value="">Select Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['id']; ?>" <?php echo ($user['program_id'] == $program['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($program['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666; display: block; margin-top: 5px;">Only applicable for students</small>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">
                            <input type="checkbox" name="is_active" value="1" <?php echo ($user['is_active']) ? 'checked' : ''; ?>> Active User
                        </label>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        <a href="users.php" class="btn btn-outline" style="padding: 12px 25px; border: 2px solid #667eea; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; color: #667eea; text-decoration: none; margin-left: 10px;">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>User Details</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">User ID</label>
                    <p style="margin: 0;"><?php echo $user['id']; ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Role</label>
                    <p style="margin: 0;">
                        <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #e9ecef;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Member Since</label>
                    <p style="margin: 0;"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Last Login</label>
                    <p style="margin: 0;"><?php echo $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Status</label>
                    <p style="margin: 0;">
                        <?php if ($user['is_active']): ?>
                            <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #43e97b; color: white;">
                                Active
                            </span>
                        <?php else: ?>
                            <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #ff6b6b; color: white;">
                                Inactive
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const programSelect = document.getElementById('program_id');
    
    function toggleProgramField() {
        if (roleSelect.value === 'student') {
            programSelect.disabled = false;
            programSelect.parentElement.querySelector('small').style.display = 'block';
        } else {
            programSelect.disabled = true;
            programSelect.parentElement.querySelector('small').style.display = 'none';
        }
    }
    
    roleSelect.addEventListener('change', toggleProgramField);
    toggleProgramField(); // Set initial state
});
</script>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>