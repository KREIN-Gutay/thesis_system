<?php
require_once 'includes/Auth.php';

// If user is logged in, redirect to their dashboard
if (Auth::check()) {
    $role = Auth::role();
    switch ($role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'student':
            header('Location: student/dashboard.php');
            break;
        case 'adviser':
            header('Location: adviser/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .hero-container {
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 40px;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .feature-card p {
            opacity: 0.8;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .footer {
            margin-top: 50px;
            opacity: 0.7;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero-container {
                padding: 20px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
            
            .logo p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="hero-container">
        <div class="logo">
            <h1>Thesis Management System</h1>
            <p>A comprehensive platform for managing academic theses with streamlined workflows, secure authentication, and intuitive user interfaces.</p>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h3>Easy Submission</h3>
                <p>Students can easily upload their theses with all required metadata and track their submission status.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Advanced Search</h3>
                <p>Powerful search and filtering capabilities to find theses by title, author, keywords, and more.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Approval Workflow</h3>
                <p>Streamlined approval process with comments and feedback from advisers and administrators.</p>
            </div>
        </div>

        <a href="login.php" class="cta-button">
            <i class="fas fa-sign-in-alt"></i> Get Started
        </a>

        <div class="footer">
            <p>&copy; 2025 Thesis Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>