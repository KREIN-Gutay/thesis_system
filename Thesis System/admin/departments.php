<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Department.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Manage Departments';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building', 'active' => true],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get all departments
$departmentModel = new Department($pdo);
$departments = $departmentModel->getAllDepartments();

ob_start();
?>

<div class="page-header">
    <h2>Manage Departments</h2>
    <p>View and manage academic departments.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>All Departments</h3>
        <p><?php echo count($departments); ?> departments found</p>
    </div>
    <?php if (count($departments) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Name</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Description</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Created</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($dept['description']); ?></td>
                            <td style="padding: 15px;"><?php echo date('M j, Y', strtotime($dept['created_at'])); ?></td>
                            <td style="padding: 15px;">
                                <a href="edit-department.php?id=<?php echo $dept['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center;">
            <i class="fas fa-building" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No departments found</h3>
            <p style="color: #666;">There are no departments in the system yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>