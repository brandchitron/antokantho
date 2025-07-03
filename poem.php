<?php
session_start();
require 'functions.php';

// Handle Like Action
if (isset($_GET['like'])) {
    if (!isset($_SESSION['user'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Login required']);
        exit;
    }

    $poemId = $_GET['id'] ?? '';
    $poem = getPoem($poemId);
    
    if (!$poem) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Poem not found']);
        exit;
    }

    $username = $_SESSION['user']['username'];
    
    // Initialize likes array if not exists
    if (!isset($poem['likes'])) {
        $poem['likes'] = [];
    }

    // Toggle like
    $liked = in_array($username, $poem['likes']);
    if ($liked) {
        // Remove like
        $poem['likes'] = array_diff($poem['likes'], [$username]);
    } else {
        // Add like
        $poem['likes'][] = $username;
    }

    savePoemData($poemId, $poem);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'liked' => !$liked,
        'likes' => count($poem['likes'])
    ]);
    exit;
}

// Handle Comment Submission
if (isset($_POST['comment_submit'])) {
    if (!isset($_SESSION['user'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Login required']);
        exit;
    }

    $poemId = $_POST['poem_id'] ?? '';
    $commentText = trim($_POST['comment'] ?? '');
    
    if (empty($poemId)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Poem ID required']);
        exit;
    }

    if (empty($commentText)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit;
    }

    $poem = getPoem($poemId);
    
    if (!$poem) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Poem not found']);
        exit;
    }

    // Initialize comments array if not exists
    if (!isset($poem['comments'])) {
        $poem['comments'] = [];
    }

    // Add new comment
    $newComment = [
        'author' => $_SESSION['user']['username'],
        'text' => htmlspecialchars($commentText),
        'timestamp' => time()
    ];

    array_unshift($poem['comments'], $newComment);
    savePoemData($poemId, $poem);

    // Return the new comment data
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'comment' => $newComment,
        'comment_count' => count($poem['comments'])
    ]);
    exit;
}

$poemId = $_GET['id'] ?? '';
$poem = getPoem($poemId);

if (!$poem) {
    header("Location: index.php");
    exit;
}

