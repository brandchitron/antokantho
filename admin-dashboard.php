<?php
session_start();
require 'functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

// Logout functionality
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Get all users
$users = [];
$userFiles = glob("data/users/*.json");
foreach ($userFiles as $file) {
    $username = basename($file, '.json');
    $user = getUserData($username);
    if ($user) {
        $users[$username] = $user;
    }
}

// Get all poems
$poems = [];
$poemFiles = glob("data/posts/*.json");
foreach ($poemFiles as $file) {
    $id = basename($file, '.json');
    $poem = getPoem($id);
    if ($poem) {
        $poem['id'] = $id;
        $poems[] = $poem;
    }
}

// Action handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban_user'])) {
        $username = $_POST['username'];
        $userData = getUserData($username);
        $userData['banned'] = true;
        saveUserData($username, $userData);
    }
    
    if (isset($_POST['unban_user'])) {
        $username = $_POST['username'];
        $userData = getUserData($username);
        $userData['banned'] = false;
        saveUserData($username, $userData);
    }
    
    if (isset($_POST['verify_user'])) {
        $username = $_POST['username'];
        $userData = getUserData($username);
        $userData['verified'] = true;
        saveUserData($username, $userData);
    }
    
    if (isset($_POST['unverify_user'])) {
        $username = $_POST['username'];
        $userData = getUserData($username);
        $userData['verified'] = false;
        saveUserData($username, $userData);
    }
    
    if (isset($_POST['make_admin'])) {
        $username = $_POST['username'];
        $userData = getUserData($username);
        $userData['is_admin'] = true;
        saveUserData($username, $userData);
    }
    
    if (isset($_POST['remove_admin'])) {
        $username = $_POST['username'];
        $userData = getUserData($username);
        $userData['is_admin'] = false;
        saveUserData($username, $userData);
    }
    
    if (isset($_POST['delete_poem'])) {
        $poemId = $_POST['poem_id'];
        $filename = "data/posts/{$poemId}.json";
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    
    // Refresh data
    header("Location: admin-dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন ড্যাশবোর্ড - অন্তঃকণ্ঠ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a3093;
            --primary-dark: #4a1d6b;
            --primary-light: #f3e6f8;
            --secondary: #9b59b6;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #e0e0e0;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary);
            color: white;
            padding-top: 20px;
            box-shadow: var(--shadow-md);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            transition: var(--transition);
            overflow-y: auto;
        }
        
        .brand {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .brand h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .brand i {
            font-size: 1.8rem;
        }
        
        .nav-links {
            margin-top: 10px;
            padding: 0 10px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border-radius: var(--radius-sm);
            margin-bottom: 5px;
            transition: var(--transition);
            font-size: 1rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
            width: calc(100% - 280px);
            max-width: 100%;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .dashboard-header h2 {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(106, 48, 147, 0.1);
            padding: 10px 15px;
            border-radius: var(--radius-md);
        }
        
        .user-info img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .user-info-text {
            line-height: 1.4;
        }
        
        .user-info-text strong {
            display: block;
            color: var(--primary-dark);
        }
        
        .user-info-text small {
            color: var(--gray);
            font-size: 0.85rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 25px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .stat-info p {
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: rgba(106, 48, 147, 0.1);
            color: var(--primary);
        }
        
        .stat-users .stat-icon { background: rgba(106, 48, 147, 0.1); color: var(--primary); }
        .stat-poems .stat-icon { background: rgba(155, 89, 182, 0.1); color: var(--secondary); }
        .stat-banned .stat-icon { background: rgba(231, 76, 60, 0.1); color: var(--danger); }
        .stat-verified .stat-icon { background: rgba(39, 174, 96, 0.1); color: var(--success); }
        
        /* Tables */
        .section {
            background: white;
            border-radius: var(--radius-md);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            width: 100%;
            overflow: hidden;
        }
        
        .section:hover {
            box-shadow: var(--shadow-md);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .section-header h3 {
            color: var(--primary);
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-header h3 i {
            color: var(--primary);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius-sm);
            width: 100%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: rgba(106, 48, 147, 0.03);
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid var(--border);
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }
        
        .status-banned {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }
        
        .status-verified {
            background: rgba(41, 128, 185, 0.1);
            color: var(--info);
        }
        
        .verified-badge {
            color: var(--info);
            margin-left: 5px;
            font-size: 0.9rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: var(--transition);
            white-space: nowrap;
        }
        
        .btn i {
            margin-right: 6px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }
        
        .btn-danger:hover {
            background: rgba(231, 76, 60, 0.2);
        }
        
        .btn-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }
        
        .btn-success:hover {
            background: rgba(39, 174, 96, 0.2);
        }
        
        .btn-primary {
            background: rgba(106, 48, 147, 0.1);
            color: var(--primary);
        }
        
        .btn-primary:hover {
            background: rgba(106, 48, 147, 0.2);
        }
        
        .btn-warning {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }
        
        .btn-warning:hover {
            background: rgba(243, 156, 18, 0.2);
        }
        
        .btn-info {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info);
        }
        
        .btn-info:hover {
            background: rgba(52, 152, 219, 0.2);
        }
        
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        /* Poem title styling */
        .poem-title {
            font-weight: 600;
            color: var(--dark);
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }
        
        .poem-title:hover {
            color: var(--primary);
        }
        
        /* Dropdown menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: var(--shadow-md);
            z-index: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }
        
        .dropdown-content a {
            color: var(--dark);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }
        
        .dropdown-content a:hover {
            background-color: var(--primary-light);
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            .mobile-menu-toggle {
                display: flex;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            th, td {
                padding: 10px;
                font-size: 0.9rem;
            }
            .section {
                padding: 15px;
            }
            .action-group {
                flex-direction: column;
                gap: 5px;
            }
            .btn {
                width: 100%;
                justify-content: flex-start;
            }
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 101;
            background: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: var(--shadow-md);
            cursor: pointer;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </div>
    
    <div class="sidebar">
        <div class="brand">
            <h1><i class="fas fa-crown"></i> অন্তঃকণ্ঠ</h1>
        </div>
        
        <div class="nav-links">
            <a href="#" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড
            </a>
            <a href="#users-section" class="nav-link">
                <i class="fas fa-users"></i> ব্যবহারকারী
            </a>
            <a href="#poems-section" class="nav-link">
                <i class="fas fa-book"></i> কবিতা
            </a>
            <div class="dropdown">
                <a href="#" class="nav-link">
                    <i class="fas fa-ellipsis-v"></i> আরও
                </a>
                <div class="dropdown-content">
                    <a href="#"><i class="fas fa-cog"></i> সেটিংস</a>
                    <a href="index.php"><i class="fas fa-home"></i> সাইটে ফিরে যান</a>
                    <a href="?action=logout"><i class="fas fa-sign-out-alt"></i> লগ আউট</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="dashboard-header">
            <h2>ড্যাশবোর্ড ওভারভিউ</h2>
            <div class="user-info">
                <img src="<?= getProfilePic($_SESSION['admin_username']) ?>" alt="Admin">
                <div class="user-info-text">
                    <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
                    <small>সুপার অ্যাডমিন</small>
                </div>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-users fade-in">
                <div class="stat-info">
                    <h3><?= count($users) ?></h3>
                    <p>সর্বমোট ব্যবহারকারী</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            <div class="stat-card stat-poems fade-in">
                <div class="stat-info">
                    <h3><?= count($poems) ?></h3>
                    <p>প্রকাশিত কবিতা</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
            
            <div class="stat-card stat-banned fade-in">
                <div class="stat-info">
                    <h3>
                        <?= count(array_filter($users, function($user) { 
                            return isset($user['banned']) && $user['banned']; 
                        })) ?>
                    </h3>
                    <p>ব্যান করা ব্যবহারকারী</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
            </div>
            
            <div class="stat-card stat-verified fade-in">
                <div class="stat-info">
                    <h3>
                        <?= count(array_filter($users, function($user) { 
                            return isset($user['verified']) && $user['verified']; 
                        })) ?>
                    </h3>
                    <p>ভেরিফাইড লেখক</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="section" id="users-section">
            <div class="section-header">
                <h3><i class="fas fa-users"></i> সকল ব্যবহারকারী</h3>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ব্যবহারকারী</th>
                            <th>ইউজারনেম</th>
                            <th>স্ট্যাটাস</th>
                            <th>ভেরিফিকেশন</th>
                            <th>এডমিন</th>
                            <th>কর্ম</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $username => $user): ?>
                        <tr class="fade-in">
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <img src="<?= getProfilePic($username) ?>" class="user-avatar">
                                    <div>
                                        <strong><?= htmlspecialchars($user['fullname'] ?? 'N/A') ?></strong>
                                        <?php if (isset($user['verified']) && $user['verified']): ?>
                                            <i class="fas fa-check-circle verified-badge" title="ভেরিফাইড ব্যবহারকারী"></i>
                                        <?php endif; ?>
                                        <br>
                                        <small><?= htmlspecialchars($user['email'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>@<?= htmlspecialchars($username) ?></td>
                            <td>
                                <span class="status <?= (isset($user['banned']) && $user['banned']) ? 'status-banned' : 'status-active' ?>">
                                    <?= (isset($user['banned']) && $user['banned']) ? 'ব্যান করা' : 'সক্রিয়' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status <?= (isset($user['verified']) && $user['verified']) ? 'status-verified' : '' ?>">
                                    <?= (isset($user['verified']) && $user['verified']) ? 'ভেরিফাইড' : 'অনির্ধারিত' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status <?= (isset($user['is_admin']) && $user['is_admin']) ? 'status-verified' : '' ?>">
                                    <?= (isset($user['is_admin']) && $user['is_admin']) ? 'হ্যাঁ' : 'না' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <!-- Ban/Unban -->
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="username" value="<?= $username ?>">
                                        <?php if (isset($user['banned']) && $user['banned']): ?>
                                            <button type="submit" name="unban_user" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-check"></i> আনব্যান
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="ban_user" class="btn btn-danger btn-sm">
                                                <i class="fas fa-ban"></i> ব্যান
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    
                                    <!-- Verify/Unverify -->
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="username" value="<?= $username ?>">
                                        <?php if (isset($user['verified']) && $user['verified']): ?>
                                            <button type="submit" name="unverify_user" class="btn btn-warning btn-sm">
                                                <i class="fas fa-times-circle"></i> আনভেরিফাই
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="verify_user" class="btn btn-info btn-sm">
                                                <i class="fas fa-check-circle"></i> ভেরিফাই
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    
                                    <!-- Admin/Remove Admin -->
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="username" value="<?= $username ?>">
                                        <?php if (isset($user['is_admin']) && $user['is_admin']): ?>
                                            <button type="submit" name="remove_admin" class="btn btn-danger btn-sm">
                                                <i class="fas fa-user-minus"></i> এডমিন অপসারণ
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="make_admin" class="btn btn-primary btn-sm">
                                                <i class="fas fa-user-shield"></i> এডমিন করুন
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Poems Table -->
        <div class="section" id="poems-section">
            <div class="section-header">
                <h3><i class="fas fa-book"></i> সকল কবিতা</h3>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>কবিতার শিরোনাম</th>
                            <th>লেখক</th>
                            <th>প্রকাশের তারিখ</th>
                            <th>লাইক</th>
                            <th>কর্ম</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($poems as $poem): 
                            $authorData = getUserData($poem['author']);
                        ?>
                        <tr class="fade-in">
                            <td>
                                <a href="poem.php?id=<?= $poem['id'] ?>" class="poem-title">
                                    <?= htmlspecialchars($poem['title']) ?>
                                </a>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <a href="profile.php?user=<?= urlencode($poem['author']) ?>">
                                        <?= htmlspecialchars($poem['author']) ?>
                                    </a>
                                    <?php if ($authorData && isset($authorData['verified']) && $authorData['verified']): ?>
                                        <i class="fas fa-check-circle verified-badge" title="ভেরিফাইড লেখক"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= date('d M Y', strtotime($poem['timestamp'])) ?></td>
                            <td><?= count($poem['likes'] ?? []) ?></td>
                            <td>
                                <div class="action-group">
                                    <a href="edit.php?id=<?= $poem['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> এডিট
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="poem_id" value="<?= $poem['id'] ?>">
                                        <button type="submit" name="delete_poem" class="btn btn-danger btn-sm" onclick="return confirm('আপনি কি নিশ্চিত যে আপনি এই কবিতাটি মুছে ফেলতে চান?')">
                                            <i class="fas fa-trash-alt"></i> ডিলিট
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                sidebar.classList.remove('active');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Highlight table row when hovering
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.backgroundColor = 'rgba(106, 48, 147, 0.03)';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.backgroundColor = '';
            });
        });
    </script>
</body>
</html>