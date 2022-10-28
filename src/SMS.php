<?php

class SMS
{
    private  $mode;
    private $endpoint;

    function __construct()
    {
        //$this->mode="live";
        $this->mode = "live";

        if ($this->mode == "live") {
            $this->endpoint = "https://sms.thinkxsoftware.com/sms_api/api.php?";
        } else {
            $this->endpoint = "127.0.0.1:8080/sms_api/api.php?";
        }
    }

 

    public function sms($message, $receivers = array(), $status,$username="ssentezo")
    {
        if ($status == 1) {
            $receipients = "";
            foreach ($receivers as $receiver) {
                $receipients .= $receiver . ",";
            }
            $receipients = substr($receipients, 0, -1);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->endpoint . "link=sendmessage");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "user=".$username."&password=log10tan10&message=" . urlencode($message) . "&reciever=" . $receipients);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $hamza = curl_exec($ch);
            curl_close($ch);
            return substr($hamza, 0, 4); // "1701" indicates success */
        }
    }

    public function buysms($amount, $username, $mobile)
    {


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . "link=buysms");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=" . $amount . "&username=" . $username . "&mobile=" . $mobile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $hamza = curl_exec($ch);
        curl_close($ch);
        return substr($hamza, 0, 4); // "1701" indicates success */

    }

    public function smsbalance($username)
    {


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . "link=smsbalance");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "&username=" . $username);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $hamza = curl_exec($ch);
        curl_close($ch);
        return substr($hamza, 0, 4); // "1701" indicates success */
        //echo $hamza        
    }
}


