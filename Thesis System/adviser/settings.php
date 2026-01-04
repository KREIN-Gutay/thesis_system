<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an adviser
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdviser('../index.php');

$title = 'Adviser Settings';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'My Students', 'url' => 'students.php', 'icon' => 'fas fa-users'],
    ['title' => 'Theses to Review', 'url' => 'review.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approved Theses', 'url' => 'approved.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Settings', 'url' => 'settings.php', 'icon' => 'fas fa-cog', 'active' => true],
];

ob_start();
?>

<div class="page-header">
    <h2>Adviser Settings</h2>
    <p>Manage your account preferences and review settings.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Review Preferences</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Auto Assignment</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="auto_assign" value="enabled" checked> 
                        Automatically assign new theses to me based on my expertise
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="max_assignments" style="display: block; margin-bottom: 8px; font-weight: 500;">Maximum Active Assignments</label>
                <select id="max_assignments" name="max_assignments" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="5">5 theses</option>
                    <option value="10" selected>10 theses</option>
                    <option value="15">15 theses</option>
                    <option value="20">20 theses</option>
                    <option value="unlimited">Unlimited</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Review Preferences
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
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="new_assignment" checked> 
                        Notify me when a new thesis is assigned for review
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="student_comment" checked> 
                        Notify me when students respond to my comments
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="deadline_reminder" checked> 
                        Send deadline reminders for pending reviews
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notifications[]" value="weekly_summary" checked> 
                        Weekly summary of my review activities
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Notification Settings
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Availability Settings</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Review Availability</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="availability[]" value="weekdays" checked> 
                        Available for reviews on weekdays
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="availability[]" value="weekends"> 
                        Available for reviews on weekends
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="response_time" style="display: block; margin-bottom: 8px; font-weight: 500;">Expected Response Time</label>
                <select id="response_time" name="response_time" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="24_hours">Within 24 hours</option>
                    <option value="48_hours" selected>Within 48 hours</option>
                    <option value="72_hours">Within 72 hours</option>
                    <option value="1_week">Within 1 week</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> Save Availability Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>