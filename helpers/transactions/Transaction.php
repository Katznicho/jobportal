<?php

namespace Ssentezo\Transaction;

use Ssentezo\Util\CommonFields;

class Transaction
{
    use CommonFields;
    protected $transactionId;
    protected $table = 'transactions';
    protected $db;
    protected $type;
    protected $description;
    protected $accounts;
    protected $status;
    protected $date;
    protected $time;
    protected $amount;
    protected $totalCredits;
    protected $totalDebits;
    function __construct($db, $amount = 0, $date = '', $type = "Default")
    {
        $this->db = $db;
        $this->date = $date ? $date : date("Y-m-d");
        $this->time = time();
        $this->init();
    }
    public function getTransactionId()
    {
        return $this->transactionId;
    }
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    public function getAmount()
    {
        return $this->amount;
    }
    public function setDate($date)
    {
        $this->date = $date;
    }
    public function setTransactionId($transId)
    {
        $this->transactionId = $transId;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function setAccounts($accountId)
    {
        $this->accounts .= ", $accountId";
    }
    public function getAccounts()
    {
        return $this->accounts;
    }
    /**
     * Sets the type of trasaction being carried out
     * @param string $type This is the type of transaction 
     *  
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    public function addCredit($amount)
    {
        $this->totalCredits += $amount;
        if ($this->totalCredits == $this->totalDebits) {
            $this->setStatus("Success");
        }
    }
    public function addDebit($amount)
    {
        $this->totalDebits += $amount;
        if ($this->totalCredits == $this->totalDebits) {
            $this->setStatus("Success");
        }
    }

    public function save()
    {
        if ($this->success()) {
            return true;
        }
        return false;
    }

    /**
     * Marks a transaction as successful
     * @return bool true if successfully marked and false otherwise
     */
    protected function success()
    {
        $this->status = 'success';
        $data =  ['description' => $this->description, 'accounts' => $this->accounts, 'status' => $this->status, 'type' => $this->type, 'time' => $this->time, 'date' => $this->date, 'amount' => $this->amount];
        $updateId =  $this->db->update('transactions', $data, ['id' => $this->transactionId]);
        if (is_numeric($updateId)) {
            return true;
        }
        return false;
    }

    /**
     * Marks an already intiated transaction as failed
     * @return bool true if mark is successful and false otherwise
     */
    protected function failed()
    {
        $this->status = 'failed';
        $data = ['description' => $this->description, 'accounts' => $this->accounts, 'status' => $this->status, 'type' => $this->type, 'time' => $this->time, 'date' => $this->date];
        $updateId =  $this->db->update('transactions', $data, ['id' => $this->transactionId]);
        if (is_numeric($updateId)) {
            return true;
        }
        return false;
    }

    /**
     * Creates a new record in the transactions table and returns it's id
     * @return int|string returns the insert id of the transaction of a string with an error message
     */
    protected function init()
    {
        $this->status = 'pending';
        $ret =  $this->db->insert('transactions', ['status' => $this->status, 'date' => $this->date]);
        if (is_numeric($ret)) {
            $this->transactionId = $ret;
        }
        return $ret;
    }
    protected function generateTransactionId()
    {
        $query = "SELECT * FROM transactions ORDER BY id DESC LIMIT 0, 1";
        $highest = $this->db->selectQuery($query)[0]['id'];
        $newId = $highest + 1;
        return $newId;
    }
    public function revert()
    {
        $this->status = 'reverted';
        $data =  ['description' => $this->description, 'accounts' => $this->accounts, 'status' => $this->status, 'type' => $this->type, 'time' => $this->time, 'date' => $this->date, 'amount' => $this->amount];
        $updateId =  $this->db->update('transactions', $data, ['id' => $this->transactionId]);
        if (is_numeric($updateId)) {
            return true;
        }
        return false;
    }
}
