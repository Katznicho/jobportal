<?php

namespace Ssentezo\Util;

use Ssentezo\Database\DbAccess;
use Ssentezo\Util\ActivityLogger;

class Auth
{
    public static function sendPasswordRecoveryEmail($mailer, $client_email, $username, $company, $activation_token)
    {
        $to       =  $client_email;
        $subject  = $company . " Password Reset";
        $link = "https://client.ssentezo.com/reset_password.php?u=$username&c=$company&token=$activation_token";

        $body = "
        <!doctype html>
        <html lang=\"en-US\">

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>Password Reset $company</title>
        <meta name=\"description\" content=\"Password reset email.\">
        <style type=\"text/css\">
        a:hover {text-decoration: underline !important;}
        </style>
        </head>

        <body marginheight=\"0\" topmargin=\"0\" marginwidth=\"0\" style=\"margin: 0px; background-color: #f2f3f8;\" leftmargin=\"0\">
        <!--100% body table-->
        <table cellspacing=\"0\" border=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#f2f3f8\"
        style=\"@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;\">
        <tr>
            <td>
                <table style=\"background-color: #f2f3f8; max-width:670px;  margin:0 auto;\" width=\"100%\" border=\"0\"
                    align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                          <a href=\"https://rakeshmandal.com\" title=\"logo\" target=\"_blank\">
                            <img width=\"60\" src=\"https://i.ibb.co/hL4XZp2/android-chrome-192x192.png\" title=\"logo\" alt=\"logo\">
                          </a>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"
                                style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\">
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style=\"padding:0 35px;\">
                                        <h1 style=\"color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">You have
                                            requested to reset your password</h1>
                                        <span
                                            style=\"display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;\"></span>
                                        <p style=\"color:#455056; font-size:15px;line-height:24px; margin:0;\">
                                            A unique link to reset your
                                            password has been generated for you. To reset your password, click the
                                            following link and follow the instructions. The link expires in 10 Minutes!.
                                        </p>
                                        <a href=\"$link\"
                                            style=\"background:#20e277;text-decoration:none !important; font-weight:500; margin-top:35px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;\">Reset
                                            Password</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                            <p style=\"font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;\">&copy; <strong>$company</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <!--/100% body table-->
        </body>

        </html>";
    }
    public static function sendStaffActivationEmail($mailer, $staff_email, $company, $domain = "")
    {

        $message = "<h3>Your details have been added to the Ssentezo system</h3> </br></br> 
                <h5>Please click the link below to set your password</h5> </br>"
            . "<a href=\"https://$domain/staff/setEmail.php?email=$staff_email\" style='background-color:#33cc00;color:white;border: 1px solid #33cc00;text-align: center;font-size: 150%;border-radius: 3px;padding:10px;'>SET YOUR PASSWORD</a>" .
            "<p>If it doesn't work, Copy and paste the link below in your web browser<p>" .
            "https://$domain/staff/setEmail.php?email=$staff_email";

        // $alt_message = 'Your details have been added to the Ssentezo system, follow the link  app.ssentezo.com/staff/setEmail.php?email=' . $staff_email . '  to set your password.';
        $body =
            "
        <!doctype html>
        <html lang=\"en-US\">

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>$company</title>
        <meta name=\"description\" content=\"OTP\">
        <style type=\"text/css\">
        a:hover {text-decoration: underline !important;}
        </style>
        </head>

        <body marginheight=\"0\" topmargin=\"0\" marginwidth=\"0\" style=\"margin: 0px; background-color: #f2f3f8;\" leftmargin=\"0\">
        <!--100% body table-->
        <table cellspacing=\"0\" border=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#f2f3f8\"
        style=\"@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;\">
        <tr>
            <td>
                <table style=\"background-color: #f2f3f8; max-width:670px;  margin:0 auto;\" width=\"100%\" border=\"0\"
                    align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"
                                style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\">
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style=\"padding:0 35px;\">
                                     $message    
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                            <p style=\"font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;\">&copy; <strong>$company</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <!--/100% body table-->
        </body>

        </html>";

        $sender = "no-reply@ssentezo.com";
        // $receiver = $staff_email;
        // $name = "Ssentezo";
        $subject = "Activation email.";

        // $email_status = $EMAIL->sendMail($sender, $receiver, $subject, $message);

        // Catch any echo with in the mailing functionality
        ob_start();
        $ret = $mailer->sendMail($sender, $staff_email, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function sendStaffActivationEmailWithPassword($mailer, $staff_email, $password, $company, $domain = "")
    {

        $message = "<h3>Your details have been added to the Ssentezo system</h3> </br></br> 
                <h5>Please click the link below to set your password</h5> </br>"
            . "<a href=\"https://$domain/staff/setEmail.php?email=$staff_email\" style='background-color:#33cc00;color:white;border: 1px solid #33cc00;text-align: center;font-size: 150%;border-radius: 3px;padding:10px;'>SET YOUR PASSWORD</a>" .
            "<p>If it doesn't work, Copy and paste the link below in your web browser<p>" .
            "https://$domain/staff/setEmail.php?email=$staff_email";

        // $alt_message = 'Your details have been added to the Ssentezo system, follow the link  app.ssentezo.com/staff/setEmail.php?email=' . $staff_email . '  to set your password.';
        $body =
            "
        <!doctype html>
        <html lang=\"en-US\">

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>$company</title>
        <meta name=\"description\" content=\"OTP\">
        <style type=\"text/css\">
        a:hover {text-decoration: underline !important;}
        </style>
        </head>

        <body marginheight=\"0\" topmargin=\"0\" marginwidth=\"0\" style=\"margin: 0px; background-color: #f2f3f8;\" leftmargin=\"0\">
        <!--100% body table-->
        <table cellspacing=\"0\" border=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#f2f3f8\"
        style=\"@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;\">
        <tr>
            <td>
                <table style=\"background-color: #f2f3f8; max-width:670px;  margin:0 auto;\" width=\"100%\" border=\"0\"
                    align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"
                                style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\">
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style=\"padding:0 35px;\">
                                     $message    
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                            <p style=\"font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;\">&copy; <strong>$company</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <!--/100% body table-->
        </body>

        </html>";

        $sender = "no-reply@ssentezo.com";
        // $receiver = $staff_email;
        // $name = "Ssentezo";
        $subject = "Activation email.";

        // $email_status = $EMAIL->sendMail($sender, $receiver, $subject, $message);

        // Catch any echo with in the mailing functionality
        ob_start();
        $ret = $mailer->sendMail($sender, $staff_email, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function sendStaffPasswordResetEmail($mailer, $staff_email, $company, $domain = '')
    {
        $table = "staff";
        $message = "<h3>Your seem to have forgotten your Ssentezo password and have requested to change it.</h3> </br></br> <h5>Please click the link below to reset your password</h5> </br>" .
            "<a href=\"https://$domain/staff/setEmail.php?email=$staff_email\" style=\"background-color:#0066ff;color:white;border: 1px solid #0066ff;text-align: center;font-size: 150%;border-radius: 3px;padding:10px;\">RESET YOUR PASSWORD</a>";
        $alt_message = "You have requested a password reset, follow the link $domain/staff/setEmail.php?email=" . $staff_email . '  to reset your password.';

        $body =
            "
        <!doctype html>
        <html lang=\"en-US\">

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>$company</title>
        <meta name=\"description\" content=\"OTP\">
        <style type=\"text/css\">
        a:hover {text-decoration: underline !important;}
        </style>
        </head>

        <body marginheight=\"0\" topmargin=\"0\" marginwidth=\"0\" style=\"margin: 0px; background-color: #f2f3f8;\" leftmargin=\"0\">
        <!--100% body table-->
        <table cellspacing=\"0\" border=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#f2f3f8\"
        style=\"@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;\">
        <tr>
            <td>
                <table style=\"background-color: #f2f3f8; max-width:670px;  margin:0 auto;\" width=\"100%\" border=\"0\"
                    align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"
                                style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\">
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style=\"padding:0 35px;\">
                                     $message    
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                            <p style=\"font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;\">&copy; <strong>$company</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <!--/100% body table-->
        </body>

        </html>";

        $sender = "no-reply@ssentezo.com";
        // $receiver = $staff_email;
        // $name = "Ssentezo";
        $subject = "Password Reset";

        // $email_status = $EMAIL->sendMail($sender, $receiver, $subject, $message);

        // Catch any echo with in the mailing functionality
        ob_start();
        $ret = $mailer->sendMail($sender, $staff_email, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function addCarbonCopy($mailer, $ccEmails)
    {
        foreach ($ccEmails as $email) {
            $mailer->addCC($email['email'], $email['name']);
        }
    }
    public static function sendTicketNotificationEmail($mailer, $staff_email, $company, $domain = "", $ticketId, $cc_emails, $ticketTitle = "")
    {

        static::addCarbonCopy($mailer, $cc_emails);
        $table = "staff";
        if (strlen($domain) < 3) {
            $domain = "app.ssentezo.com";
        }
        $key = sha1(time());
        $message = "<h3>Hello, a client from $company has created a new ticket</h3> <code style=\"margin-bottom:25px;\">\" $ticketTitle ...\"</code> <br>" .
            "<br> <p><a href=\"https://$domain/Manager_Login.php?$key&action=viewTicket&authKey=$key&authType=end-to-end&id=$ticketId&enctType=none\" style=\"background-color:#0066ff;color:white;border: 1px solid #0066ff;text-align: center;font-size: 150%;border-radius: 3px;padding:10px;\">View Ticket</a></p>";

        $body =
            "
        <!doctype html>
        <html lang=\"en-US\">

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>$company</title>
        <meta name=\"description\" content=\"Ticket\">
        <style type=\"text/css\">
        a:hover {text-decoration: underline !important;}
        </style>
        </head>

        <body marginheight=\"0\" topmargin=\"0\" marginwidth=\"0\" style=\"margin: 0px; background-color: #f2f3f8;\" leftmargin=\"0\">
        <!--100% body table-->
        <table cellspacing=\"0\" border=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#f2f3f8\"
        style=\"@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;\">
        <tr>
            <td>
                <table style=\"background-color: #f2f3f8; max-width:670px;  margin:0 auto;\" width=\"100%\" border=\"0\"
                    align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"
                                style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\">
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style=\"padding:0 35px;\">
                                     $message    
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                            <p style=\"font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;\">&copy; <strong>$company</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <!--/100% body table-->
        </body>

        </html>";

        $sender = "no-reply@ssentezo.com";
        // $receiver = $staff_email;
        // $name = "Ssentezo";
        $subject = "Support ticket";

        // $email_status = $EMAIL->sendMail($sender, $receiver, $subject, $message);

        // Catch any echo with in the mailing functionality
        ob_start();

        $ret = $mailer->sendMail($sender, $staff_email, $subject, $body);

        ob_end_clean();
        // die();
        return $ret;
    }
    public static function sendOtpCodeEmail($mailer, $client_email, $company, $code)
    {

        $sender = "no-reply@ssentezo.com";
        $to       =  $client_email;
        $subject  = "OTP for $company";
        // $link = "https://client.ssentezo.com/reset_password.php?u=$username&c=$company&token=$activation_token";

        $body =
            "
        <!doctype html>
        <html lang=\"en-US\">

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>$company</title>
        <meta name=\"description\" content=\"OTP\">
        <style type=\"text/css\">
        a:hover {text-decoration: underline !important;}
        </style>
        </head>

        <body marginheight=\"0\" topmargin=\"0\" marginwidth=\"0\" style=\"margin: 0px; background-color: #f2f3f8;\" leftmargin=\"0\">
        <!--100% body table-->
        <table cellspacing=\"0\" border=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#f2f3f8\"
        style=\"@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;\">
        <tr>
            <td>
                <table style=\"background-color: #f2f3f8; max-width:670px;  margin:0 auto;\" width=\"100%\" border=\"0\"
                    align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"
                                style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\">
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style=\"padding:0 35px;\">
                                        <h1 style=\"color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">You have
                                            attempted to Login </h1>
                                        <span
                                            style=\"display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;\"></span>
                                        <p style=\"color:#455056; font-size:15px;line-height:24px; margin:0;\">
                                            A secrete one time pin (OTP) 
                                            has been generated for you. Enter the PIN, to
                                            complete your login. This OTP expires in 10 Minutes!.
                                        </p>
                                        <h1>$code</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style=\"height:20px;\">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:center;\">
                            <p style=\"font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;\">&copy; <strong>$company</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style=\"height:80px;\">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <!--/100% body table-->
        </body>

        </html>";
        // Catch any echo with in the mailing functionality
        ob_start();
        $ret = $mailer->sendMail($sender, $to, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function generateOtp($length = 6)
    {
        $min = 0;
        $max = 9;
        $quantity = $length;
        $numbers = range($min, $max);
        shuffle($numbers);
        $result = array_slice($numbers, 0, $quantity);
        $result = implode("", $result);
        return $result;
    }
    public static function generateRandomPassword()
    {
        $password = "";

        $alphaSmall = str_split("abcdefghijklmnopqrstuvwxyz");

        $alphaCaps = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $numbers = str_split("0123456789");
        $chars = str_split("!@#$%^&*()_+=-][{}|/<>:;\"");

        // $password .= $alphaCaps[rand(0, count($alphaCaps) - 1)];
        $password .= $alphaSmall[rand(0, count($alphaSmall) - 1)];
        $password .= $numbers[rand(0, count($numbers) - 1)];
        $password .= $chars[rand(0, count($chars) - 1)];
        $password .= $alphaCaps[rand(0, count($alphaCaps) - 1)];
        $password .= $alphaCaps[rand(0, count($alphaCaps) - 1)];
        $password .= $numbers[rand(0, count($numbers) - 1)];




        return $password;
    }
    public static function checkOtpExpiry($db, $user_id)
    {

        $staff = $db->select('staff', [], ['id' => $user_id])[0];
        // $db_otp = $staff['field1'];
        $time = $staff['field2'];
        $current = time();
        // $current = $date->getTimestamp();
        $diff = $current - $time;
        $mins = floor($diff / 60);
        // echo $mins;
        // die();
        if ($mins > 10) {
            // return $mins;
            return true;
        } else {
            return false;
        }
    }
    public static function activeSupportTicket($user_id, $company_id)
    {
        $manager_db = new DbAccess('ssenhogv_manager');
        // Check active tickets with the user id
        $results = $manager_db->select('support_tickets', [], ['company_id' => $company_id, 'user_id' => $user_id, 'status' => 'pending']);
        if (is_array($results) && !empty($results)) {
            return true;
        } else {
            return false;
        }
    }
    public static function allSupportTickets($user_id = false, $company_id = false)
    {
        $manager_db = new DbAccess('ssenhogv_manager');
        if ($user_id) {
            $result  = $manager_db->select('support_tickets', [], ['user_id' => $user_id, 'company_id' => $company_id, 'order by' => 'id desc']);
        } else {
            $manager_db->update('support_tickets', ['status' => 'seen'], ['status' => 'pending', 'active_flag' => 1]);
            $result  = $manager_db->select('support_tickets', [], ['order by' => 'id desc']);
        }
        return $result;
    }
    public static function getSupportTicket($id)
    {
        $manager_db = new DbAccess('ssenhogv_manager');

        $result  = $manager_db->select('support_tickets', [], ['id' => $id]);

        return $result[0];
    }
    public static function settleTickect($id)
    {
        $manager_db = new DbAccess('ssenhogv_manager');

        $result = $manager_db->update('support_tickets', ['status' => 'settled'], ['id' => $id]);

        return $result;
    }
    public static function addAdminCommentToTicket($id, $comment)
    {
        $manager_db = new DbAccess('ssenhogv_manager');
        // `id`, `ticket_id`, `comment`, `role`, `active_flag`, `del_flag`
        $data = array(
            "ticket_id" => $id,
            "comment" => $comment,
            "role" => "Admin",
        );
        $insertId = $manager_db->insert('ticket_comments', $data);
        if (is_numeric($insertId)) {
            return true;
        }
        return false;
    }
    public static function addUserCommentToTicket($id, $comment)
    {
        $manager_db = new DbAccess('ssenhogv_manager');
        // `id`, `ticket_id`, `comment`, `role`, `active_flag`, `del_flag`
        $data = array(
            "ticket_id" => $id,
            "comment" => $comment,
            "role" => "User",
        );
        $insertId = $manager_db->insert('ticket_comments', $data);
        if (is_numeric($insertId)) {
            return true;
        }
        return false;
    }
    public static function getAllTicketsComments($id)
    {
        $managerDb = new DbAccess('ssenhogv_manager');
        $comments = $managerDb->select('ticket_comments', [], ['active_flag' => 1, 'ticket_id' => $id]);
        return $comments;
    }
    public static function createSupportTicket($db, $user_id, $otp, $company_id, $title, $details)
    {
        $manager_db = new DbAccess('ssenhogv_manager');
        $hashedOtp = md5($otp);
        $time = time();

        $ret = $db->update('staff', ['field1' => $hashedOtp, 'field2' => $time], ['id' => $user_id]);
        $data = array(
            'company_id' => $company_id,
            'user_id' => $user_id,
            'created_at' => $time,
            'status' => 'pending',
            'token' => $hashedOtp,
            'title' => $title,
            'details' => $details
        );
        $insertId = $manager_db->insert('support_tickets', $data);
        if (is_numeric($ret) && is_numeric($insertId)) {
            ActivityLogger::logActivity($user_id, "Create Support Ticket", "Success", "Request sent");
            return $insertId;
        } else {
            ActivityLogger::logActivity(
                $user_id,
                "Create Support Ticket",
                "Failed",
                "Reason for failure could be Update:$ret or Insertion: $insertId"
            );

            return false;
        }
    }

    public static function saveOtpToDb($db, $user_id, $otp)
    {
        $hashedOtp = md5($otp);
        $time = time();
        $ret = $db->update('staff', ['field1' => $hashedOtp, 'field2' => $time], ['id' => $user_id]);
        if (is_numeric($ret)) {
            return true;
        } else {
            return false;
        }
    }
    public static function verifyOtp($db, $user_id, $otp)
    {
        $staff = $db->select('staff', [], ['id' => $user_id])[0];
        $db_otp = $staff['field1'];
        $time = $staff['field2'];
        $hashedOtp = md5($otp);
        if (strcmp($db_otp, $hashedOtp) == 0) {
            //After verification Expire the OTP
            $time = $time - (10 * 60);
            $db->update('staff', ['field2' => $time], ['id' => $user_id]);
            return true;
        } else {
            return false;
        }
    }

    public static function login($ticketId)
    {

        $manager_db = new DbAccess('ssenhogv_manager');
        $result  = $manager_db->select('support_tickets', [], ['id' => $ticketId, 'active_flag' => 1]);
        $ticket = $result[0];
        $user_id = $ticket['user_id'];
        $token = $ticket['token'];
        $company_id = $ticket['company_id'];
        $company = $manager_db->select('company', [], ['id' => $company_id])[0];
        $database = $company['Data_base'];
        $db = new DbAccess($database);
        $company_name = $company['name'];
        $senderId = $company['senderId'];
        $company_db = $database;
        $otp = $token;
        if ($result == true) {
            $result = $manager_db->update('support_tickets', ['status' => 'In Progress'], ['id' => $ticketId]);

            ActivityLogger::logActivity($user_id, "OTP Confirmation", "Success", "OTP verification successful, Login successfl");
            $cols = "s.id,s.branch_id,s.role_id,s.fname,s.lname,s.email,s.gender,s.phone_no,s.dob,s.pic, r.name,r.actions";
            $q = 'SELECT ' . $cols . ' FROM staff s,staff_roles r WHERE s.role_id=r.id and s.id=' . "'" . $user_id . "'";
            $user = $db->selectQuery($q);
            $userHere = $user[0];
            // print_r($user);
            session_destroy();
            session_start();

            $_SESSION[AppConstants::$USER] = $userHere;
            // die("Heree we go");

            $_SESSION['company'] = $company_name;
            $_SESSION['senderId'] = $senderId;
            $_SESSION['company_db'] = $company_db;
            $_SESSION["email"] = $userHere['email'];
            $_SESSION["phone_no"] = $userHere['phone_no'];
            $_SESSION["pic"] = $userHere['pic'];
            $_SESSION["role"] = $userHere['name'];
            $_SESSION['user_id'] = $userHere['id'];
            $_SESSION["fullname"] = $userHere['fname'] . ' ' . $userHere['lname'];
            $_SESSION["lname"] = $userHere['lname'];

            $_SESSION["actions"] = $userHere['actions'];
            $_SESSION["role"] = $userHere['name'];
            $_SESSION["pic"] = $userHere['pic'];
            // echo json_encode($_SESSION)
            // die();

            //echo "the system is rebooting"
            header("location:../home/home_branch.php");
            die();
        } else {
            $error =  "Wrong OTP! <br> Make sure you are entering a valid OTP";
            ActivityLogger::logActivity($user_id, "OTP Verification ", "Failed", "Wrong OTP");
        }
    }
    public static function loginPossible($ticketId)
    {
        $manager_db = new DbAccess('ssenhogv_manager');
        $result  = $manager_db->select('support_tickets', [], ['id' => $ticketId, 'active_flag' => 1]);
        $ticket = $result[0];
        $user_id = $ticket['user_id'];
        $token = $ticket['token'];
        $company_id = $ticket['company_id'];
        $company = $manager_db->select('company', [], ['id' => $company_id])[0];
        $database = $company['Data_base'];
        $db = new DbAccess($database);
        // $company_name = $company['name'];
        // $senderId = $company['senderId'];
        // $company_db = $database;
        $person = $db->select('staff', [], ['id' => $user_id, 'field1' => $token]);
        if (is_array($person) && !empty($person)) {
            return true;
        } else {
            return false;
        }
    }
}
// echo Auth::generateOtp();