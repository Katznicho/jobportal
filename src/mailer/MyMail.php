<?php

namespace Ssentezo\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

class MyMail extends PHPMailer
{
    private $_host      = "server1.thinkxcloud.com";     //'your stmp server name'
    private $_user      = "no-reply@ssentezo.com";   //'your smtp username'
    private $_password  = "!Log10tan10";                 // 'your password'
    private $_name      =  "Ssentezo";

    public function __construct($exceptions = true)
    {
        $this->SMTPDebug = 1;

        // 0 = no output, 1 = errors and messages, 2 = messages only.


        $this->IsSMTP();
        $this->Host = $this->_host;
        $this->Port = "465";
        // //usually the port for TLS is 587, for SSL is 465 and non-secure is 25
        $this->SMTPSecure = "ssl";
        // //TLS, SSL or  delete the line
        $this->SMTPAuth = true;
        $this->Username = $this->_user;
        $this->Password = $this->_password;
        $this->From     = $this->_user;
        $this->FromName = $this->_name;
        $this->IsHTML(true);
        parent::__construct($exceptions);
    }

    public function sendMail($from, $to, $subject, $body)
    {
        $this->From = $this->_user;
        $this->AddAddress($to, $this->_name);
        $this->Subject = $subject;
        $this->Body = $body;

        if (!$this->Send()) {
            // echo 'Mailer error: '.$this->ErrorInfo;   //  Invalid address: (addAnAddress to): Creditplus Account Activation 

            return "failed";
        } else {

            return "success";
        }
    }

    public function PDF_Attachment($from, $to, $subject, $body, $pdf)

    {

        $this->From = $this->_user;
        $this->AddAddress($to, $this->_name);
        $this->Subject = $subject;
        $this->Body = $body;
        $this->AddAttachment($pdf, '', $encoding = 'base64', $type = 'application/pdf');



        if (!$this->Send()) {
            return "failed";
        } else {
            return "success";
        }
    }
}
