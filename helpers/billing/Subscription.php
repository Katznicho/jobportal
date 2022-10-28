<?php


/**
 * Description of subscription
 * trying to build a mult billing module that ssentezo can use to impliment Licence
 *
 * @author MAT
 */

namespace Ssentezo\Billing;

use Ssentezo\Database\DbAccess;

class Subscription
{
    private $packages_table;
    // private $subscription_table;
    private $manager_db;
    private $licence_table;
    private $companyid;
    private $company_table;

    function __construct($db, $id)
    {
        $this->manager_db = new DbAccess($db);
        $this->companyid = $id;
        $this->packages_table = "packages";
        // $this->subscription_table="subscription";
        $this->licence_table = "license";
        $this->company_table = "company";
    }




    public function create_license($period = 1, $packageid = 0)
    {
        //this methode is for generating and setting a lince for a company it must be only called if some one has paid for licence
        // $period is in months
        $token = openssl_random_pseudo_bytes(18);
        //Convert the binary data into hexadecimal representation.
        $token = bin2hex($token);
        $license_token = $token;
        $license_creation_date = date("Y-m-d");
        $license_expirely_date = date('Y-m-d', strtotime($$license_creation_date . ' + ' . $period . ' months'));
        $data = array();

        $data["companyid"] = $this->companyid;
        $data["license_token"] = $license_token;
        $data["license_creation_date"] = $license_creation_date;
        $data["license_expirely_date"] = $license_expirely_date;
        $data["packageid"] = $packageid;

        //create Licence record

        $licenseId = $this->manager_db->insert($this->licence_table, $data);

        // update company with active key
        $result = $this->manager_db->update($this->company_table, ["Active_Licence_Key" => $license_token], ["id" => $this->companyid]);
        return $result;
    }

    public function getlicence_key_by_companyid()
    {
        $token =  $units = $this->manager_db->select($this->company_table, [], ["id" => $this->companyid,  "active_flag" => 1, "del_flag" => 0])[0]['Active_Licence_Key'];
        return $token;
    }

    public function get_licence_expirydate()
    {
        //return $this->getlicence_key_by_companyid();
        $expirydate =  $units = $this->manager_db->select($this->licence_table, [], ["license_token" => $this->getlicence_key_by_companyid(),  "active_flag" => 1, "del_flag" => 0])[0]['license_expirely_date'];
        return $expirydate;
    }

    public function get_licence_packageid()
    {
        //return $this->getlicence_key_by_companyid();
        $packageid =  $this->manager_db->select($this->licence_table, [], ["license_token" => $this->getlicence_key_by_companyid(),  "active_flag" => 1, "del_flag" => 0])[0]['packageid'];
        return $packageid;
    }


    public function getdaystoexpire()
    {
        $cdate = date("Y-m-d");

        $datediff = ("SELECT DATEDIFF('" . $this->get_licence_expirydate() . "', '" . $cdate . "')");
        $datediff_res = $this->manager_db->selectQuery($datediff);
        $datedifre = null;
        foreach ($datediff_res as $datedi) {
            //date difference between today and repayment date+grace period
            $datedifre = $datedi[0];
        }
        return $datedifre;
    }

    


    //put your code here
}
