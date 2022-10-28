<?php

/**
 * This class has methods to help in making bulk transactions
 * for example monthly fees, monthly interest, etc
 */
class BulkTransaction
{
    /**
     * This method deduct the amount specified from all savings accounts
     *  @param DbAccess $db An instance of DbAcces for database connectivity
     *  @param array $accounts An array of accounts to deduct the amount from
     *  @param float|int $amount The amount to deduct from each account
     */
    public static function creditAll($db, $accounts = [], $amount, $date, $time = '12:00 ', $description = 'Monthly Fee')
    {
        // This is a heavy operation so some things may fail
        // So we have to keep track of each and every step.
        $failed = 0;
        $success = 0;
        foreach ($accounts as $account) {

            $description .= " for " . date('M, Y', strtotime($date));

            $account_balance = $account['balance'];
            // Make sure the account has some balance
            if ($account_balance == 0) {
                $failed++;
                continue;
            }
            $transaction_date = $date;
            $transaction_time = $time;
            $creation_user = 0;
            $transaction_type = $description;
            $deposited_by = "System"; //AppUtil::userFullName();
            $totalIncreBal = $account_balance + $amount;
            $type = "C";

            $set = [
                'savings_account_id' => $account['id'],
                'amount' => "$amount",
                'type' => "$type",
                'transaction_date' => "$transaction_date",
                'transaction_time' => "$transaction_time",
                'trans_type' => "$transaction_type",
                'description' => "$description",
                'creation_user' => "$creation_user",
                'incremental_balance' => "$totalIncreBal",
                'deposited_by' => "$deposited_by"
            ];

            $insertId =  $db->insert('savings_transcations', $set);

            if (is_numeric($insertId)) {
                $text = $description;
                $transaction_amount = $amount;
                $dataSheet = [
                    'reg_date' => "$transaction_date",
                    'cr_dr' => "$type",
                    "type" => "Deposit",
                    "description" => "$text",
                    "amount" => "$transaction_amount",
                    "field1" => "$insertId",
                    "trans_details" => "$text",
                    "creation_user" => AppUtil::userId()
                ];
                $sheetId = $db->insert("balance_sheet", $dataSheet);

                $update = $db->update("savings_account", ['balance' => $totalIncreBal], ["id" => $account['id']]);
                $success++;
            } else {
                $failed++;
                $response  = array(
                    "error" => true,
                    "message" => "Database insertion error"
                );
            }
        }
        return array(
            "response" => $response,
            "success" => $success,
            "failed" => $failed
        );
    }
    public static function debitAll($db, $accounts = [], $amount, $date, $time = '12:00', $description = 'Monthly Fee')
    {
        // This is a heavy operation so some things may fail
        // So we have to keep track of each and every step.
        $failed = 0;
        $success = 0;
        foreach ($accounts as $account) {

            $description .= " for " . date('M, Y', strtotime($date));
            $account_balance = $account['balance'];
            // Make sure the account has some balance
            if ($account_balance == 0) {
                $failed++;
                continue;
            }
            $transaction_date = $date;
            $transaction_time = $time;
            $creation_user = 0;
            $transaction_type = $description;
            $deposited_by = "System"; //AppUtil::userFullName();
            $totalIncreBal = $account_balance - $amount;
            $type = "D";

            $set = [
                'savings_account_id' => $account['id'],
                'amount' => "$amount",
                'type' => "$type",
                'transaction_date' => "$transaction_date",
                'transaction_time' => "$transaction_time",
                'trans_type' => "$transaction_type",
                'description' => "$description",
                'creation_user' => "$creation_user",
                'incremental_balance' => "$totalIncreBal",
                'deposited_by' => $deposited_by
            ];
            // echo json_encode($set) . "<br>";
            $insertId =  $db->insert('savings_transcations', $set);
            // echo $insertId;
            if (is_numeric($insertId)) {
                $text = "Monthly Fee";
                $transaction_amount = $amount;
                $dataSheet = [
                    'reg_date' => $transaction_date,
                    'cr_dr' => $type,
                    "type" => "Withdrawal",
                    "description" => $text,
                    "amount" => $transaction_amount,
                    "field1" => $insertId,
                    "trans_details" => $text,
                    "creation_user" => AppUtil::userId()
                ];

                $sheetId = $db->insert("balance_sheet", $dataSheet);

                $update = $db->update("savings_account", ['balance' => $totalIncreBal], ["id" => $account['id']]);
                $success++;
            } else {
                $failed++;
                print_r("Failure detected $insertId");
                $response  = array(
                    "error" => true,
                    "message" => "Database insertion error $insertId"
                );
                // echo $response . "<hr>";
                // echo $insertId;
            }
        }
        return array(
            "response" => $response,
            "success" => $success,
            "failed" => $failed
        );
    }
}
