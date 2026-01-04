<?php
require_once 'config/db.php';
require_once 'includes/Auth.php';
require_once 'models/Thesis.php';
require_once 'models/ThesisFile.php';

// Check if user is authenticated
Auth::redirectIfNotAuthenticated('login.php');

// Get thesis ID from URL
$thesisId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$thesisId) {
    header('Location: theses.php');
    exit();
}

// Get thesis details (only approved theses)
$thesisModel = new Thesis($pdo);
$thesis = $thesisModel->getThesisById($thesisId);

// Check if thesis is approved
if (!$thesis || $thesis['status'] != 'approved') {
    header('Location: theses.php');
    exit();
}

$title = 'View Thesis';

// Get thesis files
$fileModel = new ThesisFile($pdo);
$files = $fileModel->getFilesByThesis($thesisId);

// Different sidebar for different roles
$role = Auth::role();
if ($role === 'admin') {
    $sidebar_items = [
        ['title' => 'Dashboard', 'url' => 'admin/dashboard.php', 'icon' => 'fas fa-home'],
        ['title' => 'Users', 'url' => 'admin/users.php', 'icon' => 'fas fa-users'],
        ['title' => 'Departments', 'url' => 'admin/departments.php', 'icon' => 'fas fa-building'],
        ['title' => 'Programs', 'url' => 'admin/programs.php', 'icon' => 'fas fa-graduation-cap'],
        ['title' => 'Theses', 'url' => 'admin/theses.php', 'icon' => 'fas fa-book'],
        ['title' => 'Approvals', 'url' => 'admin/approvals.php', 'icon' => 'fas fa-check-circle'],
        ['title' => 'Reports', 'url' => 'admin/reports.php', 'icon' => 'fas fa-chart-bar'],
    ];
} elseif ($role === 'student') {
    $sidebar_items = [
        ['title' => 'Dashboard', 'url' => 'student/dashboard.php', 'icon' => 'fas fa-home'],
        ['title' => 'My Theses', 'url' => 'student/my-theses.php', 'icon' => 'fas fa-book'],
        ['title' => 'Submit Thesis', 'url' => 'student/submit-thesis.php', 'icon' => 'fas fa-upload'],
        ['title' => 'Thesis Library', 'url' => 'theses.php', 'icon' => 'fas fa-search'],
        ['title' => 'Profile', 'url' => 'student/profile.php', 'icon' => 'fas fa-user'],
    ];
} else { // adviser
    $sidebar_items = [
        ['title' => 'Dashboard', 'url' => 'adviser/dashboard.php', 'icon' => 'fas fa-home'],
        ['title' => 'My Students', 'url' => 'adviser/students.php', 'icon' => 'fas fa-users'],
        ['title' => 'Theses to Review', 'url' => 'adviser/review.php', 'icon' => 'fas fa-book'],
        ['title' => 'Approved Theses', 'url' => 'adviser/approved.php', 'icon' => 'fas fa-check-circle'],
        ['title' => 'Thesis Library', 'url' => 'theses.php', 'icon' => 'fas fa-search'],
        ['title' => 'Profile', 'url' => 'adviser/profile.php', 'icon' => 'fas fa-user'],
    ];
}

ob_start();
?>
<div class="thesis-container">

    <div class="thesis-grid">

        <!-- LEFT: THESIS DETAILS -->
        <div class="card fade-in">
            <div class="card-header">Thesis Details</div>
            <div class="card-body">

                <div class="detail-item">
                    <label>Title</label>
                    <p><?= htmlspecialchars($thesis['title']) ?></p>
                </div>

                <div class="detail-item">
                    <label>Abstract</label>
                    <p><?= nl2br(htmlspecialchars($thesis['abstract'])) ?></p>
                </div>

                <?php if ($thesis['keywords']): ?>
                <div class="detail-item">
                    <label>Keywords</label>
                    <p><?= htmlspecialchars($thesis['keywords']) ?></p>
                </div>
                <?php endif; ?>

                <div class="detail-item">
                    <label>Author</label>
                    <p><?= htmlspecialchars($thesis['author_first_name'].' '.$thesis['author_last_name']) ?></p>
                </div>

                <div class="detail-item">
                    <label>Adviser</label>
                    <p><?= htmlspecialchars($thesis['adviser_first_name'].' '.$thesis['adviser_last_name']) ?></p>
                </div>

                <div class="detail-item">
                    <label>Department</label>
                    <p><?= htmlspecialchars($thesis['department_name']) ?></p>
                </div>

                <div class="detail-item">
                    <label>Program</label>
                    <p><?= htmlspecialchars($thesis['program_name']) ?></p>
                </div>

                <div class="detail-item">
                    <label>Year</label>
                    <p><?= htmlspecialchars($thesis['year']) ?></p>
                </div>

                <div class="detail-item">
                    <label>Approved Date</label>
                    <p><?= date('F j, Y', strtotime($thesis['approved_at'])) ?></p>
                </div>

            </div>
        </div>

        <!-- RIGHT: FILES + REVIEWS -->
        <div>

            <!-- FILES -->
            <div class="card fade-in">
                <div class="card-header">Attached Files</div>
                <div class="card-body">
                    <?php if ($files): ?>
                        <?php foreach ($files as $file): ?>
                            <div class="file-item">
                                <strong><?= htmlspecialchars($file['file_name']) ?></strong><br>
                                <small><?= round($file['file_size']/1024,2) ?> KB</small><br>
                                <a href="<?= htmlspecialchars($file['file_path']) ?>" class="btn btn-outline">
                                    Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No files attached.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REVIEWS -->
            <div class="card fade-in" style="margin-top:20px;">
                <div class="card-header">Reviews & Comments</div>
                <div class="card-body">
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span><?= htmlspecialchars($review['first_name'].' '.$review['last_name']) ?></span>
                                    <small><?= date('M j, Y', strtotime($review['created_at'])) ?></small>
                                </div>
                                <p><?= nl2br(htmlspecialchars($review['comments'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No reviews available.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/shared/layout.php';
?>