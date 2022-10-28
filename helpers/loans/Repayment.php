<?php

namespace Ssentezo\Loans;

use ActivityLogger;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\ActivityLogger;
use Ssentezo\Database\DbAccess;
use Ssentezo\Accounting\GeneralLedger;
use Ssentezo\Accounting\ExisitingAccount;
use Ssentezo\Transaction\AccrualTransaction;
use Ssentezo\Transaction\Transaction;
use Ssentezo\Util\Date;
use Ssentezo\Util\Sms;

class Repayment
{
    protected $amount;
    protected $description;
    protected $paidOn;
    protected $dueDate;
    protected $loanId;
    protected $paidBy;

    public static function sendSms($db, $repayment_amount, $borrowerId)
    {
        $sms = new Sms(); //For sms sending 

        $borrowerDetails = $db->select("borrower", [], ["id" => $borrowerId])[0];
        $company_name = AppUtil::getCompanyName();
        $sms_status = AppUtil::sms_check($db, AppUtil::companyId());
        $message = "Dear Customer, You have made a repayment of Ugx " . $repayment_amount . " on your loan with " . $company_name;
        $receiver = array();
        $receiver[] = $borrowerDetails['mobile_no'];
        $result = $sms->sms($message, $receiver, $sms_status);
    }
    public static function makeNormalRepayment($db, $loan_id, $repayment_amount, $repayment_description, $repayment_method_id,  $collection_date, $deposited_by, $paid_on)
    {
        $sms = new Sms(); //For sms sending 
        $manager_db = new DbAccess('ssenhogv_manager'); //For accessing the manager_db
        // Get the first loan with that id(it's always 1 though)
        $loanDetails = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0];

        // Get the borrower's information
        $borrower_id = $loanDetails['borrower_id'];
        $borrowerDetails = $db->select("borrower", [], ["id" => $borrower_id])[0];
        // Know if it's a group loan or not
        $is_group = $loanDetails['is_group'];
        $data = ""; //This will store the data about a repayment that will be stored in the datatbase
        // for groups we set the is_group to 1(true)
        if ($is_group) {
            $data = [
                'loan_id' => $loan_id,
                'borrower_id' => $borrower_id,
                'amount' => $repayment_amount,
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
                'borrower_id' => $borrower_id,
                'amount' => $repayment_amount,
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
        $company_name = AppUtil::getCompanyName();
        $sms_status = AppUtil::sms_check($manager_db, AppUtil::companyId());

        if ($collection_date == AppUtil::Comparable_date_format($due_date)) {
            if ($repayment_amount > 0) {
                $insertId = $db->insert("loan_installment_paid", $data);

                if (is_numeric($insertId)) {
                    $num = AppUtil::one_less_unit($manager_db, $company_name);
                    ActivityLogger::logActivity(
                        AppUtil::userId(),
                        "Add repayment on loan #$loan_id",
                        "Success",
                        "Added repayment with id #$insertId"
                    );
                    $message = "Dear Customer, You have made a repayment of Ugx " . $repayment_amount . " on your loan with " . $company_name;
                    $receiver = array();
                    $receiver[] = $borrowerDetails['mobile_no'];
                    $result = $sms->sms($message, $receiver, $sms_status);

                    $text = 'Loan Repayment #' . $loanDetails['loan_no'];
                    $dataSheet = [
                        'reg_date' => $collection_date, 'cr_dr' => "C", "type" => "Repayment",
                        "description" => $text, "amount" => $repayment_amount, "field1" => $insertId,
                        "trans_details" => $repayment_description, "creation_user" => AppUtil::userId()
                    ];
                    $sheetId = $db->insert("balance_sheet", $dataSheet);
                    header("location:../loans/view_loan_details.php?loan_id=" . $loan_id);
                } else {
                    ActivityLogger::logActivity(
                        AppUtil::userId(),
                        "Add repayment on loan #$loan_id",
                        "Failed",
                        "Failed with reason $insertId"
                    );
                    $message = "Add repayment  failed with reason $insertId";
                }
            } else {
                $message = "Repayment amount cannot be equal to or less than zero";
                ActivityLogger::logActivity(
                    AppUtil::userId(),
                    "Add repayment on loan #$loan_id",
                    "Aborted",
                    "Reason:$message"
                );
            }
        } else {
            $message = "Wrong collection date provided!";
            ActivityLogger::logActivity(
                AppUtil::userId(),
                "Add repayment on loan #$loan_id",
                "Aborted",
                "Reason:$message"
            );
        }
        return $message;
    }

    /**
     * Handles the excess payment on a loan
     * @param DbAccess $db The database connection of the company
     * @param Transaction $transaction The transaction object of the current repayment transaction
     * @param double $amount The excess amount of maoney on the repayment
     * @param ExisitingAccount $LGF_account The Loan Guarantee account object
     * @param string $narrative The narrative the transaction
     * @param int $borrower_id The id of the borrower
     */
    public static function handleLoanGuaranteeFund($db, Transaction &$transaction, $amount, &$LGF_account, $narrative, $borrower_id)
    {
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

    /**
     * Categorize the kind of repyment basing on the due date
     * @param string $dueDate the next due date as the payment schedule
     * @return int Returns >=1|==0|<=-1 ==0 means the repayment is just on time, <=-1 means someone is paying before time and >=1 means 
     * someone is paying past time
     */
    public static function getRepaymentMode($dueDate)
    {
        // use the Date Utility 
        $dateDiff =  Date::getDateDiffInDays(date("Y-m-d"), $dueDate);
        return $dateDiff;
    }
}
