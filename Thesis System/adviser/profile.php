<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/User.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

$title = 'Adviser Profile';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users'],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user', 'active' => true],
];

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User($pdo);
    
    // Handle profile update
    if (isset($_POST['first_name']) && !isset($_POST['current_password'])) {
        $data = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name']
        ];
        
        if ($userModel->updateUser(Auth::id(), $data)) {
            $message = 'Profile updated successfully!';
            // Refresh user data
            $current_user = $userModel->getUserWithDetails(Auth::id());
        } else {
            $message = 'Error updating profile. Please try again.';
        }
    }
    
    // Handle password change
    if (isset($_POST['current_password']) && !empty($_POST['current_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        $currentUserData = $userModel->getUserById(Auth::id());
        
        if (password_verify($currentPassword, $currentUserData['password'])) {
            if ($newPassword === $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    if ($userModel->updatePassword(Auth::id(), $newPassword)) {
                        $message = 'Password changed successfully!';
                    } else {
                        $message = 'Error changing password. Please try again.';
                    }
                } else {
                    $message = 'New password must be at least 6 characters long.';
                }
            } else {
                $message = 'New password and confirmation do not match.';
            }
        } else {
            $message = 'Current password is incorrect.';
        }
    }
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $uploadDir = '../uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = 'profile_' . Auth::id() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                if ($userModel->updateProfilePicture(Auth::id(), $filePath)) {
                    $message = 'Profile picture updated successfully!';
                    // Refresh user data
                    $current_user = $userModel->getUserWithDetails(Auth::id());
                } else {
                    $message = 'Error saving profile picture to database.';
                }
            } else {
                $message = 'Error uploading profile picture.';
            }
        } else {
            $message = 'Invalid file type for profile picture. Only JPG, JPEG, PNG, and GIF are allowed.';
        }
    }
    
    // Handle signature upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] == 0) {
        $uploadDir = '../uploads/signatures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = 'signature_' . Auth::id() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['signature']['tmp_name'], $filePath)) {
                if ($userModel->updateSignature(Auth::id(), $filePath)) {
                    $message = 'Signature updated successfully!';
                    // Refresh user data
                    $current_user = $userModel->getUserWithDetails(Auth::id());
                } else {
                    $message = 'Error saving signature to database.';
                }
            } else {
                $message = 'Error uploading signature.';
            }
        } else {
            $message = 'Invalid file type for signature. Only JPG, JPEG, PNG, and GIF are allowed.';
        }
    }
}

// Get current user details
$userModel = new User($pdo);
$current_user = $userModel->getUserWithDetails(Auth::id());

ob_start();
?>

<div class="page-header">
    <h2>Adviser Profile</h2>
    <p>Manage your profile information and settings.</p>
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
                <h3>Profile Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST" enctype="multipart/form-data">
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
                <form method="POST" enctype="multipart/form-data">
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
                <form method="POST" enctype="multipart/form-data">
                    <div class="user-avatar" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 36px; margin: 0 auto 20px;">
                        <?php if (!empty($current_user['profile_picture']) && file_exists($current_user['profile_picture'])): ?>
                            <img src="<?php echo $current_user['profile_picture']; ?>" alt="Profile Picture" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <?php echo substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1); ?>
                        <?php endif; ?>
                    </div>
                    <p style="margin-bottom: 20px;">Upload a new profile picture</p>
                    <input type="file" name="profile_picture" accept="image/*" style="margin-bottom: 15px;">
                    <button type="submit" class="btn btn-outline" style="padding: 10px 20px; border: 2px solid #667eea; border-radius: 10px; color: #667eea; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer;">
                        <i class="fas fa-upload"></i> Upload Photo
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card fade-in">
            <div class="card-header">
                <h3>Signature</h3>
            </div>
            <div class="card-body" style="padding: 25px; text-align: center;">
                <form method="POST" enctype="multipart/form-data">
                    <div class="signature-preview" style="width: 200px; height: 100px; border: 2px dashed #ccc; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                        <?php if (!empty($current_user['signature']) && file_exists($current_user['signature'])): ?>
                            <img src="<?php echo $current_user['signature']; ?>" alt="Signature" style="max-width: 100%; max-height: 100%;">
                        <?php else: ?>
                            <span style="color: #666;">No signature uploaded</span>
                        <?php endif; ?>
                    </div>
                    <p style="margin-bottom: 20px;">Upload your digital signature</p>
                    <input type="file" name="signature" accept="image/*" style="margin-bottom: 15px;">
                    <button type="submit" class="btn btn-outline" style="padding: 10px 20px; border: 2px solid #667eea; border-radius: 10px; color: #667eea; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer;">
                        <i class="fas fa-file-signature"></i> Upload Signature
                    </button>
                </form>
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
                            Adviser
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