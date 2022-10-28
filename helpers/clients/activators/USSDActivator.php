<?php

namespace Ssentezo\Clients\Activators;

use ClientActivation;
use DateTime;
use Ssentezo\Database\DbAccess;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\Sms;

class USSDActivator extends Activator
{
    public $client_id;
    public $phone_number;
    public $force;
    public $company_name;
    public $company_id;
    /**
     * Create a client web activator to activate a clients for ssentezo client ussd
     * @param int $client_id The id of the client to activate
     * @param string $client_email The email of the client
     * @param string $username The username of the client
     * @param bool $force A flag to indicate whether to force activation or not
     * 
     */
    function __construct($client_id, $phone_number, $company_name, $force = false, $company_id)
    {
        $this->phone_number = $phone_number;
        $this->client_id = $client_id;
        $this->force = $force;
        $this->company_name = $company_name;
        $this->company_id = $company_id;
    }


    public function activate($db)
    {
        $db->update('borrower', ['ussd_phonenumber' => $this->phone_number], ['id' => $this->client_id]);
        $manager_db = new DbAccess("ssenhogv_manager");

        $company_details = $manager_db->select('company', [], ['name' => $this->company_name])[0];
        $platform = $manager_db->select('platforms', ['id'], ['name' => 'USSD']);
        $receiver[] = $this->phone_number;
        $pin = ClientActivation::generatePin();
        $sms = new Sms();
        $expiry_time = new DateTime();
        $expiry_time->modify('+ 10 minutes');


        if ($this->force) { //For forced activation, just update

            $receiver[] = $this->phone_number;

            $data = [
                'phone_number' => $this->phone_number,
                'user_id' => $this->client_id,
                'company_id' => $company_details['id'],
                'otp_token' => md5($pin),
                'pin' => '',
                'pin_reset_token' => '',
                'is_suspended' => 0,
                'is_otp_verified' => 0,
                'expires_at' => $expiry_time->format('Y-m-d H:i:s')

            ];

            $update_count =  $manager_db->update('ussd_clients', $data, ['phone_number' => $this->phone_number]);

            if (is_numeric($update_count) and $update_count > 0) { //Means there has been an update at least

                $message = "Your temporary pin {$pin} dail *217*146# to set your pin";
                $sms->sms($message, $receiver, true);

                $error = false;
                $message = "Success, An pin has been  to sent  $this->phone_number. It expires in 10 minutes";
                return array(
                    "error" => $error,
                    "message" => $message
                );
            }
            //If this fails continue to the next section
        }


        $data = [
            'phone_number' => $this->phone_number,
            'user_id' => $this->client_id,
            'company_id' => $company_details['id'],
            'otp_token' => md5($pin),
            'expires_at' => $expiry_time->format('Y-m-d H:i:s')
        ];
        $insertId = $manager_db->insert('ussd_clients', $data);
        if (is_numeric($insertId)) {

            //Send the user an sms for getting started
            $receiver[] = $this->phone_number;
            $message = "Your temporary pin {$pin} dial *217*146# to set your pin";
            $sms->sms($message, $receiver, true);

            //Add an entry in the platform activations table
            $db->insert(
                'platform_activations',
                ['platform_id' => $platform[0]['id'], 'user_id' => $this->user_id, 'staff_id' => AppUtil::userId()]
            );
            $error = false;
            $message = "Success, A pin has been   sent to  $this->phone_number. It expires in 10 minutes";
        } else {
            $error = true;
            $message =  "This error has occured $insertId";
        }
        return array(
            "error" => $error,
            "message" => $message
        );
    }
    public function verify()
    {
        $manager_db = new DbAccess("ssenhogv_manager");

        // print_r($this);
        if (!AppUtil::sms_check($manager_db, $this->company_name)) { //Verify the company has sms turned on as this activation entirely depends on sms
            $this->errors[] = "You can't activate USSD when sms is off. Please turn on sms to continue";
            return false;
        }


        if ($this->force) { //If it's a forced activation, it must be true
            return true;
        }

        //check if phone number exists
        $phone_number_exists =  $manager_db->select("ussd_clients", [], ['phone_number' => $this->phone_number, 'user_id' => $this->client_id]);
        if (count($phone_number_exists) > 0) { //Fail the verification if the number already activated
            $this->errors[] = "Client already activated for ussd, Please use forced activation to activate again";
            return false;
        }

        //When it has passed all the checks return true implying successful verification
        return true;
    }
}
