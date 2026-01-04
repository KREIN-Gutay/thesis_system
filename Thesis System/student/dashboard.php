<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Thesis.php';

// Check if user is authenticated and is a student
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotStudent('../index.php');

$title = 'Student Dashboard';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home', 'active' => true],
    ['title' => 'My Theses', 'url' => 'my-theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Submit Thesis', 'url' => 'submit-thesis.php', 'icon' => 'fas fa-upload'],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Fetch real-time statistics from database
$thesisModel = new Thesis($pdo);

// My Theses count
$totalTheses = $thesisModel->getStudentThesisCount(Auth::id());

// Approved theses count
$approvedTheses = $thesisModel->getStudentThesisCountByStatus(Auth::id(), 'approved');

// Under review theses count
$underReviewTheses = $thesisModel->getStudentThesisCountByStatus(Auth::id(), 'under_review');

// Draft theses count
$draftTheses = $thesisModel->getStudentThesisCountByStatus(Auth::id(), 'draft');

// Fetch recent theses
$recentTheses = $thesisModel->getRecentStudentTheses(Auth::id(), 5);

ob_start();
?>

</style>

<div class="page-header">
    <h2>Student Dashboard</h2>
    <p>Welcome back, <?php echo $_SESSION['username']; ?>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $totalTheses; ?></h4>
            <p>My Theses</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $approvedTheses; ?></h4>
            <p>Approved</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $underReviewTheses; ?></h4>
            <p>Under Review</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
            <i class="fas fa-edit"></i>
        </div>
        <div class="stat-content">
            <h4><?php echo $draftTheses; ?></h4>
            <p>Draft</p>
        </div>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>My Recent Theses</h3>
    </div>
    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Title</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Submitted Date</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recentTheses) > 0): ?>
                    <?php foreach ($recentTheses as $thesis): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['title']); ?></td>
                            <td style="padding: 15px;">
                                <?php
                                $statusClass = '';
                                switch ($thesis['status']) {
                                    case 'approved':
                                        $statusClass = '#43e97b';
                                        $statusText = 'Approved';
                                        break;
                                    case 'under_review':
                                        $statusClass = '#f093fb';
                                        $statusText = 'Under Review';
                                        break;
                                    case 'draft':
                                        $statusClass = '#ff9a9e';
                                        $statusText = 'Draft';
                                        break;
                                    case 'rejected':
                                        $statusClass = '#ff6b6b';
                                        $statusText = 'Rejected';
                                        break;
                                    default:
                                        $statusClass = '#6c757d';
                                        $statusText = ucfirst($thesis['status']);
                                }
                                ?>
                                <span class="badge" style="background: <?php echo $statusClass; ?>; color: white; padding: 5px 10px; border-radius: 20px;">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <?php 
                                if ($thesis['submitted_at'] && $thesis['status'] !== 'draft') {
                                    echo date('M j, Y', strtotime($thesis['submitted_at']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td style="padding: 15px;">
                                <?php if ($thesis['status'] === 'draft'): ?>
                                    <a href="edit-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #ffc107; border-radius: 8px; color: #ffc107; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-right: 5px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php endif; ?>
                                <a href="view-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; <?php echo ($thesis['status'] === 'draft') ? '' : 'margin-right: 5px;'; ?>">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="my-theses.php?action=delete&id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #dc3545; border-radius: 8px; color: #dc3545; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;" onclick="return confirm('Are you sure you want to delete this thesis? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #666; padding: 20px;">
                            No theses found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
.form-card {
    max-width: 900px;
    margin: auto;
}

.form-section {
    margin-bottom: 25px;
}

.form-section label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

.form-control {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1.8px solid #e1e5e9;
    font-family: 'Poppins', sans-serif;
    transition: 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,.15);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 26px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-outline {
    border: 2px solid #667eea;
    color: #667eea;
    padding: 12px 26px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
}

.file-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>