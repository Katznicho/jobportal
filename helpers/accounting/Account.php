<?php

namespace Ssentezo\Accounting;

use Ssentezo\Util\AppUtil;

class Account
{
    protected $id;
    protected $name;
    protected $category;
    protected $accNumber;
    protected $balance;
    protected $createdBy;
    protected $isLeaf;
    protected $initialBalance;
    protected $initialBalanceDate;
    protected $createdOn;
    protected $modifedBy;
    protected $modifiedOn;
    protected $activeFlag;
    protected $delFlag;
    protected $subCategoryId;

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getCategoryId()
    {
        return $this->category;
    }
    public function getAccNumber()
    {
        return $this->accNumber;
    }
    public function getBalance()
    {
        return $this->balance;
    }
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    public function getIsLeaf()
    {
        return $this->isLeaf;
    }
    public function getInitialBalance()
    {
        return $this->initialBalance;
    }
    public function getInitialBalanceDate()
    {
        return $this->initialBalanceDate;
    }
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
    public function getModifiedBy()
    {
        return $this->modifedBy;
    }
    public function getModifiedOn()
    {
        return $this->modifiedOn;
    }
    public function getActiveFlag()
    {
        return $this->activeFlag;
    }
    public function getDelFlag()
    {
        return $this->delFlag;
    }
    public function getSubCategoryId()
    {
        return $this->subCategoryId;
    }
    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }
    public function setAccNumber($accNumber)
    {
        $this->accNumber = $accNumber;
    }
    public function setBalance($amount)
    {
        $this->balance = $amount;
    }
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }
    public function setIsLeaf($isLeaf)
    {
        $this->isLeaf = $isLeaf;
    }

    public function setInitialBalance($initialBalance)
    {
        $this->initialBalance = $initialBalance;
    }
    public function setInitialBalanceDate($initialBalanceDate)
    {
        $this->initialBalanceDate = $initialBalanceDate;
    }
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }
    public function setModifiedBy($modifedBy)
    {
        $this->modifedBy = $modifedBy;
    }
    public function setModifiedOn($modifiedOn)
    {
        $this->modifiedOn = $modifiedOn;
    }
    public function setActiveFlag($activeFlag)
    {
        $this->activeFlag = $activeFlag;
    }
    public function setDelFlag($delFlag)
    {
        $this->delFlag = $delFlag;
    }
    public function setSubCategoryId($subCategoryId)
    {
        $this->subCategoryId = $subCategoryId;
    }
    // other Methods
    public function credit($amount, $db)
    {
        $this->balance += $amount;

        $data = array(
            "balance" => $this->balance,
            "modified_by" => AppUtil::userId(),
            "modified_on" => date("Y-m-d")

        );

        $insertId = $db->update('accounts', $data, ['id' => $this->id]);
        if (is_numeric($insertId)) {
            $ret =  array("status" => "success", "message" => "Account Added successfully");
        } else {
            $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
        }
        return $ret;
    }
    public function increase($amount, $db)
    {
        $this->balance += $amount;

        $data = array(
            "balance" => $this->balance,
            "modified_by" => class_exists('AppUtil') ? AppUtil::userId() : 0,
            "modified_on" => date("Y-m-d")

        );

        $insertId = $db->update('accounts', $data, ['id' => $this->id]);
        if (is_numeric($insertId)) {
            $ret =  array("status" => "success", "message" => "Account Added successfully");
        } else {
            $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
        }
        return $ret;
    }
    public function debit($amount, $db)
    {
        $this->balance -= $amount;
        $data = array(
            "balance" => $this->balance,
            "modified_by" => class_exists('AppUtil') ? AppUtil::userId() : 0,

        );

        $insertId = $db->update('accounts', $data, ['id' => $this->id]);
        if (is_numeric($insertId)) {
            // insert the transaction into the general
            $ret =  array("status" => "success", "message" => "Account Added successfully");
        } else {
            $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
        }
        return $ret;
    }
    public function decrease($amount, $db)
    {
        $this->balance -= $amount;
        $data = array(
            "balance" => $this->balance,
            "modified_by" => AppUtil::userId(),

        );
        $insertId = $db->update('accounts', $data, ['id' => $this->id]);
        if (is_numeric($insertId)) {
            // insert the transaction into the general
            $ret =  array("status" => "success", "message" => "Account Added successfully");
        } else {
            $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
        }
        return $ret;
    }
    public function create($db)
    {
        // Check for records with the same account number
        $result = $db->select("accounts", [], ['account_no' => $this->accNumber]);

        if (empty($result)) {
            $data = array(
                "name" => $this->name,
                "balance" => $this->balance,
                "account_no" => $this->accNumber,
                "category" => $this->category,
                "created_by" => AppUtil::userId(),
                "is_leaf" => $this->isLeaf,
                "initial_balance" => $this->initialBalance,
                "initial_balance_date" => $this->initialBalanceDate,
                "created_on" => date("Y-m-d"),

            );

            //For opening balance make sure we save it in the general ledger but set is_gl(is general ledger ) to 0

            $insertId = $db->insert('accounts', $data);
            // $transaction = new Transaction();
            if ($this->balance) {
                GeneralLedger::postTransaction($db, $insertId, $this->balance, '', date("Y-m-d H:i:s"), "Opening balance ", $this->balance, AppUtil::userId(), 0);
            }

            if (is_numeric($insertId)) {
                $ret =  array(
                    "status" => "success",
                    "message" => "Account Added successfully",
                    "insertId" => $insertId
                );
            } else {
                $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
            }
        } else {
            $ret =  array("status" => "failed", "message" => "An account with same account number already exists");
        }
        return $ret;
    }
    public function update($db)
    {
        // Check for records with the same account number
        $result = $db->select("accounts", [], ['id' => $this->id])[0];
        $ret = [];


        if ($result['id']) {


            $data  = array(
                'name' => $this->name,
                'category' => $this->category,
                'account_no' => $this->accNumber,
                'balance' => $this->balance,
                'created_by' => $this->createdBy,
                'is_leaf' => $this->isLeaf,
                'initial_balance' => $this->initialBalance,
                'initial_balance_date' => $this->initialBalanceDate,
                'created_on' => $this->createdOn,
                'modified_by' => $this->modifedBy,
                'modified_on' => $this->modifiedOn,
                'active_flag' => $this->activeFlag,
                'del_flag' => $this->delFlag,
                'sub_category_id' => "$this->subCategoryId"
            );


            $insertId = $db->update('accounts', $data, ['id' => $this->id]);

            if (is_numeric($insertId)) {
                $ret =  array("status" => "success", "message" => "Account Added successfully");
            } else {
                $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
            }
        } else {
            $ret =  array("status" => "failed", "message" => "Account not found");

            // die();
        }
        // die();
        return $ret;
    }
    public function delete($db)
    {
        $this->activeFlag = 0;
        $this->delFlag = 1;
    }
    public function generateAccNumber($db)
    {
        // First get the highest account number
        // First query accounts to get the biggest account number under that category
        $maxAccNumber = '';
        $maxQuery = "SELECT MAX(account_no) as maximum FROM `accounts` WHERE `category`='$this->category'";
        $result = $db->selectQuery($maxQuery);
        // If no accounts yet
        // Use the category's prefix to generate the account number
        if (is_null($result[0]['maximum'])) {
            $category = $db->select('account_categories', [], ['id' => $this->category])[0];
            $maxAccNumber = $category['prefix'];
        } else {
            // Else use the information about the highest account number to generate the account number
            $maxAccNumber = $result[0]['maximum'];
        }
        return $maxAccNumber + 1;
    }
    public function save($db)
    {

        $data = array(
            "balance" => $this->balance,
            "modifed_by" => AppUtil::userId(),
            "modified_on" => date("dd/mm/Y"),

        );
        $insertId = $db->update('accounts', $data, ['id' => $this->id]);
        if (is_numeric($insertId)) {
            $ret =  array("status" => "success", "message" => "Account Added successfully");
        } else {
            $ret =  array("status" => "failed", "message" => "Insertion failed with reason: $insertId");
        }
        return $ret;
    }
    public function validateAccountNo($db)
    {
        $accounts = $db->select('accounts', [], ['account_no' => $this->accNumber, 'active_flag' => 1, 'del_flag' => 0]);
        if (count($accounts) > 0) {
            return false;
        } else {
            return true;
        }
    }
    public static function categorize($accountCode)
    {
        return $accountCode[0];
    }
}
