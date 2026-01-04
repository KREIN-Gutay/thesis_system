<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Admin Dashboard';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home', 'active' => true],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Fetch real-time statistics from database
// Total Users
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total Theses
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM theses");
$stmt->execute();
$totalTheses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Approved Theses
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM theses WHERE status = 'approved'");
$stmt->execute();
$approvedTheses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending Review Theses
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM theses WHERE status = 'submitted' OR status = 'under_review'");
$stmt->execute();
$pendingReview = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent Activity (last 5 activities with user details)
$stmt = $pdo->prepare("SELECT al.*, u.first_name, u.last_name FROM activity_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");
$stmt->execute();
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>Admin Dashboard</h2>
    <p>Welcome back, <?php echo $_SESSION['username']; ?>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $totalUsers; ?></h4>
            <p>Total Users</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $totalTheses; ?></h4>
            <p>Total Theses</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $approvedTheses; ?></h4>
            <p>Approved</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $pendingReview; ?></h4>
            <p>Pending Review</p>
        </div>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Recent Activity</h3>
    </div>
    <div class="activity-list">
        <?php if (count($recentActivities) > 0): ?>
            <?php foreach ($recentActivities as $activity): ?>
                <div class="activity-item" style="padding: 15px 0; border-bottom: 1px solid #eee;">
                    <p><strong><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></strong> <?php echo htmlspecialchars($activity['action']); ?></p>
                    <small class="text-muted"><?php echo date('M j, Y \\a\\t g:i A', strtotime($activity['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="activity-item" style="padding: 15px 0;">
                <p>No recent activity found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>