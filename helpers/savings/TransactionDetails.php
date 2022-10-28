<?php

namespace Ssentezo\Savings;

require_once __DIR__ . "/../vendor/autoload.php";

use Ssentezo\Database\DbAccess;

class TransactionDetails
{
    protected $id;
    protected $transactionReference;
    private $db;

    public function __construct($db, $transaction_ref)
    {
        $db =  new DbAccess($db);
        $this->db = $db;
        $this->transactionReference = $transaction_ref;
    }

    // Get the transaction details
    public function getTransactionDetails()
    {

        $details = $this->db->select('withdrawal_transactions', [], ['transaction_reference' => $this->transactionReference])[0];
        return $details;
    }

    public function getDepositTransactionDetails()
    {

        $details = $this->db->select('make_deposit', [], ['transaction_reference' => $this->transactionReference])[0];
        return $details;
    }
}
