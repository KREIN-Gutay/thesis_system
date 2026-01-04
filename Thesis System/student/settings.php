<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is a student
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotStudent('../index.php');

$title = 'Student Settings';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Theses', 'url' => 'my-theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Submit Thesis', 'url' => 'submit-thesis.php', 'icon' => 'fas fa-upload'],
    ['title' => 'Settings', 'url' => 'settings.php', 'icon' => 'fas fa-cog', 'active' => true],
];

ob_start();
?>

<div class="page-header">
    <h2>Account Settings</h2>
    <p>Manage your account preferences and notification settings.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Notification Preferences</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email Notifications</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="thesis_approved" checked> 
                        Notify me when my thesis is approved
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="thesis_rejected" checked> 
                        Notify me when my thesis needs revisions
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="review_comments" checked> 
                        Notify me when I receive review comments
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="system_updates" checked> 
                        Send me important system updates
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Privacy Settings</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Profile Visibility</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="visibility" value="public" checked> 
                        Public (Anyone can view my profile)
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="visibility" value="students_only"> 
                        Students Only (Only fellow students can view my profile)
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="visibility" value="private"> 
                        Private (Only I can view my profile)
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Thesis Visibility</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="thesis_visibility" value="approved_only" checked> 
                        Only show approved theses on my profile
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Privacy Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>