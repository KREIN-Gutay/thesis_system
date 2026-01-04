<?php
require_once '../config/db.php';
require_once '../includes/Auth.php';

// Check if user is authenticated and is an admin
Auth::redirectIfNotAuthenticated('../login.php');
Auth::redirectIfNotAdmin('../index.php');

$title = 'Reports';

$sidebar_items = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['title' => 'Users', 'url' => 'users.php', 'icon' => 'fas fa-users'],
    ['title' => 'Departments', 'url' => 'departments.php', 'icon' => 'fas fa-building'],
    ['title' => 'Programs', 'url' => 'programs.php', 'icon' => 'fas fa-graduation-cap'],
    ['title' => 'Theses', 'url' => 'theses.php', 'icon' => 'fas fa-book'],
    ['title' => 'Approvals', 'url' => 'approvals.php', 'icon' => 'fas fa-check-circle'],
    ['title' => 'Activity Logs', 'url' => 'activity-logs.php', 'icon' => 'fas fa-history'],
    ['title' => 'Reports', 'url' => 'reports.php', 'icon' => 'fas fa-chart-bar', 'active' => true],
];

// Get report parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'thesis_summary';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 year'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Initialize report data
$report_data = [];
$chart_data = [];

switch ($report_type) {
    case 'thesis_summary':
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
        $report_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get thesis submissions by month
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM theses 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'user_statistics':
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
        $report_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get user registrations by month
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM users 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'department_performance':
        // Get department statistics
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
        $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

ob_start();
?>

<div class="page-header">
    <h2>Reports & Analytics</h2>
    <p>Generate and export reports on system usage and thesis statistics.</p>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h3>Report Filters</h3>
    </div>
    <div class="card-body" style="padding: 25px;">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <select name="report_type" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
                    <option value="thesis_summary" <?php echo ($report_type == 'thesis_summary') ? 'selected' : ''; ?>>Thesis Summary</option>
                    <option value="user_statistics" <?php echo ($report_type == 'user_statistics') ? 'selected' : ''; ?>>User Statistics</option>
                    <option value="department_performance" <?php echo ($report_type == 'department_performance') ? 'selected' : ''; ?>>Department Performance</option>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-family: 'Poppins', sans-serif;">
            </div>
            
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 20px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-filter"></i> Generate Report
                </button>
                <a href="export-report.php?report_type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success" style="padding: 12px 20px; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; margin-left: 10px; text-decoration: none;">
                    <i class="fas fa-file-export"></i> Export to CSV
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($report_type == 'thesis_summary'): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['total_theses'] ?? 0; ?></h4>
                <p>Total Theses</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['approved_theses'] ?? 0; ?></h4>
                <p>Approved</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['rejected_theses'] ?? 0; ?></h4>
                <p>Rejected</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['submitted_theses'] ?? 0; ?></h4>
                <p>Submitted</p>
            </div>
        </div>
    </div>
    
    <?php if (!empty($chart_data)): ?>
        <div class="card fade-in">
            <div class="card-header">
                <h3>Thesis Submissions Over Time</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <canvas id="thesisChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?>
    
<?php elseif ($report_type == 'user_statistics'): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['total_users'] ?? 0; ?></h4>
                <p>Total Users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['total_students'] ?? 0; ?></h4>
                <p>Students</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['total_advisers'] ?? 0; ?></h4>
                <p>Advisers</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $report_data['total_admins'] ?? 0; ?></h4>
                <p>Administrators</p>
            </div>
        </div>
    </div>
    
    <?php if (!empty($chart_data)): ?>
        <div class="card fade-in">
            <div class="card-header">
                <h3>User Registrations Over Time</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <canvas id="userChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?>
    
<?php elseif ($report_type == 'department_performance'): ?>
    <div class="card fade-in">
        <div class="card-header">
            <h3>Department Performance</h3>
        </div>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Department</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Total Theses</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Approved Theses</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Approval Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $dept): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px;"><?php echo htmlspecialchars($dept['department_name']); ?></td>
                            <td style="padding: 15px;"><?php echo $dept['total_theses']; ?></td>
                            <td style="padding: 15px;"><?php echo $dept['approved_theses']; ?></td>
                            <td style="padding: 15px;">
                                <?php 
                                $approval_rate = ($dept['total_theses'] > 0) ? round(($dept['approved_theses'] / $dept['total_theses']) * 100, 2) : 0;
                                echo $approval_rate . '%';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($chart_data) && in_array($report_type, ['thesis_summary', 'user_statistics'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('<?php echo ($report_type == 'thesis_summary') ? 'thesisChart' : 'userChart'; ?>').getContext('2d');
    
    // Prepare chart data
    const labels = <?php echo json_encode(array_column($chart_data, 'month')); ?>;
    const data = <?php echo json_encode(array_column($chart_data, 'count')); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '<?php echo ($report_type == 'thesis_summary') ? 'Thesis Submissions' : 'User Registrations'; ?>',
                data: data,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#764ba2',
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include '../views/shared/layout.php';
?>