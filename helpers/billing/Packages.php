<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of packages
 *
 * @author MAT
 */

namespace Ssentezo\Billing;

use Ssentezo\Database\DbAccess;

class Packages
{
    private $packages_table;
    private $manager_db;
    private $companyid;
    private $licence_table;
    private $company_table;
    private $subscription_table;


    /**
     * @param DbAccess $db The manager db
     * @param int $id the company id of the company
     */
    function __construct($db, $id)
    {
        $this->manager_db = $db;
        $this->companyid = $id;
        $this->packages_table = "packages";
        $this->subscription_table = "subscription";
        $this->licence_table = "license";
        $this->company_table = "company";
    }

    public function create_package($name, $numberofusers, $price, $duration = 1, $description = '')
    {
        $data = array();
        $data["package_name"] = $name;
        $data["number_of_users"] = $numberofusers;
        $data["price"] = $price;

        $data['duration'] = $duration;
        $data['description'] = $description;
        $id = $this->manager_db->insert($this->packages_table, $data);
        return $id;
    }
    public function update_package($id, $data = array())
    {
        $result = $this->manager_db->update($this->packages_table, $data, ["id" => $id]);
        return $result;
    }

    public function getpackagedata($where = array())
    {
        $result = $this->manager_db->select($this->packages_table, [], $where);
        return $result;
    }

    public function getAllPackages()
    {
        $result = $this->manager_db->select($this->packages_table, []);
        return $result;
    }

    //put your code here
}
