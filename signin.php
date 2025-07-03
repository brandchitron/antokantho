<?php
session_start();
require 'functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $userRef = getUserData($username);
    
    if($userRef && validate_password($password, $userRef['password'])) {
        $_SESSION['user'] = $userRef;
        header('Location: index.php');
        exit();
    } else {
        $error = "ভুল ইউজারনেম বা পাসওয়ার্ড";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>লগইন - অন্তঃকণ্ঠ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* signup.php এর স্টাইল রিইউজ করুন */
        <?php include 'signup.css'; ?>
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <i class="fas fa-heart"></i>
            <span>অন্তঃকণ্ঠ</span>
        </div>
        
        <h2>লগইন করুন</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">ইউজারনেম</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">পাসওয়ার্ড</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn">লগইন করুন</button>
        </form>
        
        <div class="auth-footer">
            অ্যাকাউন্ট নেই? <a href="signup.php">রেজিস্টার করুন</a>
        </div>
        
        <?php if($_SERVER['REMOTE_ADDR'] === '127.0.0.1'): ?>
            <div class="dev-login">
                <h3>ডেভেলপার লগইন</h3>
                <p>দ্রুত টেস্টিং এর জন্য:</p>
                <div class="dev-login-links">
                    <a href="login.php?devlogin&user=chitronbhattacharjee" class="dev-link">chitronbhattacharjee</a>
                    <a href="login.php?devlogin&user=testuser1" class="dev-link">testuser1</a>
                    <a href="login.php?devlogin&user=testuser2" class="dev-link">testuser2</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>