<?php
// api.php
require 'functions.php';

// Set default headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Get request parameters
$content = $_GET['content'] ?? 'random';
$total = min(10, max(1, (int)($_GET['total'] ?? 1)));
$username = $_GET['user'] ?? null;

try {
    // Fetch poems from data directory
    $poems = getPoems($content, $total, $username);
    
    // Format response
    if ($total === 1) {
        $poem = $poems[0];
        echo json_encode([
            'status' => 'success',
            'title' => $poem['title'],
            'poem' => $poem['content'],
            'writter' => $poem['author'],
            'api' => 'Api owned by Chitron Bhattacharjee'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $response = [
            'status' => 'success',
            'poems' => array_map(function($poem) {
                return [
                    'title' => $poem['title'],
                    'poem' => $poem['content'],
                    'writter' => $poem['author']
                ];
            }, $poems),
            'api' => 'Api owned by Chitron Bhattacharjee'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // Detailed error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ],
        'api' => 'Api owned by Chitron Bhattacharjee'
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Get poems based on sorting criteria
 * 
 * @param string $sort Sorting method (random/time/name/length)
 * @param int $limit Number of poems to return
 * @param string|null $username Filter by username
 * @return array Array of poems
 */
function getPoems(string $sort = 'random', int $limit = 1, ?string $username = null): array {
    $files = glob("data/posts/*.json");
    
    if (empty($files)) {
        throw new Exception("No poems found in database");
    }
    
    $allPoems = [];
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (!$data) continue;
        
        // Apply username filter
        if ($username && $data['author'] !== $username) continue;
        
        $data['id'] = basename($file, '.json');
        $allPoems[] = $data;
    }
    
    if (empty($allPoems)) {
        throw new Exception($username 
            ? "No poems found for user '$username'" 
            : "No poems available");
    }
    
    // Apply sorting
    switch ($sort) {
        case 'time':
            usort($allPoems, function($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });
            break;
            
        case 'name':
            usort($allPoems, function($a, $b) {
                return strcmp($a['title'], $b['title']);
            });
            break;
            
        case 'length':
            usort($allPoems, function($a, $b) {
                return strlen($b['content']) <=> strlen($a['content']);
            });
            break;
            
        case 'random':
        default:
            shuffle($allPoems);
            break;
    }
    
    return array_slice($allPoems, 0, $limit);
}