<?php

namespace Ssentezo\Savings;

use Exception;
use Ssentezo\Database\DbAccess;
use Ssentezo\Savings\SavingsAccount;
use Ssentezo\Savings\TransactionDetails;
use Ssentezo\Util\ActivityLogger;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\SmsClass;
use Ssentezo\Payments\SsentezoWallet;
use Ssentezo\Util\Logger;

class CompleteTransaction
{
    private $db;
    private $manager_db;
    private $db_name;

    public function __construct($db_name)
    {
        $this->db =  new DbAccess($db_name);
        $this->db_name = $db_name;
    }

    public  function onFailure($type, $amount, $ref, $user_id, $phone_number)
    {

        $table = "savings_transcations";

        $saving_fees =  $this->db->select($table, ['fee_applied'], ['transaction_reference' => $ref, 'amount' => $amount, 'trans_type' => $type])[0]['fee_applied'];


        //update savings_transcations table set del_flag = 1 where transaction_ref = $ref;
        $res = $this->db->update($table, ['del_flag' => '1', 'active_flag' => '0'], ['transaction_reference' => $ref]);
        //update savings_accounts table set balance = balance + $amount where user_id = $user_id;

        if ($res) {
            $oldBalance = $this->db->select("savings_account", ['balance'], ["borrower_id" => $user_id])[0]['balance'];
            $totalIncreBal = $oldBalance + $amount + $saving_fees;
            $this->db->update('savings_account', ['balance' => $totalIncreBal], ['borrower_id' => $user_id]);
        }

        //savings_applied_charges table set del_flag = 1 where transaction_reference = $ref;
        $res = $this->db->update('savings_applied_charges', ['del_flag' => '1', 'active_flag' => '0'], ['transaction_reference' => $ref]);
        //delete from balance_sheet where transaction_reference = $ref;
        $sql = "delete from balance_sheet where transaction_reference = '$ref'";
        $this->db->delete($sql);
        return true;
    }

    public  function onSuccess($type, $amount, $deposited_by, $narrative, $user_id, $company_name,  $ref, $message, $platform = null)
    {

        $table = "savings_transcations";
        $manager_db =  new DbAccess("ssenhogv_manager");
        $sms_status = AppUtil::sms_check($manager_db, $company_name);
        $sms = new SmsClass();
        //$can_transact = AppUtil::units_check($manager_db, $company_name);



        $transaction_date = date("Y/m/d");
        $transaction_time = date("h:i:sa");
        $transaction_type = $type;
        $transaction_amount = $amount;
        $transaction_fees = [];
        $deposited_by = $deposited_by;
        $transaction_to = "0";
        $transaction_description = $narrative;
        //get automatic saving fees
        $automatic_fees = $this->db->select("savings_fees", [], [
            "active_flag" => 1, "mode_of_application" => "automatic", "transaction_type" => $transaction_type, "channel" => "Mobile Money"
        ]);



        if (count($automatic_fees) > 0) {
            foreach ($automatic_fees as $auto) {

                array_push($transaction_fees, $auto['id']);
            }
        }



        if ($transaction_type == "Deposit") {
            // $accountObj->credit($_POST['transaction_amount'], $db);
            $type = "C";
        } else if ($transaction_type == "Withdrawal") {
            // $accountObj->debit($_POST['transaction_amount'], $db);
            $type = "D";
        } else if ($transaction_type == "Transfer") {
            $type = "T";
        }

        //get Old Balance to have Incremental balance...
        $oldBalance = $this->db->select("savings_account", ['balance'], ["borrower_id" => $user_id])[0]['balance'];



        $savings_id = $this->db->select("savings_account", ['id'], ["borrower_id" => $user_id])[0]['id'];
        $newAmount = $type == "C" ? (int)$transaction_amount : (int)(-$transaction_amount);
        $totalIncreBal = $oldBalance + $newAmount;


        $totalFee = 0;


        if (count($transaction_fees) > 0) {
            foreach ($transaction_fees as $fee) {

                $valueFee = 0;

                $ids = $fee;
                $feeDetails = $this->db->select("savings_fees", [], ["id" => $ids])[0];



                if ($feeDetails['charge_mtd'] == "fixed") {
                    //$valueFee = $feeDetails['charge_amount'];
                    $valueFee = $feeDetails['charge_amount'];
                    $totalFee += $valueFee;
                }
                if ($feeDetails['charge_mtd'] == "percentage") {

                    $valueFee = ($feeDetails['charge_rate'] / 100) * $transaction_amount;
                    $totalFee += $valueFee;
                }
                if ($feeDetails['charge_mtd'] == "range") {
                    $db = new DbAccess("ssenhogv_manager");
                    $company_id = $db->select('company', [], ['Data_base' => $this->db_name])[0]['id'];
                    $fee_ranges = $db->select('fee_charges', [], ["saving_fee_id" => $ids, 'company_id' => $company_id]);
                    if (count($fee_ranges) > 0) {
                        foreach ($fee_ranges as $range) {
                            if ($amount <= $range['upper_limit'] and $amount >= $range['lower_limit']) {
                                $valueFee = $range['fee'];
                                $totalFee += $range['fee'];
                            } else {
                                continue;
                            }
                        }
                    }
                }



                $data1 = [
                    'saving_id' => $savings_id,
                    'saving_fee_id' => $fee,
                    'amount' => $valueFee,
                    "creation_user" => 0,
                    'transaction_reference' => $ref,
                ];

                $chargeId = $this->db->insert("savings_applied_charges", $data1);
                //charge a unit on transaction fee
                if (is_numeric($chargeId)) {

                    $num = AppUtil::one_less_unit($manager_db, $company_name);
                }
                //insert into savings transactions
                $incremental_balance = (int)$totalIncreBal - (int)$totalFee;
                try {
                $sql = "INSERT INTO $table (savings_account_id, savings_account_to, transaction_date, transaction_time, trans_type,
                amount, creation_user, deposited_by, description, transaction_reference, 
                 incremental_balance, type, channel) 

               VALUES ('$savings_id', '$transaction_to','$transaction_date', '$transaction_time', 'Fees',
                '$valueFee','0','$deposited_by',  'Transaction Fees', '$ref',  '$incremental_balance', 'D', 'Mobile Money')";
               $insertId = $this->db->sql($sql);
                    
                } catch (Exception $e) {
                }
                $num = AppUtil::one_less_unit($manager_db, $company_name);
            }
        } else {

            $data1 = [
                "savings_account_id" => $savings_id,
                "savings_account_to" => $transaction_to,
                "amount" => '0',
                "type" => "D",
                "transaction_date" => $transaction_date,
                "transaction_time" => $transaction_time,
                "trans_type" => "Fees",
                "description" => "transaction fees",
                "creation_user" =>  $user_id,
                'incremental_balance' => $totalIncreBal,
                "deposited_by" => $deposited_by,
                'transaction_reference' => $ref,
            ];

            $insertId1 = $this->db->insert($table, $data1);


            AppUtil::one_less_unit($manager_db, $company_name);
        }

