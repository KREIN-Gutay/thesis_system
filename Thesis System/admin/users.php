<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/User.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Manage Users';

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

// Get all users
$userModel = new User($pdo);
$users = $userModel->getAllUsers();

ob_start();
?>

<div class="page-header">
    <h2>Manage Users</h2>
    <p>View and manage all system users.</p>
</div>

<div style="margin-bottom: 20px; text-align: right;">
    <a href="#" class="btn btn-primary" style="padding: 10px 20px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-plus"></i> Create User
    </a>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>All Users</h3>
        <p><?php echo count($users); ?> users found</p>
    </div>
    <?php if (count($users) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Username</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Name</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Email</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Role</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Program</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td style="padding: 15px;">
                                <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #e9ecef;">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($user['program_name'] ?? 'N/A'); ?></td>
                            <td style="padding: 15px;">
                                <?php if ($user['is_active']): ?>
                                    <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #43e97b; color: white;">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #ff6b6b; color: white;">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-right: 5px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <!-- <a href="#" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #28a745; border-radius: 8px; color: #28a745; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-eye"></i> View
                                </a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center;">
            <i class="fas fa-users" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No users found</h3>
            <p style="color: #666;">There are no users in the system yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>