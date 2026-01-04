<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/User.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Admin Profile';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user', 'active' => true],
];

// Get current user details
$userModel = new User($pdo);
$current_user = $userModel->getUserWithDetails(Auth::id());

ob_start();
?>

<div class="page-header">
    <h2>Admin Profile</h2>
    <p>Manage your profile information and settings.</p>
</div>

<div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
    <div class="col-md-8" style="flex: 0 0 66.666%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Profile Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="username" style="display: block; margin-bottom: 8px; font-weight: 500;">Username</label>
                        <input type="text" id="username" name="username" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($current_user['username']); ?>" readonly>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($current_user['email']); ?>" readonly>
                    </div>
                    
                    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                        <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="first_name" style="display: block; margin-bottom: 8px; font-weight: 500;">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($current_user['first_name']); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="last_name" style="display: block; margin-bottom: 8px; font-weight: 500;">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($current_user['last_name']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card fade-in">
            <div class="card-header">
                <h3>Change Password</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="current_password" style="display: block; margin-bottom: 8px; font-weight: 500;">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="new_password" style="display: block; margin-bottom: 8px; font-weight: 500;">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="confirm_password" style="display: block; margin-bottom: 8px; font-weight: 500;">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-lock"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Profile Picture</h3>
            </div>
            <div class="card-body" style="padding: 25px; text-align: center;">
                <div class="user-avatar" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 36px; margin: 0 auto 20px;">

                    <?php echo substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1); ?>
                </div>
                <p style="margin-bottom: 20px;">Upload a new profile picture</p>
                <button class="btn btn-outline" style="padding: 10px 20px; border: 2px solid #667eea; border-radius: 10px; color: #667eea; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer;">
                    <i class="fas fa-upload"></i> Upload Photo
                </button>
            </div>
        </div>
        
        <div class="card fade-in">
            <div class="card-header">
                <h3>Account Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Role</label>
                    <p style="margin: 0;">
                        <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #e9ecef;">
                            Administrator
                        </span>
                    </p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Member Since</label>
                    <p style="margin: 0;"><?php echo date('F j, Y', strtotime($current_user['created_at'])); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Last Login</label>
                    <p style="margin: 0;"><?php echo $current_user['last_login'] ? date('F j, Y g:i A', strtotime($current_user['last_login'])) : 'Never'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>