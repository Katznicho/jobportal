<?php

namespace Ssentezo\Billing;

use Ssentezo\Database\DbAccess;

/**
 * All the information about bills and licenses 
 * This class can help in checking whether one can transact of not,
 * Check expiration date of license 
 * How many units left, etc 
 */
class Billing
{
    private $companyName;
    private $billingType;
    private $licenseDuration;
    private $availableUnits;
    private $totalUnits;
    private $remainingTime;
    private $activationDate;
    private $company_table;
    private $licence_table;
    private $companyId;
    private $manager_db;
    private $subscription;
    function __construct($db, $id)
    {
        $this->manager_db = new DbAccess($db);
        $this->subscription = new Subscription($db, $id);
        $this->companyId = $id;
        $this->company_table = "company";
        $this->licence_table = "license";
    }

    public function totalunits()
    {
        //get total remaining transaction Units

        $units = $this->manager_db->select($this->company_table, [], ["id" => $this->companyId,  "active_flag" => 1, "del_flag" => 0])[0]['units'];
        return $units;
    }
    /**
     * Reduce one unit from the company
     */
    public static function one_less_unit($company_id)
    {
        $manager_db = new DbAccess(MANAGER_DB);
        $units =  $manager_db->select("company", [], ["id" => $company_id,  "active_flag" => 1, "del_flag" => 0])[0]['units'];
        $updateQuery = "UPDATE  company set units = units-1 where id = $company_id";
        $updateCount = $manager_db->updateQuery($updateQuery);
        return $updateCount;
    }

    public function GetLicencetypebyid()
    {
        // Licence_type => if type is Monthly-> they pay monthly if Transactional->they pay transactional
        $type = $this->manager_db->select($this->company_table, [], ["id" => $this->companyId,  "active_flag" => 1, "del_flag" => 0])[0]['Licence_type'];
        return $type;
    }

    /**
     * Check if a company can make operate. This checks to see if units are enough or the license is not expired
     * @param int $company_id The id of the company 
     * @return bool true if the company can transact, false otherwise
     */
    public static function can_transact($company_id)
    {
        $manager_db = new DbAccess(MANAGER_DB);
        $flag = false;
        $units = $manager_db->select("company", [], ["id" => $company_id,  "active_flag" => 1, "del_flag" => 0])[0]['units'];
        if ($units > 0) {
            $flag = true;
        }
        return $flag;
    }
}
