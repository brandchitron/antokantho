<?php
header('Content-Type: application/json');
require '../functions.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false];

switch($action) {
    case 'check_username':
        $username = $_GET['username'] ?? '';
        if (empty($username)) {
            $response['error'] = 'ইউজারনেম প্রদান করুন';
            break;
        }
        
        $response['exists'] = getUserData($username) !== null;
        $response['success'] = true;
        break;
        
    case 'update_profile':
        if (!isset($_SESSION['user'])) {
            $response['error'] = 'অননুমোদিত অ্যাক্সেস';
            break;
        }
        
        $username = $_SESSION['user']['username'];
        $userData = getUserData($username);
        
        $userData['fullname'] = $_POST['fullname'] ?? $userData['fullname'];
        $userData['bio'] = $_POST['bio'] ?? $userData['bio'] ?? '';
        
        // প্রোফাইল পিক আপডেট
        if (isset($_FILES['profile_pic'])) {
            $uploadDir = 'assets/uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = $username . '.' . $ext;
            $targetFile = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
                $userData['profile_pic'] = $targetFile;
            }
        }
        
        saveUserData($username, $userData);
        $_SESSION['user'] = $userData;
        
        $response['success'] = true;
        $response['user'] = $userData;
        break;
        
    default:
        $response['error'] = 'অবৈধ একশন';
}

echo json_encode($response);
?>