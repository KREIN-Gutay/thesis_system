<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Program.php';
require_once '../models/Department.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Edit Program';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap', 'active' => true],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
];

// Get program ID from URL parameter
$programId = $_GET['id'] ?? null;

if (!$programId) {
    header('Location: programs.php');
    exit;
}

$programModel = new Program($pdo);
$program = $programModel->getProgramById($programId);

if (!$program) {
    header('Location: programs.php');
    exit;
}

// Get all departments for dropdown
$departmentModel = new Department($pdo);
$departments = $departmentModel->getAllDepartments();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'department_id' => $_POST['department_id'],
        'name' => $_POST['name'],
        'description' => $_POST['description']
    ];
    
    if ($programModel->updateProgram($programId, $data)) {
        $message = 'Program updated successfully!';
        // Refresh program data
        $program = $programModel->getProgramById($programId);
    } else {
        $message = 'Error updating program. Please try again.';
    }
}

ob_start();
?>

<div class="page-header">
    <h2>Edit Program</h2>
    <p>Update program information.</p>
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
                <h3>Program Information</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Program Name</label>
                        <input type="text" id="name" name="name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo htmlspecialchars($program['name']); ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="department_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Department</label>
                        <select id="department_id" name="department_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo ($program['department_id'] == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500;">Description</label>
                        <textarea id="description" name="description" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif; min-height: 100px;"><?php echo htmlspecialchars($program['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-save"></i> Update Program
                        </button>
                        <a href="programs.php" class="btn btn-outline" style="padding: 12px 25px; border: 2px solid #667eea; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; color: #667eea; text-decoration: none; margin-left: 10px;">
                            <i class="fas fa-arrow-left"></i> Back to Programs
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4" style="flex: 0 0 33.333%; padding: 0 15px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3>Program Details</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Program ID</label>
                    <p style="margin: 0;"><?php echo $program['id']; ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Current Department</label>
                    <p style="margin: 0;"><?php echo htmlspecialchars($program['department_name']); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Created</label>
                    <p style="margin: 0;"><?php echo date('F j, Y', strtotime($program['created_at'])); ?></p>
                </div>
                
                <div class="detail-row" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Last Updated</label>
                    <p style="margin: 0;"><?php echo $program['updated_at'] ? date('F j, Y g:i A', strtotime($program['updated_at'])) : 'Never'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>