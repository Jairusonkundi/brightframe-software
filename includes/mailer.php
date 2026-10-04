<?php
/**
 * Shared SMTP mailer — the single place form handlers come for email
 * notifications (handlers/quote_handler.php and handlers/contact_handler.php).
 *
 * Relies on PHPMailer, installed via Composer. Run `composer install` from
 * the repo root once; config comes from config/.env:
 *
 *   ADMIN_EMAIL     recipient for notifications (defaults to the owner)
 *   SMTP_HOST       relay host                        (default smtp.gmail.com)
 *   SMTP_PORT       relay port                        (default 465)
 *   SMTP_SECURE     'ssl' or 'tls'                    (default ssl)
 *   SMTP_USER       authenticated account, e.g. the Gmail address
 *   SMTP_PASS       the account's App Password (NOT the normal password)
 *   MAIL_FROM       envelope From address (defaults to SMTP_USER)
 *   MAIL_FROM_NAME  display name                      (default "Brightframe Software")
 *
 * Gmail App Passwords require 2-Step Verification enabled on the account —
 * see Google's "App passwords" settings. Keep SMTP_PASS out of git: it lives
 * in config/.env (gitignored); config/.env.example only carries the naming
 * convention.
 *
 * Always returns a bool and never throws — form handlers have already saved
 * the record to the database before notifying, so a failed email is logged,
 * not fatal.
 */

require_once __DIR__ . '/../config/env.php';

function send_email_notification(string $to, string $subject, string $plainBody, string $replyTo): bool
{
    $host     = env('SMTP_HOST', 'smtp.gmail.com');
    $port     = (int) env('SMTP_PORT', '465');
    $secure   = strtolower(env('SMTP_SECURE', 'ssl'));
    $user     = env('SMTP_USER', '');
    $pass     = env('SMTP_PASS', '');
    $from     = env('MAIL_FROM', $user !== '' ? $user : 'no-reply@brightframesoftware.local');
    $fromName = env('MAIL_FROM_NAME', 'Brightframe Software');

    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true); // exceptions on error

    try {
        if ($user === '' || $pass === '') {
            // SMTP not configured yet — fall back to PHP mail() so local
            // development keeps working (or at least keeps failing soft).
            $mail->isMail();
        } else {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = in_array($secure, ['ssl', 'tls'], true) ? $secure : 'ssl';
            $mail->Port       = $port;
            $mail->Timeout    = 15;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($from, $fromName);
        $mail->addReplyTo($replyTo);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $plainBody;

        $sent = $mail->send();
        if (!$sent) {
            error_log('Email notification failed to send: ' . $mail->ErrorInfo);
        }
        return $sent;
    } catch (Throwable $e) {
        error_log('Email notification threw: ' . $e->getMessage());
        return false;
    }
}