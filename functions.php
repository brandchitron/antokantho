<?php
/**
 * অন্তঃকণ্ঠ - Bengali Poetry Platform
 * Complete Functions File with Like/Comment Functionality
 */

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 30, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// ========================
// PASSWORD FUNCTIONS (Base64)
// ========================

function base64_encrypt($password) {
    return base64_encode($password);
}

function base64_decrypt($encrypted) {
    return base64_decode($encrypted);
}

function validate_password($input, $stored) {
    return $input === base64_decrypt($stored);
}

// ========================
// USER MANAGEMENT
// ========================

function getUserByIP($ip = null) {
    $ip = $ip ?? $_SERVER['REMOTE_ADDR'];
    $users = getAllUsers();
    
    foreach ($users as $user) {
        if (isset($user['ip']) && $user['ip'] === $ip) {
            return $user;
        }
    }
    return false;
}

function getAllUsers() {
    $users = [];
    $files = glob('data/users/*.json');
    
    foreach ($files as $file) {
        $userData = json_decode(file_get_contents($file), true);
        if ($userData) {
            $users[] = $userData;
        }
    }
    
    return $users;
}

function saveUserData($username, $data) {
    if (!file_exists('data/users')) {
        mkdir('data/users', 0777, true);
    }
    $file = "data/users/{$username}.json";
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function getUserData($username) {
    $file = "data/users/{$username}.json";
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

// ========================
// POEM MANAGEMENT (WITH LIKE/COMMENT FUNCTIONALITY)
// ========================

function handlePoemLike($poemId) {
    if (!isset($_SESSION['user']['username'])) {
        return ['success' => false, 'message' => 'লগ ইন করুন'];
    }

    $poem = getPoem($poemId);
    if (!$poem) {
        return ['success' => false, 'message' => 'কবিতা পাওয়া যায়নি'];
    }

    $username = $_SESSION['user']['username'];
    $likes = $poem['likes'] ?? [];

    // Toggle like
    $liked = false;
    if (($key = array_search($username, $likes)) !== false) {
        unset($likes[$key]); // Remove like
    } else {
        $likes[] = $username; // Add like
        $liked = true;
    }

    // Update poem
    $poem['likes'] = array_values($likes); // Reindex array
    savePoemData($poemId, $poem);

    return [
        'success' => true,
        'liked' => $liked,
        'likes' => count($poem['likes'])
    ];
}

function handlePoemComment($poemId, $commentText) {
    if (!isset($_SESSION['user']['username'])) {
        return ['success' => false, 'message' => 'লগ ইন করুন'];
    }

    $poem = getPoem($poemId);
    if (!$poem) {
        return ['success' => false, 'message' => 'কবিতা পাওয়া যায়নি'];
    }

    $commentText = trim($commentText);
    if (empty($commentText)) {
        return ['success' => false, 'message' => 'মন্তব্য খালি থাকতে পারে না'];
    }

    // Add new comment
    $newComment = [
        'author' => $_SESSION['user']['username'],
        'text' => htmlspecialchars($commentText),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    $poem['comments'] = $poem['comments'] ?? [];
    array_unshift($poem['comments'], $newComment); // Add to beginning

    // Save poem
    savePoemData($poemId, $poem);

    return [
        'success' => true,
        'comment' => $newComment
    ];
}

function savePoem($data) {
    if (!file_exists('data/posts')) {
        mkdir('data/posts', 0777, true);
    }
    $id = uniqid();
    $file = "data/posts/{$id}.json";
    
    $poemData = [
        'id' => $id,
        'title' => $data['title'] ?? '',
        'content' => $data['content'] ?? '',
        'author' => $data['author'] ?? '',
        'timestamp' => time(),
        'last_updated' => time(),
        'likes' => [],
        'comments' => []
    ];
    
    file_put_contents($file, json_encode($poemData, JSON_PRETTY_PRINT));
    return $id;
}

function savePoemData($poemId, $data) {
    if (!file_exists('data/posts')) {
        mkdir('data/posts', 0777, true);
    }
    $filename = "data/posts/{$poemId}.json";
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
}

function getPoem($poemId) {
    $filename = "data/posts/{$poemId}.json";
    if (file_exists($filename)) {
        $data = json_decode(file_get_contents($filename), true);
        if (!isset($data['likes'])) $data['likes'] = [];
        if (!isset($data['comments'])) $data['comments'] = [];
        return $data;
    }
    return false;
}

function getRandomPoems($limit = 10) {
    if (!file_exists('data/posts')) {
        return [];
    }
    
    $files = glob("data/posts/*.json");
    if (empty($files)) return [];
    
    shuffle($files);
    $poems = [];
    
    foreach(array_slice($files, 0, $limit) as $file) {
        $id = basename($file, '.json');
        $poem = getPoem($id);
        if ($poem) {
            $poem['id'] = $id;
            $poems[] = $poem;
        }
    }
    
    return $poems;
}

// ========================
// ADMIN FUNCTIONS
// ========================

function admin_logout() {
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit;
}

function ensureAdminPrivileges() {
    $adminUsername = 'chitronbhattacharjee';
    $adminFile = "data/users/{$adminUsername}.json";
    
    $adminData = [
        'username' => $adminUsername,
        'password' => base64_encrypt('chitron@2448766'),
        'email' => 'admin@example.com',
        'is_admin' => true,
        'verified' => true,
        'created_at' => time(),
        'profile_pic' => 'assets/images/default-profile.png'
    ];
    
    if (!file_exists($adminFile)) {
        saveUserData($adminUsername, $adminData);
    }
}

// ========================
// VERIFICATION FUNCTIONS
// ========================

function isVerified($username) {
    $user = getUserData($username);
    return isset($user['verified']) && $user['verified'];
}

function verifyUser($username) {
    $user = getUserData($username);
    if ($user) {
        $user['verified'] = true;
        saveUserData($username, $user);
        return true;
    }
    return false;
}

function unverifyUser($username) {
    $user = getUserData($username);
    if ($user) {
        $user['verified'] = false;
        saveUserData($username, $user);
        return true;
    }
    return false;
}

// ========================
// UTILITY FUNCTIONS
// ========================

function formatDate($timestamp) {
    if (is_string($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    
    $diff = time() - $timestamp;
    if ($diff < 60) return "এখনই";
    if ($diff < 3600) return floor($diff/60) . " মিনিট আগে";
    if ($diff < 86400) return floor($diff/3600) . " ঘণ্টা আগে";
    return date('d M Y', $timestamp);
}

function excerpt($text, $length = 100) {
    $text = strip_tags($text);
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function getProfilePic($username) {
    $user = getUserData($username);
    $default = 'assets/images/default-profile.png';
    
    if (!$user || !isset($user['profile_pic'])) {
        return $default;
    }
    
    return file_exists($user['profile_pic']) ? $user['profile_pic'] : $default;
}

function isLiked($poemId) {
    if (!isset($_SESSION['user'])) return false;
    $poem = getPoem($poemId);
    return $poem && isset($poem['likes']) && in_array($_SESSION['user']['username'], $poem['likes']);
}

function isAdmin() {
    if (!isset($_SESSION['user'])) return false;
    
    $username = $_SESSION['user']['username'];
    if ($username === 'chitronbhattacharjee') return true;
    
    $userData = getUserData($username);
    return isset($userData['is_admin']) && $userData['is_admin'] === true;
}

// ========================
// INITIALIZATION
// ========================

function checkDataDirectories() {
    $directories = [
        'data',
        'data/users',
        'data/posts',
        'assets',
        'assets/images',
        'assets/uploads'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    
    // Create default profile image
    $defaultProfile = 'assets/images/default-profile.png';
    if (!file_exists($defaultProfile)) {
        $im = imagecreatetruecolor(200, 200);
        $bgColor = imagecolorallocate($im, 106, 48, 147);
        imagefill($im, 0, 0, $bgColor);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledellipse($im, 100, 80, 80, 80, $white);
        imagefilledrectangle($im, 80, 120, 120, 170, $white);
        imagepng($im, $defaultProfile);
        imagedestroy($im);
    }
}

// Initialize the application
checkDataDirectories();
ensureAdminPrivileges();

// Handle AJAX requests for likes and comments
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['poem_id'])) {
        echo json_encode(handlePoemLike($_GET['poem_id']));
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'comment' && isset($_POST['poem_id']) && isset($_POST['comment'])) {
        echo json_encode(handlePoemComment($_POST['poem_id'], $_POST['comment']));
        exit;
    }
}