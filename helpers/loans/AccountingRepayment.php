<?php

namespace Ssentezo\Loans;

use Exception;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\ActivityLogger;
use Ssentezo\Database\DbAccess;
use Ssentezo\Accounting\GeneralLedger;
use Ssentezo\Accounting\ExisitingAccount;
use Ssentezo\Transaction\AccrualTransaction;
use Ssentezo\Transaction\Transaction;


class AccountingRepayment
{

    public static function handleRepayment($db, $data,)
    {

        $loan_id = $data['loan_id'];
        $repayment_method_id = $data['repayment_method_id'];
        $repayment_collected_date = trim($data['repayment_collected_date']);
        $repayment_description = $data['repayment_description'];
        $deposited_by = $data['deposited_by'];
        $paid_on = $data['repayment_date'];
        //Get all the account ids
        $main_account = $data['main_account'];
        $dest_account = $data['dest_account'];
        $interest_account = $data['interest_account'];
        $accrual_account = $data['accrual_account'];
        $deffered_account = $data['deffered_account'];
        $current_month_account = $data['current_month_account'];
        $repayment_amount = $data['repayment_amount'];

        $loanDetails = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0];
        $collection_date = AppUtil::nextDueDate($db, $loanDetails);
        $repyamentMode = Repayment::getRepaymentMode($collection_date);
        //Initialize the transaction and get the transaction handle
        $transaction = self::init($db, $loan_id, $repayment_amount, $dest_account, $deposited_by);
      

        if (isset($_GET['close_off'])) {
            $accounts['main_account'] = $main_account;
            $accounts['dest_account'] = $dest_account;
            $accounts['interest_account'] = $interest_account;
            $accounts['accrual_account'] = $accrual_account;
            $accounts['current_month_account'] = $current_month_account;
            $result = self::closeOffRepayment($db, $loan_id, $repayment_amount, $repayment_description, $repayment_method_id, $repayment_collected_date, $deposited_by, $paid_on, $accounts, $transaction);
            $balance = $result['balance'];
            $message = $result['message'];
            $error = $result['error'];
            if ($error) {
                $updateId = self::revert($db, $transaction);
                if (is_numeric($updateId)) {
                    $result['message'] .= '<br>Fortunately the transaction was reverted Successfully';
                } else {
                    $result['message'] .= '<br>Unfortunately the transaction wasn\'t reverted Successfully Reason: $updateId';
                }
                return $result;
            }
        } else {
            $balance = $repayment_amount;
            while ($balance && $collection_date) {
                $interest_to_pay = Loan::getInterestInstallmentAmount($loanDetails);
                $principal_to_pay = Loan::getPrincipalInstallmentAmount($loanDetails);
                $min_installment_amount = $interest_to_pay + $principal_to_pay;
                if ($balance < $min_installment_amount) { //if the amount can't cover a full installment then break
                    break;
                }
                if ($repyamentMode == 0) {
                    // This is the normal scedule

                    $accounts['main_account'] = $main_account;
                    $accounts['dest_account'] = $dest_account;
                    $accounts['interest_account'] = $interest_account;
                    $accounts['accrual_account'] = $accrual_account;
                    $result = self::normalRepayment($db, $loan_id, $balance, $repayment_description, $repayment_method_id, $collection_date, $deposited_by, $paid_on, $accounts, $transaction);
                } else if ($repyamentMode >= 1) {
                    // It's before due date
                    $accounts['main_account'] = $main_account;
                    $accounts['dest_account'] = $dest_account;
                    $accounts['accrual_account'] = $accrual_account;
                    $accounts['current_month_account'] = $current_month_account;
                    $accounts['deffered_interest_account'] = $deffered_account;
                    $result = self::earlyRepayment($db, $loan_id, $balance, $repayment_description, $repayment_method_id, $collection_date, $deposited_by, $paid_on, $accounts, $transaction);
                } else if ($repyamentMode <= -1) {
                    // This is for past due date 
                    $accounts['main_account'] = $main_account;
                    $accounts['dest_account'] = $dest_account;
                    $accounts['interest_account'] = $interest_account;
                    $accounts['accrual_account'] = $accrual_account;
                    $result = self::normalRepayment($db, $loan_id, $balance, $repayment_description, $repayment_method_id, $collection_date, $deposited_by, $paid_on, $accounts, $transaction);
                }

                //Check the result and see whether to continue or revert
                $error = $result['error'];
                $message = $result['message'];
                $balance = $result['balance'];
                if ($error) {
                    $updateId = self::revert($db, $transaction);
                    if (is_numeric($updateId)) {
                        $result['message'] .= '<br>Fortunately the transaction was reverted Successfully';
                    } else {
                        $result['message'] .= '<br>Unfortunately the transaction wasn\'t reverted Successfully Reason: $updateId';
                    }
                    return $result;
                }
                $collection_date = AppUtil::nextDueDate($db, $loanDetails);

                $repyamentMode = Repayment::getRepaymentMode($collection_date);
            }
        }
        $borrower_id = $loanDetails['borrower_id'];
        $narrative = "Loan Repayment on Loan no #$loan_id By " . $deposited_by;
        $result = self::commit($db, $transaction, $balance, $narrative, $borrower_id);

