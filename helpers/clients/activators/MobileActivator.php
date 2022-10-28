<?php

namespace Ssentezo\Clients\Activators;

use ClientActivation;
use DateTime;

class MobileActivator extends Activator
{
    public $username;
    public $client_id;
    public $lient_email;
    public $company_name;
    public $force;
    public $errors;

    /**
     * Create a client web activator to activate a clients for ssentezo client web
     * @param int $client_id The id of the client to activate
     * @param string $client_email The email of the client
     * @param string $username The username of the client
     * @param bool $force A flag to indicate whether to force activation or not
     * 
     */
    function __construct($client_id, $client_email, $username, $company_name, $force = false)
    {
        $this->client_id = $client_id;
        $this->client_email = $client_email;
        $this->username = $username;
        $this->force = $force;
        $this->company_name = $company_name;
    }

    public function verify($db)
    {
        if ($this->force) {
            return true;
        }
        if (ClientActivation::activated($db, $this->client_id)) {
            $this->errors[] = "Client already activated, You can use force to by pass this check";
            return false;
        }
        if (ClientActivation::usernameAlreadyExists($db, $this->username) && !ClientActivation::activationLinkExpired($db, $this->username)) {
            $this->errors[] = "Activation in progress, You can bypass this checking by using force";
            return false;
        }

        return true;
    }
    /**
     * Activate the client for web
     * @param DbAccess $db The database connectiuon of the comapany
     * @param Mailer $mailer A mailer object 
     * @param string $domain The domain on which the ssentezzo client is running
     * @return array An associative array with two keys error and message if error is true it means an error occurred
     */
    public function activate($db, $mailer)
    {
        $otp = ClientActivation::generateOTP();
        $result = ClientActivation::sendActivationEmailMobile($mailer, $this->client_email, $this->username, $this->company_name, $otp);
        // Insert into the database only when email is successfully sent.
        if ($result == "success") {
            // echo json_encode(array(
            $db->update('borrower', ['email' => $this->client_email], ['id' => $this->client_id]);
            $date = new DateTime();
            // Current time to track expiry of link
            $date = $date->getTimestamp();

            $insertId =  $db->insert(
                'clients',
                [
                    'username' => $this->username,
                    'email' => $this->client_email,
                    // 'password',
                    'created_at' => $date,
                    // 'password_reset_token',
                    // 'last_seen',
                    'user_id' => $this->client_id,
                    // 'account_activation_token' => $hashed_token,
                    'otp_token' => md5($otp),
                ]
            );
            if (is_numeric($insertId)) {
                $error = false;
                $message = "Success, An activation email has been sent to $this->client_email. It expires in 24 Hours";
            } else {
                $error = true;
                $message = $result . "Activation email sent but failed to save user with reason $insertId";
            }
        } else {
            $error = true;
            $message = $result . "Failed to send activation link, Check to see if the email is valid or Contact system Admin for support";
        }
        return array(
            "error" => $error,
            "message" => $message
        );
    }
}
