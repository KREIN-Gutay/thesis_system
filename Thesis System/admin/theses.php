<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Thesis.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Manage Theses';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book', 'active' => true],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Handle search
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get theses (either all or search results)
$thesisModel = new Thesis($pdo);
if (!empty($searchQuery)) {
    $theses = $thesisModel->searchTheses($searchQuery);
} else {
    $theses = $thesisModel->getAllTheses();
}

ob_start();
?>

<div class="page-header">
    <h2>Manage Theses</h2>
    <p>View and manage all submitted theses.</p>
</div>

<div class="search-form" style="margin-bottom: 25px;">
    <form method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search theses by title, abstract, or keywords..." 
               value="<?php echo htmlspecialchars($searchQuery); ?>"
               style="flex: 1; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
        <button type="submit" class="btn btn-primary" 
                style="padding: 12px 20px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($searchQuery)): ?>
            <a href="theses.php" class="btn btn-outline" 
               style="padding: 12px 20px; border: 2px solid #667eea; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; color: #667eea; text-decoration: none;">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3><?php echo !empty($searchQuery) ? 'Search Results' : 'All Theses'; ?></h3>
        <p><?php echo count($theses); ?> theses found<?php echo !empty($searchQuery) ? ' for "' . htmlspecialchars($searchQuery) . '"' : ''; ?> (Last updated: <?php echo date('M j, Y \\a\\t g:i A'); ?>)</p>
    </div>
    <?php if (count($theses) > 0): ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Title</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Author</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Department</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Year</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Submitted</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($theses as $thesis): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['title']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['author_first_name'] . ' ' . $thesis['author_last_name']); ?></td>
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
                                <?php echo $thesis['submitted_at'] ? date('M j, Y', strtotime($thesis['submitted_at'])) : '-'; ?>
                            </td>
                            <td style="padding: 15px;">
                                <a href="../view-public-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-eye"></i> View
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
            <p style="color: #666;">There are no theses in the system yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>