<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Thesis.php';
require_once '../models/ThesisFile.php';

// Check if user is authenticated and is a student
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotStudent('../index.php');

// Get thesis ID from URL
$thesisId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$thesisId) {
    header('Location: my-theses.php');
    exit();
}

// Get thesis details
$thesisModel = new Thesis($pdo);
$thesis = $thesisModel->getThesisById($thesisId);

// Check if thesis belongs to current user
if (!$thesis || $thesis['author_id'] != Auth::id()) {
    header('Location: my-theses.php');
    exit();
}

$title = 'View Thesis';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Theses', 'url' => 'my-theses.php', 'icon' => 'fas fa-book', 'active' => true],
    ['title' => 'Submit Thesis', 'url' => 'submit-thesis.php', 'icon' => 'fas fa-upload'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Get thesis files
$fileModel = new ThesisFile($pdo);
$files = $fileModel->getFilesByThesis($thesisId);

ob_start();
?>

<div class="page-header">
    <h2><?php echo htmlspecialchars($thesis['title']); ?></h2>
    <p>View details of your thesis submission.</p>
</div>

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
                
                <?php if ($thesis['submitted_at']): ?>
                    <div class="detail-row" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Submitted Date:</label>
                        <p style="margin: 0;"><?php echo date('F j, Y \a\t g:i A', strtotime($thesis['submitted_at'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($thesis['approved_at']): ?>
                    <div class="detail-row" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Approved Date:</label>
                        <p style="margin: 0;"><?php echo date('F j, Y \a\t g:i A', strtotime($thesis['approved_at'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Adviser Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <?php if ($thesis['adviser_id']): ?>
                    <div class="detail-row" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Name:</label>
                        <p style="margin: 0;"><?php echo htmlspecialchars($thesis['adviser_first_name'] . ' ' . $thesis['adviser_last_name']); ?></p>
                    </div>
                <?php else: ?>
                    <p>No adviser assigned yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
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
        
        <div class="card fade-in">
            <div class="card-header">
                <h3>Adviser Feedback</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <?php
                // Get review logs
                $stmt = $pdo->prepare("SELECT rl.*, u.first_name, u.last_name FROM review_logs rl JOIN users u ON rl.reviewer_id = u.id WHERE rl.thesis_id = ? ORDER BY rl.created_at DESC");
                $stmt->execute([$thesisId]);
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                <small style="color: #666;"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                            <p style="margin: 0; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($review['comments'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No feedback available for this thesis yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<style>
.page-header h2 {
    color: #0f172a;
    font-weight: 700;
}

.page-header p {
    color: #475569;
}

.thesis-wrapper {
    max-width: 1200px;
    margin: auto;
}

.thesis-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #2563eb, #0d9488);
    color: #fff;
    padding: 16px 20px;
    font-weight: 600;
}

.card-body {
    padding: 22px;
}

.detail {
    margin-bottom: 16px;
}

.detail label {
    font-weight: 600;
    color: #334155;
    display: block;
    margin-bottom: 4px;
}

.detail p {
    margin: 0;
    color: #0f172a;
    line-height: 1.6;
}

.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
}

.status-draft { background:#e2e8f0; color:#334155; }
.status-submitted { background:#fde68a; color:#92400e; }
.status-under_review { background:#bae6fd; color:#075985; }
.status-approved { background:#bbf7d0; color:#065f46; }
.status-rejected { background:#fecaca; color:#7f1d1d; }

.file-card {
    background: #f1f5f9;
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 12px;
}

.file-card a {
    margin-top: 8px;
    display: inline-block;
    color: #2563eb;
    font-weight: 500;
}

.review {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 14px;
    margin-bottom: 14px;
}

.review:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    color: #0f172a;
}

@media (max-width: 900px) {
    .thesis-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>