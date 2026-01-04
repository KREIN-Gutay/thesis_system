<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Department.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Edit Department';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building', 'active' => true],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get department ID from URL parameter
$departmentId = $_GET['id'] ?? null;

if (!$departmentId) {
    header('Location: departments.php');
    exit;
}

$departmentModel = new Department($pdo);
$department = $departmentModel->getDepartmentById($departmentId);

if (!$department) {
    header('Location: departments.php');
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description']
    ];
    
    if ($departmentModel->updateDepartment($departmentId, $data)) {
        $message = 'Department updated successfully!';
        // Refresh department data
        $department = $departmentModel->getDepartmentById($departmentId);
    } else {
        $message = 'Error updating department. Please try again.';
    }
}

ob_start();
?>

<div class="page-header">
    <h2>Edit Department</h2>
    <p>Update department information.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-info" style="padding: 15px; border-radius: 10px; margin-bottom: 25px; background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
    <div class="col-md-8" style="flex: 0 0 66.666%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Department Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Department Name</label>
                        <input type="text" id="name" name="name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($department['name']); ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500;">Description</label>
                        <textarea id="description" name="description" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif; min-height: 100px;"><?php echo htmlspecialchars($department['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-save"></i> Update Department
                        </button>
                        <a href="departments.php" class="btn btn-outline" style="padding: 12px 25px; border: 2px solid #667eea; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; color: #667eea; text-decoration: none; margin-left: 10px;">
                            <i class="fas fa-arrow-left"></i> Back to Departments
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Department Details</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Department ID</label>
                    <p style="margin: 0;"><?php echo $department['id']; ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Created</label>
                    <p style="margin: 0;"><?php echo date('F j, Y', strtotime($department['created_at'])); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Last Updated</label>
                    <p style="margin: 0;"><?php echo $department['updated_at'] ? date('F j, Y g:i A', strtotime($department['updated_at'])) : 'Never'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>