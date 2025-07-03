<?php
session_start();
require 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get poem ID from URL
$poemId = $_GET['id'] ?? '';
if (empty($poemId)) {
    header("Location: index.php");
    exit;
}

// Get poem data
$poem = getPoem($poemId);
if (!$poem) {
    header("Location: index.php");
    exit;
}

// Check if the current user is the author or admin
$isOwner = $_SESSION['user']['username'] === $poem['author'];
$isAdmin = isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'];

if (!$isOwner && !$isAdmin) {
    header("Location: poem.php?id=" . $poemId);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validate inputs
    if (empty($title) || empty($content)) {
        $error = "শিরোনাম এবং কবিতা উভয়ই প্রয়োজন";
    } else {
        // Update poem data
        $poem['title'] = $title;
        $poem['content'] = $content;
        $poem['last_updated'] = time();
        
        // Save updated poem
        if (savePoemData($poemId, $poem)) {
            header("Location: poem.php?id=" . $poemId);
            exit;
        } else {
            $error = "কবিতা আপডেট করতে সমস্যা হয়েছে";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কবিতা সম্পাদনা - অন্তঃকণ্ঠ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

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

        .edit-page {
            padding: 2rem 0;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .edit-form {
            background-color: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .form-title {
            font-size: 1.75rem;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .form-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
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
            min-height: 300px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .error-message {
            color: var(--danger);
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .edit-form {
                padding: 1.5rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo">অন্তঃকণ্ঠ</a>
            <?php if(isset($_SESSION['user'])): ?>
                <div class="user-menu">
                    <img src="<?= getProfilePic($_SESSION['user']['username']) ?>" class="profile-pic" alt="<?= htmlspecialchars($_SESSION['user']['username']) ?>">
                    <div class="dropdown">
                        <a href="profile.php?user=<?= $_SESSION['user']['username'] ?>">প্রোফাইল</a>
                        <a href="post.php">নতুন কবিতা</a>
                        <a href="logout.php">লগ আউট</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container edit-page">
        <div class="edit-form">
            <h1 class="form-title"><i class="fas fa-edit"></i> কবিতা সম্পাদনা</h1>
            
            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title" class="form-label">শিরোনাম</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-control" 
                        value="<?= htmlspecialchars($poem['title'] ?? '') ?>" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="content" class="form-label">কবিতা</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        class="form-control" 
                        required
                    ><?= htmlspecialchars($poem['content'] ?? '') ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> সংরক্ষণ করুন
                    </button>
                    <a href="poem.php?id=<?= $poemId ?>" class="btn btn-outline">
                        <i class="fas fa-times"></i> বাতিল করুন
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Focus on title field when page loads
        document.getElementById('title').focus();
    </script>
</body>
</html>