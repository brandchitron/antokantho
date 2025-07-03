<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user']['username'];
$userData = getUserData($username);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $bio = trim($_POST['bio']);
    $email = trim($_POST['email']);
    
    $userData['fullname'] = $fullname;
    $userData['bio'] = $bio;
    $userData['email'] = $email;
    
    // Update profile picture
    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = 'assets/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $filename = $username . '.' . $ext;
        $targetFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            $userData['profile_pic'] = $targetFile;
        }
    }
    
    saveUserData($username, $userData);
    $_SESSION['user'] = $userData;
    
    $success = "প্রোফাইল সফলভাবে আপডেট হয়েছে!";
}

// Process account deletion
if (isset($_POST['delete_account'])) {
    // Delete all poems
    $files = glob("data/posts/*.json");
    foreach ($files as $file) {
        $poem = json_decode(file_get_contents($file), true);
        if ($poem && isset($poem['author']) && $poem['author'] === $username) {
            unlink($file);
        }
    }
    
    // Delete user file
    unlink("data/users/{$username}.json");
    
    // Destroy session
    session_destroy();
    
    // Redirect to homepage
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্রোফাইল সম্পাদনা - অন্তঃকণ্ঠ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8e44ad;
            --primary-dark: #732d91;
            --primary-light: #f3e6f8;
            --danger: #e74c3c;
            --danger-light: #fde8e8;
            --success: #2ecc71;
            --success-light: #e8f5e9;
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .main-header {
            background-color: var(--bg-white);
            box-shadow: var(--shadow-sm);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-block;
        }

        .user-menu {
            display: flex;
            align-items: center;
            position: relative;
            margin-left: auto;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid var(--primary);
            transition: var(--transition);
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--bg-white);
            box-shadow: var(--shadow-md);
            border-radius: var(--radius-sm);
            padding: 10px 0;
            min-width: 180px;
            display: none;
            z-index: 10;
        }

        .user-menu:hover .dropdown {
            display: block;
        }

        .dropdown a {
            display: block;
            padding: 8px 20px;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .dropdown a:hover {
            background-color: var(--bg-light);
            color: var(--primary);
        }

        /* Edit Profile Container */
        .edit-profile-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
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

        /* Profile Header */
        .profile-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 2.5rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--bg-white) 100%);
            border-bottom: 1px solid var(--border);
        }

        .profile-avatar-container {
            position: relative;
            margin-right: 2rem;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--bg-white);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .profile-avatar:hover {
            transform: scale(1.03);
        }

        .profile-info {
            flex: 1;
            min-width: 250px;
        }

        .profile-info h1 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .username {
            display: inline-block;
            font-size: 1.1rem;
            color: var(--primary);
            background: rgba(142, 68, 173, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            margin-bottom: 1rem;
        }

        /* Form Styles */
        .form-content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.2);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 1.7;
        }

        /* File Input */
        .file-input-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .file-input-label {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary-light);
            color: var(--primary-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            border: 1px dashed var(--primary);
        }

        .file-input-label:hover {
            background: rgba(142, 68, 173, 0.2);
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        /* Success Message */
        .success-message {
            background-color: var(--success-light);
            color: var(--success);
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
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

        /* Danger Zone */
        .danger-zone {
            margin-top: 3rem;
            padding: 2rem;
            background-color: var(--danger-light);
            border-radius: var(--radius-md);
            border-left: 5px solid var(--danger);
        }

        .danger-zone h3 {
            color: var(--danger);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .danger-zone p {
            margin-bottom: 1.5rem;
            color: var(--text-medium);
            line-height: 1.7;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c53030;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }
            
            .profile-avatar-container {
                margin-right: 0;
                margin-bottom: 1.5rem;
            }
            
            .form-content {
                padding: 1.5rem;
            }
            
            .danger-zone {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo">অন্তঃকণ্ঠ</a>
            <div class="user-menu">
                <img src="<?= getProfilePic($_SESSION['user']['username']) ?>" class="profile-pic" alt="<?= htmlspecialchars($_SESSION['user']['username']) ?>">
                <div class="dropdown">
                    <a href="profile.php?user=<?= $_SESSION['user']['username'] ?>">প্রোফাইল</a>
                    <a href="post.php">নতুন কবিতা</a>
                    <a href="logout.php">লগ আউট</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="edit-profile-container">
            <div class="profile-header">
                <div class="profile-avatar-container">
                    <img src="<?= getProfilePic($username) ?>" class="profile-avatar" id="profile-avatar" alt="<?= htmlspecialchars($userData['fullname']) ?>">
                </div>
                <div class="profile-info">
                    <h1><?= htmlspecialchars($userData['fullname']) ?></h1>
                    <span class="username">@<?= htmlspecialchars($username) ?></span>
                </div>
            </div>
            
            <div class="form-content">
                <?php if(isset($success)): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_pic">প্রোফাইল ছবি</label>
                        <div class="file-input-container">
                            <label for="profile_pic" class="file-input-label">
                                <i class="fas fa-camera"></i> ছবি নির্বাচন করুন
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" class="file-input" onchange="previewImage(event)">
                            <div class="file-name" id="file-name">কোন ফাইল নির্বাচন করা হয়নি</div>
                        </div>
                        <p class="help-text">জেপিজি, পিএনজি বা জিআইফি ফরম্যাট, সর্বোচ্চ ২এমবি</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="fullname">পুরো নাম</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" value="<?= htmlspecialchars($userData['fullname']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">ইমেইল</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">আপনার সম্পর্কে</label>
                        <textarea id="bio" name="bio" class="form-control"><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> প্রোফাইল আপডেট করুন
                    </button>
                </form>
                
                <div class="danger-zone">
                    <h3><i class="fas fa-exclamation-triangle"></i> ডেঞ্জার জোন</h3>
                    <p>এই অ্যাকাউন্ট মুছে ফেলার মাধ্যমে আপনি আপনার সমস্ত কবিতা এবং ডেটা স্থায়ীভাবে মুছে ফেলবেন। এই কাজটি পূর্বাবস্থায় ফেরানো যাবে না।</p>
                    
                    <form method="POST" onsubmit="return confirmDelete()">
                        <button type="submit" name="delete_account" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> অ্যাকাউন্ট মুছে ফেলুন
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Profile picture preview
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-avatar').src = e.target.result;
                    document.getElementById('file-name').textContent = file.name;
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Confirm account deletion
        function confirmDelete() {
            return confirm('আপনি কি নিশ্চিত যে আপনি আপনার অ্যাকাউন্ট স্থায়ীভাবে মুছে ফেলতে চান?\n\nএই কাজটি পূর্বাবস্থায় ফেরানো যাবে না এবং আপনার সমস্ত কবিতা মুছে যাবে।');
        }
    </script>
</body>
</html>