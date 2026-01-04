<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

$title = 'My Students';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users', 'active' => true],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Fetch students assigned to this adviser
$stmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.student_id, p.name as program_name, COUNT(t.id) as thesis_count
    FROM users u
    LEFT JOIN programs p ON u.program_id = p.id
    LEFT JOIN theses t ON u.id = t.author_id
    WHERE u.role = 'student' AND t.adviser_id = ?
    GROUP BY u.id, u.first_name, u.last_name, u.student_id, p.name
    ORDER BY u.last_name, u.first_name
");
$stmt->execute([Auth::id()]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>My Students</h2>
    <p>Students assigned to you as their adviser</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Student List</h3>
    </div>
    <div class="table-responsive">
        <?php if (count($students) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th>Theses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['program_name']); ?></td>
                            <td><?php echo $student['thesis_count']; ?></td>
                            <td>
                                <a href="view-student.php?id=<?php echo $student['id']; ?>" class="btn btn-outline" style="padding: 8px 15px;">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 25px; text-align: center; color: #666;">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h4>No Students Assigned</h4>
                <p>You don't have any students assigned to you as their adviser yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>