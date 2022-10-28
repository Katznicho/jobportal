<?php
require '../plugins/PHPMailer-master/src/Exception.php';
require '../plugins/PHPMailer-master/src/PHPMailer.php';
require '../plugins/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    public function email($sender, $receiver, $message, $subject, $sender_name = "")
    {

        $email_status = "";
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions

        try {
            //Recipients
            if (strlen($sender_name) > 0) {
                $mail->setFrom($sender, $sender_name, 0);
                $mail->addReplyTo($sender, $sender_name);
            } else {
                $mail->setFrom($sender);
                $mail->addReplyTo($sender);
            }

            $mail->addAddress($receiver);     // Add a recipient             
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = $message;

            $mail->send();
            $email_status = "success";
        } catch (Exception $e) {
            $email_status = "failed";
        }

        return $email_status;
    }
}
