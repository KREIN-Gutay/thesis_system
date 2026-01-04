<?php
require_once 'config/db.php';
require_once 'includes/Auth.php';
require_once 'models/ActivityLog.php';

// Log activity before destroying session
if (Auth::check()) {
    $activityLog = new ActivityLog($pdo);
    $activityLog->logActivity(Auth::id(), 'LOGOUT', 'User logged out');
}

Auth::logout();
header('Location: login.php');
exit();
?>