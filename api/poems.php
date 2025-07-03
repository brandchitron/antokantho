<?php
header('Content-Type: application/json');
require '../functions.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false];

switch($action) {
    case 'like':
        if (!isset($_SESSION['user'])) {
            $response['error'] = 'লগইন প্রয়োজন';
            break;
        }
        
        $poemId = $_POST['id'] ?? '';
        $poem = getPoem($poemId);
        
        if (!$poem) {
            $response['error'] = 'কবিতা পাওয়া যায়নি';
            break;
        }
        
        $username = $_SESSION['user']['username'];
        $likes = $poem['likes'] ?? [];
        
        if (in_array($username, $likes)) {
            // আনলাইক
            $likes = array_diff($likes, [$username]);
        } else {
            // লাইক
            $likes[] = $username;
        }
        
        $poem['likes'] = $likes;
        file_put_contents("../data/posts/{$poemId}.json", json_encode($poem, JSON_PRETTY_PRINT));
        
        $response['success'] = true;
        $response['likes'] = count($likes);
        $response['isLiked'] = in_array($username, $likes);
        break;
        
    case 'comment':
        // কমেন্ট ফাংশনালিটি ইমপ্লিমেন্ট করুন
        break;
        
    default:
        $response['error'] = 'অবৈধ একশন';
}

echo json_encode($response);
?>