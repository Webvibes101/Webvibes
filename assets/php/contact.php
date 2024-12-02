<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Include database configuration
include_once("/var/www/html/webvibes/config.php");

// Get form data
$name = $_POST['name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$department = $_POST['department'];
$message = $_POST['message'];
$recaptchaResponse = $_POST['g-recaptcha-response']; // reCAPTCHA response from the form

// Google reCAPTCHA Secret Key
$secretKey = '6LfwaoIqAAAAADZf1OX7I4EsDTKoaLQHSG_SoSEk';  // Replace with your reCAPTCHA secret key

// Verify reCAPTCHA response
$verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse";
$response = file_get_contents($verifyUrl);
$responseKeys = json_decode($response, true);

if (intval($responseKeys["success"]) !== 1) {
    echo json_encode(["success" => false, "message" => "reCAPTCHA verification failed. Please try again."]);
    exit;
}
// Sanitize inputs to prevent SQL injection
$name = $conn->real_escape_string($name);
$surname = $conn->real_escape_string($surname);
$email = $conn->real_escape_string($email);
$department = $conn->real_escape_string($department);
$message = $conn->real_escape_string($message);

// Insert data into the database
$sql = "INSERT INTO contact_form (name, surname, email, department, message) VALUES ('$name', '$surname', '$email', '$department', '$message')";
if ($conn->query($sql) === TRUE) {
    // Send email to the user
    $fromEmail = 'no-reply@webvibes.ca';
    $fromName = 'WebVibes Support';
    $sendToEmail = $email; // Send email to the user
    $sendToName = "$name $surname";
    $subject = 'Thank you for contacting WebVibes!';
    
    $body = "Dear $name $surname,\n\nThank you for reaching out to us. We have received your message regarding $department.\n\nMessage: $message\n\nOur team will get back to you shortly.\n\nBest regards,\nWebVibes Team";
    
    try {
        // PHPMailer settings
        $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.zohocloud.ca';  // Use the correct Zoho SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'admin@webvibes.ca';  // Your Zoho email address
                $mail->Password = 'Waheguru@1988'; // Use app-specific password if needed
                $mail->SMTPSecure = 'ssl';  // Use 'ssl' for encryption (as a string)
                $mail->Port = 465;  // Use port 465 for SSL connection

        // Sender and receiver settings
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($sendToEmail, $sendToName);
        $mail->addReplyTo($fromEmail);
        
        // Email content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send email
        if ($mail->send()) {
            echo json_encode(["success" => true, "message" => "Form submitted successfully. Email sent!"]);
        } else {
            echo json_encode(["success" => true, "message" => "Form submitted successfully, but email could not be sent."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

// Close database connection
$conn->close();
?>
