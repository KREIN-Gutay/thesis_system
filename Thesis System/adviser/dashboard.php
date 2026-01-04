<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

$title = 'Adviser Dashboard';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home', 'active' => true],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users'],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Fetch real-time statistics from database
// My Students
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT u.id) as total FROM users u JOIN theses t ON u.id = t.author_id WHERE u.role = 'student' AND t.adviser_id = ?");
$stmt->execute([Auth::id()]);
$myStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Theses to Review
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM theses WHERE adviser_id = ? AND status IN ('submitted', 'under_review')");
$stmt->execute([Auth::id()]);
$thesesToReview = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Approved Theses
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM theses WHERE adviser_id = ? AND status = 'approved'");
$stmt->execute([Auth::id()]);
$approvedTheses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending Comments
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM review_logs rl JOIN theses t ON rl.thesis_id = t.id WHERE t.adviser_id = ? AND rl.reviewer_id = ?");
$stmt->execute([Auth::id(), Auth::id()]);
$pendingComments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

ob_start();
?>

<div class="page-header">
    <h2>Adviser Dashboard</h2>
    <p>Welcome back, <?php echo $_SESSION['username']; ?>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $myStudents; ?></h4>
            <p>My Students</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $thesesToReview; ?></h4>
            <p>Theses to Review</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $approvedTheses; ?></h4>
            <p>Approved Theses</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $pendingComments; ?></h4>
            <p>Pending Comments</p>
        </div>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Theses Needing Review</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Title</th>
                    <th>Submitted Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch recent theses to review
                $stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name FROM theses t JOIN users u ON t.author_id = u.id WHERE t.adviser_id = ? AND t.status IN ('submitted', 'under_review') ORDER BY t.submitted_at DESC LIMIT 5");
                $stmt->execute([Auth::id()]);
                $recentTheses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($recentTheses) > 0):
                    foreach ($recentTheses as $thesis):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($thesis['title']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($thesis['submitted_at'])); ?></td>
                    <td>
                        <a href="review-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px;">
                            <i class="fas fa-eye"></i> Review
                        </a>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">
                        No theses needing review at this time.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>