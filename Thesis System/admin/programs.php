<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Program.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Manage Programs';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap', 'active' => true],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get all programs
$programModel = new Program($pdo);
$programs = $programModel->getAllPrograms();

ob_start();
?>

<div class="page-header">
    <h2>Manage Programs</h2>
    <p>View and manage academic programs.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>All Programs</h3>
        <p><?php echo count($programs); ?> programs found</p>
    </div>
    <?php if (count($programs) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Name</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Department</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Description</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Created</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $program): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($program['name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($program['department_name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($program['description']); ?></td>
                            <td style="padding: 15px;"><?php echo date('M j, Y', strtotime($program['created_at'])); ?></td>
                            <td style="padding: 15px;">
                                <a href="edit-program.php?id=<?php echo $program['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
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
            <i class="fas fa-graduation-cap" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No programs found</h3>
            <p style="color: #666;">There are no programs in the system yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>