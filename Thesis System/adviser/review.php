<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Thesis.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

$title = 'Theses to Review';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users'],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book', 'active' => true],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Get theses assigned to this adviser that need review
$thesisModel = new Thesis($pdo);
$theses = $thesisModel->findAll("theses WHERE adviser_id = " . Auth::id() . " AND status IN ('submitted', 'under_review') ORDER BY submitted_at DESC");

ob_start();
?>

<div class="page-header">
    <h2>Theses to Review</h2>
    <p>Review and provide feedback on theses assigned to you.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Pending Reviews</h3>
    </div>
    <?php if (count($theses) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Student</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Title</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Submitted Date</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($theses as $thesis): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;">
                                <?php 
                                // Get student name
                                $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                                $stmt->execute([$thesis['author_id']]);
                                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                                ?>
                            </td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['title']); ?></td>
                            <td style="padding: 15px;">
                                <?php echo $thesis['submitted_at'] ? date('M j, Y', strtotime($thesis['submitted_at'])) : '-'; ?>
                            </td>
                            <td style="padding: 15px;">
                                <?php
                                $statusClass = '';
                                switch ($thesis['status']) {
                                    case 'draft':
                                        $statusClass = 'background: #ff9a9e; color: white;';
                                        break;
                                    case 'submitted':
                                        $statusClass = 'background: #f093fb; color: white;';
                                        break;
                                    case 'under_review':
                                        $statusClass = 'background: #a6c1ee; color: white;';
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
                                    <?php echo ucfirst(str_replace('_', ' ', $thesis['status'])); ?>
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <a href="review-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center;">
            <i class="fas fa-book" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No theses to review</h3>
            <p style="color: #666;">There are currently no theses assigned to you for review.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>