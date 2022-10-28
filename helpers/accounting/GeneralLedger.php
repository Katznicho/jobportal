<?php

namespace Ssentezo\Accounting;

use Ssentezo\Transaction\Transaction;
use Ssentezo\Util\AppUtil;
//
class GeneralLedger extends Ledger
{
    static protected $transactions;

    public static function postTransaction($db, $accountId, $amount, $type, $date, $narrative, $balance, $userId, $transactionId, $force = 0)
    {
        $data = array(
            'account_id' => $accountId,
            'date' => $date,
            'narrative' => $narrative,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance,
            'created_by' => $userId,
            'trans_id' => $transactionId
        );
        if ($force == 1) {
            $insertId = $db->insert('general_ledger', $data);
        } else {
            if ($amount == 0) {
                //Don't make the transaction

            } else {
                $insertId = $db->insert('general_ledger', $data);
            }
        }

        return $insertId;
    }
    public static function editTransaction($db, $accountId, $amount, $type, $date, $narrative, $balance, $userId, $transactionId, $force = 0)
    {
        $updateData = array(
            'date' => $date,
            'narrative' => $narrative,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance,
            'created_by' => $userId,
        );
        if ($force == 1) {
            $update = $db->update('general_ledger', $updateData, ['account_id' => $accountId, 'trans_id' => $transactionId]);
        } else {
            if ($amount == 0) {
                //Don't make the transaction

            } else {
                $update = $db->update('general_ledger', $updateData, ['account_id' => $accountId, 'trans_id' => $transactionId]);
            }
        }
        return $update;
    }
    public static function deleteTransaction($db, $transactionId)
    {
        $updateId =  $db->update('general_ledger', ['active_flag' => 0, 'del_flag' => 1], ['trans_id' => $transactionId]);
        if (is_numeric($updateId)) {
            return true;
        } else {
            return false;
        }
    }
    public static function revert($db, &$transaction)
    {
        $transactionId = $transaction->getTransactionId();
        return self::deleteTransaction($db, $transactionId);
    }
}
