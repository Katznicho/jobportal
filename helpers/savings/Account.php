<?php

namespace Ssentezo\Savings;

use Ssentezo\Database\DbAccess;

/**
 * This is the building block of a transaction
 * All transaction must involve at least an account 
 * This class gives you power to transact 
 * It implements the most basic operations i.e increase, decrease and transfer 
 * With these operations you can literary make any kind of transaction You need not to worry about
 * the underlying implementation.
 */
class Account
{
    /**
     * @var int 
     * The id of the account
     */
    public $id;

    /**
     * @var int
     * The id of the savings product
     */
    public $savingsProductId;
    /**
     * @var int
     * The account number
     */
    public $accountNumber;
    /**
     * @var string
     * The account name
     */
    public $accountName;
    /**
     * @var string
     * The description of the account
     */
    public $description;
    /**
     * @var int
     * The id of the borrower
     */
    public $borrowerId;
    /**
     * @var double
     * The balance of the account
     */
    public $balance;
    /**
     * @var string
     * The date the account was created
     */
    public $creationDate;
    /**
     * @var int
     * The id of the user who created the account
     */
    public $creationUser;
    /**
     * @var int
     * The id of the user who last modified the account
     */
    public $lastModifiedBy;
    /**
     * @var string
     * The date the account was last modified
     */
    public $lastModifiedDate;
    /**
     * @var int
     * The active flag of the account
     */
    public $activeFlag;
    /**
     * @var int
     * The delete flag of the account
     */
    public $delFlag;
    /**
     * @var int
     * The flag to indicate if the account is a group account
     */
    public $isGroup;


    /**
     * @var array 
     * The errors will be stored here for any operation.
     * 
     */
    public $errors;
    /**
     *  Instantiates a savings account object 
     * @param DbAccess $db The database connection of the company
     * @param int $account_id The id of the account
     */
    function __construct($db, $account_id)
    {
        $savings_account = $db->select('savings_account', [], ['id' => $account_id])[0];
        $this->id = $savings_account["id"];
        $this->savingsProductId = $savings_account["savings_product_id"];
        $this->accountNumber = $savings_account["account_no"];
        $this->accountName = $savings_account["account_name"];
        $this->description = $savings_account["description"];
        $this->borrowerId = $savings_account["borrower_id"];
        $this->balance = $savings_account["balance"];
        $this->creationDate = $savings_account["creation_date"];
        $this->creationUser = $savings_account["creation_user"];
        $this->lastModifiedBy = $savings_account["last_modified_by"];
        $this->lastModifiedDate = $savings_account["last_modified_date"];
        $this->activeFlag = $savings_account["active_flag"];
        $this->delFlag = $savings_account["del_flag"];
        $this->isGroup = $savings_account["is_group"];
        return $this;
    }

    /**
     * Decrease the balance on this account by the specified amount
     * @param DbAccess $db The database connection of the company
     * @param float $amount The amount to remove from the account
     * @return string|int Int if the deduction was successful, string with the error message otherwise
     *
     */
    public function decrease($db, $amount)
    {
        $query = "UPDATE savings_account SET balance = balance-$amount WHERE id=" . $this->id;
        $result =  $db->updateQuery($query);
        return $result;
    }

    /**
     * Increase the balance on this account by the specified amount
     * @param DbAccess $db The database connection of the company
     * @param float $amount The amount to add to the account
     * @return string|int Int if the increament was successful, string with the error message otherwise
     */
    public function increase($db, $amount)
    {
        $query = "UPDATE savings_account SET balance = balance+$amount WHERE id=" . $this->id;
        $result =  $db->updateQuery($query);
        return $result;
    }

    /**
     * Transfer funds from this account to another account
     * @param DbAccess $db The database connection of the company
     * @param Account $toAccount The account to transfer funds to
     * @param float $amount The amount to transfer
     */
    public function transfer($db, Account $toAccount, $amount)
    {
        $this->errors = []; //Reset errors
        $ret = $this->decrease($db, $amount);
        if (is_numeric($ret)) { //Successful then deposit funds on the destination account
            $ret = $toAccount->increase($db, $amount);
            if (is_numeric($ret)) {
                return true;
            }
            $this->errors[] = $ret;
            //The increament of second account failed 
            //So we need to reverse the decreament of the first account
            $ret = $this->increase($db, $amount);
            $this->errors[] = $ret;
            return false;
        }
        $this->errors[] = $ret;
        return false; //The deduction of first account failed
    }

    /**
     * Tests if an account meets the conditions to be involved in a transaction
     *  @param float $amount The amount to check if it can be transacted
     * @param string $type  The type of transaction to test C means debit(decrease), C means Credit(increase) and T means Transfer (wire)
     */
    public function can_transact($amount, $type)
    {
        if ($type == 'D' || $type == 'T') //Don't allow withdrawals to negative balance
            if ($this->balance > $amount) {
                return true;
            }
        return false;
    }

    /**
     * Attempts to revert a Deposit or Withdrawal Transaction.
     * Please DON'T use it on Transfers since they are self reversing on failure.  
     */
    public function revert()
    {
        
    }
}
