<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../lib/connect.php';
require_once '../models/User.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
$pdo = Database::connect();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
header('Content-Type: application/json');
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-errors.log');

function generateCode() {
    return sprintf("%06d", mt_rand(0, 999999));
}

function sendCode($clientEmail, $code) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '83abe2001@smtp-brevo.com'; 
        $mail->Password = 'xsmtpsib-767a89e3882c34e6bdb92890b57f5edf031320ae71ac12b73f7fae24c05e6c14-mJYsxO7XMr1kbURj';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';

        $mail->setFrom('obsidiannoreply99@gmail.com', 'Assembler');
        $mail->addAddress($clientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code Is: ' . $code;
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; background-color: #f9f9f9; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);'>
                <h2 style='color: #4CAF50; text-align: center;'>Verification Code</h2>
                <p style='font-size: 16px; line-height: 1.5; text-align: center;'>
                    Hello,
                </p>
                <p style='font-size: 16px; line-height: 1.5; text-align: center;'>
                    Please verify your email address. Your verification code is:
                </p>
                <p style='font-size: 24px; font-weight: bold; text-align: center; color: #4CAF50;'>
                    " . $code . "
                </p>
                <p style='font-size: 14px; line-height: 1.5; text-align: center; color: #777;'>
                    This code is valid for 10 minutes. If you did not request this code, please ignore this email.
                </p>
                <div style='text-align: center; margin-top: 20px;'>
                    <a href='#' style='text-decoration: none; background-color: #4CAF50; color: white; padding: 10px 20px; border-radius: 5px; font-size: 16px;'>
                        I got it, Thanks !
                    </a>
                </div>
                <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                <p style='font-size: 12px; text-align: center; color: #aaa;'>
                    This email was sent automatically, please do not respond to it.
                </p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending email: ' . $mail->ErrorInfo);
        return false;
    }
}

// nakhdo lrequête 'action' mn POST : 
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// traitement 3la 7sap had l'action
switch ($action) {
    case 'request':
        if (!isset($data['email']) || empty($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email required']);
            exit();
        }
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'this email doesn\'t exist']);
            exit();
        }
        
        $code = generateCode();
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $pdo->prepare("INSERT INTO Verifcodes (email, code, expiry) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE code = ?, expiry = ?");
        $stmt->execute([$email, $code, $expiry, $code, $expiry]);
        
        if (sendCode($email, $code)) {
            echo json_encode(['success' => 'Code sent successfuly']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error sending email']);
        }
        break;
        
    case 'verify':
        if (!isset($data['email']) || !isset($data['code']) || empty($data['email']) || empty($data['code'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and code are required']);
            exit();
        }
        
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $code = $data['code'];
        
        $stmt = $pdo->prepare("SELECT * FROM verifcodes WHERE email = ? AND code = ? AND expiry > NOW()");
        $stmt->execute([$email, $code]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid code or expired']);
            exit();
        }
        
        echo json_encode(['success' => 'Code verified successfuly']);
        break;
        
    case 'reset':
        if (!isset($data['email']) || !isset($data['code']) || !isset($data['password']) || !isset($data['confirm_password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            exit();
        }
        
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $code = $data['code'];
        $password = $data['password'];
        $confirmPassword = $data['confirm_password'];
        
        if ($password !== $confirmPassword) {
            http_response_code(400);
            echo json_encode(['error' => 'Passwords don\'t match']);
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT * FROM verifcodes WHERE email = ? AND code = ? AND expiry > NOW()");
        $stmt->execute([$email, $code]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid code or expired']);
            exit();
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        
        $stmt = $pdo->prepare("DELETE FROM verifcodes WHERE email = ?");
        $stmt->execute([$email]);
        
        echo json_encode(['success' => 'Password reset successfuly']);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action not reconized']);
}

?>