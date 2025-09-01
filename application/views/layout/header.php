<?php
$current_user = $this->session->userdata('username');
$is_logged_in = $this->session->userdata('user_id');
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Student Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 60px;
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: #f0f0f0;
        }
        
        .navbar-nav {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 20px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-name {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .btn-logout {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background-color: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-1px);
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .navbar-container {
                flex-direction: column;
                height: auto;
                padding: 15px 20px;
            }
            
            .navbar-nav {
                margin-top: 10px;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .user-info {
                margin-top: 10px;
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Main content area */
        .main-content {
            flex: 1;
            padding: 20px 0;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        /* Common form and button styles */
        .btn { 
            padding: 8px 16px; 
            text-decoration: none; 
            margin: 2px; 
            border-radius: 4px; 
            display: inline-block; 
            transition: all 0.3s ease; 
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-add { 
            background-color: #4CAF50; 
            color: white; 
            margin-bottom: 20px; 
        }
        
        .btn-edit { 
            background-color: #008CBA; 
            color: white; 
        }
        
        .btn-delete { 
            background-color: #f44336; 
            color: white; 
        }
        
        .btn-submit { 
            background-color: #4CAF50; 
            color: white;
            padding: 12px 24px;
        }
        
        .btn-cancel { 
            background-color: #f44336; 
            color: white;
            padding: 12px 24px;
        }
        
        .btn:hover { 
            opacity: 0.8; 
            transform: translateY(-1px); 
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Table styles */
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 20px; 
        }
        
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        
        th { 
            background-color: #f8f9fa; 
            font-weight: bold;
            color: #333;
        }
        
        /* Form styles */
        .form-group { 
            margin: 20px 0; 
        }
        
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold;
            color: #333;
        }
        
        .required {
            color: #e74c3c;
        }
        
        input[type="text"], input[type="email"], input[type="password"], textarea { 
            width: 100%; 
            max-width: 400px;
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        /* Message styles */
        .error, .success {
            margin: 15px 0;
            padding: 12px;
            border-radius: 4px;
        }
        
        .error {
            color: #e74c3c;
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            border-left: 4px solid #e74c3c;
        }
        
        .success {
            color: #2e7d32;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-left: 4px solid #2e7d32;
        }
        
        .stats {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #1976d2;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .fade-out {
            animation: fadeOut 0.5s ease-in-out;
            animation-fill-mode: forwards;
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .breadcrumb {
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #007cba;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo site_url($is_logged_in ? 'students' : '/'); ?>" class="navbar-brand">
                SMS
            </a>
            
            <?php if ($is_logged_in): ?>
                <ul class="navbar-nav">
                    <li><a href="<?php echo site_url('students'); ?>" class="nav-link <?php echo ($this->router->class == 'students' && $this->router->method == 'index') ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo site_url('students/manage/add'); ?>" class="nav-link <?php echo ($this->router->method == 'manage' && $this->uri->segment(3) == 'add') ? 'active' : ''; ?>">Add Student 7</a></li>
                    <li><a href="<?php echo site_url('students/test_db'); ?>" class="nav-link <?php echo ($this->router->method == 'test_db') ? 'active' : ''; ?>">Test DB</a></li>
                </ul>
                
                <div class="user-info">
                    <span class="user-name">Welcome, <?php echo htmlspecialchars($current_user); ?></span>
                    <a href="<?php echo site_url('auth/logout'); ?>" class="btn-logout">Logout</a>
                </div>
            <?php else: ?>
                <ul class="navbar-nav">
                    <li><a href="<?php echo site_url('auth/login'); ?>" class="nav-link">Login</a></li>
                    <li><a href="<?php echo site_url('auth/signup'); ?>" class="nav-link">Sign Up</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">