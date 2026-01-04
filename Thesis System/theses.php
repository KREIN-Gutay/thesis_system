<?php
require_once 'config/db.php';
require_once 'includes/Auth.php';
require_once 'models/Thesis.php';
require_once 'models/Department.php';
require_once 'models/Program.php';

// Check if user is authenticated
Auth::redirectIfNotAuthenticated('login.php');

$title = 'Thesis Library';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get departments and programs for filters
$departmentModel = new Department($pdo);
$programModel = new Program($pdo);

$departments = $departmentModel->getAllDepartments();
$programs = $programModel->getAllPrograms();

// Build query based on filters
$whereClause = "WHERE t.status = 'approved'";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (t.title LIKE ? OR t.abstract LIKE ? OR t.keywords LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($department_id > 0) {
    $whereClause .= " AND t.department_id = ?";
    $params[] = $department_id;
}

if ($program_id > 0) {
    $whereClause .= " AND t.program_id = ?";
    $params[] = $program_id;
}

if ($year > 0) {
    $whereClause .= " AND t.year = ?";
    $params[] = $year;
}

// Get filtered theses
$stmt = $pdo->prepare("
    SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
           adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
           d.name as department_name, p.name as program_name
    FROM theses t
    LEFT JOIN users u ON t.author_id = u.id
    LEFT JOIN users adv ON t.adviser_id = adv.id
    LEFT JOIN departments d ON t.department_id = d.id
    LEFT JOIN programs p ON t.program_id = p.id
    $whereClause
    ORDER BY t.created_at DESC
");
$stmt->execute($params);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="page-header">
    <h2>Thesis Library</h2>
    <p>Browse and search approved theses.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Search & Filters</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" placeholder="Search by title, abstract, or keywords">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <select name="department_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo ($department_id == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <select name="program_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="">All Programs</option>
                    <?php foreach ($programs as $prog): ?>
                        <option value="<?php echo $prog['id']; ?>" <?php echo ($program_id == $prog['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prog['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 120px;">
                <select name="year" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="">All Years</option>
                    <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 20px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if (!empty($search) || $department_id > 0 || $program_id > 0 || $year > 0): ?>
                    <a href="theses.php" class="btn btn-outline" style="padding: 12px 20px; border: 2px solid #6c757d; border-radius: 10px; color: #6c757d; text-decoration: none; font-family: 'Poppins', sans-serif; font-weight: 500; margin-left: 10px;">
                        Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Thesis Results</h3>
        <p><?php echo count($theses); ?> theses found</p>
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
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($theses as $thesis): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;">
                                <strong><?php echo htmlspecialchars($thesis['title']); ?></strong>
                                <div style="margin-top: 5px;">
                                    <small style="color: #666;">
                                        Adviser: <?php echo htmlspecialchars($thesis['adviser_first_name'] . ' ' . $thesis['adviser_last_name']); ?>
                                    </small>
                                </div>
                            </td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['author_first_name'] . ' ' . $thesis['author_last_name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['department_name']); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($thesis['year']); ?></td>
                            <td style="padding: 15px;">
                                <a href="view-public-thesis.php?id=<?php echo $thesis['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; border: 2px solid #667eea; border-radius: 8px; color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
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
            <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">No theses found</h3>
            <p style="color: #666;">Try adjusting your search criteria or filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

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
        ['title' => 'Thesis Library', 'url' => 'theses.php', 'icon' => 'fas fa-search', 'active' => true],
        ['title' => 'Profile', 'url' => 'student/profile.php', 'icon' => 'fas fa-user'],
    ];
} else { // adviser
    $sidebar_items = [
        ['title' => 'Dashboard', 'url' => 'adviser/dashboard.php', 'icon' => 'fas fa-home'],
        ['title' => 'My Students', 'url' => 'adviser/students.php', 'icon' => 'fas fa-users'],
        ['title' => 'Theses to Review', 'url' => 'adviser/review.php', 'icon' => 'fas fa-book'],
        ['title' => 'Approved Theses', 'url' => 'adviser/approved.php', 'icon' => 'fas fa-check-circle'],
        ['title' => 'Thesis Library', 'url' => 'theses.php', 'icon' => 'fas fa-search', 'active' => true],
        ['title' => 'Profile', 'url' => 'adviser/profile.php', 'icon' => 'fas fa-user'],
    ];
}

include 'views/shared/layout.php';
?>