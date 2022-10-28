<?php

namespace Ssentezo\Transaction;

use Ssentezo\Accounting\GeneralLedger;

class ExisitingTransaction extends Transaction
{
    /**
     * 
     */
    function __construct($db, $transaction_id)
    {
        $this->transactionId = $transaction_id;
        $transaction = $db->select($this->table, [], ['id' => $transaction_id])[0];
        // $this->accounts = $transaction['accounts'];
        $this->time = $transaction['time'];
        $this->date = $transaction['date'];
        // print_r($transaction);
        // die();
        $this->db = $db;
    }
    public function updateTransaction($amount, $description, $date)
    {
        $this->amount = $amount;
        $this->description = $description;
        $this->date = $date;
        // $this->accounts = $accounts;
        // $this->status = $status;
    }
    public static function deleteTransaction($db, $transaction_id)
    {
        $transaction = $db->update('transactions', ['active_flag' => 0, "del_flag" => 1], ['id' => $transaction_id])[0];
        $accounts = $transaction['accounts'];
        $accounts = explode(",", $accounts);
        foreach ($accounts as $account_id) {
            if (!$account_id) {
                continue;
            }
            // GeneralLedger::deleteTransaction();
        }

        //Delete the transaction from the general ledger
        return GeneralLedger::deleteTransaction($db, $transaction_id);
    }
}
