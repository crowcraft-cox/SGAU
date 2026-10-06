<?php
// Inclure PHPMailer manuellement
require_once APP_ROOT . '/scratch/vendor/PHPMailer-6.9.1/src/Exception.php';
require_once APP_ROOT . '/scratch/vendor/PHPMailer-6.9.1/src/PHPMailer.php';
require_once APP_ROOT . '/scratch/vendor/PHPMailer-6.9.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public static function send($to, $subject, $body) {
        $mail = new PHPMailer(true);
        
        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST') ?: 'sandbox.smtp.mailtrap.io';
            
            // SMTPAuth seulement si username est défini et non "null"
            $username = getenv('SMTP_USERNAME');
            if ($username && $username !== 'null') {
                $mail->SMTPAuth   = true;
                $mail->Username   = $username;
                $mail->Password   = getenv('SMTP_PASSWORD');
            } else {
                $mail->SMTPAuth   = false;
            }
            
            $mail->Port       = getenv('SMTP_PORT') ?: 2525;
            
            // Log local au lieu de l'envoi si on n'a pas de serveur SMTP configuré
            if (!$username || $username === 'null') {
                $logFile = APP_ROOT . '/storage/logs/emails.log';
                if (!is_dir(dirname($logFile))) {
                    mkdir(dirname($logFile), 0777, true);
                }
                $logEntry = "[" . date('Y-m-d H:i:s') . "] TO: $to | SUBJECT: $subject\nBODY:\n$body\n\n--------------------------\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                return true; // Simuler un succès
            }

            // Destinataires
            $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: 'no-reply@openlu.org', getenv('SMTP_FROM_NAME') ?: 'SGAU OPENLU');
            $mail->addAddress($to);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("L'email n'a pas pu être envoyé. Erreur Mailer: {$mail->ErrorInfo}");
            return false;
        }
    }
}
