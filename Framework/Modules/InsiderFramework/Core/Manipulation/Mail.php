<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle mails
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Mail
 *
 * @author Marcello Costa
 */
trait Mail
{
    /**
     * Function to send emails
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Mail
     *
     * @param string $to           Recipients
     * @param string $from         Sender
     * @param string $frompass     Sender's email password
     * @param string $subject      Message subject
     * @param string $message      Message body
     * @param string $messagealt   Alternative message (no html)
     * @param string $smtpserver   SMTP server that will send the message
     * @param int    $smtpport     Port of the SMTP server that will send the message
     * @param bool   $smtpauth     If the SMTP server uses authentication
     * @param string $smtpsecure   What is the SMTP server authentication type
     *                             (possible values: tls or ssl)
     * @param string $replyto      To whom the message will be answered
     * @param array  $ccto         To whom the message should be sent in copy
     * @param array  $bccto        To whom the message should be sent in a blind copy
     * @param array  $attachfiles  Path of files to be attached to the message
     * @param string $content_type Type of message content
     * @param string $charset      Message encoding
     *
     * @return bool Return of the send operation
     */
    public static function sendMail(
        string $to,
        string $from,
        string $frompass,
        string $subject,
        string $message,
        string $messagealt,
        string $smtpserver,
        int $smtpport,
        bool $smtpauth = true,
        string $smtpsecure = "tls",
        array $replyto = null,
        array $ccto = null,
        array $bccto = null,
        array $attachfiles = null,
        string $content_type = "text/html",
        string $charset = ENCODE
    ): bool {
        /*
        $mail = new \PHPMailer();
        $mail->CharSet = $charset;
        $mail->isSMTP();
        $mail->Host = $smtpserver;
        $mail->SMTPAuth = $smtpauth;
        $mail->Username = $from;
        $mail->Password = $frompass;
        $mail->SMTPSecure = $smtpsecure;
        $mail->Port = $smtpport;
        $mail->setFrom($from, $from);
        $mail->addAddress($to);
        $mail->addReplyTo($replyto, $replyto);

        if ($ccto !== null) {
            foreach ($ccto as $cc) {
                $mail->addCC($cc);
            }
        }

        if ($bccto !== null) {
            foreach ($bccto as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        if ($attachfiles !== null) {
            foreach ($attachfiles as $attfile) {
                $mail->addAttachment($attfile);
            }
        }

        if ($content_type === "text/html") {
            $mail->isHTML(true);
        } else {
            $mail->isHTML(false);
        }

        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $messagealt;
        $mail->setLanguage(
            'br',
            $phpmailerDir . DIRECTORY_SEPARATOR .
            "language" . DIRECTORY_SEPARATOR
        );

        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return true;
        }
        */

        return true;
    }
}
