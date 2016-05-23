<?php

define('APP_PATH', realpath('..'));

require APP_PATH . '/vendor/autoload.php';

use BobbyFramework\Utils\Email;

$mail = new Email();
$mail->setTo('n.wallerand@gmail.com', 'Recipient 1')
    ->setSubject('Test Message')
    ->setFrom('n.wallerand@gmail.com', 'Mail Bot')
    ->addMailHeader('Reply-To', 'sender@gmail.com', 'Mail Bot')
    ->addMailHeader('Cc', 'bill@example.com', 'Bill Gates')
    ->addMailHeader('Bcc', 'steve@example.com', 'Steve Jobs')
    ->addGenericHeader('X-Mailer', 'PHP/' . phpversion())
    ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
    ->setMessage('<strong>This is a test message.</strong>');

if (true === $mail->send()) {
    echo 'Email was sent successfully!';
} else {
    echo 'An error occurred. We could not send email';
}