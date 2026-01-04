<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/ActivityLog.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Activity Logs';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history', 'active' => true],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get filter parameters
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Build query based on filters
$whereClause = "";
$params = [];

if (!empty($action)) {
    $whereClause = "WHERE al.action = ?";
    $params[] = $action;
}

if ($user_id > 0) {
    $whereClause .= ($whereClause ? " AND" : "WHERE") . " al.user_id = ?";
    $params[] = $user_id;
}

// Get activities
$stmt = $pdo->prepare("
    SELECT al.*, u.username, u.first_name, u.last_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    $whereClause
    ORDER BY al.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get users for filter dropdown
$stmt = $pdo->prepare("SELECT id, username, first_name, last_name FROM users ORDER BY username");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get distinct actions for filter dropdown
$stmt = $pdo->prepare("SELECT DISTINCT action FROM activity_logs ORDER BY action");
$stmt->execute();
$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>Activity Logs</h2>
    <p>View system activity and user actions.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Filter Activities</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <select name="action" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?php echo htmlspecialchars($act['action']); ?>" <?php echo ($action == $act['action']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($act['action']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <select name="user_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($user_id == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username'] . ' (' . $user['first_name'] . ' ' . $user['last_name'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 20px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if (!empty($action) || $user_id > 0): ?>
                    <a href="activity-logs.php" class="btn btn-outline" style="padding: 12px 20px; border: 2px solid #6c757d; border-radius: 10px; color: #6c757d; text-decoration: none; font-family: 'Poppins', sans-serif; font-weight: 500; margin-left: 10px;">
                        Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Recent Activities</h3>
        <p><?php echo count($activities); ?> activities found</p>
    </div>
    <?php if (count($activities) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Date & Time</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">User</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Action</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Description</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></td>
                            <td style="padding: 15px;">
                                <?php if ($activity['username']): ?>
                                    <?php echo htmlspecialchars($activity['username']); ?>
                                    <div style="margin-top: 3px;">
                                        <small style="color: #666;">
                                            <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <em>System</em>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; background: #e9ecef;">
                                    <?php echo htmlspecialchars($activity['action']); ?>
                                </span>
                            </td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($activity['description']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center;">
            <i class="fas fa-history" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No activities found</h3>
            <p style="color: #666;">There are no activities matching your current filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>