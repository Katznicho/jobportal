<?php

namespace Ssentezo\Controllers;

use Ssentezo\Billing\Billing;
use Ssentezo\Database\DbAccess;
use Ssentezo\Transaction\SavingTransaction;
use Ssentezo\Util\ActivityLogger;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\Logger;
use Ssentezo\Util\UI\Alert;

class SavingsController
{
    public static function getTransactionsBranch($db, $request)
    {
        $from_date = $to_date = $description = $query = '';
        $transactions = [];
        if (isset($request['start_date']) && $request['start_date'] > 0 && isset($request['end_date']) && $request['end_date']) {
            $from_date = $request['start_date'];
            $to_date = $request['end_date'];
            $query = "SELECT * FROM savings_transcations WHERE CAST(creation_date AS DATE) >= '$from_date' AND CAST(creation_date AS DATE) <= '$to_date' ";
            $description = "Savings Transactions from $from_date to $to_date";
        } else if (isset($request['start_date']) && $request['start_date']) {
            $from_date = $request['start_date'];
            $query = "SELECT * FROM savings_transcations WHERE CAST(creation_date AS DATE) >= '$from_date' ";
            $description = "Savings Transactions from $from_date upto date";
        } else if (isset($request['end_date']) && $request['end_date']) {
            $query = "SELECT * FROM savings_transcations WHERE CAST(creation_date AS DATE) <= '$to_date' ";
            $to_date = $request['end_date'];
            $description = "Savings Transactions from beginning upto $to_date";
        } else {
            $from_date = date("Y-m-d");
            $to_date = date("Y-m-d");
            $query = "SELECT * FROM savings_transcations WHERE CAST(creation_date AS DATE) >= '$from_date' AND CAST(creation_date AS DATE) <= '$to_date' ";
            $description = "Savings Transactions for today";
        }
        if ($query) {
            $query .= " AND active_flag = 1 AND del_flag = 0  Order by id desc";
            $transactions = $db->selectQuery($query);
        }
        // return ["transactions" => $transactions, "from_date" => $from_date, "to_date" => $to_date];
        return [$transactions, $from_date,  $to_date, $description];
    }
    public static function addTransaction($db, $request, $savings_id)
    {

        $manager_db = new DbAccess(MANAGER_DB);
        //Ensure that the company has units or an active license
        if (!Billing::can_transact(AppUtil::companyId())) {
            $message = "You have zero transaction units and hence cannot make this transaction.";
            ActivityLogger::logActivity($_SESSION['user_id'], "Add Transaction", "Failed", $message);
            Alert::setSessionAlert($message, 'danger');
            return ['message' => $message, "error" => true];
        }
        $data = $request;

        $transaction_to = "0";
        $data['dest_account'] = 0;
        if (strlen($request['transfer_to']) > 0) {
            $data['dest_account'] = $request['transfer_to'];
        }
        $data['narrative'] = $request['transaction_description'];
        $type = "";
        $transaction_type = $request['transaction_type'];
        if ($transaction_type == "Deposit") {
            $type = "C";
        } else if ($transaction_type == "Withdrawal") {
            $type = "D";
        } else if ($transaction_type == "Transfer") {
            $type = "T";
        }
        $data['transaction_type'] = $type;
        $transaction = new SavingTransaction($db, $savings_id, $data);
        $transaction->save();
        print_r($transaction);
        die();

        if (!isset($transaction_fees)) {
            $transaction_fees = [];
        }



        // $totalIncreBal = $oldBalance + $newAmount;

        $table = "savings_account";
        $company_name = AppUtil::getCompanyName();
        // ActivityLogger::logActivity(AppUtil::userId(), "Add Transaction", "sucfailed", json_encode($data));
        $insertId = $db->insert($table, $data);
        if (is_numeric($insertId)) {

            $borrower_id = $db->select("savings_account", ['borrower_id'], ["id" => $savings_id])[0];
            //$unique_no = $$db->select("borrower",[], ["id" => $borrower_id['borrower_id']])[0];
            $borrower = $db->select("borrower", [], ["id" => $borrower_id['borrower_id']])[0];
            // $message = "Dear Customer, You have made a " . $transaction_type . " of Ugx " . $transaction_amount . "
            //  on your savings account with " . $company_name;
            $receiver = array();
            $receiver[] = $borrower['mobile_no'];
            $unique_no = $borrower['unique_no'];
            // $result = $sms->sms($message, $receiver, $sms_status);

            //add balance sheet ***

            $text = "savings $type";


            ActivityLogger::logActivity($_SESSION['user_id'], "Add Transaction", "Success", $transaction_type);
            // don't print a receipt if user doesn't want 
            if (isset($request['print_receipt']) && $request['print_receipt']) {
            } else {
                // header("location:print_recept.php?borrower=" . $borrower_name . "&valueFee=" . $valueFee . "&amount=" . $request['transaction_amount'] . "&debositedby=" . $request['deposited_by'] . "&transaction_type=" . $request['transaction_type'] . "&savings_id=" . $unique_no . "&officer=" . AppUtil::userFullName());
                die();
            }
            $borrower_id = $borrower['id'];
            header("Location: ./view_savings_borrower.php?saver=$borrower_id");
            die();
        } else {
            ActivityLogger::logActivity($_SESSION['user_id'], "Add Transaction", "Failed", "Database Error");
            Logger::error(AppUtil::getCompanyName() . "::Database Error", ["query" => $db->query]);
            Alert::setSessionAlert("Un expected Error Occured <br>Please try again later", 'danger');
            // print_r($insertId);
        }
    }
}
