<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = $_POST['category'] ?? 'Other';
    
    if (empty($title) || empty($content)) {
        $error = "শিরোনাম এবং কবিতার বিষয়বস্তু আবশ্যক";
    } else {
        $poemData = [
            'title' => $title,
            'content' => $content,
            'author' => $_SESSION['user']['username'],
            'category' => $category,
            'timestamp' => date('Y-m-d H:i:s'),
            'likes' => [],
            'comments' => []
        ];
        
        $poemId = savePoem($poemData);
        
        if ($poemId) {
            $success = "কবিতা সফলভাবে প্রকাশিত হয়েছে!";
            $_POST = []; // Clear form
        } else {
            $error = "কবিতা প্রকাশ করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন কবিতা - অন্তঃকণ্ঠ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8e44ad;
            --primary-dark: #732d91;
            --primary-light: #e8d6f0;
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
            max-width: 800px;
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

        /* Post Page Styles */
        .post-page {
            padding: 2rem 0;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .post-page h1 {
            font-size: 2.2rem;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        .post-page h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
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
        form {
            background-color: var(--bg-white);
            padding: 2rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }

        form:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.2);
        }

        .form-group textarea {
            min-height: 300px;
            resize: vertical;
            line-height: 1.7;
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
            margin-top: 1rem;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .btn i {
            font-size: 1.1rem;
        }

        /* Character Counter */
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .post-page h1 {
                font-size: 1.8rem;
            }
            
            form {
                padding: 1.5rem;
            }
            
            .form-group textarea {
                min-height: 250px;
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

    <main class="container post-page">
        <h1><i class="fas fa-pen-fancy"></i> নতুন কবিতা লিখুন</h1>
        
        <?php if($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php elseif($success): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="poem-form">
            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> শিরোনাম</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required maxlength="100">
                <div class="char-counter"><span id="title-counter">0</span>/100</div>
            </div>
            
            <div class="form-group">
                <label for="category"><i class="fas fa-tag"></i> ধরণ</label>
                <select id="category" name="category">
                    <option value="Love" <?= ($_POST['category'] ?? '') === 'Love' ? 'selected' : '' ?>>প্রেম</option>
                    <option value="Nature" <?= ($_POST['category'] ?? '') === 'Nature' ? 'selected' : '' ?>>প্রকৃতি</option>
                    <option value="Life" <?= ($_POST['category'] ?? '') === 'Life' ? 'selected' : '' ?>>জীবন</option>
                    <option value="Philosophy" <?= ($_POST['category'] ?? '') === 'Philosophy' ? 'selected' : '' ?>>দর্শন</option>
                    <option value="Other" <?= ($_POST['category'] ?? 'Other') === 'Other' ? 'selected' : '' ?>>অন্যান্য</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content"><i class="fas fa-pen"></i> কবিতার বিষয়বস্তু</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                <div class="char-counter"><span id="content-counter">0</span>/5000</div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> প্রকাশ করুন
            </button>
        </form>
    </main>

    <script>
        // Character counters
        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');
        const titleCounter = document.getElementById('title-counter');
        const contentCounter = document.getElementById('content-counter');
        
        // Initialize counters
        titleCounter.textContent = titleInput.value.length;
        contentCounter.textContent = contentInput.value.length;
        
        // Update counters on input
        titleInput.addEventListener('input', () => {
            titleCounter.textContent = titleInput.value.length;
        });
        
        contentInput.addEventListener('input', () => {
            contentCounter.textContent = contentInput.value.length;
        });
        
        // Prevent form submission if content is too long
        document.getElementById('poem-form').addEventListener('submit', (e) => {
            if (titleInput.value.length > 100) {
                e.preventDefault();
                alert('শিরোনাম ১০০ অক্ষরের বেশি হতে পারবে না');
                return;
            }
            
            if (contentInput.value.length > 5000) {
                e.preventDefault();
                alert('কবিতার বিষয়বস্তু ৫০০০ অক্ষরের বেশি হতে পারবে না');
                return;
            }
        });
        
        // Auto-resize textarea
        contentInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Initialize textarea height
        contentInput.style.height = 'auto';
        contentInput.style.height = (contentInput.scrollHeight) + 'px';
    </script>
</body>
</html>