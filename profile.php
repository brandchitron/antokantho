<?php
session_start();
require 'functions.php';

$profileUser = isset($_GET['user']) ? $_GET['user'] : (isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : '');
$userData = getUserData($profileUser);

if (!$userData) {
    header("Location: index.php");
    exit;
}

$isOwner = isset($_SESSION['user']['username']) && $_SESSION['user']['username'] === $profileUser;
$poems = [];

// Load user's poems
$files = glob("data/posts/*.json");
foreach ($files as $file) {
    $poem = json_decode(file_get_contents($file), true);
    if ($poem && isset($poem['author']) && $poem['author'] === $profileUser) {
        $poem['id'] = basename($file, '.json');
        $poems[] = $poem;
    }
}

// Sort poems by date
usort($poems, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($userData['fullname']) ?> - অন্তঃকণ্ঠ</title>
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

/* Tooltip for verified badge */
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

        /* Profile Page Styles */
        .profile-page {
            padding: 2rem 0;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Profile Header */
        .profile-header {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            align-items: center;
            background-color: var(--bg-white);
            padding: 2.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            z-index: 0;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--bg-white);
            box-shadow: var(--shadow-md);
            position: relative;
            z-index: 1;
            transition: var(--transition);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .profile-info {
            flex: 1;
            min-width: 250px;
            position: relative;
            z-index: 1;
        }

        .profile-info h1 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .username {
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 1rem;
            display: inline-block;
            background: var(--primary-light);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
        }

        .bio {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--text-medium);
            line-height: 1.7;
        }

        .join-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .profile-actions {
            margin-left: auto;
            position: relative;
            z-index: 1;
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

        /* Profile Content */
        .profile-content {
            background-color: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .profile-content h2 {
            font-size: 1.75rem;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .profile-content h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        /* Poem List */
        .poem-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .poem-card {
            background-color: var(--bg-light);
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .poem-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            background-color: var(--bg-white);
        }

        .poem-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }

        .poem-card h3 a {
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .poem-card h3 a:hover {
            color: var(--primary);
        }

        .poem-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .poem-meta .likes {
            color: var(--danger);
        }

        .poem-excerpt {
            color: var(--text-medium);
            line-height: 1.7;
        }

        .no-poems {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
            font-size: 1.1rem;
        }

        /* Stats Section */
        .profile-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            flex: 1;
            background: var(--bg-white);
            padding: 1rem;
            border-radius: var(--radius-sm);
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem;
                text-align: center;
                justify-content: center;
            }
            
            .profile-info {
                text-align: center;
            }
            
            .profile-actions {
                margin-left: 0;
                width: 100%;
                justify-content: center;
            }
            
            .profile-content {
                padding: 1.5rem;
            }
            
            .profile-content h2 {
                font-size: 1.5rem;
                text-align: center;
            }
            
            .profile-content h2::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .poem-list {
                grid-template-columns: 1fr;
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

    <main class="container profile-page">
        <div class="profile-header">
            <img src="<?= getProfilePic($profileUser) ?>" class="profile-avatar" alt="<?= htmlspecialchars($userData['fullname']) ?>">
            
           <div class="profile-info">
           <h1>
           <?= htmlspecialchars($userData['fullname']) ?>
           <?php if (isVerified($profileUser)): ?>
           <span class="verified-badge">
           <i class="fas fa-check-circle"></i>
           </span>
           <?php endif; ?>
           </h1>
           <p class="username">
           @<?= htmlspecialchars($profileUser) ?>
           <?php if (isVerified($profileUser)): ?>
           <span class="verified-badge">
           <i class="fas fa-check-circle"></i>
           </span>
           <?php endif; ?>
           </p>
           <p class="bio"><?= nl2br(htmlspecialchars($userData['bio'] ?? '')) ?></p>
           <p class="join-date">
           <i class="far fa-calendar-alt"></i> সদস্য since <?= date('d M Y', strtotime($userData['joined'])) ?>
           </p>
           </div>
            
            <?php if($isOwner): ?>
                <div class="profile-actions">
                    <a href="edit-profile.php" class="btn">
                        <i class="fas fa-user-edit"></i> প্রোফাইল এডিট
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-content">
            <h2><i class="fas fa-book-open"></i> কবিতাসমূহ</h2>
            
            <?php if(!empty($poems)): ?>
                <div class="profile-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?= count($poems) ?></div>
                        <div class="stat-label">কবিতা</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= array_sum(array_map(function($poem) { return count($poem['likes'] ?? []); }, $poems)) ?></div>
                        <div class="stat-label">পছন্দ</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= array_sum(array_map(function($poem) { return count($poem['comments'] ?? []); }, $poems)) ?></div>
                        <div class="stat-label">মন্তব্য</div>
                    </div>
                </div>
                
                <div class="poem-list">
                    <?php foreach($poems as $poem): ?>
                        <div class="poem-card">
                            <h3><a href="poem.php?id=<?= $poem['id'] ?>"><?= htmlspecialchars($poem['title']) ?></a></h3>
                            <div class="poem-meta">
                                <span class="date"><i class="far fa-clock"></i> <?= formatDate($poem['timestamp']) ?></span>
                                <span class="likes"><i class="fas fa-heart"></i> <?= count($poem['likes'] ?? []) ?></span>
                            </div>
                            <div class="poem-excerpt">
                                <?= excerpt($poem['content'], 150) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-poems">
                    <i class="far fa-bookmark" style="font-size: 2rem; margin-bottom: 1rem;"></i><br>
                    কোনো কবিতা পাওয়া যায়নি
                </p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Simple animation for poem cards
        document.querySelectorAll('.poem-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    </script>
</body>
</html>