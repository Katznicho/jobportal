<?php

namespace Ssentezo\Clients\Activators;

class Activator
{
    public $client_email;
    public $client_mobile;
    public $company_name;
    public $activation_token;
    public $client_domain;
    public $errors = [];

    public function __construct($client_id)
    {
        $this->user_email = $client_id;
        $this->company_name;
    }
    public function getErrors()
    {
        return array(
            "error" => true,
            "message" => implode(", ", $this->errors)
        );
    }
}
