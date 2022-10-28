<?php

namespace Ssentezo\Clients\Activators;

use ClientActivation;
use DateTime;

class WebActivator extends Activator
{
    public $username;
    public $client_id;
    public $lient_email;
    public $company_name;
    public $force;

    /**
     * Create a client web activator to activate a clients for ssentezo client web
     * @param int $client_id The id of the client to activate
     * @param string $client_email The email of the client
     * @param string $username The username of the client
     * @param bool $force A flag to indicate whether to force activation or not
     * 
     */
    public function __construct($client_id, $client_email, $username, $company_name, $force = false)
    {
        $this->client_id = $client_id;
        $this->client_email = $client_email;
        $this->username = $username;
        $this->force = $force;
        $this->company_name = $company_name;
    }

    /**
     * Verify if the activation is okay and has no conflicts or errors
     * @return bool true|false Returns true if the verification was successful and false other wise
     */
    public function verify($db)
    {
        if (!$this->client_email) {
            $this->errors[] = "Please an email is required to active the client";
            return false;
        }

        if ($this->force) {
            return true;
        }
        // It's now time to check if the email is really for this client
        $email_status = ClientActivation::isEmailHis($db, $this->client_email, $this->client_id);
        if ($email_status['error'] == true) {
            $message = $email_status['message'];
            $this->errors[] = $message;
            return false;
        }
        if (ClientActivation::activated($db, $this->client_id)) {
            $this->errors[] = "Client already activated, You can used force activation to activate again";
            return false;
        }
        if (ClientActivation::usernameAlreadyExists($db, $this->username) && !ClientActivation::activationLinkExpired($db, $this->username)) {
            $this->errors[] = "Activation already in progress. Please use force activation to continue";
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
    public function activate($db, $mailer, $domain, $company_id)
    {
        $activation_token = ClientActivation::generateToken();

        $date = new DateTime();
        // Current time to track expiry of link
        $date = $date->getTimestamp();


        $result = ClientActivation::sendActivationEmailWeb(
            $mailer,
            $this->client_email,
            $this->username,
            $this->company_name,
            $activation_token,
            $domain
        );

        $hashed_token = md5($activation_token);
        // Insert into the database only when email is successfully sent.
        if ($result == "success") {

            if ($this->force) { //Incase of force you should update the record, don't delete as it's used by the mobile activation as well

                $data =  [
                    'username' => $this->username,
                    'email' => $this->client_email,
                    'created_at' => $date,
                    'user_id' => $this->client_id,
                    'account_activation_token' => $hashed_token,
                ];
                $update_count = $db->update('clients', $data, ['username' => $this->username]);
                if ($update_count > 0) {
                    $db->update('borrower', ['email' => $this->client_email], ['id' => $this->client_id]);

                    return array(
                        "error" => false,
                        "message" => "Success, An activation email has been sent to $this->client_email. It expires in 24 Hours"
                    );
                }
                //else continue meaning no record is expexted to be there
            }


            $data =   [
                'username' => $this->username,
                'email' => $this->client_email,
                'created_at' => $date,
                'user_id' => $this->client_id,
                'account_activation_token' => $hashed_token,
            ];
            $insertId = $db->insert('clients', $data);

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
