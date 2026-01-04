<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Thesis.php';
require_once '../models/ThesisFile.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

// Get thesis ID from URL
$thesisId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$thesisId) {
    header('Location: review.php');
    exit();
}

// Get thesis details
$thesisModel = new Thesis($pdo);
$thesis = $thesisModel->getThesisById($thesisId);

// Check if thesis is assigned to current adviser
if (!$thesis || $thesis['adviser_id'] != Auth::id()) {
    header('Location: review.php');
    exit();
}

$title = 'Review Thesis';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users'],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book', 'active' => true],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Get thesis files
$fileModel = new ThesisFile($pdo);
$files = $fileModel->getFilesByThesis($thesisId);

// Handle form submission for review
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/Security.php';
    
    $comments = Security::sanitizeInput($_POST['comments']);
    $action = Security::sanitizeInput($_POST['action']); // 'approve' or 'reject'
    
    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        $message = 'Invalid action.';
        $messageType = 'danger';
    }
    
    try {
        // Update thesis status
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
        $approvedAt = ($action === 'approve') ? 'NOW()' : 'NULL';
        
        $stmt = $pdo->prepare("UPDATE theses SET status = ?, approved_at = " . (($action === 'approve') ? 'NOW()' : 'NULL') . " WHERE id = ?");
        $stmt->execute([$newStatus, $thesisId]);
        
        // Add review log
        $stmt = $pdo->prepare("INSERT INTO review_logs (thesis_id, reviewer_id, comments) VALUES (?, ?, ?)");
        $stmt->execute([$thesisId, Auth::id(), $comments]);
        
        // Log activity
        require_once '../models/ActivityLog.php';
        $activityLog = new ActivityLog($pdo);
        $activityLog->logActivity(Auth::id(), 'THESIS_REVIEW', ucfirst($action) . ' thesis: ' . $thesis['title']);
        
        $message = "Thesis {$action}ed successfully!";
        $messageType = 'success';
        
        // Refresh thesis data
        $thesis = $thesisModel->getThesisById($thesisId);
    } catch (Exception $e) {
        $message = 'Error processing review: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

ob_start();
?>

<div class="page-header">
    <h2>Review Thesis</h2>
    <p>Review and provide feedback on "<?php echo htmlspecialchars($thesis['title']); ?>"</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; border-radius: 10px; margin-bottom: 25px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
    <div class="col-md-8" style="flex: 0 0 66.666%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Thesis Details</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Title:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($thesis['title']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Abstract:</label>
                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($thesis['abstract'])); ?></p>
                </div>
                
                <?php if (!empty($thesis['keywords'])): ?>
                    <div class="detail-row" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Keywords:</label>
                        <p style="margin: 0;"><?php echo htmlspecialchars($thesis['keywords']); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Student:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($thesis['author_first_name'] . ' ' . $thesis['author_last_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Department:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($thesis['department_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Program:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($thesis['program_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Year:</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($thesis['year']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Status:</label>
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
                    <span class="badge" style="padding: 5px 15px; border-radius: 20px; font-size: 14px; <?php echo $statusClass; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $thesis['status'])); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Attached Files</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <?php if (count($files) > 0): ?>
                    <?php foreach ($files as $file): ?>
                        <div class="file-item" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-file-pdf" style="font-size: 24px; color: #dc3545;"></i>
                                <div>
                                    <p style="margin: 0; font-weight: 500;"><?php echo htmlspecialchars($file['file_name']); ?></p>
                                    <small style="color: #666;">
                                        <?php echo round($file['file_size'] / 1024, 2); ?> KB
                                    </small>
                                </div>
                            </div>
                            <div style="margin-top: 10px;">
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No files attached to this thesis.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Review & Feedback</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="comments" style="display: block; margin-bottom: 8px; font-weight: 500;">Comments *</label>
                <textarea id="comments" name="comments" rows="6" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" placeholder="Provide your feedback and comments here..." required></textarea>
            </div>
            
            <div class="form-group" style="display: flex; gap: 15px;">
                <button type="submit" name="action" value="approve" class="btn btn-success" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                    <i class="fas fa-check"></i> Approve Thesis
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%); color: white;">
                    <i class="fas fa-times"></i> Reject Thesis
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>