        // $text = 'Loan Repayment #' . $loanDetails['loan_no'];
        // $dataSheet = [
        //     'reg_date' => $collection_date, 'cr_dr' => "C", "type" => "Repayment",
        //     "description" => $text, "amount" => $repayment_amount, "field1" => $insertId,
        //     "trans_details" => $repayment_description, "creation_user" => AppUtil::userId()
        // ];
        // $sheetId = $db->insert("balance_sheet", $dataSheet);
        return $result;
    }


    /**
     * Initializes a repayment based on accounting, It first debits the bank with all the repayment amount and returns the transaction handle
     * @param DbAccess $db The database connection of the company
     * @param int $loan_id The id of the loan to which the repayment is being made
     * @param double $repayment_amount The mount being paid
     * @param int $dest_account The id of the chart of account to which money will be put
     * @param string $deposited_by The person who made the payment of the repayment
     * @return &Transaction   Returns a transaction object of the current transaction
     */
    public static function init($db, $loan_id, $repayment_amount, $dest_account, $deposited_by)
    {

        $date = date("Y-m-d");
        $date_time = date("Y-m-d H:i:s");
        $transaction = new Transaction($db);
        $destAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $dest_account])[0]);
        $narrative = "Loan Repayment on Loan no #$loan_id By " . $deposited_by;
        $transaction->setDescription($narrative);
        echo number_format($repayment_amount);
        $transaction->setAmount($repayment_amount*1.0);
        echo number_format($transaction->getAmount());
        $transaction->setType("Loan");
        $transaction->setDate($date);
        $destAccount->increase($repayment_amount, $db);
        GeneralLedger::postTransaction($db, $dest_account, $repayment_amount, 'D', $date_time, $narrative, $destAccount->getBalance(), AppUtil::userId(), $transaction->getTransactionId());
        $transaction->setAccounts($destAccount->getId());
        $transaction->addDebit($repayment_amount); //Increment the total debits
        return $transaction;
    }


    /**
     * Saves the transaction state to the database
     * @param DbAccess $db The database connection of the company
     * @param Transaction $transaction The transaction object to be saved
     * @param double $amount The amount in the repayment transaction
     * @param string $narrative The narrative of the transaction
     * @param int $borrower_id The id of the borrower
     * @return bool true| false Whether the transaction was saved or not
     */
    public static function commit($db, &$transaction, $amount, $narrative, $borrower_id)
    {
        if ($amount > 0) {
            $LGF_account_id = LoanGuaranteeFund::getLGFAccountId($db);
            $LGF_account = new ExisitingAccount($db->select('accounts', [], ['id' => $LGF_account_id])[0]);

            $date_time = date("Y-m-d H:i:s");
            $transId = $transaction->getTransactionId();
            $LGF_account_id = LoanGuaranteeFund::getLGFAccountId($db);
            $LGF_account = new ExisitingAccount($db->select('accounts', [], ['id' => $LGF_account_id])[0]);
            $transaction->setAccounts($LGF_account->getId());
            $LGF_account->increase($amount, $db);

            GeneralLedger::postTransaction($db, $LGF_account_id, $amount, 'C', $date_time, $narrative, $LGF_account->getBalance(), AppUtil::userId(), $transId);
            if (!LoanGuaranteeFund::check_profie($db, $borrower_id)) {
                LoanGuaranteeFund::create_profile($db, $borrower_id, AppUtil::userId());
            }
            LoanGuaranteeFund::add_money($db, $borrower_id, $amount, AppUtil::userId());
        }

        return $transaction->save();
    }

    /**
     * Reverts the transaction state to the previous state and deletes the repayment.
     * @param DbAccess $db The database connection of the company
     * @param Transaction $transaction The transaction object to be reverted
     * @return int|bool Returns installments deleted or 0 if none were deleted
     */
    public static function revert($db, &$transaction)
    {

        $status = GeneralLedger::revert($db, $transaction);
        $status1 = $transaction->revert();
        if ($status and $status1) {
            $updateId = $db->update('loan_installment_paid', ['active_flag' => 0, 'del_flag' => 1], ['trans_id' => $transaction->getTransactionId()]);
        } else {
            $updateId = false;
        }

        return $updateId;
    }

    /**
     * Make a normal repayment on  a loan
     * @param DbAccess $db The connection to the company's database
     * @param int $loan_id The id of the loan to which a repayment is being made
     * @param double $repayment_amount The amount to be paid
     * @param string $repayment_description The description about the repayment
     * @param int $repayment_method_id The id of the repayment method used
     * @param string $collection_date The date on the loan schedule for the repayment
     * @param string $deposited_by The person who made the repayment
     * @param string $paid_on The date on which the repayment is made
     * @param array $accounts An associative array of account ids for accounting purposes
     * @param Transaction &$transaction Reference to the transaction object representing the current transaction
     */
    public static function normalRepayment($db, $loan_id, $repayment_amount, $repayment_description, $repayment_method_id, $collection_date, $deposited_by, $paid_on, $accounts = [], &$transaction = 0)
    {
        $error = false;
        $message = '';

        $main_account = $accounts['main_account'];
        $interest_account = $accounts['interest_account'];
        $accrual_account = $accounts['accrual_account'];

        $loanDetails = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0]; // Get the first loan with that id(it's always 1 though)
        $borrower_id = $loanDetails['borrower_id']; // Get the borrower's information
        $is_group = $loanDetails['is_group']; // Know if it's a group loan or not
        $interest_to_pay = Loan::getInterestInstallmentAmount($loanDetails);
        $principal_to_pay = Loan::getPrincipalInstallmentAmount($loanDetails);
        $accrual_pay = $main_pay = $balance = 0;
        $total_accrued = $loanDetails['total_accrued'];
        $total = ($interest_to_pay + $principal_to_pay);
        if ($total > $repayment_amount) {
            $message =  "The repayment amount ($repayment_amount) should be at  least " . number_format($total, 2);
            $error = true;
            return ['error' => $error, 'message' => $message];
        }

        /* The payable is the total amount to pay in a single repayment*/
        $payable = $total;
        $main_pay = $principal_to_pay;
        $accrual_pay = $interest_to_pay;
        $balance = $repayment_amount - $payable;

        // Load accounts
        $mainAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $main_account])[0]);

        $interestAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $interest_account])[0]);
        $accrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $accrual_account])[0]);

        $data = [];
        if ($is_group) {
            $data = [
                'loan_id' => $loan_id,
                'borrower_id' => $borrower_id,
                'amount' => $payable,
                "principal_installment" => $principal_to_pay,
                "interest_installment" => $interest_to_pay,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $collection_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                'is_group' => 1,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on,
                "trans_id" => $transaction->getTransactionId()
            ];
        } else {
            $data = [
                'loan_id' => $loan_id,
                "trans_id" => $transaction->getTransactionId(),
                'borrower_id' => $borrower_id,
                'amount' => $payable,
                "principal_installment" => $principal_to_pay,
                "interest_installment" => $interest_to_pay,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $collection_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on
            ];
        }

        $collection_date =  AppUtil::Comparable_date_format($collection_date);
        $due_date = AppUtil::nextDueDate($db, $loanDetails);
        echo "Due date $due_date vs collection date $collection_date";
        if ($collection_date == AppUtil::Comparable_date_format($due_date)) { //Make sure the collection date is right and matches the loan schedule

            $insertId = $db->insert("loan_installment_paid", $data);

            if (is_numeric($insertId)) {

                $date_time = date("Y-m-d H:i:s");
                $date = date("Y-m-d");
                //Accounting bit of it.
                $totalDebits = 0;
                $totalCredits = 0;
                $narrative = "Loan Repayment on Loan no #$loan_id By " . $deposited_by;
                $transaction->setDescription($narrative);
                $transaction->setType("Loan");
                $transaction->setAmount($repayment_amount);
                $transaction->setDate($date);
                $transId = $transaction->getTransactionId();

                // First work on th interest accrual account 
                $accrual_des = $narrative;
                $accrualAccount->decrease($accrual_pay, $db);
                GeneralLedger::postTransaction($db, $accrual_account, $accrual_pay, 'C', $date_time, $accrual_des, $accrualAccount->getBalance(), AppUtil::userId(), $transId);
                $transaction->setAccounts($accrualAccount->getId());
                $totalCredits += $accrual_pay;

                // Work on the main account second
                $main_des = $narrative;
                $mainAccount->decrease($main_pay, $db);
                GeneralLedger::postTransaction($db, $main_account, $main_pay, 'C', $date_time, $main_des, $mainAccount->getBalance(), AppUtil::userId(), $transId);
                $transaction->setAccounts($mainAccount->getId());
                $totalCredits += $main_pay;

                //Increment the total debits and credits
                $transaction->addCredit($totalCredits);
                $transaction->addDebit($totalDebits);

                ActivityLogger::logActivity(AppUtil::userId(),  "Add repayment on loan #$loan_id", "Success", "Added repayment with id #$insertId");

                $text = 'Loan Repayment #' . $loanDetails['loan_no'];
                // $dataSheet = [
                //     'reg_date' => $collection_date, 'cr_dr' => "C", "type" => "Repayment",
                //     "description" => $text, "amount" => $repayment_amount, "field1" => $insertId,
                //     "trans_details" => $repayment_description, "creation_user" => AppUtil::userId()
                // ];
                // $sheetId = $db->insert("balance_sheet", $dataSheet);
            } else {
                $message = "Failed with reason $insertId";
                $error  = true;
                ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id", "Failed", "Failed with reason $insertId");
            }
        } else {
            $message = "Wrong collection date provided!";
            $error = true;
            ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id", "Aborted", "Reason:$message");
        }
        return ['error' => $error, 'message' => $message, 'balance' => $balance];
    }

    /**
     * Firstly uses accounting to make an early repayment for a loan(Early repayment means a repeymne before the due date)
     * @param DbAccess $db The database connection for the company's database
     * @param int $loan_id The loan id
     * @param float $repayment_amount The amount to be paid 
     * @param string $repayment_description A description about the repayment
     * @param int $repayment_method_id An identifier of the repayment method used
     * @param string $collection_date The due date of the loan
     * @param string $deposited_by The name of the personn who made the deposit
     * @param string $paid_on Date on which the repayment was made 
     * @param array $accounts An associative array of account ids required for the accounting bit of it
     * @return string A summary|error message of what happened
     */
    public static function earlyRepayment($db, $loan_id, $repayment_amount, $repayment_description, $repayment_method_id,  $collection_date, $deposited_by, $paid_on, $accounts, &$transaction)
    {
        $error = false;
        $message = '';
        $main_account = $accounts['main_account'];
        $interest_account = $accounts['interest_account'];
        $accrual_account = $accounts['accrual_account'];
        $current_month_account = $accounts['current_month_account'];
        $deffered_interest_account = $accounts['deffered_interest_account'];

        $loanDetails = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0];

        // Get the borrower's information
        $borrower_id = $loanDetails['borrower_id'];
        $is_group = $loanDetails['is_group'];

        $interest_to_pay = Loan::getInterestInstallmentAmount($loanDetails);
        $principal_to_pay = Loan::getPrincipalInstallmentAmount($loanDetails);

        $total_to_pay = $principal_to_pay + $interest_to_pay;
        // Reject the repayment if the amount is less than the expexted
        // It's a standard that will make the accounting easier, We don't allow halves as repayments
        if ((int)$total_to_pay > (int)$repayment_amount) {
            $error = false;
            $message =  "Error! Expexted Amount:" . number_format($total_to_pay, 2) . " but Found " . number_format($repayment_amount, 2) . " Make sure you pay the expected amount";
            return ['error' => $error, 'message' => $message];
        }

        $total_accrued = $loanDetails['total_accrued'];
        //ensure total accrued is correct 
        $accruedSofar = AccrualTransaction::totalAccruedInterest($loanDetails, $db); //Obtained the mathematical accrued interest by calculation
        $prevDueDate = AppUtil::prevDueDate($db, $loanDetails);
        if ($total_accrued > $interest_to_pay) { //If we have the total accrued so far more tha the expected interest, it implies an error

            $total_accrued = $accruedSofar;

            //return "[case 2]The accrued amount is " . $total_accrued;
        }

        //Also check if the calculated accrued so far is negative and cancel the operation immetiately as it means it's in the future
        // if ($accruedSofar < 0) {
        //     $error = true;
        //     $message =  "Error!. A repayment was previouly made on this loan for the due date " . date("d F, Y ", strtotime(str_replace("/", "-", $prevDueDate))) .
        //         " but Today is " . date("d F, Y ") . ". So we expected the previous repayment date to be earlier than today";
        //     return ['error' => $error, 'message' => $message];
        // }

        // Load accounts
        $mainAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $main_account])[0]);
        $interestAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $interest_account])[0]);
        $accrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $accrual_account])[0]);
        $currentMonthAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $current_month_account])[0]);
        $defferedInterestAccount = new  ExisitingAccount($db->select('accounts', [], ['id' => $deffered_interest_account])[0]);

        $accrualPay = $mainPay = $balance = 0;
        $accrualPay = $interest_to_pay;
        $mainPay =    $principal_to_pay; //$repayment_amount - $accrualPay;
        $payable = ($mainPay + $accrualPay);
        $balance = $repayment_amount - $payable;

        if ($is_group) {
            $data = [
                'loan_id' => $loan_id,
                "trans_id" => $transaction->getTransactionId(),
                'borrower_id' => $borrower_id,
                'amount' => $payable,
                'principal_installment' => $principal_to_pay,
                'interest_installment' => $interest_to_pay,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $collection_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                'is_group' => 1,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on
            ];
        } else {
            $data = [
                'loan_id' => $loan_id,
                "trans_id" => $transaction->getTransactionId(),
                'borrower_id' => $borrower_id,
                'amount' => $payable,
                'principal_installment' => $principal_to_pay,
                'interest_installment' => $interest_to_pay,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $collection_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on
            ];
        }

        $collection_date =  AppUtil::Comparable_date_format($collection_date);
        $due_date = AppUtil::nextDueDate($db, $loanDetails);
        if ($collection_date == AppUtil::Comparable_date_format($due_date)) {

            $insertId = $db->insert("loan_installment_paid", $data);
            if (is_numeric($insertId)) {

                $date_time = date("Y-m-d H:i:s");
                $totalDebits = $totalCredits = 0;
                $narrative = "Loan Repayment on Loan no #$loan_id By " . $deposited_by;
                $transId = $transaction->getTransactionId();

                // 2 Credit the loan portifolio account
                $transaction->setAccounts($mainAccount->getId());
                $mainAccount->decrease($mainPay, $db);
                $main_des = $narrative;
                GeneralLedger::postTransaction($db, $main_account, $mainPay, 'C', $date_time, $main_des, $mainAccount->getBalance(), AppUtil::userId(), $transId);
                $totalCredits += $mainPay;

                // 3 Credit accrual account 
                $accrual_des = $narrative;
                $amount = $total_accrued;
                $transaction->setAccounts($accrualAccount->getId());
                $accrualAccount->decrease($amount, $db);
                GeneralLedger::postTransaction($db, $accrual_account, $amount, 'C', $date_time, $accrual_des, $accrualAccount->getBalance(), AppUtil::userId(), $transId);
                $totalCredits += $amount;

                // Deffer the remainion interwest to the deffered interest account
                // $transaction->setAccounts($)
                $amount = $interest_to_pay - $total_accrued;
                $transaction->setAccounts($defferedInterestAccount->getId());
                $defferedInterestAccount->increase($amount, $db);
                GeneralLedger::postTransaction($db, $defferedInterestAccount->getId(), $amount, 'C', $date_time, $narrative, $defferedInterestAccount->getBalance(), AppUtil::userId(), $transId);

                //We have to create a deferred loan interest record
                $db->insert('loan_interest_deferrals', array("loan_id" => $loan_id, "trans_id" => $transId, "status" => 0, "account_id" => $defferedInterestAccount->getId(), "amount" => $amount, "created_by" => AppUtil::userId()));

                $transaction->addCredit($totalCredits);
                $transaction->addDebit($totalDebits);

                // now reduce the accrued interest on the loan
                $db->update('loans', ["total_accrued" => 0], ['id' => $loan_id]);


                //code...
                ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id", "Success", "Added repayment with id #$insertId");
                // $text = 'Loan Repayment #' . $loanDetails['loan_no'];
                // $dataSheet = [
                //     'reg_date' => $collection_date, 'cr_dr' => "C", "type" => "Repayment",
                //     "description" => $text, "amount" => $repayment_amount, "field1" => $insertId,
                //     "trans_details" => $repayment_description, "creation_user" => AppUtil::userId()
                // ];
                // $sheetId = $db->insert("balance_sheet", $dataSheet);
            } else {

                ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id",  "Failed", "Failed with reason $insertId");
                $error = true;
                $message =  "Error! An Un expexted error Occurred. We are working our best to get it fixed, Please try again later.";
            }
        } else {

            $message = "Error! Wrong collection date provided!";
            $error = true;
            ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id", "Aborted", "Reason:$message");
            echo $collection_date;
        }
        return ['error' => $error, 'message' => $message, 'balance' => $balance];
    }
    /**
     * Make an early loan repayment using the accounting feature and closes off the laon
     * @param DbAccess $db The database connection for the company
     * @param int $loan_id The id of the loan 
     * @param double $repayment_amount The amount being payed
     * @param string $repyament_description A description about the repayment
     * @param int $repayment_method_id the id of the repayment method being used
     * @param string $collection_date The date on which the repayment money is collected/received
     * @param string $deposited_by The person making the repayment
     * @param string $paid_on The date on which the repayment is made
     * @param array $accounts An array of account ids required for this operation
     */
    public static function closeOffRepayment($db, $loan_id, $repayment_amount, $repayment_description, $repayment_method_id,  $collection_date, $deposited_by, $paid_on, $accounts, Transaction &$transaction)
    {


        $main_account = $accounts['main_account'];
        $dest_account = $accounts['dest_account'];
        $interest_account = $accounts['interest_account'];
        $accrual_account = $accounts['accrual_account'];
        $current_month_account = $accounts['current_month_account'];
        $loanDetails = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0];
        $payments = $db->select("loan_installment_paid", [], ["loan_id" => $loan_id, "active_flag" => 1, "del_flag" => 0]);
        $totalAmountToPay = 0;
        $Installments = Loan::totalAmountsToPay($loanDetails, $payments);
        // $totalPaid = $Installments[2];

        //Get all the penalties
        $afterMP = new AftermaturityPenalty();
        $afterMP = $afterMP->totalaftermpenalty($loan_id);

        $sumPenalty = new SchedulePenalty();
        $sumPenalty = $sumPenalty->totalschedulpenalty($loan_id);


        $totalDue = $Installments[0];
        $totalInterst = $Installments[1]; //This is the total interest for the loan
        $totalPrincipal = $totalDue - $totalInterst; //This is the total principal for the loan
        $totalInterestPaid = Loan::totalInterestPaid($loan_id, $db);
        $totalPrincipalPaid = Loan::totalPrincipalPaid($loan_id, $db);

        //Now we have to calculate the remaining interest and principal, such that we can pay the exact amounts
        $total_interest_to_pay = $totalInterst - $totalInterestPaid;
        $total_principal_to_pay = $totalPrincipal - $totalPrincipalPaid;

        $balance = 0;
        //Compute the right total amount to pay to close off loan
        if (is_null($loanDetails['overriden_due'])) {
            $balance = $Installments[3] + $afterMP + $sumPenalty;
        } else {
            $balance = $loanDetails['overriden_due'] + $afterMP + $sumPenalty - $Installments[2];
        }
        $totalAmountToPay = $balance;
        //If the amount is lessthan the amount to pay deny the repayment
        if ($totalAmountToPay > $repayment_amount) {
            ActivityLogger::logActivity(AppUtil::userId(), "Close Off Loan #$loan_id", "failed", "Insufficient Amount");
            $error = true;
            $message =  "Error! Insufficient amount to close off loan, Required " . number_format($totalAmountToPay, 2) . " it's less by " . number_format($totalAmountToPay - $repayment_amount, 2);
            return ['error' => $error, 'message' => $message, 'balance' => $balance];
        }

        // Get the borrower's information
        $borrower_id = $loanDetails['borrower_id'];
        $is_group = $loanDetails['is_group'];
        // $interestToPay = Loan::getInterestInstallmentAmount($loan);
        // $principal_to_pay = Loan::getPrincipalInstallmentAmount($loan);
        $total_accrued = $loanDetails['total_accrued'];

        // Load accounts
        $mainAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $main_account])[0]);
        $destAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $dest_account])[0]);
        $interestAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $interest_account])[0]);
        $accrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $accrual_account])[0]);
        $currentMonthAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $current_month_account])[0]);
        $transId = $transaction->getTransactionId();

        if ($is_group) {
            $data = [
                'loan_id' => $loan_id,
                'trans_id' => $transId,
                'borrower_id' => $borrower_id,
                'amount' => $totalAmountToPay, //$repayment_amount,
                'interest_installment' => $total_interest_to_pay,
                'principal_installment' => $total_principal_to_pay,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $collection_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                'is_group' => 1,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on
            ];
        } else {
            $data = [
                'loan_id' => $loan_id,
                'trans_id' => $transId,
                'borrower_id' => $borrower_id,
                'amount' => $totalAmountToPay, //$repayment_amount,
                'interest_installment' => $total_interest_to_pay,
                'principal_installment' => $total_principal_to_pay,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $collection_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on
            ];
        }

        $collection_date =  AppUtil::Comparable_date_format($collection_date);
        $due_date = AppUtil::nextDueDate($db, $loanDetails);

        if ($collection_date == AppUtil::Comparable_date_format($due_date)) {


            $insertId = $db->insert("loan_installment_paid", $data);

            if (is_numeric($insertId)) {

                $date_time = date("Y-m-d H:i:s");
                $date = date("Y-m-d");
                $loan_no = $loanDetails['loan_no']; //Get the loan number, It's not always the same as loan id

                $totalDebits = 0;
                $totalCredits = 0;
                $accrualPay = $mainPay = $loanGuaranteeFundPay = 0;

                $narrative = "Loan close off Repayment on Loan no #$loan_no By " . $deposited_by;



                /* 2. Credit the loan portifolio account */
                $mainPay = $total_principal_to_pay;
                $transaction->setAccounts($mainAccount->getId());
                $mainAccount->decrease($mainPay, $db);
                $main_des = $narrative;
                GeneralLedger::postTransaction($db, $main_account, $mainPay, 'C', $date_time, $main_des, $mainAccount->getBalance(), AppUtil::userId(), $transId);
                $totalCredits += $mainPay;

                /* 3. Credit interest Income account */
                $amount = $total_interest_to_pay;
                $transaction->setAccounts($interestAccount->getId());
                $interestAccount->increase($amount, $db);
                GeneralLedger::postTransaction($db, $interestAccount->getId(), $amount, 'C', $date_time, $narrative, $interestAccount->getBalance(), AppUtil::userId(), $transId);
                $destAccount->decrease($accrualPay, $db);
                $totalCredits += $amount;

                /* 4. Credit accrual account */
                $accrual_des = $narrative;
                $amount = $total_accrued;
                $transaction->setAccounts($accrualAccount->getId());
                $accrualAccount->decrease($amount, $db);
                GeneralLedger::postTransaction($db, $accrualAccount->getId(), $amount, 'C', $date_time, $accrual_des, $accrualAccount->getBalance(), AppUtil::userId(), $transId);
                $totalCredits += $amount;

                /* 5 Debit the current month interest with accrued so far */
                $amount = $total_accrued;
                $accrual_des = $narrative;
                $transaction->setAccounts($currentMonthAccount->getId());
                GeneralLedger::postTransaction($db, $currentMonthAccount->getId(), $amount, 'D', $date_time, $narrative, $currentMonthAccount->getBalance(), AppUtil::userId(), $transId);
                $currentMonthAccount->decrease($repayment_amount, $db);
                $totalDebits += $amount;

                //Handel the penalties as well no implementation for now

                $transaction->addDebit($totalDebits);
                $transaction->addCredit($totalCredits);
                // $balance = $repayment_amount - $totalAmountToPay; //This will only work if we have worked on the penalties
                $balance = $repayment_amount - ($total_principal_to_pay + $total_interest_to_pay);
            } else {
                $message = "Error! Unexpected error occured Reason: $insertId";
                $error = true;
                ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id", "Failed", "Failed with reason $insertId");
            }
        } else {
            $message = "Wrong collection date provided!";
            $error = true;
            ActivityLogger::logActivity(AppUtil::userId(), "Add repayment on loan #$loan_id", "Aborted", "Reason:$message");
        }
        return ['error' => $error, 'message' => $message, 'balance' => $balance];
    }
}
