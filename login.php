<?php
require 'functions.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$isRegister = isset($_GET['register']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if ($isRegister) {
        // Registration logic
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        
        if (empty($username) || empty($password) || empty($fullname)) {
            $error = 'সব ফিল্ড পূরণ করুন';
        } elseif (getUserData($username)) {
            $error = 'ইউজারনেম ইতিমধ্যে ব্যবহৃত';
        } else {
            // IP check
            $existingUser = getUserByIP();
            if ($existingUser) {
                $error = "আপনি ইতিমধ্যে একটি অ্যাকাউন্ট তৈরি করেছেন ({$existingUser['username']})। লগ ইন করুন।";
            } else {
                $userData = [
                    'username' => $username,
                    'password' => base64_encode($password), // Base64 encoding
                    'fullname' => $fullname,
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'joined' => date('Y-m-d H:i:s'),
                    'profile_pic' => 'assets/images/default-profile.png'
                ];
                
                saveUserData($username, $userData);
                $_SESSION['user'] = $userData;
                header('Location: index.php');
                exit;
            }
        }
    } else {
        // Login logic
        $user = getUserData($username);
        
        // Base64 verification
        if (!$user || base64_decode($user['password']) !== $password) {
            $error = 'ভুল ইউজারনেম বা পাসওয়ার্ড';
        } else {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isRegister ? 'রেজিস্টার' : 'লগ ইন' ?> - অন্তঃকণ্ঠ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8e44ad;
            --primary-dark: #732d91;
            --primary-light: #f3e6f8;
            --secondary: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --text-dark: #2c3e50;
            --text-medium: #34495e;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --border: #e0e0e0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-medium);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(135deg, var(--primary-light) 0%, var(--bg-light) 100%);
        }

        .auth-page {
            width: 100%;
            max-width: 500px;
        }

        .auth-container {
            background-color: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .auth-container h2 {
            font-size: 1.75rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        .auth-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.error {
            background-color: #fdecea;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert.success {
            background-color: #e8f5e9;
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert i {
            font-size: 1.2rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.2);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.875rem 2rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            margin-top: 0.5rem;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .btn i {
            font-size: 1.1rem;
        }

        /* Footer Links */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Social Login */
        .social-login {
            margin-top: 2rem;
            text-align: center;
        }

        .social-login p {
            position: relative;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .social-login p::before,
        .social-login p::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background-color: var(--border);
        }

        .social-login p::before {
            left: 0;
        }

        .social-login p::after {
            right: 0;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .facebook {
            background-color: #3b5998;
        }

        .google {
            background-color: #db4437;
        }

        .twitter {
            background-color: #1da1f2;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .auth-container {
                padding: 1.75rem;
            }
            
            .auth-container h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <h2>
            <i class="fas <?= $isRegister ? 'fa-user-plus' : 'fa-sign-in-alt' ?>"></i>
            <?= $isRegister ? 'নতুন অ্যাকাউন্ট তৈরি করুন' : 'লগ ইন করুন' ?>
        </h2>
        
        <?php if($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <?php if($isRegister): ?>
                <div class="form-group">
                    <label for="fullname"><i class="fas fa-user"></i> পুরো নাম</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> ইমেইল (ঐচ্ছিক)</label>
                    <input type="email" id="email" name="email">
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username"><i class="fas fa-user-tag"></i> ইউজারনেম</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group password-container">
                <label for="password"><i class="fas fa-lock"></i> পাসওয়ার্ড</label>
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas <?= $isRegister ? 'fa-user-plus' : 'fa-sign-in-alt' ?>"></i>
                <?= $isRegister ? 'রেজিস্টার' : 'লগ ইন' ?>
            </button>
        </form>
        
        <div class="auth-footer">
            <?php if($isRegister): ?>
                ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php">লগ ইন করুন</a>
            <?php else: ?>
                নতুন ব্যবহারকারী? <a href="login.php?register">অ্যাকাউন্ট তৈরি করুন</a>
            <?php endif; ?>
        </div>
        
        <div class="social-login">
            <p>বা সোশ্যাল মিডিয়ার মাধ্যমে</p>
            <div class="social-icons">
                <a href="#" class="social-icon facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon google" title="Google"><i class="fab fa-google"></i></a>
                <a href="#" class="social-icon twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Auto-focus first input
        document.querySelector('input').focus();
    </script>
</body>
</html>