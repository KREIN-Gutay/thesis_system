<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

// Get report parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'thesis_summary';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 year'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Set headers for CSV export
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="thesis_report_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

switch ($report_type) {
    case 'thesis_summary':
        // Output header row
        fputcsv($output, ['Thesis Report - Summary', '', '', '']);
        fputcsv($output, ['Generated on', date('Y-m-d H:i:s'), '', '']);
        fputcsv($output, ['Period', $start_date . ' to ' . $end_date, '', '']);
        fputcsv($output, ['', '', '', '']);

        // Get thesis statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_theses,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_theses,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_theses,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted_theses,
                SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review_theses,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_theses
            FROM theses 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Output statistics
        fputcsv($output, ['Metric', 'Count', '', '']);
        fputcsv($output, ['Total Theses', $stats['total_theses'], '', '']);
        fputcsv($output, ['Approved', $stats['approved_theses'], '', '']);
        fputcsv($output, ['Rejected', $stats['rejected_theses'], '', '']);
        fputcsv($output, ['Submitted', $stats['submitted_theses'], '', '']);
        fputcsv($output, ['Under Review', $stats['under_review_theses'], '', '']);
        fputcsv($output, ['Draft', $stats['draft_theses'], '', '']);

        // Get detailed thesis data
        fputcsv($output, ['', '', '', '']);
        fputcsv($output, ['Detailed Thesis Data', '', '', '']);
        fputcsv($output, ['Title', 'Author', 'Status', 'Submitted Date']);

        $stmt = $pdo->prepare("
            SELECT t.title, CONCAT(u.first_name, ' ', u.last_name) as author, t.status, t.submitted_at
            FROM theses t
            JOIN users u ON t.author_id = u.id
            WHERE t.created_at BETWEEN ? AND ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($theses as $thesis) {
            fputcsv($output, [$thesis['title'], $thesis['author'], $thesis['status'], $thesis['submitted_at']]);
        }
        break;

    case 'user_statistics':
        // Output header row
        fputcsv($output, ['User Report - Statistics', '', '', '']);
        fputcsv($output, ['Generated on', date('Y-m-d H:i:s'), '', '']);
        fputcsv($output, ['Period', $start_date . ' to ' . $end_date, '', '']);
        fputcsv($output, ['', '', '', '']);

        // Get user statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as total_students,
                SUM(CASE WHEN role = 'adviser' THEN 1 ELSE 0 END) as total_advisers,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins
            FROM users 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Output statistics
        fputcsv($output, ['Metric', 'Count', '', '']);
        fputcsv($output, ['Total Users', $stats['total_users'], '', '']);
        fputcsv($output, ['Students', $stats['total_students'], '', '']);
        fputcsv($output, ['Advisers', $stats['total_advisers'], '', '']);
        fputcsv($output, ['Administrators', $stats['total_admins'], '', '']);

        // Get detailed user data
        fputcsv($output, ['', '', '', '']);
        fputcsv($output, ['Detailed User Data', '', '', '']);
        fputcsv($output, ['Username', 'Name', 'Role', 'Registration Date']);

        $stmt = $pdo->prepare("
            SELECT username, CONCAT(first_name, ' ', last_name) as full_name, role, created_at
            FROM users
            WHERE created_at BETWEEN ? AND ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            fputcsv($output, [$user['username'], $user['full_name'], $user['role'], $user['created_at']]);
        }
        break;

    case 'department_performance':
        // Output header row
        fputcsv($output, ['Department Performance Report', '', '', '']);
        fputcsv($output, ['Generated on', date('Y-m-d H:i:s'), '', '']);
        fputcsv($output, ['Period', $start_date . ' to ' . $end_date, '', '']);
        fputcsv($output, ['', '', '', '']);

        // Get department statistics
        fputcsv($output, ['Department', 'Total Theses', 'Approved Theses', 'Approval Rate']);

        $stmt = $pdo->prepare("
            SELECT 
                d.name as department_name,
                COUNT(t.id) as total_theses,
                SUM(CASE WHEN t.status = 'approved' THEN 1 ELSE 0 END) as approved_theses
            FROM departments d
            LEFT JOIN theses t ON d.id = t.department_id AND t.created_at BETWEEN ? AND ?
            GROUP BY d.id, d.name
            ORDER BY approved_theses DESC
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($departments as $dept) {
            $approval_rate = ($dept['total_theses'] > 0) ? round(($dept['approved_theses'] / $dept['total_theses']) * 100, 2) : 0;
            fputcsv($output, [$dept['department_name'], $dept['total_theses'], $dept['approved_theses'], $approval_rate . '%']);
        }
        break;
}

// Close output stream
fclose($output);
exit();
