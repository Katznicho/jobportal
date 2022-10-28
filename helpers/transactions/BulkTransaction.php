<?php

namespace Ssentezo\Transaction;

use Ssentezo\Loans\ActiveLoan;
use Ssentezo\Util\Logger;
use Ssentezo\Util\AppUtil;

class BulkTransaction
{
    public static function  loanInterestAccruals($loansData, $db)
    {

        foreach ($loansData as $loanData) {

            $loan_no = $loanData['loan_no'];
            $id = $loanData['id'];
            $totalAccrued = $loanData['total_accrued'];
            $lastAccruedOn = $loanData['last_accrued_on'];

            Logger::info("Working on loan no #$loan_no",  ['id' => $id, 'total_accrued' => $totalAccrued, 'last_accrued_on' => $lastAccruedOn]);


            // Check if loan is still eligible for accruing interest
            if (ActiveLoan::canAccrue($loanData, $db)) {
                // Run the accrual for that loan.
                AccrualTransaction::accrueLoanInterest($loanData, $db);
            } else {
                Logger::notice("The loan can't be accrued");
            }

            Logger::info("Finished working on loan no #$loan_no");
        }
    }
    public static function savingsInterestAccurals()
    {
    }

    /**
     * Moves the accrued interest from the current month/deffered interest account to the specific loan product interest income account
     * @link https://www.docs.ssentezo.com/loans/due-date-accrual
     * @param array $loansData An array of all loans as fetched from the database
     * @param DbAccess The connection to the company's database
     */
    public static function loanDueDateInterestAccrual($loansData, $db)
    {
        

        foreach ($loansData as $loanData) {

            $loan_no = $loanData['loan_no'];
            $id = $loanData['id'];
            $totalAccrued = $loanData['total_accrued'];
            $lastAccruedOn = $loanData['last_accrued_on'];

            Logger::info("Working on loan no #$loan_no",  ['id' => $id, 'total_accrued' => $totalAccrued, 'last_accrued_on' => $lastAccruedOn]);


            // Check if loan is due, This operation is only valid for due loans
            if (ActiveLoan::isDueDate($loanData, date("Y-m-d"))) {
                Logger::info("True: This is a due date for this loan...");
                // Run the accrual for that loan.
                AccrualTransaction::dueDateAccrueLoanInterest($loanData, $db);
            } else {
                Logger::notice("Loan is not yet due So we can't perform the transaction");
            }

            Logger::info("Finished working on loan no #$loan_no");
        }
    }
}
