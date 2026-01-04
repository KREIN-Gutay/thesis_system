<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';
require_once '../models/Department.php';
require_once '../models/Program.php';
require_once '../models/User.php';
require_once '../models/Thesis.php';
require_once '../models/ThesisFile.php';

// Check if user is authenticated and is a student
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotStudent('../index.php');

// Check if we're editing an existing thesis
$editing = false;
$thesisId = null;
$existingThesis = null;

if (isset($_GET['id'])) {
    $thesisId = intval($_GET['id']);
    $thesisModel = new Thesis($pdo);
    $existingThesis = $thesisModel->getThesisById($thesisId);

    // Verify the thesis belongs to the current user
    if ($existingThesis && $existingThesis['author_id'] == Auth::id()) {
        $editing = true;
        $title = 'Edit Thesis';
    } else {
        // Redirect if thesis doesn't belong to user
        header('Location: my-theses.php');
        exit;
    }
} else {
    $title = 'Submit Thesis';
}

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Theses', 'url' => 'my-theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Submit Thesis', 'url' => 'submit-thesis.php', 'icon' => 'fas fa-upload', 'active' => true],
    ['title' => 'Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user'],
];

// Get departments and programs for dropdowns
$departmentModel = new Department($pdo);
$programModel = new Program($pdo);
$userModel = new User($pdo);

$departments = $departmentModel->getAllDepartments();
$programs = $programModel->getAllPrograms();

