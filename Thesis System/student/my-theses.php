<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Thesis.php';

// Check if user is authenticated and is a student
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotStudent('../index.php');

$title = 'My Theses';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Theses', 'url' => 'my-theses.php', 'icon' => 'fas fa-book', 'active' => true],
    ['title' => 'Submit Thesis', 'url' => 'submit-thesis.php', 'icon' => 'fas fa-upload'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Handle delete action
$message = '';
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $thesisId = $_GET['id'];
    $thesisModel = new Thesis($pdo);
    
    // Verify the thesis belongs to the current user before deleting
    $thesis = $thesisModel->getThesisById($thesisId);
    if ($thesis && $thesis['author_id'] == Auth::id()) {
        if ($thesisModel->deleteThesis($thesisId, Auth::id())) {
            $message = 'Thesis deleted successfully!';
        } else {
            $message = 'Error deleting thesis. Please try again.';
        }
    } else {
        $message = 'You do not have permission to delete this thesis.';
    }
}

// Get student's theses
$thesisModel = new Thesis($pdo);
$theses = $thesisModel->getThesesByAuthor(Auth::id());

ob_start();
?>

<div class="page-header">
    <h2>My Theses</h2>
    <p>View and manage your submitted theses.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-info" style="padding: 15px; border-radius: 10px; margin-bottom: 25px; background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card fade-in">
    <div class="card-header">
        <h3>Thesis Submissions</h3>
    </div>
    <?php if (count($theses) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Title</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Year</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Submitted Date</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($theses as $thesis): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['title']); ?></td>
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
                                <?php echo $thesis['submitted_at'] ? date('M j, Y', strtotime($thesis['submitted_at'])) : '-'; ?>
                            </td>
                            <td style="padding: 15px;">
                                <a href="view-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-right: 5px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if ($thesis['status'] === 'draft'): ?>
                                    <a href="edit-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #ffc107; border-radius: 8px; color: #ffc107; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-right: 5px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #dc3545; border-radius: 8px; color: #dc3545; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;" onclick="return confirm('Are you sure you want to delete this thesis? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete
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
            <p style="color: #666; margin-bottom: 25px;">You haven't submitted any theses yet.</p>
            <a href="submit-thesis.php" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Submit Your First Thesis
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>