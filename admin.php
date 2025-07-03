<?php
session_start();
require 'functions.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-dashboard.php");
    exit;
}

// Hardcoded admin credentials
$admin_username = 'chitron';
$admin_password = '2448766';

// Login handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin-dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - অন্তঃকণ্ঠ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a3093;
            --primary-dark: #4a1d6b;
            --primary-light: #f3e6f8;
            --danger: #e74c3c;
            --success: #27ae60;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --radius-md: 10px;
            --shadow-md: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Hind Siliguri', sans-serif;
        }
        
        body {
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(135deg, var(--primary-light) 0%, var(--bg-light) 100%);
        }
        
        .login-container {
            background: var(--white);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            transform: translateY(-20px);
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(-20px); }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
        }
        
        .logo p {
            color: var(--text-light);
            margin-top: 5px;
        }
        
        .login-form .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(106, 48, 147, 0.2);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 38px;
            color: var(--primary);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 48, 147, 0.3);
        }
        
        .login-btn i {
            margin-right: 8px;
        }
        
        .error-message {
            color: var(--danger);
            margin-bottom: 1.5rem;
            text-align: center;
            padding: 10px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .admin-notice {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-light);
            font-size: 0.9rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1><i class="fas fa-crown"></i> অন্তঃকণ্ঠ</h1>
            <p>অ্যাডমিন প্যানেল</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST">
            <div class="form-group">
                <label for="username">ইউজারনেম</label>
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="username" name="username" required placeholder="আপনার ইউজারনেম লিখুন">
            </div>
            
            <div class="form-group">
                <label for="password">পাসওয়ার্ড</label>
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password" name="password" required placeholder="আপনার পাসওয়ার্ড লিখুন">
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> লগ ইন
            </button>
        </form>
        
        <div class="admin-notice">
            <i class="fas fa-shield-alt"></i> শুধুমাত্র অনুমোদিত অ্যাডমিনিস্ট্রেটরের জন্য
        </div>
    </div>
</body>
</html>