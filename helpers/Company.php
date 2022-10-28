<?php

/**
 * @author Benjamin Daaki, Thinkx Sofware
 */
class Company
{
    protected $status = 0; //Status of the company, Can be suspended 0 or active 1, suspended at creation
    protected $units = 0; // Total units available initially 0
    protected $twoFactorAuthStatus = 0; // The twho factor aunthentication status 0 means deactive 1 means active
    protected $smsStatus = 0; // The ststus of sms 0 means off, 1 means active
    protected $companyName;   //Name of the comapany
    protected $companyTel;  // The phone number for the company
    protected $address; // The physical address or location
    protected $email; // the Company's email address
    protected $senderId;  // Sender id is only available if the company subscribed to sending of sms's
    protected $databaseName;  // Name of database, each company has a unique database
    protected $unitCharge = 250;  // Charge per unit, It varies but it's 250 shs by default
    protected $totalUnits = 0;  // Total units the company has ever bought

    // Theses domains will be used to generate links. 
    protected $clientDomain = "client.ssentezo.com/client";
    protected $mainDomain = "app.ssentezo.com";


    function __construct($companyName = "")
    {
        $this->companyName = $companyName;
        if (strlen($companyName) > 5) {
            $this->databaseName = $this->generateDatabaseName();
        }
    }
    public function getCompanyName()
    {
        return $this->companyName;
    }
    public function getCompanyTel()
    {
        return $this->companyTel;
    }
    public function getAddress()
    {
        return $this->address;
    }
    public function getDatabaseName()
    {
        return $this->databaseName;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getSenderId()
    {
        return $this->senderId;
    }
    public function setCompanyName($name)
    {
        $this->companyName = $name;
    }
    public function setCompanyTel($tel)
    {
        $this->companyTel = $tel;
    }
    public function setAddress($address)
    {
        $this->address = $address;
    }
    public function setDatabaseName($db)
    {
        $this->databaseName = $db;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
    }
    public function generateDatabaseName()
    {
        // Make sure you update the databaseName as well 
        $this->databaseName =  "ssenhogv_" . str_replace(" ", '', strtolower($this->companyName));
        return $this->databaseName;
    }

    public function getConnection($database = "")
    {
        $host = "localhost";
        $uname = "root";
        $pass = "!Log19tan88";

        $conn = new mysqli($host, $uname, $pass);

        return $conn;
    }

    public function createDatabase()
    {
        $conn = $this->getConnection();
        $dbName = $this->generateDatabaseName();
        // First create a database
        $q = "CREATE DATABASE " . $dbName . "";
        mysqli_query($conn, $q);
        // Switch to that database
        $useQuery = "USE $dbName";
        mysqli_query($conn, $useQuery);

        $filename = '../SQLDB/new_blueprint.sql';
        // echo $filename;
        $op_data = '';
        $lines = file($filename);
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '') { //This IF Remove Comment Inside SQL FILE
                // echo $line;
                continue;
            }
            $op_data .= $line;
            if (substr(trim($line), -1, 1) == ';') {  //Breack Line Upto ';' NEW QUERY
                // echo $op_data;
                mysqli_query($conn, $op_data);
                // echo mysqli_error($conn);
                $op_data = '';
            }
        }
        return true;
    }
    public function registerCompany()
    {
        $managerDb = "ssenhogv_manager";
        $conn = $this->getConnection();
        // $dbName = $this->generateDatabaseName();
        // Switch to that database
        $useQuery = "USE $managerDb";
        mysqli_query($conn, $useQuery);
        $registerQuery = "INSERT INTO `company` SET 
        `name`='$this->companyName',
        `senderId`='$this->senderId', 
        `address`='$this->address', 
        `email`='$this->email',
        `phone`='$this->companyTel',
        `Data_base`='$this->databaseName',
        `unit_charge`='$this->unitCharge',
        `units`='$this->units', 
        `total_units`='$this->totalUnits', 
        `sms_status`='$this->smsStatus',
        `status`='$this->status', 
        `active_flag`='1',
        `del_flag`='0', 
        `two_factor_auth_status`='$this->twoFactorAuthStatus',
        `client_domain`='$this->clientDomain',
        `main_domain`='$this->mainDomain'";
        $result = mysqli_query($conn, $registerQuery);

        if ($result) {
            return   mysqli_insert_id($conn);
        } else {
            return mysqli_error($conn);
        }
    }
    public function checkIfCompanyExists()
    {
        $managerDb = "ssenhogv_manager";
        $conn = $this->getConnection();
        // $dbName = $this->generateDatabaseName();
        // Switch to that database
        $useQuery = "USE $managerDb";
        mysqli_query($conn, $useQuery);

        $registerQuery = "SELECT * FROM `company` WHERE 
        `name`='$this->companyName' OR 
        `Data_base`='$this->databaseName'
        ";
        $result = mysqli_query($conn, $registerQuery);
        while ($row = mysqli_fetch_assoc($result)) {
            return true;
        }
        return false;
    }
}
