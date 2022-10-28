<?php

namespace Ssentezo\Transaction;

use Ssentezo\Accounting\ExisitingAccount;
use Ssentezo\Accounting\GeneralLedger;
use Ssentezo\Database\DbAccess;
use Ssentezo\Util\Date;
use Ssentezo\Util\Logger;
use Ssentezo\Loans\Loan;
use Ssentezo\Util\AutoRepair;
use Ssentezo\Util\Checker;
use Ssentezo\Util\AppUtil;

class AccrualTransaction
{

    public static function accrueLoanInterest($loanData, DbAccess $db)
    {

        /**
         * These are the loans table fields, They are just for reference just incase you want a certain column name 
         * `id`, `loan_product_id`, `borrower_id`, `loan_no`,
         *  `disbursement_mtd`, `principal_amt`, `release_date`, `interest_mtd`, 
         * `loan_interest`, `loan_interest_pd`, `loan_duration`, `loan_duration_pd`, 
         * `repayment_cycle`, `no_repayment_cycle`, `description`, `status`, `disbursement_date`,
         *  `application_date`, `creation_user`, `creation_date`, `last_modified_by`, `last_modified_date`,
         *  `active_flag`, `del_flag`, `is_group`, `collector`, `field3`, `overriden_due`, `overriden_maturity_date`,
         *  `total_accrued`, `last_accrued_on`, 
         * `is_accruable`
         */

        // Get the loan product to which the loan belongs
        $loan_product = $db->select('loan_product', [], ['id' => $loanData['loan_product_id']])[0];
        $loan_id = $loanData['id'];
        $loan_no =  $loanData['loan_no'];
        $creation_date = $loanData['creation_date'];
        $total_accrued =  $loanData['total_accrued'];
        $last_accrual_date = $loanData['last_accrued_on'];
        $interest_account =  $loan_product['interest_account_id'];
        $accrual_account = $loan_product['accrual_account_id'];
        $current_month_accrualAccount = $loan_product['current_month_accrual_account_id'];

        $borrower = $db->select('borrower', [], ['id' => $loanData['borrower_id']])[0];
        $first_name = $borrower['fname'];
        $last_name = $borrower['lname'];
        $title = $borrower['title'];
        $mobile_no =  $borrower['mobile_no'];
        $email = $borrower['email'];
        $unique_no =  $borrower['unique_no'];
        $logger_extra_loan_details = ['Loan_id=' => $loan_id, 'loan_no =' => $loan_no];
        Logger::info("Initiating  interest  accrual for Loan No. $loan_no", $logger_extra_loan_details);

        // Accounting bit of it.
        // First instantiate the account objects for both accounts

        $interestAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $interest_account])[0]);
        $accrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $accrual_account])[0]);
        $currentMonthAccrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $current_month_accrualAccount])[0]);

        Logger::info("We are working with the following accounts", array(
            "interestAccount" => $interestAccount->describe(),
            "accrualAccount" => $accrualAccount->describe(),
            "currentMonthAccrualAccount" => $currentMonthAccrualAccount->describe(),
        ));
        Logger::info("Checking whether accounts are well mapped");
        if (!$interestAccount->getId() || !$accrualAccount->getId() || !$currentMonthAccrualAccount->getAccNumber()) {
            Logger::error("The account mappings for the loan product to which this loan belongs has problems", ['No account(s) was attached to accrual or/and  interest ']);
            return;
        } else {
            $amount = 0;
            $date_time = date("Y-m-d H:i:s");
            $date = date("Y-m-d");
            $date_from = '';
            $date_to = $date_time;
            // If the last accrual date is null then we use the creation date since it implies the loan has never accrued.
            if (is_null($last_accrual_date)) {
                $date_from = $creation_date;
                // for a loan with this scenario should first of all be checked to see if it 
                // hasn't spent over 24 hours
                // If it has spent over 24 hours the we count the number of hours to compesate
            } else {
                $date_from = $last_accrual_date;
            }


            //Get hours since last accrual 
            $hoursPassed = Date::getDatediffInHours($date_from, $date_to);
            $hours_since_last_approval = $hoursPassed;
            Logger::info("Time elapsed:  $hoursPassed Hours");

            $hourlyAccrualAmount = static::calculateAccrualAmountHourly($loanData);


            $amount = 24 * $hourlyAccrualAmount;
            $amount = static::calculateDailyInterest($loanData);
            $dailyInterest = $amount;
            Logger::info("Daily interest is $amount", [$amount]);

            Logger::notice("Accrual info for loan #$loan_no", ['Time Elapsed' => $hoursPassed, 'Total Amount' => $amount]);

            // Again you should avoid accruing zeros 
            if ($amount > 0.01) {
                // Make sure we only accrue after the pre configured time  interval
                $minutes_since_last_accrual = $hoursPassed * 60;
                // This means there is no guard for un planned accrual.
                // In case by mistake a person runs the file, ammount for a day will be accrued
                // on each accrualable loan
                if ($minutes_since_last_accrual >= 0) { //static::getAccrualIntervalMinutes()) {

                    Logger::info("Initiating a transaction ", ['amount' => $amount, 'minutes_ellapsed' => $minutes_since_last_accrual]);
                    $transaction =  new Transaction($db);
                    Logger::info("Initiated Transaction with Id " . $transaction->getTransactionId());
                    $transaction->setType("Loan");
                    $transaction->setDate($date);
                    $transaction->setAmount($amount);
                    //   Work on the crediting of the current month interest income account
                    Logger::info("Attempting to credit the current month interest income account ");
                    $transaction->setDescription("Interest Accrual for Loan no #$loan_no");
                    $transaction->setAccounts($currentMonthAccrualAccount->getId());
                    $narrative = "Interest on loan Nunmber: $loan_no for  Date: $date_time ";
                    $currentMonthAccrualAccount->increase($amount, $db);
                    GeneralLedger::postTransaction($db, $currentMonthAccrualAccount->getId(), $amount, 'C', $date_time, $narrative, $currentMonthAccrualAccount->getBalance(), 0, $transaction->getTransactionId());
                    Logger::info("Successfully Credited The interest income account(" . $interestAccount->getName() . ") ");


                    //Work on the debiting of the interest accrual account
                    Logger::info("Attempting to debit the interest accrual account ");
                    $transaction->setAccounts($accrualAccount->getId());
                    $narrative = "Interest accrued on loan Nunmber: $loan_no for  Date: $date_time ";
                    $interestAccount->increase($amount, $db);
                    GeneralLedger::postTransaction($db, $accrualAccount->getId(), $amount, 'D', $date_time, $narrative, $accrualAccount->getBalance(), 0, $transaction->getTransactionId());
                    Logger::info("Successfully debited The interest accrual account(" . $accrualAccount->getName() . ") ");
                    // Update the loan with the new accrual details
                    $db->update(
                        'loans',
                        ['total_accrued' => $total_accrued + $amount, 'last_accrued_on' => $date_time],
                        ['id' => $loan_id]
                    );
                    // also add a record to the loan accruals transaction
                    $data = array('loan_id' => $loan_id, 'trans_id' => $transaction->getTransactionId(), 'amount' => $amount, 'date' => $date_time, 'total_amount' => $total_accrued + $amount);
                    $db->insert('loan_interest_accruals', $data);
                    $result = $transaction->save();
                    Logger::info("Transaction successfully saved with Transaction Id " . $transaction->getTransactionId(), ['Returned boolean ' => $result]);
                } else {
                    Logger::error("Accrual Rejected Because It was too sooner than the expected time", $logger_extra_loan_details);
                }
            } else {
                // Logger::warning("The amount to accrue is 0 ", $logger_extra_loan_details);
            }
        }
    }


    public static function dueDateAccrueLoanInterest($loanData, DbAccess $db)
    {
        $loan_product = $db->select('loan_product', [], ['id' => $loanData['loan_product_id']])[0];
        $loan_id = $loanData['id'];
        $loan_no =  $loanData['loan_no'];
        $creation_date = $loanData['creation_date'];
        $total_accrued =  $loanData['total_accrued'];
        $last_accrual_date = $loanData['last_accrued_on'];
        $interest_account =  $loan_product['interest_account_id'];
        $accrual_account = $loan_product['accrual_account_id'];
        $current_month_accrualAccount = $loan_product['current_month_accrual_account_id'];

        $borrower = $db->select('borrower', [], ['id' => $loanData['borrower_id']])[0];
        $first_name = $borrower['fname'];
        $last_name = $borrower['lname'];
        $title = $borrower['title'];
        $mobile_no =  $borrower['mobile_no'];
        $email = $borrower['email'];
        $unique_no =  $borrower['unique_no'];
        $logger_extra_loan_details = ['Loan_id=' => $loan_id, 'loan_no =' => $loan_no];
        Logger::info("Initiating  Due date  accrual for Loan No. $loan_no", $logger_extra_loan_details);

        // Accounting bit of it.
        // First instantiate the account objects for both accounts

        $interestAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $interest_account])[0]);
        $accrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $accrual_account])[0]);
        $currentMonthAccrualAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $current_month_accrualAccount])[0]);

        if (!$interestAccount->getId() || !$currentMonthAccrualAccount->getAccNumber()) {
            return;
        } else {
            $amount = 0;
            $date_time = date("Y-m-d H:i:s");
            $date = date("Y-m-d");
            //Get the interest to be earned on the due date
            $amount = Loan::getInterestInstallmentAmount($loanData);

            $transaction =  new Transaction($db);
            Logger::info("Initiated Transaction with Id " . $transaction->getTransactionId());

            // Debit the current month interest income account with the interest to be earned
            Logger::info("Attempting to Debit the current month interest income account ");
            $transaction->setDescription("Interest for Loan no #$loan_no");
            $transaction->setAccounts($currentMonthAccrualAccount->getId());
            $narrative = "Interest on loan Nunmber: $loan_no for due  Date: $date_time ";
            $transaction->setDate($date);
            $transaction->setAmount($amount);
            $transaction->setType("Loan");
            $currentMonthAccrualAccount->decrease($amount, $db);
            GeneralLedger::postTransaction($db, $currentMonthAccrualAccount->getId(), $amount, 'D', $date_time, $narrative, $currentMonthAccrualAccount->getBalance(), 0, $transaction->getTransactionId());
            Logger::info("Successfully Debited The current month interest income account(" . $currentMonthAccrualAccount->getName() . ") ");

            //Work on the crediting of the interest income account
            Logger::info("Attempting to crdit the interest income account ");
            $transaction->setAccounts($interestAccount->getId());
            // $narrative = "Interest accrued on loan Nunmber: $loan_no ";//use the previous narrative
            $interestAccount->increase($amount, $db);
            GeneralLedger::postTransaction($db, $interestAccount->getId(), $amount, 'C', $date_time, $narrative, $interestAccount->getBalance(), 0, $transaction->getTransactionId());
            Logger::info("Successfully debited The interest accrual account(" . $interestAccount->getName() . ") ");

            $transaction->save();
            Logger::info("Transaction successfully saved with Transaction Id " . $transaction->getTransactionId());



            Logger::info("Checking for deferred interest for loan no $loan_no ..... ");
            // Work on the deffered interest
            // this is the interest that was credited on the deffered interest income account
            // When the client paid before the due date. It's not always there but it's crutial we check and see
            $sql =    "SELECT * FROM loan_interest_deferrals WHERE `loan_id`=$loan_id AND `status`=0";
            $allDeferrals = $db->selectQuery($sql);
            Logger::info("Found " . count($allDeferrals));
            foreach ($allDeferrals as $deferral) {
                $amount = $deferral['amount'];
                $deferral_id = $deferral['id'];
                $deferral_account_id =  $deferral['account_id'];
                $deferralAccount = new ExisitingAccount($db->select('accounts', [], ['id' => $deferral_account_id])[0]);
                // Instantiate a transaction
                $transaction = new Transaction($db);
                $narrative = "deffered interest for loan no #$loan_no";
                $transaction->setDescription($narrative);
                $transaction->setAmount($amount);
                $transaction->setDate($date);
                $transaction->setType("Loan");
                $transId = $transaction->getTransactionId();

                // Start with the defferal accoount
                $transaction->setAccounts($deferralAccount->getAccNumber());
                $deferralAccount->decrease($amount, $db);
                GeneralLedger::postTransaction($db, $deferralAccount->getId(), $amount, 'D', $date_time, $narrative, $deferralAccount->getBalance(), 0, $transId);

                // Now work on the accrued interest account
                $transaction->setAccounts($accrualAccount->getId());
                $accrualAccount->decrease($amount, $db);
                GeneralLedger::postTransaction($db, $accrualAccount->getId(), $amount, 'C', $date_time, $narrative, $accrualAccount->getBalance(), 0, $transId);

                // Save the transaction
                $transaction->save();

                // Update the deferral record as 1 for complete
                $db->update('loan_interest_deferrals', ['status' => 1], ['id' => $deferral_id]);
            }
        }
    }

    private static function getAccrualIntervalMinutes()
    {
        return 10;
        $manager_db = new  DbAccess('ssenhogv_manager');
        $config = $manager_db->select('config', [], ['name' => 'accrueIntervalDays', 'active_flag' => 1, 'del_flag' => 0])[0];
        if (is_array($config) && count($config) > 0) {
            $accrualIntervalDays = $config['value'];
            // This is a bit good as it's sysatematic
            return 60 * 24 * $accrualIntervalDays;
        } else {
            // This is equivalent to 1 day
            // Repair the error such that next time we find it fixed
            $autoRepair = new AutoRepair($manager_db);
            $autoRepair->dbRepair();
            return 1;
        }
    }
    private static function nomalizeDailyInterest($interestInstallment, $repayMentCycle)
    {
        switch ($repayMentCycle) {
            case 'Daily':
                return $interestInstallment;
                break;
            case 'Weekly':
                return $interestInstallment / 7;

                break;
            case 'BiWieekly':
                return $interestInstallment / 14;

                break;
            case 'Monthly':
                return $interestInstallment / 30;

                break;
            case 'BiMonthly':
                return $interestInstallment / 60;

                break;
            case 'Yearly':
                return $interestInstallment / 365;

                break;
        }
    }
    private static function calculateDailyInterest($loanDetails)
    {
        $interest = Loan::getInterestInstallmentAmount($loanDetails);
        $dailyInterest = 0;
        //Normalize converts whatever interst into daily value
        $interest = static::nomalizeDailyInterest($interest, $loanDetails['repayment_cycle']);
        return $interest;
    }
    private static function calculateAccrualAmountHourly($loanDetails)
    {
        $interest = Loan::getInterestInstallmentAmount($loanDetails);
        $hourlyInterest = 0;
        switch ($loanDetails['loan_interest_pd']) {
            case 'Day':
                $hourlyInterest = $interest / 24;
                break;
            case 'Week':
                $hourlyInterest = $interest / (24 * 7);
                break;
            case 'Month':
                // echo "Monthly realized";
                $hourlyInterest = $interest / (24 * 30);
                break;
            case 'Year':
                $hourlyInterest = $interest / (24 * 365);
                break;
        }
        return $hourlyInterest;
    }
    private static function calculateInterestRatePerHour($interest, $interest_pd)
    {
        $interestPerHour = 0;
        switch ($interest_pd) {
            case 'Day':
                $interestPerHour = $interest / 24;
                break;
            case 'Week':
                $interestPerHour = $interest / (24 * 7);
                break;
            case 'Month':
                $interestPerHour = $interest / (24 * 30);
                break;
            case 'Year':
                $interestPerHour = $interest / (24 * 365);
                break;
        }
        return $interestPerHour;
    }
    private static function calculateInterestRatePerMinute($interest, $interest_pd)
    {
        $interestPerMinute = 0;
        switch ($interest_pd) {
            case 'Day':
                $interestPerMinute = $interest / (24 * 60);
                break;
            case 'Week':
                $interestPerMinute = $interest / (24 * 60 * 7);
                break;
            case 'Month':
                $interestPerMinute = $interest / (24 * 60 *  30);
                break;
            case 'Year':
                $interestPerMinute = $interest / (24 * 60 * 365);
                break;
        }
        return $interestPerMinute;
    }
    private static function calculateInterestRatePerDay($interest, $interest_pd)
    {
        $interestPerDay = 0;
        switch ($interest_pd) {
            case 'Day':
                $interestPerDay = $interest;
                break;
            case 'Week':
                $interestPerDay = $interest / 7;
                break;
            case 'Month':
                $interestPerDay = $interest /  30;
                break;
            case 'Year':
                $interestPerDay = $interest / 365;
                break;
        }
        return $interestPerDay;
    }
    private static function calculateInterestRatePerMonth($interest, $interest_pd)
    {
        $interestPerMonth = 0;
        switch ($interest_pd) {
            case 'Day':
                $interestPerMonth = $interest * 30;
                break;
            case 'Week':
                $interestPerMonth = $interest * 4;
                break;
            case 'Month':
                $interestPerMonth = $interest;
                break;
            case 'Year':
                $interestPerMonth = $interest / 12;
                break;
        }
        return $interestPerMonth;
    }
    private static function calculateInterestRatePerYear($interest, $interest_pd)
    {
        $interestPerYear = 0;
        switch ($interest_pd) {
            case 'Day':
                $interestPerYear = $interest * 356;
                break;
            case 'Week':
                $interestPerYear = $interest * 52.143;
                break;
            case 'Month':
                $interestPerYear = $interest * 12;
                break;
            case 'Year':
                $interestPerYear = $interest;
                break;
        }
        return $interestPerYear;
    }

    /**
     * Get the total accrued interst for a loan as of the specified date 
     * @param array $loanData An associative array of the loan record
     * @param DbAccess $db The database connection of the company
     * @param string $date_time The date on which you want to know the accrued interest amount If not specified current date is used
     * @return int The amount that has accrued since the last due date  
     */
    public static function totalAccruedInterest($loanData, $db, $date = '')
    {
        //If no value passed use the current date
        if (!$date) {
            $date = date("Y-m-d");
        }
        $amount = static::calculateDailyInterest($loanData);
        // echo "Daily interest " . $amount;
        $previousDueDate = AppUtil::prevDueDate($db, $loanData);
        $dateDiff =  Date::getDateDiffInDays($previousDueDate, $date);
        return $dateDiff * $amount;
    }
}