        $transaction_data = [
            "savings_account_id" => $savings_id,
            "savings_account_to" => $transaction_to,
            "amount" => $transaction_amount,
            "type" => $type,
            "transaction_date" => $transaction_date,
            "transaction_time" => $transaction_time,
            "trans_type" => $transaction_type,
            "description" => $transaction_description,
            "creation_user" => '0',
            'incremental_balance' => $totalIncreBal,
            "deposited_by" => $deposited_by,
            'transaction_reference' => $ref,
            "channel" => "Mobile Money",
            "platform" => $platform,
            "fee_applied" => $totalFee
        ];
        $insertId = $this->db->insert("savings_transcations", $transaction_data);
        //reduce one unit
        if (is_numeric($insertId)) {
            $num = AppUtil::one_less_unit($manager_db, $company_name);
        }

        //update balance sheet
        $text = "savings $type";

        $dataSheet = [
            'reg_date' => $transaction_date, 'cr_dr' => $type, "type" => "Savings",
            "description" => $text, "amount" => $transaction_amount, "field1" => $insertId,
            "trans_details" => $text, "creation_user" => '0', 'transaction_reference' => $ref,
        ];
        $dataSheet1 = [
            'reg_date' => $transaction_date, 'cr_dr' => "D", "type" => "Saving Fees",
            "description" => $text, "amount" => $totalFee, "field1" => $insertId1,
            "trans_details" => $text, "creation_user" => '0',
            'transaction_reference' => $ref,
        ];
        $sheetId = $this->db->insert("balance_sheet", $dataSheet);
        $sheetId1 = $this->db->insert("balance_sheet", $dataSheet1);

        $update = $this->db->update("savings_account", ['balance' => $totalIncreBal], ["borrower_id" => $user_id]);
        $update1 = $this->db->update("savings_account", ['balance' => $totalIncreBal - $totalFee], ["borrower_id" => $user_id]);

        $borrower = $this->db->select("borrower", [], ["id" => $user_id])[0];
        $message = "Dear Customer, You have made a " . $transaction_type . " of Ugx " . $amount . " on your savings account with " . $company_name;

        if ($transaction_type == "Deposit") {
            $receiver = array();
            $receiver[] = $borrower['mobile_no'];
            $result = $sms->sms($message, $receiver, $sms_status);
        }




        return true;
    }
}
