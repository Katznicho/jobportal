<?php

namespace Ssentezo\Loans;

use Ssentezo\Database\DbAccess;
use Ssentezo\Util\Logger;
use Ssentezo\Loans\Loan;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\Date;

class ActiveLoan extends Loan
{

    public function __construct($loanDetails)
    {
        $this->id = $loanDetails['id'];
        $this->loanproduct = $loanDetails['loan_product_id'];
        $this->borrower =  $loanDetails['borrower_id'];
        $this->loanNumber = $loanDetails['loan_no'];
        $this->disbursmentMethod =  $loanDetails['disbursement_mtd'];
        $this->principal = $loanDetails['principal_amt'];
        $this->releaseDate =  $loanDetails['release_date'];
        $this->interestMethod =  $loanDetails['interest_mtd'];
        $this->interestRate =  $loanDetails['loan_interest'];
        $this->interestPeriod =  $loanDetails['loan_interest_pd'];
        $this->duration = $loanDetails['loan_duration'];
        $this->durationPeriod =  $loanDetails['loan_duration_pd'];
        $this->repaymentCycle = $loanDetails['repayment_cycle'];
        $this->numberOfRepayments =  $loanDetails['no_repayment_cycle'];
        $this->description = $loanDetails['description'];
        $this->status = $loanDetails['status'];
        $this->disbursmentMethod = $loanDetails['disbursement_date'];
        $this->applicationDate = $loanDetails['application_date'];
        $this->creationUser = $loanDetails['creation_user'];
        $this->creationDate =  $loanDetails['creation_date'];
        $this->lastModifiedBy = $loanDetails['last_modified_by'];
        $this->lastModifiedDate =  $loanDetails['last_modified_date'];
        $this->activeFlag = $loanDetails['active_flag'];
        $this->delFlag =  $loanDetails['del_flag'];
        $this->isGroup =  $loanDetails['is_group'];
        $this->collector =  $loanDetails['collector'];
    }
    public static function canAccrue($loanDetails, DbAccess $db)
    {
        $collection_date = self::nextDueDate($db, $loanDetails);
        if ($collection_date == NULL) {
            Logger::notice("The loan is fully paid");
            return false;
        } else {
            Logger::info("Loan Accruable next Collection date is $collection_date");
            return true;
        }
    }

    public static function isDueDate($loanDetails, $date = '')
    {

        $repayment_dates = Loan::repaymentDates($loanDetails);
        if (!$date) {
            $date = date("Y-m-d");
        }

        if (in_array($date, $repayment_dates)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Determines whether a loan is due or not (Remember a loan is due when it expects a repayment)
     * @param array $loanDetails The assiciative array with loan details whose keys are the db loan table's columns
     * @param DbAccess $db The connection to the company's database
     * @return true|false It returns true if the loan is due and false otherwise
     */
    public static function isDue($loanDetails, $db)
    {
        $collection_date = AppUtil::nextDueDate($db, $loanDetails);
        $dateDiff =  Date::getDateDiffInDays(date("Y-m-d"), $collection_date);
        if ($dateDiff == 0) {
            return true;
        } else {
            return false;
        }
    }


    public static function nextDueDate(DbAccess $db, $loanDetails)
    {

        $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id'], 'active_flag' => 1, "del_flag" => 0]);
        $loan_totals = static::totalAmountsToPay($loanDetails, $repayments);
        $total_pending_amount = $loan_totals[3];
        $total_amount_paid = $loan_totals[2];
        $collection_date = NULL;

        if ($total_pending_amount > 0) {
            $Repayment_dates = static::repaymentDates($loanDetails);

            //to get the installments that are to be made on each schedule date
            $Remaining_principle = $loanDetails['principal_amt'];
            for ($i = 1; $i <= count($Repayment_dates); $i++) {
                $installment = static::getInstallmentAmount($loanDetails, $Remaining_principle, $i)[2];
                if ($installment > $total_amount_paid) {
                    $date = str_replace("/", "-", $Repayment_dates[($i - 1)]);
                    $collection_date = date('d/m/Y', strtotime($date));
                    break;
                } else {
                    $total_amount_paid -= $installment;
                }
                $Remaining_principle -= $installment[0];
            }
        }

        return $collection_date;
    }

    public static function isPassedDue(DbAccess $db, $loanDetails)
    {
    }
    public static function getInstallmentDetails($loanDetails)
    {
    }
}
