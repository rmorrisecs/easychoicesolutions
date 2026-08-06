<?php
/**
 * Contact form mail settings for Easy Choice Solutions.
 *
 * Emails are sent through Hostinger SMTP FROM noreply@…
 * and delivered TO the addresses in $mail_to.
 *
 * Tip: If you read mail in Gmail, either:
 *  - set up forwarding from info@ in Hostinger Webmail, or
 *  - add your Gmail address to $mail_to / $mail_bcc below.
 */
$mail_smtp = [
    'host'       => 'smtp.hostinger.com',
    'username'   => 'noreply@easychoicesolutions.com',
    'password'   => '*s50Lroot',
    'port'       => 465,
    'encryption' => 'smtps', // smtps = 465, tls = 587
];

// Primary inbox for new contact form messages
$mail_to = [
    'info@easychoicesolutions.com',
    'robert@easychoicesolutions.com',
    'rmorrisg@gmail.com',
];

// Optional blind copies (e.g. a personal Gmail you check daily)
$mail_bcc = [
    // 'you@gmail.com',
];

$mail_from = [
    'address' => 'noreply@easychoicesolutions.com',
    'name'    => 'Easy Choice Solutions Website',
];
