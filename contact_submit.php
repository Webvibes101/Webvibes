<?php
// Database configuration
include_once("config.php");

// Get form data
$name = $_POST['name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$department = $_POST['department'];
$message = $_POST['message'];

// Sanitize inputs
$name = $conn->real_escape_string($name);
$surname = $conn->real_escape_string($surname);
$email = $conn->real_escape_string($email);
$department = $conn->real_escape_string($department);
$message = $conn->real_escape_string($message);

// Insert data into the database
$sql = "INSERT INTO contact_form (name, surname, email, department, message) VALUES ('$name', '$surname', '$email', '$department', '$message')";
if ($conn->query($sql) === TRUE) {
    // Send email to the user
    $to = $email;
    $subject = "Thank you for contacting us!";
    $body = "Dear $name $surname,\n\nThank you for reaching out to us. We have received your message regarding $department.\n\nMessage: $message\n\nOur team will get back to you shortly.\n\nBest regards,\nYour Company Name";
    $headers = "From: no-reply@webvibes.ca";

    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(["success" => true, "message" => "Form submitted successfully. Email sent!"]);
    } else {
        echo json_encode(["success" => true, "message" => "Form submitted successfully, but email could not be sent."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

// Close connection
$conn->close();
?>
