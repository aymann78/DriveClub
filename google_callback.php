<?php
session_start();

// Define your Google credentials
$client_id = '';
$client_secret = '';
$redirect_uri = 'http://localhost/DriveClubSystem/google_callback.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Exchange the code for an access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_fields = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP code
    curl_close($ch);

    $token_data = json_decode($response, true);

    if ($http_code == 200 && isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];

        // Get user information
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
        $user_response = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_response, true);

        if (isset($user_data['id'])) {
            $google_id = $user_data['id'];
            $email = $user_data['email'];
            $first_name = $user_data['given_name'] ?? ''; // Empty string if there is no name
            $last_name = $user_data['family_name'] ?? ''; // Empty string if there is no last name

            // Connect to the database
            $conn = new mysqli('localhost', 'root', '', 'driveclub');
            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Check if the user already exists with google_id
            $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
            $stmt->bind_param('s', $google_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // User exists, login
                $_SESSION['user_id'] = $user['id'];
                header('Location: mi-cuenta.php');
                exit;
            } else {
                // Check if the email is already registered
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user) {
                    // Link google_id to the existing user
                    $stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                    $stmt->bind_param('si', $google_id, $user['id']);
                    $stmt->execute();
                    $_SESSION['user_id'] = $user['id'];
                    header('Location: mi-cuenta.php');
                    exit;
                } else {
                    // Create new user with password as NULL
                    $password = null; // No initial password
                    $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, google_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssss', $email, $password, $first_name, $last_name, $google_id);
                    $stmt->execute();
                    $user_id = $conn->insert_id;
                    $_SESSION['user_id'] = $user_id;
                    header('Location: mi-cuenta.php');
                    exit;
                }
            }
            $stmt->close();
            $conn->close();
        } else {
            header('Location: login.php?error=google_auth_failed');
            exit;
        }
    } else {
        // Show the error for debugging
        echo "Error getting the token: HTTP $http_code - " . $response;
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>