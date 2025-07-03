<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require 'functions.php';

// Handle Like Action
if (isset($_POST['action']) && $_POST['action'] === 'like') {
    if (!isset($_SESSION['user'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Login required']);
        exit;
    }

    $poemId = $_POST['poem_id'] ?? '';
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
if (isset($_POST['action']) && $_POST['action'] === 'comment') {
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

// র্যান্ডম কবিতা লোড
$poems = getRandomPoems(10);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অন্তঃকণ্ঠ - কবিতার সামাজিক নেটওয়ার্ক</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* Enhanced CSS */
    :root {
      --primary-color: #8e44ad;
      --primary-dark: #732d91;
      --secondary-color: #3498db;
      --text-color: #2c3e50;
      --light-text: #7f8c8d;
      --bg-color: #f9f9f9;
      --card-bg: #ffffff;
      --border-color: #e0e0e0;
      --like-color: #e74c3c;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --border-radius: 8px;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    /* Verified Badge Styles */
    .verified-badge {
        color: var(--secondary-color);
        margin-left: 5px;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
    }
    
    .verified-badge i {
        font-size: 1em;
    }
    
    .verified-badge {
        position: relative;
    }
    
    .verified-badge:hover::after {
        content: "Verified Writer";
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--text-color);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 10;
        margin-bottom: 5px;
        font-family: 'Hind Siliguri', sans-serif;
    }
    
    .author-name {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    body {
      font-family: 'Hind Siliguri', sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
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
    
    /* Header Styles */
    .main-header {
      background-color: var(--card-bg);
      box-shadow: var(--shadow);
      position: sticky;
      top: 0;
      z-index: 100;
      padding: 15px 0;
    }
    
    .logo-container {
    margin-right: auto;
    }
    
    .container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: flex-start; /* পরিবর্তিত লাইন */
    align-items: center;
    position: relative;
    }
    
    .logo {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary-color);
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
      border: 2px solid var(--primary-color);
      transition: transform 0.3s ease;
    }
    
    .profile-pic:hover {
      transform: scale(1.05);
    }
    
    .dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background-color: var(--card-bg);
      box-shadow: var(--shadow);
      border-radius: var(--border-radius);
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
      color: var(--text-color);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .dropdown a:hover {
      background-color: var(--bg-color);
      color: var(--primary-color);
    }
    
    .auth-buttons {
      display: flex;
      gap: 15px;
      margin-left: auto;
    }
    
    .btn {
      padding: 8px 20px;
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn.outline {
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
      background: transparent;
    }
    
    .btn.outline:hover {
      background-color: var(--primary-color);
      color: white;
    }
    
    .btn {
      background-color: var(--primary-color);
      color: white;
      border: 2px solid var(--primary-color);
    }
    
    .btn:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }
    
    /* Main Content */
    main {
      padding: 30px 0;
    }
    
    .feed {
      display: flex;
      flex-direction: column;
      gap: 25px;
      max-width: 800px;
      margin: 0 auto;
    }
    
    /* Poem Card */
    .poem-card {
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .poem-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }
    
    .poem-header {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .author-pic {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 15px;
      border: 2px solid var(--primary-color);
    }
    
    .author-name {
      font-weight: 600;
      color: var(--primary-color);
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .author-name:hover {
      color: var(--primary-dark);
      text-decoration: underline;
    }
    
    .poem-date {
      font-size: 14px;
      color: var(--light-text);
      display: block;
      margin-top: 2px;
    }
    
    .poem-content h3 {
      margin-bottom: 10px;
    }
    
    .poem-content h3 a {
      color: var(--text-color);
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .poem-content h3 a:hover {
      color: var(--primary-color);
    }
    
    .poem-content p {
      color: var(--text-color);
      margin-bottom: 15px;
      white-space: pre-line;
      line-height: 1.8;
    }
    
    /* Poem Actions */
    .poem-actions {
      display: flex;
      gap: 15px;
      padding-top: 15px;
      border-top: 1px solid var(--border-color);
    }
    
    .poem-actions button {
      background: none;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      font-family: 'Hind Siliguri', sans-serif;
      font-size: 15px;
      color: var(--light-text);
      transition: all 0.3s ease;
      padding: 5px 10px;
      border-radius: 4px;
    }
    
    .poem-actions button:hover {
      background-color: rgba(0, 0, 0, 0.05);
      color: var(--text-color);
    }
    
    .like-btn.liked, .like-btn.liked:hover {
      color: var(--like-color);
    }
    
    .like-btn.liked i {
      font-weight: 900;
    }
    
    .comment-btn:hover {
      color: var(--secondary-color);
    }
    
    .share-btn:hover {
      color: var(--primary-color);
    }
    
    /* Empty Feed */
    .empty-feed {
      text-align: center;
      padding: 50px 20px;
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
    }
    
    .empty-feed p {
      font-size: 18px;
      color: var(--light-text);
      margin-bottom: 20px;
    }
    
    .empty-feed a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
    }
    
    .empty-feed a:hover {
      text-decoration: underline;
    }
    
    /* See More Button Styles */
    .poem-text {
      max-height: 150px;
      overflow: hidden;
      position: relative;
      transition: all 0.3s ease;
    }
    
    .poem-text.expanded {
      max-height: none;
    }
    
    .see-more-btn {
      display: block;
      text-align: center;
      margin-top: 10px;
      color: var(--primary-color);
      cursor: pointer;
      font-weight: 500;
      background: none;
      border: none;
      font-family: 'Hind Siliguri', sans-serif;
      font-size: 15px;
      padding: 5px;
    }
    
    .see-more-btn:hover {
      text-decoration: underline;
    }
    
    .poem-text:not(.expanded)::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 50px;
      background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
    }
    
    /* Comments Section */
    .comments-section {
      display: none;
      margin-top: 15px;
      border-top: 1px solid var(--border-color);
      padding-top: 15px;
    }
    
    .comment-form {
      margin-bottom: 15px;
    }
    
    .comment-input {
      width: 100%;
      padding: 10px;
      border-radius: 4px;
      border: 1px solid var(--border-color);
      margin-bottom: 10px;
      font-family: 'Hind Siliguri', sans-serif;
      resize: vertical;
      min-height: 80px;
    }
    
    .submit-comment {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 4px;
      cursor: pointer;
      font-family: 'Hind Siliguri', sans-serif;
      transition: background-color 0.3s ease;
    }
    
    .submit-comment:hover {
      background-color: var(--primary-dark);
    }
    
    .comments-list {
      margin-top: 15px;
    }
    
    .comment {
      padding: 10px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 10px;
    }
    
    .comment-author {
      display: flex;
      align-items: center;
      margin-bottom: 5px;
    }
    
    .comment-avatar {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      margin-right: 10px;
      object-fit: cover;
    }
    
    .comment-date {
      margin-left: 10px;
      font-size: 12px;
      color: var(--light-text);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .container {
        padding: 0 15px;
      }
      
      .logo {
        font-size: 24px;
      }
      
      .auth-buttons {
        gap: 10px;
      }
      
      .btn {
        padding: 6px 15px;
        font-size: 14px;
      }
      
      .poem-card {
        padding: 15px;
      }
      
      .poem-actions {
        gap: 10px;
        flex-wrap: wrap;
      }
      
      .poem-actions button {
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
                    <img src="<?= getProfilePic($_SESSION['user']['username']) ?>" class="profile-pic">
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

    <main class="container">
        <div class="feed">
            <?php if(!empty($poems)): ?>
                <?php foreach($poems as $poem): ?>
                    <div class="poem-card" data-id="<?= $poem['id'] ?>">
                        <div class="poem-header">
                            <img src="<?= getProfilePic($poem['author']) ?>" class="author-pic">
                            <div>
                                <a href="profile.php?user=<?= $poem['author'] ?>" class="author-name">
                                    <?= $poem['author'] ?>
                                    <?php if (isVerified($poem['author'])): ?>
                                        <span class="verified-badge">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <span class="poem-date"><?= formatDate($poem['timestamp']) ?></span>
                            </div>
                        </div>
                        <div class="poem-content">
                            <h3><a href="poem.php?id=<?= $poem['id'] ?>"><?= htmlspecialchars($poem['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                            <div class="poem-text">
                                <?= nl2br(htmlspecialchars($poem['content'], ENT_QUOTES, 'UTF-8')) ?>
                            </div>
                            <button class="see-more-btn">আরো দেখুন</button>
                        </div>
                        <div class="poem-actions">
                            <button class="like-btn <?= isLiked($poem['id']) ? 'liked' : '' ?>" data-poem-id="<?= $poem['id'] ?>">
                                <i class="fas fa-heart"></i> <span class="like-count"><?= count($poem['likes'] ?? []) ?></span>
                            </button>
                            <button class="comment-btn" data-poem-id="<?= $poem['id'] ?>">
                                <i class="fas fa-comment"></i> <span class="comment-count"><?= count($poem['comments'] ?? []) ?></span>
                            </button>
                            <button class="share-btn" data-poem-id="<?= $poem['id'] ?>">
                                <i class="fas fa-share-alt"></i> শেয়ার
                            </button>
                        </div>
                        
                        <!-- Comments Section -->
                        <div class="comments-section" id="comments-<?= $poem['id'] ?>">
                            <?php if(isset($_SESSION['user'])): ?>
                                <div class="comment-form">
                                    <textarea class="comment-input" placeholder="আপনার মন্তব্য লিখুন..."></textarea>
                                    <button class="submit-comment" data-poem-id="<?= $poem['id'] ?>">পোস্ট করুন</button>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; margin-bottom: 15px;">
                                    মন্তব্য করতে <a href="login.php" style="color: var(--primary-color);">লগইন</a> করুন
                                </p>
                            <?php endif; ?>
                            <div class="comments-list">
                                <?php foreach($poem['comments'] ?? [] as $comment): ?>
                                    <div class="comment">
                                        <div class="comment-author">
                                            <img src="<?= getProfilePic($comment['author']) ?>" class="comment-avatar">
                                            <a href="profile.php?user=<?= $comment['author'] ?>" class="author-name"><?= $comment['author'] ?></a>
                                            <span class="comment-date"><i class="far fa-clock"></i> <?= formatDate($comment['timestamp']) ?></span>
                                        </div>
                                        <p><?= htmlspecialchars($comment['text'], ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if(empty($poem['comments'])): ?>
                                    <p style="text-align: center; color: var(--light-text);">কোন মন্তব্য নেই</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-feed">
                    <p>কোনো কবিতা পাওয়া যায়নি। প্রথম কবিতা লিখতে <a href="post.php">এখানে ক্লিক করুন</a></p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    // Helper functions
    function getProfilePic(username) {
        return `data/users/${username}.json`; // This should be replaced with actual profile pic URL logic
    }

    function formatDate(timestamp) {
        const date = new Date(timestamp * 1000);
        return date.toLocaleString();
    }

    // See More functionality
    document.querySelectorAll('.see-more-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemText = this.previousElementSibling;
            poemText.classList.toggle('expanded');
            this.textContent = poemText.classList.contains('expanded') ? 'কম দেখুন' : 'আরো দেখুন';
        });
    });

    // Like functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemId = this.getAttribute('data-poem-id');
            const likeCount = this.querySelector('.like-count');
            
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=like&poem_id=${poemId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.classList.toggle('liked');
                    likeCount.textContent = data.likes;
                    
                    // Update heart icon
                    const icon = this.querySelector('i');
                    if(data.liked) {
                        icon.classList.add('fas');
                        icon.classList.remove('far');
                    } else {
                        icon.classList.add('far');
                        icon.classList.remove('fas');
                    }
                } else {
                    alert(data.message);
                    if(data.message === 'Login required') {
                        window.location.href = 'login.php';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Comment toggle functionality
    document.querySelectorAll('.comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemId = this.getAttribute('data-poem-id');
            const commentsSection = document.getElementById(`comments-${poemId}`);
            
            if(commentsSection.style.display === 'none') {
                commentsSection.style.display = 'block';
            } else {
                commentsSection.style.display = 'none';
            }
        });
    });

    // Submit comment functionality
    document.querySelectorAll('.submit-comment').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemId = this.getAttribute('data-poem-id');
            const commentInput = this.parentElement.querySelector('.comment-input');
            const commentText = commentInput.value.trim();
            const commentsList = this.parentElement.nextElementSibling;
            const commentCount = document.querySelector(`.comment-btn[data-poem-id="${poemId}"] .comment-count`);
            
            if(commentText === '') {
                alert('মন্তব্য খালি থাকতে পারে না');
                return;
            }
            
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=comment&poem_id=${poemId}&comment=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Add new comment to the list
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <div class="comment-author">
                            <img src="${getProfilePic(data.comment.author)}" class="comment-avatar">
                            <a href="profile.php?user=${data.comment.author}" class="author-name">${data.comment.author}</a>
                            <span class="comment-date"><i class="far fa-clock"></i> ${formatDate(data.comment.timestamp)}</span>
                        </div>
                        <p>${data.comment.text}</p>
                    `;
                    
                    commentsList.insertBefore(newComment, commentsList.firstChild);
                    commentInput.value = '';
                    
                    // Update comment count
                    commentCount.textContent = data.comment_count;
                    
                    // If this is the first comment, remove the empty state
                    if(commentsList.querySelector('p')) {
                        commentsList.querySelector('p').remove();
                    }
                } else {
                    alert(data.message);
                    if(data.message === 'Login required') {
                        window.location.href = 'login.php';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Share button functionality
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemId = this.getAttribute('data-poem-id');
            const shareUrl = `${window.location.origin}/poem.php?id=${poemId}`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'অন্তঃকণ্ঠ - কবিতার সামাজিক নেটওয়ার্ক',
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

    // Initialize comment sections as hidden
    document.querySelectorAll('.comments-section').forEach(section => {
        section.style.display = 'none';
    });
    </script>
</body>
</html>