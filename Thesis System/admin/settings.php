<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Admin Settings';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar'],
    ['title' => 'Settings', 'url' => 'settings.php', 'icon' => 'fas fa-cog', 'active' => true],
];

ob_start();
?>

<div class="page-header">
    <h2>System Settings</h2>
    <p>Configure system-wide settings and preferences.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>General Settings</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="site_name" style="display: block; margin-bottom: 8px; font-weight: 500;">Site Name</label>
                <input type="text" id="site_name" name="site_name" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="Thesis Management System">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="site_email" style="display: block; margin-bottom: 8px; font-weight: 500;">Site Email</label>
                <input type="email" id="site_email" name="site_email" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="admin@university.edu">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="timezone" style="display: block; margin-bottom: 8px; font-weight: 500;">Timezone</label>
                <select id="timezone" name="timezone" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="UTC">UTC</option>
                    <option value="America/New_York" selected>Eastern Time (US & Canada)</option>
                    <option value="America/Chicago">Central Time (US & Canada)</option>
                    <option value="America/Denver">Mountain Time (US & Canada)</option>
                    <option value="America/Los_Angeles">Pacific Time (US & Canada)</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>File Upload Settings</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="max_file_size" style="display: block; margin-bottom: 8px; font-weight: 500;">Maximum File Size (MB)</label>
                <input type="number" id="max_file_size" name="max_file_size" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;" value="10" min="1" max="100">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Allowed File Types</label>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="allowed_types[]" value="pdf" checked> PDF
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="allowed_types[]" value="doc" checked> DOC
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="allowed_types[]" value="docx" checked> DOCX
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Notification Settings</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email Notifications</label>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="notifications[]" value="new_submission" checked> New Thesis Submission
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="notifications[]" value="approval_status" checked> Approval Status Changes
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="notifications[]" value="review_comments" checked> Review Comments
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>