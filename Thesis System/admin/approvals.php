<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Manage Approvals';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle', 'active' => true],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get all approvals
$stmt = $pdo->prepare("
    SELECT a.*, t.title as thesis_title, u.first_name, u.last_name, adv.first_name as adviser_first, adv.last_name as adviser_last
    FROM approvals a
    JOIN theses t ON a.thesis_id = t.id
    JOIN users u ON t.author_id = u.id
    JOIN users adv ON a.approver_id = adv.id
    ORDER BY a.created_at DESC
");
$stmt->execute();
$approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>Manage Approvals</h2>
    <p>View and manage thesis approvals.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>All Approvals</h3>
        <p><?php echo count($approvals); ?> approvals found (Last updated: <?php echo date('M j, Y \\a\\t g:i A'); ?>)</p>
    </div>
    <?php if (count($approvals) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Thesis Title</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Author</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Adviser</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Comments</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvals as $approval): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($approval['thesis_title']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($approval['first_name'] . ' ' . $approval['last_name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($approval['adviser_first'] . ' ' . $approval['adviser_last']); ?></td>
                            <td style="padding: 15px;">
                                <?php
                                $statusClass = '';
                                switch ($approval['status']) {
                                    case 'pending':
                                        $statusClass = 'background: #f093fb; color: white;';
                                        break;
                                    case 'approved':
                                        $statusClass = 'background: #43e97b; color: white;';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'background: #ff6b6b; color: white;';
                                        break;
                                }
                                ?>
                                <span class="badge" style="padding: 5px 10px; border-radius: 20px; font-size: 12px; <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($approval['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($approval['comments'] ?? 'N/A'); ?></td>
                            <td style="padding: 15px;"><?php echo date('M j, Y', strtotime($approval['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center;">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No approvals found</h3>
            <p style="color: #666;">There are no approvals in the system yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>