<?php
class WithdrawRequests
{
    public static function allRequests($db, $search = false, $search_param = '')
    {
        $final_array = [];
        // Apply search if any the search_params is passes
        if ($search && $search_param) {
            $all_requests = $db->select('withdraw_requests', [], ['status' => $search_param]);
        } else {
            $all_requests = $db->select('withdraw_requests', [], []);
        }


        foreach ($all_requests as $request) {
            $borrower_id = $request['borrower_id'];
            $borrower = $db->select('borrower', [], ['active_flag' => 1, 'del_flag' => 0, 'id' => $borrower_id]);
            if ($borrower[0]) {
                $id = $request['id'];
                $fullname = $borrower[0]['title'] . " " . $borrower[0]['fname'] . " " . $borrower[0]['lname'];
                $created_at = $request['created_at'];
                $settled = $request['settled'];
                $status = $request['status'];
                $amount = $request['amount'];
                $settled_at = $request['settled_at'];
                $final_array[] = array(
                    "id" => $id,
                    "fullname" => $fullname, //$borrower_id;
                    "createdAt" => $created_at,
                    "settled" => $settled,
                    "status" => $status,
                    "amount" => $amount,
                    "settledAt" => $settled_at
                );
            } else {
                continue;
            }
        }
        return $final_array;
        // Get the borrower's info

    }
    public static function getRequest($db, $request_id)
    {
    }
    public static function approveRequest($db, $transaction_id)
    {

        $request = $db->select('withdraw_requests', [], ['id' => $transaction_id]);
        //
        $request = $request[0];
        $settled = $request['settled'];
        // id of borrower who requested
        $borrower_id = $request['borrower_id'];
        // echo $borrower_id;
        // if borrower id is empty
        if ($borrower_id && !$settled) {

            $borrower_details = $db->select('borrower', [], ['id' => $borrower_id]);
            // echo json_encode($borrower_details);
            $account_details = $db->select('savings_account', [], ['borrower_id' => $borrower_id]);
            $account_balance = $account_details[0]['balance'];
            $withdraw_amount = $request['amount'];
            $savings_id = $account_details[0]['id'];
            // echo json_encode($account_details);
            // echo $withdraw_amount;
            // echo $account_balance;
            if ($withdraw_amount <= $account_balance) {

                $manager_db = new DbAcess("ssenhogv_manager");
                $db = new DbAcess();

                $company_name = $_SESSION['company'];
                $sms_status = AppUtil::sms_check($manager_db, $company_name);
                $can_transact = AppUtil::units_check($manager_db, $company_name);

                $sms = new SMS();

                // $table = "savings_transcations";
                // print_r($request);
                // die();


                if ($can_transact) {
                    // $timestamp = time();
                    $transaction_date = date('y-m-d h:m:s');
                    $transaction_time = date("h:i a");
                    $transaction_type = 'Withdrawal';
                    $transaction_amount = $withdraw_amount;
                    $transaction_fees = [];
                    $deposited_by = ''; //AppUtil::userFullName();
                    $totalIncreBal = $account_balance - $withdraw_amount;
                    if (!isset($transaction_fees)) {
                        $transaction_fees = [];
                    }
                    //                     echo json_encode(array(
                    // $transaction_date,
                    // $transaction_time,
                    // $transaction_type,
                    // $transaction_fees,
                    // $deposited_by,
                    //                     ));
                    // die();
                    $transaction_description = 'Online Transaction';

                    $data = [
                        "savings_account_id" => $savings_id,
                        // "savings_account_to" => $transaction_to,
                        "amount" => $transaction_amount,
                        "type" => "D",
                        "transaction_date" => $transaction_date,
                        "transaction_time" => $transaction_time,
                        "trans_type" => $transaction_type,
                        "description" => $transaction_description,
                        "creation_user" =>  AppUtil::userId(),
                        'incremental_balance' => $totalIncreBal,
                        "deposited_by" => $deposited_by
                    ];

                    $table = 'savings_transcations';
                    $insertId = $db->insert($table, $data);
                    if (is_numeric($insertId)) {
                        $num = AppUtil::one_less_unit($manager_db, $company_name);
                        // This breaks the script incase the transation field is not given
                        // a value in the form.
                        if (count($transaction_fees) > 0) {
                            foreach ($transaction_fees as $fee) {

                                $valueFee = 0;
                                $ids = $fee;
                                $feeDetails = $db->select("savings_fees", [], ["id" => $ids])[0];
                                if ($feeDetails['charge_mtd'] == "fixed") {
                                    $valueFee = $feeDetails['charge_amount'];
                                }
                                if ($feeDetails['charge_mtd'] == "percentage") {
                                    $valueFee = ($feeDetails['charge_rate'] / 100) * $transaction_amount;
                                }
                            }
                        }

                        // compulsory chareges
                        $feecompulsory = $db->select("savings_fees", ["charge_amount"], ["deductable" => 4])[0];

                        $smsfee = $feecompulsory["charge_amount"];
                        if ($valueFee > 0) {
                            $valueFee = $valueFee + $smsfee;
                        } else {
                            $valueFee = 0 + $smsfee;
                        }
                        // echo $valueFee;
                        // die();
                        $data1 = ['saving_id' => $insertId, 'saving_fee_id' => $fee, 'amount' => $valueFee, "creation_user" => AppUtil::userId()];
                        $chargeId = $db->insert("savings_applied_charges", $data1);
                        $data1 = [
                            "savings_account_id" => $savings_id,
                            // "savings_account_to" => $transaction_to,
                            "amount" => $valueFee,
                            "type" => "D",
                            "transaction_date" => $transaction_date,
                            "transaction_time" => $transaction_time,
                            "trans_type" => "Fees",
                            "description" => "transaction fees",
                            "creation_user" =>  AppUtil::userId(),
                            'incremental_balance' => $totalIncreBal - $valueFee,
                            "deposited_by" => $deposited_by
                        ];
                        $insertId1 = $db->insert($table, $data1);
                        $borrower_id = $db->select("savings_account", ['borrower_id'], ["id" => $savings_id])[0];
                        //$unique_no = $$db->select("borrower",[], ["id" => $borrower_id['borrower_id']])[0];
                        $borrower = $db->select("borrower", [], ["id" => $borrower_id['borrower_id']])[0];
                        $message = "Dear Customer, You have made a " . $transaction_type . " of Ugx " . $transaction_amount . " on your savings account with " . $company_name;
                        $receiver = array();
                        $receiver[] = $borrower['mobile_no'];
                        $unique_no = $borrower['unique_no'];
                        $result = $sms->sms($message, $receiver, $sms_status);

                        //add balance sheet ***
                        $type = "D";
                        $text = "savings $type";

                        $dataSheet = [
                            'reg_date' => $transaction_date, 'cr_dr' => $type, "type" => "Savings",
                            "description" => $text, "amount" => $transaction_amount, "field1" => $insertId,
                            "trans_details" => $text, "creation_user" => AppUtil::userId()
                        ];
                        $dataSheet1 = [
                            'reg_date' => $transaction_date, 'cr_dr' => "D", "type" => "Saving Fees",
                            "description" => $text, "amount" => $valueFee, "field1" => $insertId1,
                            "trans_details" => $text, "creation_user" => AppUtil::userId()
                        ];
                        $sheetId = $db->insert("balance_sheet", $dataSheet);
                        $sheetId1 = $db->insert("balance_sheet", $dataSheet1);




                        $update = $db->update("savings_account", ['balance' => $totalIncreBal], ["id" => $savings_id]);

                        $update1 = $db->update("savings_account", ['balance' => $totalIncreBal - $valueFee], ["id" => $savings_id]);
                        $db->update('withdraw_requests', ["status" => "approved", "settled" => 1, "settled_at" => $transaction_date], ['id' => $transaction_id]);

                        return ["error" => false, "message" => "Request Approved"];
                        //print_r($update);
                    } else {
                        print_r($insertId);
                    }
                } else {
                    return ["error" => true, "message" => "You have zero transaction units and hence cannot make this transaction."];
                }
            } else {
                return ["error" => true, "message" => "Insufficient Funds on client's account"];
            }
        } else {
            return ["error" => true, "message" => "Unknown client made this request"];
        }
    }
}