// Get advisers for dropdown
$advisers = $userModel->findAll("users WHERE role = 'adviser'");

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    require_once '../includes/Security.php';

    $title = Security::sanitizeInput($_POST['title']);
    $abstract = Security::sanitizeInput($_POST['abstract']);
    $keywords = Security::sanitizeInput($_POST['keywords']);
    $department_id = intval($_POST['department_id']);
    $program_id = intval($_POST['program_id']);
    $adviser_id = intval($_POST['adviser_id']);
    $year = intval($_POST['year']);

    // Validate required fields
    if (empty($title) || empty($abstract) || empty($department_id) || empty($program_id) || empty($adviser_id) || empty($year)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'danger';
    } else {
        try {
            $thesisModel = new Thesis($pdo);

            if ($editing) {
                // Update existing thesis
                $thesisData = [
                    'title' => $title,
                    'abstract' => $abstract,
                    'keywords' => $keywords,
                    'adviser_id' => $adviser_id,
                    'department_id' => $department_id,
                    'program_id' => $program_id,
                    'year' => $year,
                    'status' => 'draft'
                ];

                $result = $thesisModel->updateThesis($thesisId, $thesisData);

                if ($result) {
                    // Log activity
                    require_once '../models/ActivityLog.php';
                    $activityLog = new ActivityLog($pdo);
                    $activityLog->logActivity(Auth::id(), 'THESIS_UPDATE', 'Updated thesis draft: ' . $title);

                    $message = 'Thesis updated successfully!';
                    $messageType = 'success';

                    // Refresh thesis data
                    $existingThesis = $thesisModel->getThesisById($thesisId);
                } else {
                    $message = 'Error updating thesis.';
                    $messageType = 'danger';
                }
            } else {
                // Create new thesis record
                $thesisData = [
                    'title' => $title,
                    'abstract' => $abstract,
                    'keywords' => $keywords,
                    'author_id' => Auth::id(),
                    'adviser_id' => $adviser_id,
                    'department_id' => $department_id,
                    'program_id' => $program_id,
                    'year' => $year,
                    'status' => 'draft'
                ];

                // Insert thesis
                $stmt = $pdo->prepare("
                    INSERT INTO theses (title, abstract, keywords, author_id, adviser_id, department_id, program_id, year, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $thesisData['title'],
                    $thesisData['abstract'],
                    $thesisData['keywords'],
                    $thesisData['author_id'],
                    $thesisData['adviser_id'],
                    $thesisData['department_id'],
                    $thesisData['program_id'],
                    $thesisData['year'],
                    $thesisData['status']
                ]);

                $thesisId = $pdo->lastInsertId();

                // Handle file upload if provided
                if (isset($_FILES['thesis_file']) && $_FILES['thesis_file']['error'] == 0) {
                    require_once '../includes/Security.php';

                    $uploadDir = '../assets/uploads/';

                    // Validate file upload
                    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    $maxFileSize = 10 * 1024 * 1024; // 10MB

                    $fileValidation = Security::validateFileUpload($_FILES['thesis_file'], $allowedTypes, $maxFileSize);

                    if ($fileValidation['valid']) {
                        // Sanitize filename
                        $originalName = Security::sanitizeInput(basename($_FILES['thesis_file']['name']));
                        $fileName = uniqid() . '_' . $originalName;
                        $uploadFile = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['thesis_file']['tmp_name'], $uploadFile)) {
                            // Save file record
                            $fileModel = new ThesisFile($pdo);
                            $fileData = [
                                'thesis_id' => $thesisId,
                                'file_name' => $fileName,
                                'file_path' => $uploadFile,
                                'file_size' => $_FILES['thesis_file']['size'],
                                'mime_type' => $_FILES['thesis_file']['type']
                            ];

                            $stmt = $pdo->prepare("
                                INSERT INTO thesis_files (thesis_id, file_name, file_path, file_size, mime_type) 
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $fileData['thesis_id'],
                                $fileData['file_name'],
                                $fileData['file_path'],
                                $fileData['file_size'],
                                $fileData['mime_type']
                            ]);

                            // Log activity
                            require_once '../models/ActivityLog.php';
                            $activityLog = new ActivityLog($pdo);
                            $activityLog->logActivity(Auth::id(), 'THESIS_SUBMIT', 'Submitted thesis: ' . $title);

                            $message = 'Thesis submitted successfully!';
                            $messageType = 'success';
                        } else {
                            $message = 'Failed to upload file.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'File validation error: ' . $fileValidation['error'];
                        $messageType = 'danger';
                    }
                } else {
                    // Log activity for draft save
                    require_once '../models/ActivityLog.php';
                    $activityLog = new ActivityLog($pdo);
                    $activityLog->logActivity(Auth::id(), 'THESIS_DRAFT_SAVE', 'Saved thesis draft: ' . $title);

                    $message = 'Thesis draft saved successfully!';
                    $messageType = 'success';
                }
            }
        } catch (Exception $e) {
            $message = 'Error submitting thesis: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

ob_start();
?>

<div class="page-header">
    <h2><?php echo $editing ? 'Edit Thesis' : 'Submit New Thesis'; ?></h2>
    <p><?php echo $editing ? 'Update your thesis details below.' : 'Fill in the details below to submit your thesis.'; ?></p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; border-radius: 10px; margin-bottom: 25px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card fade-in">
    <div class="card-header">
        <h3>Thesis Details</h3>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <?php if ($editing): ?>
            <input type="hidden" name="thesis_id" value="<?php echo $thesisId; ?>">
        <?php endif; ?>
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="title" style="display: block; margin-bottom: 8px; font-weight: 500;">Title *</label>
            <input type="text" id="title" name="title" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo $editing ? htmlspecialchars($existingThesis['title']) : ''; ?>" required>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="abstract" style="display: block; margin-bottom: 8px; font-weight: 500;">Abstract *</label>
            <textarea id="abstract" name="abstract" rows="6" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required><?php echo $editing ? htmlspecialchars($existingThesis['abstract']) : ''; ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="keywords" style="display: block; margin-bottom: 8px; font-weight: 500;">Keywords</label>
            <input type="text" id="keywords" name="keywords" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="<?php echo $editing ? htmlspecialchars($existingThesis['keywords']) : ''; ?>" placeholder="Enter keywords separated by commas">
        </div>

        <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
            <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="department_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Department *</label>
                    <select id="department_id" name="department_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo ($editing && $existingThesis['department_id'] == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="program_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Program *</label>
                    <select id="program_id" name="program_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programs as $prog): ?>
                            <option value="<?php echo $prog['id']; ?>" data-dept="<?php echo $prog['department_id']; ?>" <?php echo ($editing && $existingThesis['program_id'] == $prog['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($prog['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
            <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="adviser_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Adviser *</label>
                    <select id="adviser_id" name="adviser_id" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required>
                        <option value="">Select Adviser</option>
                        <?php foreach ($advisers as $adviser): ?>
                            <option value="<?php echo $adviser['id']; ?>" <?php echo ($editing && $existingThesis['adviser_id'] == $adviser['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($adviser['first_name'] . ' ' . $adviser['last_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6" style="flex: 0 0 50%; padding: 0 10px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="year" style="display: block; margin-bottom: 8px; font-weight: 500;">Year *</label>
                    <select id="year" name="year" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" required>
                        <option value="">Select Year</option>
                        <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($editing && $existingThesis['year'] == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label for="thesis_file" style="display: block; margin-bottom: 8px; font-weight: 500;">Thesis File (PDF/DOC/DOCX, max 10MB)</label>
            <input type="file" id="thesis_file" name="thesis_file" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" accept=".pdf,.doc,.docx">
        </div>

        <div class="form-group" style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <i class="fas fa-save"></i> <?php echo $editing ? 'Update Draft' : 'Save Draft'; ?>
            </button>
            <button type="submit" name="submit_thesis" value="1" class="btn btn-success" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                <i class="fas fa-paper-plane"></i> <?php echo $editing ? 'Update Thesis' : 'Submit Thesis'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    // Filter programs based on selected department
    document.getElementById('department_id').addEventListener('change', function() {
        const deptId = this.value;
        const programOptions = document.querySelectorAll('#program_id option[data-dept]');

        programOptions.forEach(option => {
            if (deptId === '' || option.getAttribute('data-dept') === deptId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        // Reset program selection
        document.getElementById('program_id').value = '';
    });
</script>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>