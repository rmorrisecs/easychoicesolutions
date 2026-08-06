<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';

$configFile = __DIR__ . '/mail-config.php';
if (!is_file($configFile)) {
    header('Location: /contact?sent=0');
    exit;
}
require $configFile;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact');
    exit;
}

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

$redirectBase = '/contact';
if (!empty($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/contact') === false) {
    $redirectBase = '/';
}

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirectBase . '?sent=0');
    exit;
}

if ($subject === '') {
    $subject = 'Website inquiry';
}

/**
 * Append a line to the local submission log (does not replace email).
 */
function ecs_log_submission(string $status, array $data, string $detail = ''): void
{
    $dir = __DIR__ . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    $line = sprintf(
        "[%s] %s | name=%s | email=%s | subject=%s | %s\n",
        date('c'),
        $status,
        str_replace(["\n", '|'], ' ', $data['name']),
        str_replace(["\n", '|'], ' ', $data['email']),
        str_replace(["\n", '|'], ' ', $data['subject']),
        str_replace(["\n", '|'], ' ', $detail)
    );
    @file_put_contents($dir . '/submissions.log', $line, FILE_APPEND | LOCK_EX);
}

$payload = [
    'name'    => $name,
    'email'   => $email,
    'subject' => $subject,
    'message' => $message,
];

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mail_smtp['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_smtp['username'];
    $mail->Password   = $mail_smtp['password'];
    $mail->Port       = (int)$mail_smtp['port'];
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    if (($mail_smtp['encryption'] ?? 'smtps') === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }

    $mail->setFrom($mail_from['address'], $mail_from['name']);

    foreach ($mail_to as $to) {
        $to = trim((string)$to);
        if ($to !== '') {
            $mail->addAddress($to);
        }
    }
    foreach ($mail_bcc as $bcc) {
        $bcc = trim((string)$bcc);
        if ($bcc !== '') {
            $mail->addBCC($bcc);
        }
    }

    $mail->addReplyTo($email, $name);

    $mail->Subject = 'New contact form: ' . $subject;
    $mail->Body    = "New website contact form submission\n"
        . "================================\n\n"
        . "Name: {$name}\n"
        . "Email: {$email}\n"
        . "Subject: {$subject}\n\n"
        . "Message:\n{$message}\n\n"
        . "--------------------------------\n"
        . 'Received: ' . date('c') . "\n"
        . 'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

    if (empty($mail_to) && empty($mail_bcc)) {
        throw new Exception('No mail recipients configured.');
    }

    $mail->send();

    // Optional confirmation to the visitor (helps verify outbound mail works)
    try {
        $confirm = new PHPMailer(true);
        $confirm->isSMTP();
        $confirm->Host       = $mail_smtp['host'];
        $confirm->SMTPAuth   = true;
        $confirm->Username   = $mail_smtp['username'];
        $confirm->Password   = $mail_smtp['password'];
        $confirm->Port       = (int)$mail_smtp['port'];
        $confirm->CharSet    = 'UTF-8';
        $confirm->Encoding   = 'base64';
        $confirm->SMTPSecure = (($mail_smtp['encryption'] ?? 'smtps') === 'tls')
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;

        $confirm->setFrom($mail_from['address'], 'Easy Choice Solutions');
        $confirm->addAddress($email, $name);
        $confirm->Subject = 'We received your message — Easy Choice Solutions';
        $confirm->Body    = "Hi {$name},\n\n"
            . "Thanks for contacting Easy Choice Solutions. We received your message"
            . ($subject !== '' ? " about \"{$subject}\"" : '')
            . " and will get back to you soon.\n\n"
            . "— Easy Choice Solutions LLC\n"
            . "info@easychoicesolutions.com\n"
            . "(480) 270-4380\n";
        $confirm->send();
    } catch (Exception $confirmError) {
        // Confirmation is best-effort; owner notification already succeeded.
        ecs_log_submission('CONFIRM_FAIL', $payload, $confirmError->getMessage());
    }

    ecs_log_submission('SENT', $payload, 'to=' . implode(',', $mail_to));
    header('Location: ' . $redirectBase . '?sent=1');
    exit;
} catch (Exception $e) {
    ecs_log_submission('FAIL', $payload, $mail->ErrorInfo ?: $e->getMessage());
    header('Location: ' . $redirectBase . '?sent=0');
    exit;
}