$author = getUserData($poem['author']);
$isOwner = isset($_SESSION['user']['username']) && $_SESSION['user']['username'] === $poem['author'];
$isAdmin = isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'];
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($poem['title']) ?> - অন্তঃকণ্ঠ</title>
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

        .verified-badge {
            color: #3498db;
            margin-left: 5px;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
        }

        .verified-badge i {
            font-size: 1.1em;
        }

        .verified-badge {
            position: relative;
        }

        .verified-badge:hover::after {
            content: "Verified User";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
            margin-bottom: 5px;
        }

        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-medium);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .main-header {
            background-color: var(--bg-white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 15px 0;
        }

        .logo-container {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
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

        .auth-buttons {
            display: flex;
            gap: 15px;
            margin-left: auto;
        }

        .btn {
            padding: 8px 20px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn.outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn.outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }

        .btn:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .poem-page {
            padding: 2rem 0;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .poem-full {
            background: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .poem-full:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .poem-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1.5rem;
            border: 3px solid var(--bg-white);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .author-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .author-info {
            flex: 1;
        }

        .author-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .author-name:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .poem-date {
            color: var(--text-light);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .poem-date i {
            font-size: 0.9rem;
        }

        .poem-title {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
            line-height: 1.3;
            font-weight: 700;
            position: relative;
            padding-bottom: 1rem;
        }

        .poem-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .poem-content {
            font-size: 1.25rem;
            line-height: 1.9;
            white-space: pre-line;
            margin-bottom: 2.5rem;
            color: var(--text-medium);
            padding: 0 1rem;
        }

        .poem-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            background: var(--bg-light);
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .action-btn i {
            font-size: 1.1rem;
        }

        .like-btn {
            color: var(--danger);
        }

        .like-btn.liked {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .comment-btn {
            color: var(--secondary);
        }

        .share-btn {
            color: var(--primary);
        }

        .edit-btn {
            color: var(--primary-dark);
            background: rgba(142, 68, 173, 0.1);
        }

        .delete-btn {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .comments-section {
            background: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
        }

        .section-title {
            font-size: 1.75rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .comment-form {
            margin-bottom: 2.5rem;
        }

        .comment-form textarea {
            width: 100%;
            padding: 1.25rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            min-height: 150px;
            font-family: inherit;
            font-size: 1rem;
            margin-bottom: 1.25rem;
            resize: vertical;
            transition: var(--transition);
        }

        .comment-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.2);
        }

        .btn-comment {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-comment:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .comment {
            padding: 1.5rem;
            background: var(--bg-light);
            border-radius: var(--radius-sm);
            transition: var(--transition);
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .comment:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .comment-author {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .comment-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--bg-white);
            box-shadow: var(--shadow-sm);
        }

        .comment-author-name {
            font-weight: 600;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .poem-page {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            padding: 2rem 0;
        }

        .comments-section {
            order: 2;
            width: 100%;
        }

        .poem-full {
            order: 1;
            width: 100%;
        }

        @media (min-width: 992px) {
            .poem-page {
                flex-direction: column;
            }
        }

        .comment-author-name:hover {
            color: var(--primary);
        }

        .comment-date {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .comment-content {
            color: var(--text-medium);
            line-height: 1.7;
            padding-left: 0.5rem;
        }

        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 20px rgba(142, 68, 173, 0.4);
            cursor: pointer;
            z-index: 10;
            transition: var(--transition);
            display: none;
        }

        .fab:hover {
            background: var(--primary-dark);
            transform: translateY(-5px) scale(1.05);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-light);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .poem-full, .comments-section {
                padding: 1.5rem;
            }
            
            .poem-header {
                flex-direction: column;
                text-align: center;
            }
            
            .author-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .poem-title {
                font-size: 2rem;
                text-align: center;
            }
            
            .poem-title:after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .poem-content {
                padding: 0;
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
                text-align: center;
            }
            
            .section-title:after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .poem-actions {
                justify-content: center;
            }
            
            .auth-buttons {
                gap: 10px;
            }
            
            .btn {
                padding: 6px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo-container">
                <a href="index.php" class="logo">অন্তঃকণ্ঠ</a>
            </div>
            
            <?php if(isset($_SESSION['user'])): ?>
                <div class="user-menu">
                    <img src="<?= getProfilePic($_SESSION['user']['username']) ?>" class="profile-pic" alt="<?= htmlspecialchars($_SESSION['user']['username']) ?>">
                    <div class="dropdown">
                        <a href="profile.php?user=<?= $_SESSION['user']['username'] ?>">প্রোফাইল</a>
                        <a href="post.php">নতুন কবিতা</a>
                        <a href="logout.php">লগ আউট</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn">লগ ইন</a>
                    <a href="login.php?register" class="btn outline">রেজিস্টার</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container poem-page">
        <article class="poem-full">
            <div class="poem-header">
                <img src="<?= getProfilePic($poem['author']) ?>" class="author-avatar" alt="<?= htmlspecialchars($poem['author']) ?>">
                <div class="author-info">
                    <a href="profile.php?user=<?= $poem['author'] ?>" class="author-name">
                        <?= htmlspecialchars($poem['author']) ?>
                        <?php if (isVerified($poem['author'])): ?>
                            <span class="verified-badge">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="poem-date">
                        <i class="far fa-clock"></i>
                        প্রকাশিত: <?= formatDate($poem['timestamp']) ?>
                        <?php if(isset($poem['last_updated'])): ?>
                            <br><i class="fas fa-pen-fancy"></i> আপডেট: <?= formatDate($poem['last_updated']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <h1 class="poem-title"><?= htmlspecialchars($poem['title']) ?></h1>
            
            <div class="poem-content">
                <?= nl2br(htmlspecialchars($poem['content'])) ?>
            </div>
            
            <div class="poem-actions">
                <button class="action-btn like-btn <?= isLiked($poemId) ? 'liked' : '' ?>" data-poem-id="<?= $poemId ?>">
                    <i class="fas fa-heart"></i> 
                    <span class="like-count"><?= count($poem['likes'] ?? []) ?></span>
                </button>
                
                <button class="action-btn comment-btn" onclick="document.getElementById('comment-textarea').focus()">
                    <i class="fas fa-comment"></i> 
                    <span class="comment-count"><?= count($poem['comments'] ?? []) ?></span>
                </button>
                
                <button class="action-btn share-btn" data-poem-id="<?= $poemId ?>">
                    <i class="fas fa-share-alt"></i> শেয়ার
                </button>
                
                <?php if($isOwner || $isAdmin): ?>
                    <a href="edit.php?id=<?= $poemId ?>" class="action-btn edit-btn">
                        <i class="fas fa-edit"></i> সম্পাদনা
                    </a>
                    
                    <button class="action-btn delete-btn" onclick="confirmDelete('<?= $poemId ?>')">
                        <i class="fas fa-trash-alt"></i> মুছুন
                    </button>
                <?php endif; ?>
            </div>
        </article>
        
        <section class="comments-section">
            <h2 class="section-title">
                <i class="far fa-comments"></i> মন্তব্যসমূহ
            </h2>
            
            <?php if(isset($_SESSION['user'])): ?>
                <form class="comment-form" method="POST" onsubmit="submitComment(event)">
                    <input type="hidden" name="poem_id" value="<?= $poemId ?>">
                    <input type="hidden" name="comment_submit" value="1">
                    <textarea id="comment-textarea" name="comment" placeholder="আপনার মন্তব্য লিখুন..." required></textarea>
                    <button type="submit" class="btn-comment">
                        <i class="fas fa-paper-plane"></i> মন্তব্য পোস্ট করুন
                    </button>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>মন্তব্য করতে <a href="login.php" style="color: var(--primary);">লগইন</a> করুন</p>
                </div>
            <?php endif; ?>
            
            <div class="comments-list">
                <?php foreach($poem['comments'] ?? [] as $comment): ?>
                    <div class="comment">
                        <div class="comment-author">
                            <img src="<?= getProfilePic($comment['author']) ?>" class="comment-avatar" alt="<?= htmlspecialchars($comment['author']) ?>">
                            <a href="profile.php?user=<?= $comment['author'] ?>" class="comment-author-name">
                                <?= htmlspecialchars($comment['author']) ?>
                                <?php if (isVerified($comment['author'])): ?>
                                    <span class="verified-badge">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <span class="comment-date">
                                <i class="far fa-clock"></i> <?= formatDate($comment['timestamp']) ?>
                            </span>
                        </div>
                        <div class="comment-content">
                            <?= htmlspecialchars($comment['text']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if(empty($poem['comments'])): ?>
                    <div class="empty-state">
                        <i class="far fa-comment-dots"></i>
                        <p>কোন মন্তব্য নেই। প্রথম মন্তব্য করুন!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <a href="#" class="fab" title="শীর্ষে যান">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <script>
        // Show/hide FAB based on scroll position
        window.addEventListener('scroll', () => {
            const fab = document.querySelector('.fab');
            if (window.scrollY > 300) {
                fab.style.display = 'flex';
            } else {
                fab.style.display = 'none';
            }
        });

        // Smooth scroll to top
        document.querySelector('.fab').addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Like button functionality
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const poemId = this.getAttribute('data-poem-id');
                const likeCount = this.querySelector('.like-count');
                
                fetch(`poem.php?id=${poemId}&like=1`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('liked');
                        likeCount.textContent = data.likes;
                    }
                });
            });
        });
        
        // Comment submission
        function submitComment(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            fetch('poem.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new comment to the list
                    const commentList = document.querySelector('.comments-list');
                    const emptyState = commentList.querySelector('.empty-state');
                    
                    if (emptyState) {
                        emptyState.remove();
                    }
                    
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'comment';
                    commentDiv.innerHTML = `
                        <div class="comment-author">
                            <img src="${getProfilePic(data.comment.author)}" class="comment-avatar" alt="${data.comment.author}">
                            <a href="profile.php?user=${data.comment.author}" class="comment-author-name">${data.comment.author}</a>
                            <span class="comment-date">
                                <i class="far fa-clock"></i> ${formatDate(data.comment.timestamp)}
                            </span>
                        </div>
                        <div class="comment-content">
                            ${data.comment.text}
                        </div>
                    `;
                    
                    commentList.prepend(commentDiv);
                    form.reset();
                    
                    // Update comment count
                    const commentCount = document.querySelector('.comment-count');
                    if (commentCount) {
                        commentCount.textContent = data.comment_count;
                    }
                }
            });
        }

        // Share button functionality
        document.querySelectorAll('.share-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const poemId = this.getAttribute('data-poem-id');
                const shareUrl = `${window.location.origin}/poem.php?id=${poemId}`;
                
                if (navigator.share) {
                    navigator.share({
                        title: '<?= htmlspecialchars($poem['title']) ?>',
                        text: 'এই সুন্দর কবিতাটি পড়ুন:',
                        url: shareUrl,
                    })
                    .catch(err => {
                        console.log('Error sharing:', err);
                        fallbackCopy(shareUrl);
                    });
                } else {
                    fallbackCopy(shareUrl);
                }
            });
        });
        
        function fallbackCopy(shareUrl) {
            const el = document.createElement('textarea');
            el.value = shareUrl;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('লিংক কপি করা হয়েছে!');
        }

        function confirmDelete(poemId) {
            if (confirm('আপনি কি নিশ্চিত এই কবিতাটি মুছতে চান?')) {
                fetch(`api/delete_poem.php?id=${poemId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'profile.php?user=<?= $_SESSION['user']['username'] ?? '' ?>';
                    }
                });
            }
        }

        // Helper functions for JavaScript
        function getProfilePic(username) {
            return `data/users/${username}.json`; // This should be replaced with actual profile pic URL logic
        }

        function formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleString();
        }
    </script>
</body>
</html>