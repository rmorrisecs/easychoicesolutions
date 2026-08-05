<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';        // SMTP host (confirm with your provider)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@easychoicesolutions.com';     // full email you log in with
        $mail->Password   = '*s50Lroot';               // that mailbox's password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           // or ENCRYPTION_STARTTLS
        $mail->Port       = 465;                                   // 465 = SMTPS, 587 = STARTTLS

        $mail->setFrom('noreply@easychoicesolutions.com', 'Website Contact Form');
        $mail->addAddress('info@easychoicesolutions.com');         // where the form arrives
        $mail->addReplyTo($email, $name);                          // click Reply to answer the visitor

        $mail->Subject = "New Contact Form Submission: $subject";
        $mail->Body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

        $mail->send();
        $mail->send();

        // Redirect back to homepage with a success flag
        header("Location: index.html?sent=1");
        exit;

    } catch (Exception $e) {
        // On failure, send them back with an error flag
        header("Location: index.html?sent=0");
        exit;
    }
}
?>
