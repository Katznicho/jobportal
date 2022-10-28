<?php
class ClientActivation
{
    private static $domain = 'https://member.premieremployeesacco.com/ssentezo-client/';
    public static function generateToken()
    {
        $random_number = rand();
        $token = md5($random_number);
        return $token;
    }
    public static function isEmailHis($db, $email, $borrower_id)
    {
        $result = $db->select('borrower', [], ['email' => $email, 'active_flag' => 1, 'del_flag' => 0]);
        if ($result) {
            if (count($result) > 1) {
                $message = "Email conflicts detected, Different clients are having the same email
            , please first solve this conflict";
                $error = true;
            } else {
                if ($result[0]['id'] == $borrower_id) {
                    $message = "success";
                    $error = false;
                } else {
                    $client_name = $result[0]['fname'] . $result[0]['lname'];
                    $error = true;
                    $message = "This email belongs to a different client, $client_name";
                }
            }
        } else {
            $error = false;
            $message = "It's a new email";
        }
        return ["error" => $error, "message" => $message];
    }
    public static function generateUsername($userEmail, $company_id)
    {
        $first_email_part = strtok($userEmail, '@');
        $server_username = $company_id . '@' . $first_email_part;
        return $server_username;
    }
    public static function usernameAlreadyExists($db, $username)
    {
        $result = $db->select('clients', [], ['username' => $username]);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function activationLinkExpired($db, $username)
    {
        $result = $db->select('clients', [], ['username' => $username]);
        if ($result[0]) {
            $initiated_at = $result[0]['created_at'];
            $date = new DateTime();
            $current = $date->getTimestamp();
            $diff = $current - $initiated_at;
            $mins = floor($diff / 60);
            if ($mins > 10) {
                return $mins;
            } else {
                return false;
            }
        }
    }

    public static function generateOTP()
    {
        $date = new DateTime();
        $current = $date->getTimestamp();
        $otp = substr($current, strlen(strval($current)) - 6, strlen(strval($current)));
        return $otp;
    }

    public static function generatePin()
    {
        $date = new DateTime();
        $current = $date->getTimestamp();
        $pin = substr($current, strlen(strval($current)) - 4, strlen(strval($current)));
        return $pin;
    }


    private static function formatOTP($otp)
    {
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);
        $otp = substr_replace($otp, '-', 3, 0);
        return $otp;
    }

    //send web email to client
    public static function sendActivationEmailWeb($mailer, $client_email, $username, $company, $activation_token, $client_domain = '')
    {
        $to       =  $client_email;
        $subject  = $company . " Account Activation";

        $link = "https://" . $client_domain . "confirm_account.php?u=$username&c=$company&token=$activation_token";

        $body =  "<!DOCTYPE html>
        <html>
        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>Password Reset $company</title>
        <meta name=\"description\" content=\"Account Activation email.\">
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
                                        <h1 style=\"color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">
                                        Account activation. 
                                        </h1>
                                        <h2><br> Your username is </h2>
                                        <h1 style=\"color:green; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">$username</h1>
                            
                                        <span
                                            style=\"display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;\"></span>
                                        <p style=\"color:#455056; font-size:15px;line-height:24px; margin:0;\">
                                            A unique link to set up your
                                            account has been generated for you. To activate your account, click the
                                            following link and follow the instructions. The link expires in 10 Minutes!.
                                        </p>
                                        <a href=\"$link\"
                                            style=\"background:#20e277;text-decoration:none !important; font-weight:500; margin-top:35px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;\">Activate
                                            Account</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style=\"height:40px;\">
                                    <p>If the button doesn't work, Copy and paste this link into your web browser
                                    <a href=\"$link\">$link</td>
                                </tr>
                                <tr>
                                 <td style=\"height:20px;\">&nbsp;</td>
                                <br>
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
        $ret = $mailer->sendMail($mailer->From, $to, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function sendActivationEmailMobile($mailer, $client_email, $username, $company, $otp)
    {
        $to       =  $client_email;
        $subject  = $company . " Account Activation";
        $otp = self::formatOTP($otp);


        $body =  "<!DOCTYPE html>
        <html>

        <head>
        <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />
        <title>Activate Mobile App $company</title>
        <meta name=\"description\" content=\"Account Activation email.\">
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
                                        <h1 style=\"color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">
                                        Account activation. 
                                        </h1>
                                        <h2><br> Your username is </h2>
                                        <h1 style=\"color:green; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">$username</h1>
                                        <h2><br> Your One Time Password is </h2>
                                        <h1 style=\"color:green; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;\">
                                         $otp
                
                                        </h1>
                                        <p><br> This is a one time password that will be used to activate your account </p>
                                        
                                        <h2><br> Get the Client App </h2>
                                        <p><br> Download the free client  android or ios app to get started </p>
                                        
                                        <a href=\"https://play.google.com/store/apps/details?id=com.client.app\" style=\"color:#1e1e2d; text-decoration:none;\">
                                            <button style=\"background-color:#1e1e2d; border-radius:3px; border:none;cursor:pointer; padding:10px 20px; color:#fff; font-weight:500; font-size:16px;\">
                                                Download Client App
                                            </button>
                                        </a>

                                        

                                    </td>
                                </tr>
                                
                            </table>
                        </td>
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
        $ret = $mailer->sendMail($mailer->From, $to, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function sendPasswordRecoveryEmail($mailer, $client_email, $username, $company, $activation_token, $client_domain = '')
    {
        $to       =  $client_email;
        $subject  = $company . " Password Reset";
        $link = "https://$client_domain/reset_password.php?u=$username&c=$company&token=$activation_token";

        $body =
            "
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
        // Catch any echo with in the mailing functionality
        ob_start();
        $ret = $mailer->sendMail($mailer->From, $to, $subject, $body);
        ob_end_clean();
        // die();
        return $ret;
    }
    public static function activated($db, $user_id)
    {
        $result = $db->select('clients', [], ['user_id' => $user_id]);

        if (strlen($result[0]['password']) >= 32 || strlen($result[0]['password_reset_token']) >= 32) {
            return true;
        } else {
            return false;
        }
    }
    public static function sendFeedback($error, $message)
    {
        echo json_encode(array(
            "error" => $error,
            "message" => $message
        ));
    }
}
