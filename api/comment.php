<?php
// api/comment.php
require '../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$poemId = $_POST['poem_id'] ?? '';
$commentText = trim($_POST['comment'] ?? '');

if (empty($poemId) || empty($commentText)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$poem = getPoem($poemId);
if (!$poem) {
    echo json_encode(['success' => false, 'message' => 'Poem not found']);
    exit;
}

// Initialize comments array if not exists
if (!isset($poem['comments'])) {
    $poem['comments'] = [];
}

// Add new comment
$newComment = [
    'id' => uniqid(),
    'author' => $_SESSION['user']['username'],
    'text' => htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8'),
    'timestamp' => time(),
    'likes' => []
];

$poem['comments'][] = $newComment;
savePoemData($poemId, $poem);

echo json_encode([
    'success' => true,
    'comment' => [
        'id' => $newComment['id'],
        'author' => $newComment['author'],
        'text' => $newComment['text'],
        'timestamp' => formatDate($newComment['timestamp']),
        'avatar' => getProfilePic($newComment['author'])
    ]
]);
?>