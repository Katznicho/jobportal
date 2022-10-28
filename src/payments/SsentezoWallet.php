<?php

namespace App\Payments;

use DbAcess;
use App\Database\DbAccess;
use App\Payments\Wallet;
use App\Util\ActivityLogger;
use App\Util\Logger;
use Unirest\Request;
use Unirest\Request\Body;
//import the class that will be used to make the request




class SsentezoWallet extends Wallet
{
    private $name;
    private $provider;
    private $username;
    private $password;
    private $environment = "production";
    protected $endPoint = "https://wallet.ssentezo.com/api/";
    private $payload;
    private $currency = "UGX";
    private $callback = '';
    private $db;
    /**
     * Instantiates a ssentezo wallet object which will be used to make real money transactions 
     * @param string $username The ssentezo wallet api username of the company
     * @param string $password The ssentezo wallet api password of the company 
     */
    function __construct($manager_db, $company_id)
    {


        $this->db = new DbAccess($manager_db);

        //$this->db = new DbAcess($manager_db);
        //Get the ssentezo wallet credentials of the company

        $wallet = $this->db->select('wallets', [], ['company_id' => $company_id])[0];
        if (!empty($wallet)) {
            $this->name = $wallet['name'];
            $this->provider =  trim($wallet['provider']);
            $this->username = trim($wallet['username']);
            $this->password = trim($wallet['password']);
        } else {
            throw new \Exception("Wallet not found");
        }
    }

    public function withdraw($msisdn, $amount, $reason, $externalReference)
    {

        $this->payload = array(
            "msisdn" => $msisdn,
            "amount" => $amount,
            "reason" => $reason,
            "externalReference" => $externalReference,
            "callback" => 'https://app.ssentezo/savings/withdraw_callback.php',
            "currency" => $this->currency,
            "environment" => $this->environment,
        );




        $this->setEndPoint($this->endPoint . "withdraw");

        $response =  $this->sendRequestNew();

        ActivityLogger::logActivity(0, "Send Withdraw Request", "success", "Successful");
        return $response;
    }

    /**
     * @param string $msisdn  The mobile number which will be prompted to authoroze the transaction
     * @param double $amount The amount to be deposited
     * @param string $reason The reason for the deposit,
     * @param string $externalReference An identifier to this transaction which will be passed in the callback
     * and can be used to check the status of the transaction.
     * @return true|false It's true if success and false if failed
     */
    public function formatMobileLocal($mobile)
    {
        $length = strlen($mobile);
        $m = '0';
        //format 1: +256752665888
        if ($length == 13)
            return $m .= substr($mobile, 4);
        elseif ($length == 12) //format 2: 256752665888
            return $m .= substr($mobile, 3);
        elseif ($length == 10) //format 3: 0752665888

            return $mobile;
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }

    public function deposit($msisdn, $amount, $reason, $externalReference)
    {
        $this->payload = array(
            "msisdn" => $this->formatMobileLocal($msisdn),
            "amount" => $amount,
            "reason" => $reason,
            "externalReference" => $externalReference,
            "callback" => 'https://app.ssentezo.com/ssentezo/savings/savings_depositcallback.php',
            "currency" => $this->currency,
            "environment" => $this->environment,
        );

        $this->setEndPoint($this->endPoint . "deposit");
        $response =  $this->sendRequestNew();
        ActivityLogger::logActivity(0, "Deposit successfull", "success", "Successful");
        return $response;
    }

    public function deposit_ussd($msisdn, $amount, $reason, $externalReference, $phone_number)
    {
        $this->payload = array(
            "msisdn" => $this->formatMobileLocal($msisdn),
            "amount" => $amount,
            "reason" => $reason,
            "externalReference" => $externalReference,
            "callback" => 'https://app.ssentezo.com/ssentezo/savings/savings_depositcallback.php',
            "currency" => $this->currency,
            "environment" => $this->environment,
        );




        $this->setEndPoint($this->endPoint . "deposit");

        $response =  $this->sendRequestNew();
        ActivityLogger::logActivity(0, "Deposit successfull", "success", "Successful");
        return $response;
    }

    

    /**
     * Sets the end point of the api
     * @param string $url A full url of the endpoint i.e https://wallet.ssentezo.com/api/deposit
     * 
     */
    public function setEndPoint($url)
    {
        $this->endPoint = $url;
    }


    /**
     * Returns the current end point of the api
     * @return string A full url of the current endpoint
     * 
     */
    public function getEndPoint()
    {
        return $this->endPoint;
    }

    /**
     * This is a private method that makes the api calls
     * @return string a string containing the response.
     */
    private function sendRequest()
    {
        $ch = curl_init($this->endPoint);
        // $ch = curl_init("localhost/ssentezo/sample.php");
        curl_setopt($ch, CURLOPT_HEADER, 0); //Remove the headers from the response
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); //The response will be injson format
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function sendRequestNew()
    {


        //set headers to form data
        $headers = array('Content-Type: multipart/form-data');
        //set body to form data
        $body = $this->payload;
        Request::auth($this->username, $this->password);
        $response = Request::post($this->endPoint, $headers, $body);

        return $response;
    }
}
