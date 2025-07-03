<?php
// api/like.php
require '../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$poemId = $_GET['poem_id'] ?? '';
if (empty($poemId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid poem ID']);
    exit;
}

$poem = getPoem($poemId);
if (!$poem) {
    echo json_encode(['success' => false, 'message' => 'Poem not found']);
    exit;
}

$username = $_SESSION['user']['username'];

// Initialize likes array if not exists
if (!isset($poem['likes'])) {
    $poem['likes'] = [];
}

// Toggle like status
$likeIndex = array_search($username, $poem['likes']);
if ($likeIndex === false) {
    // Add like
    $poem['likes'][] = $username;
} else {
    // Remove like
    array_splice($poem['likes'], $likeIndex, 1);
}

// Save updated poem data
savePoemData($poemId, $poem);

echo json_encode([
    'success' => true,
    'liked' => ($likeIndex === false),
    'likes_count' => count($poem['likes'])
]);
?>