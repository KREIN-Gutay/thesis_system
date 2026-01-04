<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

$title = 'Approved Theses';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users'],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle', 'active' => true],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Fetch approved theses for this adviser
$stmt = $pdo->prepare("
    SELECT t.*, u.first_name, u.last_name, u.student_id, p.name as program_name
    FROM theses t
    JOIN users u ON t.author_id = u.id
    JOIN programs p ON t.program_id = p.id
    WHERE t.adviser_id = ? AND t.status = 'approved'
    ORDER BY t.approved_at DESC
");
$stmt->execute([Auth::id()]);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>Approved Theses</h2>
    <p>Theses you have approved as an adviser</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Approved Theses List</h3>
    </div>
    <div class="table-responsive">
        <?php if (count($theses) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Title</th>
                        <th>Program</th>
                        <th>Approved Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($theses as $thesis): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']); ?>
                                <small>(<?php echo htmlspecialchars($thesis['student_id']); ?>)</small>
                            </td>
                            <td><?php echo htmlspecialchars($thesis['title']); ?></td>
                            <td><?php echo htmlspecialchars($thesis['program_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($thesis['approved_at'])); ?></td>
                            <td>
                                <a href="review-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 25px; text-align: center; color: #666;">
                <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h4>No Approved Theses</h4>
                <p>You haven't approved any theses yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>