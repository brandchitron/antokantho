<?php
session_start();
require 'functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    
    // Basic validation
    if(empty($username) || empty($password) || empty($email)) {
        $error = "সব ফিল্ড পূরণ করুন";
    } else {
        // Check if username exists
        $userRef = getUserData($username);
        
        if($userRef) {
            $error = "ইউজারনেম ইতিমধ্যে ব্যবহৃত";
        } else {
            // Create user
            $userData = [
                'username' => $username,
                'password' => base64_encrypt($password),
                'email' => $email,
                'fullname' => $fullname,
                'join_date' => date('Y-m-d'),
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
            
            saveUserData($username, $userData);
            $_SESSION['user'] = $userData;
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>রেজিস্ট্রেশন - অন্তঃকণ্ঠ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a3093;
            --secondary: #9b59b6;
            --text: #333;
            --light: #f8f9fa;
            --border: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Hind Siliguri', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .auth-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            margin: 20px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logo i {
            font-size: 3rem;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            text-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            color: var(--text);
        }
        
        .form-control:focus {
            outline: 2px solid var(--primary);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: white;
            color: var(--primary);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 1rem;
        }
        
        .auth-footer a {
            color: white;
            font-weight: 600;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            margin-top: 10px;
            height: 5px;
            background: #ddd;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s, background 0.3s;
        }
        
        .strength-text {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        @media (max-width: 576px) {
            .auth-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <i class="fas fa-heart"></i>
            <span>অন্তঃকণ্ঠ</span>
        </div>
        
        <h2>অ্যাকাউন্ট তৈরি করুন</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="fullname">পুরো নাম</label>
                <input type="text" id="fullname" name="fullname" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="username">ইউজারনেম</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">ইমেইল</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">পাসওয়ার্ড</label>
                <input type="password" id="password" name="password" class="form-control" required 
                       oninput="checkPasswordStrength(this.value)">
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <div class="strength-text" id="strength-text">পাসওয়ার্ড শক্তি: খুব দুর্বল</div>
            </div>
            
            <button type="submit" class="btn">রেজিস্ট্রেশন সম্পন্ন করুন</button>
        </form>
        
        <div class="auth-footer">
            ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php">লগ ইন করুন</a>
        </div>
    </div>
    
    <script>
        function checkPasswordStrength(password) {
            const meter = document.getElementById('strength-meter');
            const text = document.getElementById('strength-text');
            
            let strength = 0;
            if (password.length > 0) strength = 1;
            if (password.length > 6) strength = 2;
            if (password.length > 8 && /[A-Z]/.test(password)) strength = 3;
            if (password.length > 10 && /[A-Z]/.test(password) && /[0-9]/.test(password)) strength = 4;
            if (password.length > 12 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength = 5;
            
            const colors = ['#e74c3c', '#f39c12', '#f1c40f', '#2ecc71', '#27ae60'];
            const texts = ['খুব দুর্বল', 'দুর্বল', 'মধ্যম', 'শক্তিশালী', 'খুব শক্তিশালী'];
            
            meter.style.width = `${(strength / 5) * 100}%`;
            meter.style.backgroundColor = colors[strength - 1] || '#e74c3c';
            text.textContent = `পাসওয়ার্ড শক্তি: ${texts[strength - 1] || 'খুব দুর্বল'}`;
            text.style.color = colors[strength - 1] || '#e74c3c';
        }
    </script>
</body>
</html>