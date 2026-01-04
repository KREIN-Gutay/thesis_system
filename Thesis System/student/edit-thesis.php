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
    // Redirect if no thesis ID provided
    header('Location: my-theses.php');
    exit;
}

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Theses', 'url' => 'my-theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Submit Thesis', 'url' => 'submit-thesis.php', 'icon' => 'fas fa-upload'],
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

// Get existing thesis files
$fileModel = new ThesisFile($pdo);
$existingFiles = $fileModel->getFilesByThesis($thesisId);

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
                            $fileData = [
                                'thesis_id' => $thesisId,
                                'file_name' => $fileName,
                                'file_path' => $uploadFile,
                                'file_size' => $_FILES['thesis_file']['size'],
                                'mime_type' => $_FILES['thesis_file']['type']
                            ];

                            $fileModel = new ThesisFile($pdo);
                            $fileModel->createFile($fileData);

                            $message .= ' File uploaded successfully!';
                        } else {
                            $message .= ' Failed to upload file.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message .= ' File validation error: ' . $fileValidation['error'];
                        $messageType = 'danger';
                    }
                }

                // Log activity
                if ($messageType !== 'danger') {
                    require_once '../models/ActivityLog.php';
                    $activityLog = new ActivityLog($pdo);
                    $activityLog->logActivity(Auth::id(), 'THESIS_UPDATE', 'Updated thesis draft: ' . $title);

                    $message = 'Thesis updated successfully!' . (isset($message) && strpos($message, 'File uploaded successfully') ? ' File uploaded successfully!' : '');
                    $messageType = 'success';
                }

                // Refresh thesis data
                $existingThesis = $thesisModel->getThesisById($thesisId);
            } else {
                $message = 'Error updating thesis.';
                $messageType = 'danger';
            }
        } catch (Exception $e) {
            $message = 'Error updating thesis: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

ob_start();
?>
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
</style>

<div class="page-header">
    <h2>Edit Thesis</h2>
    <p>Update your thesis details below.</p>
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

        <?php if (count($existingFiles) > 0): ?>
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Existing Files:</label>
                <?php foreach ($existingFiles as $file): ?>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        <i class="fas fa-file-pdf" style="font-size: 20px; color: #dc3545;"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($file['file_name']); ?></div>
                            <div style="font-size: 12px; color: #666;"><?php echo round($file['file_size'] / 1024, 2); ?> KB</div>
                        </div>
                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-outline" style="padding: 5px 10px; border: 1px solid #667eea; border-radius: 5px; color: #667eea; text-decoration: none; font-size: 12px;">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="thesis_file" style="display: block; margin-bottom: 8px; font-weight: 500;">Thesis File (PDF/DOC/DOCX, max 10MB)</label>
            <input type="file" id="thesis_file" name="thesis_file" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" accept=".pdf,.doc,.docx">
            <small style="color: #666; display: block; margin-top: 5px;">Uploading a new file will add it to your thesis (existing files will remain).</small>
        </div>

        <div class="form-group" style="margin-bottom: 25px; display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <i class="fas fa-save"></i> Update Thesis
            </button>
            <a href="my-theses.php" class="btn btn-outline" style="padding: 12px 25px; border: 2px solid #667eea; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; color: #667eea; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
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

    // Trigger department change on page load to filter programs
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('department_id').dispatchEvent(new Event('change'));
    });
</script>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>