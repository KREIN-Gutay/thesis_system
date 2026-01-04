<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

// Get student ID from URL
$studentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$studentId) {
    header('Location: students.php');
    exit();
}

// Verify that this student is assigned to the current adviser
$stmt = $pdo->prepare("
    SELECT u.*, p.name as program_name, d.name as department_name, COUNT(t.id) as thesis_count
    FROM users u
    LEFT JOIN programs p ON u.program_id = p.id
    LEFT JOIN departments d ON p.department_id = d.id
    LEFT JOIN theses t ON u.id = t.author_id
    WHERE u.id = ? AND u.role = 'student' AND t.adviser_id = ?
    GROUP BY u.id, p.name, d.name
");
$stmt->execute([$studentId, Auth::id()]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: students.php');
    exit();
}

$title = 'Student Details';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users', 'active' => true],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Get student's theses
$stmt = $pdo->prepare("
    SELECT t.*, d.name as department_name, p.name as program_name
    FROM theses t
    LEFT JOIN departments d ON t.department_id = d.id
    LEFT JOIN programs p ON t.program_id = p.id
    WHERE t.author_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$studentId]);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>Student Details</h2>
    <p>View details for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
</div>

<div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
    <div class="col-md-8" style="flex: 0 0 66.666%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Student Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Student ID:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($student['student_id']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Name:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Email:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Program:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($student['program_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Department:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($student['department_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Theses Submitted:</label>
                    <p style="margin: 0;"><?php echo $student['thesis_count']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="card fade-in">
            <div class="card-header">
                <h3>Student's Theses</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (count($theses) > 0): ?>
                    <div class="table-responsive">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                                    <th style="padding: 15px; text-align: left; font-weight: 600;">Title</th>
                                    <th style="padding: 15px; text-align: left; font-weight: 600;">Department</th>
                                    <th style="padding: 15px; text-align: left; font-weight: 600;">Year</th>
                                    <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                                    <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($theses as $thesis): ?>
                                    <tr style="border-bottom: 1px solid #e9ecef;">
                                        <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['title']); ?></td>
                                        <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['department_name']); ?></td>
                                        <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['year']); ?></td>
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
                        <h3 style="margin-bottom: 15px;">No theses found</h3>
                        <p style="color: #666;">This student hasn't submitted any theses yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Actions</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <a href="students.php" class="btn btn-primary" style="display: block; width: 100%; padding: 12px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; text-decoration: none; margin-bottom: 15px;">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </a>
                
                <a href="review.php" class="btn btn-outline" style="display: block; width: 100%; padding: 12px; text-align: center; border: 2px solid #667eea; color: #667eea; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; text-decoration: none; margin-bottom: 15px;">
                    <i class="fas fa-book"></i> Review Theses
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>