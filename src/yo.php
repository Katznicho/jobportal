<?php
//  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Name:  Yo Payments
 *
 * Author: Arnold Mwumva Ford
 *         ford@meridiansoftech.net / fordarnold@gmail.com
 *         @fordarnold
 *
 * Location: Kampala, Uganda
 *
 * Created:  24/07/2012
 *
 * Description:  Library to work with Yo! Payments API.
 *
 *
 */


class Yo
{
    protected $_ci;
    protected $mode;
    protected $api_username;
    protected $api_password;
    protected $sandbox_url;

    protected $status_message;
    protected $transaction_id;

    function __construct()
    {
        // get settings from config
        $this->mode        = 'live';
        // $this->mode        ='sandbox';
        $this->api_username = '100642266324'; //live uname
        // $this->api_username ='90008431667';  //sand box uname
        // $this->api_username ='anishinani';  //sand box uname
        $this->api_password  = 'fbNU-QoEg-Fmfd-paEu-zOzu-hH80-tG8u-jBcm'; //live password
        // $this->api_password  = '4biB-9qYR-bjHF-8W0e-KPvW-26qi-Yb0O-qmZR'; // sandbox password
        //   $this->api_password  = 'log10tan10'; // sandbox password
        // Get appropriate API endpoint
        if ($this->mode == 'sandbox')
            $this->api_endpoint = "https://41.220.12.206/services/yopaymentsdev/task.php";
        else
            $this->api_endpoint = "https://paymentsapi1.yo.co.ug/ybs/task.php";
    }

    /*getters */
    public function getUserName()
    {
        return $this->api_username;
    }
    public function getPassword()
    {
        return $this->api_password;
    }
    public function getModel()
    {
        return $this->mode;
    }
    /**
     * Deposit funds into Yo! Payments account from a phone's Mobile Money account
     * 
     * 
     * @param float $amount The amount of money to deposit
     * @param string $phone Phone number to pull Mobile Money from <br> [Format]: 256772123456
     * @param string $narrative A description of the transaction 
     * @param string $ref_text The text to be returned to the user's phone after the transaction is complete
     * 
     * @return xml The XML Request String to be sent to the Yo! Payments Server
     *
     * ------------------------------------------------------------------------------------------------------------------
     *
     */
    public function deposit($amount, $phone, $narrative, $ref_text = "")
    {
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml_request = $xml_request . '<AutoCreate>';
        $xml_request = $xml_request . '<Request>';
        $xml_request = $xml_request . '<APIUsername>' . $this->api_username . '</APIUsername>';
        $xml_request = $xml_request . '<APIPassword>' . $this->api_password . '</APIPassword>';
        $xml_request = $xml_request . '<Method>acdepositfunds</Method>';
        $xml_request = $xml_request . '<NonBlocking>FALSE</NonBlocking>';
        $xml_request = $xml_request . '<Amount>' . $amount . '</Amount>';
        $xml_request = $xml_request . '<Account>' . $phone . '</Account>';
        $xml_request = $xml_request . '<Narrative>' . $narrative . '</Narrative>';
        $xml_request = $xml_request . '<ProviderReferenceText>' . $ref_text . '</ProviderReferenceText>';
        $xml_request = $xml_request . '</Request>';
        $xml_request = $xml_request . '</AutoCreate>';

        return $this->send_xml_request($xml_request);
    }

    /**
     * Withdraw funds from Yo! Payments account and add to a phone's Mobile Money account
     * 
     * 
     * @param float $amount The amount of money to withdraw
     * @param string $phone Phone number to add Mobile Money to <br> [Format]: 256772123456
     * @param string $narrative A description of the transaction 
     * @param string $ref_text The text to be returned to the user's phone after the transaction is complete
     * 
     * @return xml The XML Request String to be sent to the Yo! Payments Server
     *
     * ------------------------------------------------------------------------------------------------------------------
     *
     */
    public function withdraw($amount, $phone, $narrative = "", $ref_text = "")
    {
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml_request = $xml_request . '<AutoCreate>';
        $xml_request = $xml_request . '<Request>';
        $xml_request = $xml_request . '<APIUsername>' . $this->api_username . '</APIUsername>';
        $xml_request = $xml_request . '<APIPassword>' . $this->api_password . '</APIPassword>';
        $xml_request = $xml_request . '<Method>acwithdrawfunds</Method>';
        $xml_request = $xml_request . '<NonBlocking>FALSE</NonBlocking>';
        $xml_request = $xml_request . '<Amount>' . $amount . '</Amount>';
        $xml_request = $xml_request . '<Account>' . $phone . '</Account>';
        $xml_request = $xml_request . '<Narrative>' . $narrative . '</Narrative>';
        $xml_request = $xml_request . '<ProviderReferenceText>' . $ref_text . '</ProviderReferenceText>';
        $xml_request = $xml_request . '</Request>';
        $xml_request = $xml_request . '</AutoCreate>';

        return $this->send_xml_request($xml_request);
    }

    /**
     * Implement cURL request to Yo! Servers
     *
     * ------------------------------------------------------------------------------------------------------------------
     */

    public function send_xml_request($xml_request)
    {
        try {
            $ch = curl_init(); //initiate the curl session 

            curl_setopt($ch, CURLOPT_URL, $this->api_endpoint); //set to API endpoint
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // tell curl to return data in a variable 
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Content-length: " . strlen($xml_request)));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request); // post the xml 
            curl_setopt($ch, CURLOPT_TIMEOUT, (int)120); // set timeout in seconds 

            $xml_response = curl_exec($ch);

            if ($xml_response === FALSE)
                $this->status_message = 'The transaction failed. Please try again.'; //show_error('There was an error connecting to Yo! Payments. Please try again.');

            curl_close($ch);

            // Get Status
            // $status_code = $this->get_status($xml_response);

            return $xml_response;
        } catch (Exception $e) {
            $this->status_message = $e->getMessage();
        }
    }

    /**
     * Get the Status Message for the Request
     *
     * ------------------------------------------------------------------------------------------------------------------
     */

    public function get_status($xml_response)
    {
        $xml = simplexml_load_string($xml_response);
        if (!$xml) {
            return 'false';
        } else {
            $this->status_message = $xml->Response->StatusMessage;
            return $xml->Response->StatusCode;
        }
    }

    public function getStatusMessage()
    {
        return $this->status_message;
    }

    /**
     * Log any Errors encountered
     *
     * ------------------------------------------------------------------------------------------------------------------
     */
    //this is not yet implements
    // but its a good method_exists
    public function log_errors($xml_response)
    {
        // TODO: log errors to file / database
    }


    public function getYoRef($xml_response)
    {


        $xml = simplexml_load_string($xml_response);
        if (!$xml) {
            return 'false';
        } else {
            // $this->status_message = $xml->Response->StatusMessage;
            return $xml->Response->TransactionReference;
        }
    }
}
// withdraw($amount, $phone, $narrative="", $ref_text="")
// $yo = new Yo();
// $xml_response = $yo->withdraw(31000, 256772093837, $narrative = "helo this  \n this is just a test", $ref_text = "");
////withdraw($amount, $phone, $narrative="", $ref_text="")
////echo $xml_response;
// echo $yo->get_status($xml_response);



/* End of file Yo.php */
