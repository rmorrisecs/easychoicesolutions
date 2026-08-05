<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact');
    exit;
}

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? 'Website inquiry'));
$message = trim((string)($_POST['message'] ?? ''));

$redirectBase = '/contact';
if (!empty($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/contact') === false) {
    // Submissions from the homepage contact section return there
    $redirectBase = '/';
}

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirectBase . '?sent=0');
    exit;
}

if ($subject === '') {
    $subject = 'Website inquiry';
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@easychoicesolutions.com';
    $mail->Password   = '*s50Lroot';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('noreply@easychoicesolutions.com', 'Website Contact Form');
    $mail->addAddress('info@easychoicesolutions.com');
    $mail->addReplyTo($email, $name);

    $mail->Subject = 'New Contact Form Submission: ' . $subject;
    $mail->Body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $mail->send();

    header('Location: ' . $redirectBase . '?sent=1');
    exit;
} catch (Exception $e) {
    header('Location: ' . $redirectBase . '?sent=0');
    exit;
}
