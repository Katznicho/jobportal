<?php

namespace Ssentezo\Loans;

use DbAcess;
use Ssentezo\Database\DbAccess;

class Loan
{
    protected $id;
    protected $loanproduct;
    protected $borrower;
    protected $loanNumber;
    protected $disbursmentMethod;
    protected $principal;
    protected $releaseDate;
    protected $interestMethod;
    protected $interestRate;
    protected $interestPeriod;
    protected $duration;
    protected $durationPeriod;
    protected $repaymentCycle;
    protected $numberOfRepayments;
    protected $description;
    protected $status;
    protected $disbursementDate;
    protected $applicationDate;
    protected $creationUser;
    protected $creationDate;
    protected $lastModifiedDate;
    protected $lastModifiedBy;
    protected $activeFlag;
    protected $delFlag;
    protected $isGroup;
    protected $collector;
    protected $maturityDate;
    protected $balance;

    // Getters and setters
    public function getId()
    {
        return $this->id;
    }
    public function getLoanproduct()
    {
        return $this->loanproduct;
    }
    public function getBorrower()
    {
        return $this->borrower;
    }
    public function getLoanNumber()
    {
        return $this->loanNumber;
    }
    public function getDisbursmentMethod()
    {
        return $this->disbursmentMethod;
    }
    public function getPrincipal()
    {
        return $this->principal;
    }
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }
    public function getInterestMethod()
    {
        return  $this->interestMethod;
    }
    public function getInterestRate()
    {
        return $this->interestRate;
    }
    public function getInterestPeriod()
    {
        return $this->interestPeriod;
    }
    public function getDuration()
    {
        return $this->duration;
    }
    public function getDurationPeriod()
    {
        return $this->durationPeriod;
    }
    public function getRepaymentCycle()
    {
        return $this->repaymentCycle;
    }
    public function getNumberOfRepayments()
    {
        return $this->numberOfRepayments;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function get_status()
    {
        return $this->status;
    }
    public function getDisbursementDate()
    {
        return $this->disbursmentMethod;
    }
    public function getApplicationDate()
    {
        return $this->applicationDate;
    }
    public function getCreationUser()
    {
        return $this->creationUser;
    }
    public function getCreationDate()
    {
        return $this->creationDate;
    }
    public function getLastModifiedDate()
    {
        return $this->lastModifiedDate;
    }
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }
    public function getActiveFlag()
    {
        return $this->activeFlag;
    }
    public function getDelFlag()
    {
        return $this->delFlag;
    }
    public function getIsGroup()
    {
        return $this->isGroup;
    }
    public function getCollector()
    {
        return $this->collector;
    }
    // public function getMaturityDate()
    // {
    // return $this->maturityDate;
    // }
    public function getBalance()
    {
        return $this->balance;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    public function setLoanproduct($loanproduct)
    {
        $this->loanproduct = $loanproduct;
    }
    public function setBorrower($borrower)
    {
        $this->borrower = $borrower;
    }
    public function setLoanNumber($loanNumber)
    {
        $this->loanNumber = $loanNumber;
    }
    public function setDisbursmentMethod($disbursmentMethod)
    {
        $this->disbursmentMethod = $disbursmentMethod;
    }
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    }
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
    }
    public function setInterestMethod($interestMethod)
    {
        $this->interestMethod = $interestMethod;
    }
    public function setInterestRate($interestRate)
    {
        $this->interestRate = $interestRate;
    }
    public function setInterestPeriod($interestPeriod)
    {
        $this->interestPeriod = $interestPeriod;
    }
    public function setDuration($durationPeriod)
    {
        $this->duration = $durationPeriod;
    }
    public function setDurationPeriod($durationPeriod)
    {
        $this->durationPeriod = $durationPeriod;
    }
    public function setRepaymentCycle($repaymentCycle)
    {
        $this->repaymentCycle = $repaymentCycle;
    }
    public function setNumberOfRepayments($numberOfRepayments)
    {
        $this->numberOfRepayments = $numberOfRepayments;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function setDisbursementDate($disbursmentMethod)
    {
        $this->disbursmentMethod = $disbursmentMethod;
    }
    public function setApplicationDate($applicationDate)
    {
        $this->applicationDate = $applicationDate;
    }
    public function setCreationUser($creationUser)
    {
        $this->creationUser = $creationUser;
    }
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
    public function setLastModifiedDate($lastModifiedDate)
    {
        $this->lastModifiedDate = $lastModifiedDate;
    }
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }
    public function setActiveFlag($activeFlag)
    {
        $this->activeFlag = $activeFlag;
    }
    public function setDelFlag($delFlag)
    {
        $this->delFlag = $delFlag;
    }
    public function setIsGroup($isGroup)
    {
        $this->isGroup = $isGroup;
    }
    public function setCollector($collector)
    {
        $this->collector = $collector;
    }
    public function setMaturityDate($maturityDate)
    {
        $this->maturityDate = $maturityDate;
    }
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }
    public static function totalInterestPaid($loan_id, $db)
    {
        $query = "SELECT SUM(interest_installment) as total_interest FROM loan_installment_paid WHERE loan_id = $loan_id AND active_flag=1 AND del_flag=0";
        $total = $db->selectQuery($query)[0];
        return $total['total_interest'];
    }
    public static function totalPrincipalPaid($loan_id, $db)
    {
        $query = "SELECT SUM(principal_installment) as total_principal FROM loan_installment_paid WHERE loan_id = $loan_id AND active_flag=1 AND del_flag=0";
        $total = $db->selectQuery($query)[0];
        return $total['total_principal'];
    }
    public static function totalAmountsToPay($loanDetails, $Repayments)
    {

        $totalDue = $totalPaid = $totalInterest = $pending = 0;
        $result = array();
        $num = self::numberOfInstallments($loanDetails);

        foreach ($Repayments as $repay) {
            $paid = $repay['amount'];
            $totalPaid += (int)$paid;
        }

        $Remaining_principle = $loanDetails['principal_amt'];

        for ($i = 1; $i <= $num; $i++) {
            $installments = self::getInstallmentAmount($loanDetails, $Remaining_principle, $i);

            if ($loanDetails['interest_mtd'] == "Reducing Balance - Equal Installments" || $loanDetails['interest_mtd'] == "Reducing Balance - Equal Principal") {
                $Remaining_principle -= $installments[0];
            }

            $totalDue += $installments[2];
            $totalInterest += $installments[1];
        }

        $pending = $totalDue - $totalPaid;

        $result[] = $totalDue;
        $result[] = $totalInterest;
        $result[] = $totalPaid;
        $result[] = $pending;

        return $result;
    }

    public static function getInstallmentAmount($loanDetails, $Principle = 0, $repayment_no = 0)
    {
        $interest_installment = self::getInterestInstallmentAmount($loanDetails, $Principle, $repayment_no);
        $principal_installement = self::getPrincipalInstallmentAmount($loanDetails, $Principle, $repayment_no);
        $result = array();

        $due = $principal_installement + $interest_installment;
        $result[] = $principal_installement;
        $result[] = $interest_installment;
        $result[] = $due;


        return $result;
    }


    public static function numberOfInstallments($loanDetails)
    {
        $LoanDuration = $loanDetails['loan_duration'];
        $LoanDurationPeriod = $loanDetails['loan_duration_pd'];
        $loanRepaymentCycle = $loanDetails['repayment_cycle'];

        $repaymentPeriods = self::RepayemntPeriod($loanRepaymentCycle);

        if (isset($LoanDurationPeriod)) {
            $totalRepayments = 0;
            $yearly = $repaymentPeriods['yearly'];
            $monthly = $repaymentPeriods['monthly'];
            $weekly = $repaymentPeriods['weekly'];
            $daily = $repaymentPeriods['daily'];



            if ($LoanDurationPeriod == "Days") {
                $totalRepayments = $LoanDuration * $daily;
            }
            if ($LoanDurationPeriod == "Weeks") {
                $totalRepayments = $LoanDuration * $weekly;
            }
            if ($LoanDurationPeriod == "Months") {
                $totalRepayments = $LoanDuration * $monthly;
            }
            if ($LoanDurationPeriod == "Years") {
                $totalRepayments = $LoanDuration * $yearly;
            }
        }
        return $totalRepayments;
    }

    public static function LoanDurationInYears($loanDetails)
    {
        $LoanDuration = $loanDetails['loan_duration'];
        $LoanDurationPeriod = $loanDetails['loan_duration_pd'];
        $result = 0;
        if (isset($LoanDurationPeriod)) {

            if ($LoanDurationPeriod == "Days") {
                $result = $LoanDuration / 365;
            }
            if ($LoanDurationPeriod == "Weeks") {
                $result = $LoanDuration / 52;
            }
            if ($LoanDurationPeriod == "Months") {
                $result = $LoanDuration / 12;
            }
            if ($LoanDurationPeriod == "Years") {
                $result = $LoanDuration;
            }
        }
        return $result;
    }
    public static function RepaymentCycle($loanRepaymentCycle)
    {
        $cycle = "";

        if ($loanRepaymentCycle == "Daily") {
            $cycle = "1 day";
        }
        if ($loanRepaymentCycle == "Weekly") {
            $cycle = "1 week";
        }
        if ($loanRepaymentCycle == "Biweekly") {
            $cycle = "2 week";
        }
        if ($loanRepaymentCycle == "Monthly") {
            $cycle = "1 month";
        }
        if ($loanRepaymentCycle == "Bimonthly") {
            $cycle = "2 month";
        }
        if ($loanRepaymentCycle == "Quarterly") {
            $cycle = "3 month";
        }
        if ($loanRepaymentCycle == "Semi-Annual") {
            $cycle = "6 month";
        }
        if ($loanRepaymentCycle == "Yearly") {
            $cycle = "1 year";
        }

        return $cycle;
    }

    public static function RepayemntPeriod($loanRepaymentCycle)
    {
        $result = array();

        $yearly = 0;
        $monthly = 0;
        $weekly = 0;
        $daily = 0;
        $fraction_of_year = 0;

        if ($loanRepaymentCycle == "Daily") {
            $yearly = 365;
            $monthly = 30;
            $weekly = 7;
            $daily = 1;
            $fraction_of_year = 1 / 365;
        }
        if ($loanRepaymentCycle == "Weekly") {
            $yearly = 52;
            $monthly = 4;
            $weekly = 1;
            $fraction_of_year = 1 / 52;
        }
        if ($loanRepaymentCycle == "Biweekly") {
            $yearly = 26;
            $monthly = 2;
            $biweekly = 1;
            $fraction_of_year = 1 / 26;
        }
        if ($loanRepaymentCycle == "Monthly") {
            $yearly = 12;
            $monthly = 1;
            $fraction_of_year = 1 / 12;
        }
        if ($loanRepaymentCycle == "Bimonthly") {
            $yearly = 6;
            $monthly = 1 / 2;
            $fraction_of_year = 1 / 6;
        }
        if ($loanRepaymentCycle == "Quarterly") {
            $yearly = 4;
            $monthly = 1 / 3;
            $fraction_of_year = 1 / 4;
        }
        if ($loanRepaymentCycle == "Semi-Annual") {
            $yearly = 2;
            $monthly = 1 / 6;
            $fraction_of_year = 1 / 2;
        }
        if ($loanRepaymentCycle == "Yearly") {
            $yearly = 1;
            $fraction_of_year = 1;
        }

        $result['yearly'] = $yearly;
        $result['monthly'] = $monthly;
        $result['weekly'] = $weekly;
        $result['daily'] = $daily;
        $result['fraction_of_year'] = $fraction_of_year;

        return $result;
    }

    public static function Interest_loan_Period_Synchronisation($loanDetails)
    {
        $LoanDuration = $loanDetails['loan_duration'];
        $LoanDurationPeriod = $loanDetails['loan_duration_pd'];
        $interest_period = $loanDetails['loan_interest_pd'];

        $interest_multiplier = 0;
        $interest_period_factor = 0;

        if ($interest_period == "Day") {
            $interest_period_factor = 365;
        }
        if ($interest_period == "Week") {
            $interest_period_factor = 52;
        }
        if ($interest_period == "Month") {
            $interest_period_factor = 12;
        }
        if ($interest_period == "Year") {
            $interest_period_factor = 1;
        }

        if ($LoanDurationPeriod == "Days") {
            $loan_fraction_of_year = $LoanDuration / 365;
        }
        if ($LoanDurationPeriod == "Weeks") {
            $loan_fraction_of_year = $LoanDuration / 52;
        }
        if ($LoanDurationPeriod == "Months") {
            $loan_fraction_of_year = $LoanDuration / 12;
        }
        if ($LoanDurationPeriod == "Years") {
            $loan_fraction_of_year = $LoanDuration;
        }

        $interest_multiplier = $loan_fraction_of_year * $interest_period_factor;

        return $interest_multiplier;
    }

    public static function periodicInterestPercentage($loanDetails)
    {
        $multiplier = self::Interest_loan_Period_Synchronisation($loanDetails);
        $num_of_installments = self::numberOfInstallments($loanDetails);
        $rate = $loanDetails['loan_interest'] / 100;
        $AnnualInterestRate = $multiplier * $rate;
        $periodic_interest_rate = $AnnualInterestRate / $num_of_installments;

        return $periodic_interest_rate;
    }

    public static function getPrincipalInstallmentAmount($loanDetails, $Principle = 0, $repayment_no = 0)
    {
        $principle_installment = 0;
        switch ($loanDetails['interest_mtd']) {

            case "Reducing Balance - Equal Installments":
                $principle_installment = self::reducingBalanceEqualInstallmentsAmount($loanDetails, $Principle)['principle'];
                break;

            case "Reducing Balance - Equal Principal":
                $principle_installment = self::reducingBalanceEqualPrincipalAmount($loanDetails, $Principle)['principle'];
                break;

            case "Interest-Only":
                $principle_installment = self::InterestOnlyAmount($loanDetails, $repayment_no)['principle']; // interest from "interest only" is the same as interest from falt rate
                break;
            case "Kazi Interest-Only":
                $interest_installment = self::reducingBalancekaziinterest($loanDetails, $Principle)['principle'];
                break;
            case "Compound Interest":
                $principle_installment = self::compoundInterestInstallmentAmount($loanDetails)['principle'];
                break;

            default:
                $principle_installment = $loanDetails["principal_amt"] / self::numberOfInstallments($loanDetails);
                break;
        }

        return $principle_installment;
    }

    public static function getInterestInstallmentAmount($loanDetails, $Principle = 0, $repayment_no = 0)
    {
        $interest_installment = 0;
        switch ($loanDetails['interest_mtd']) {

            case "Reducing Balance - Equal Installments":
                $interest_installment = self::reducingBalanceEqualInstallmentsAmount($loanDetails, $Principle)['interest'];
                break;

            case "Reducing Balance - Equal Principal":
                $interest_installment = self::reducingBalanceEqualPrincipalAmount($loanDetails, $Principle)['interest'];
                break;

            case "Interest-Only":
                $interest_installment = self::InterestOnlyAmount($loanDetails, $repayment_no)['interest']; // interest from "interest only" is the same as interest from falt rate
                break;

            case "Compound Interest":
                $interest_installment = self::compoundInterestInstallmentAmount($loanDetails)['interest'];
                break;
            case "Kazi Interest-Only":
                $interest_installment = self::reducingBalancekaziinterest($loanDetails, $Principle)['interest'];
                break;

            default:
                $interest_installment = self::flatRateInstallmentAmount($loanDetails);
                break;
        }

        return $interest_installment;
    }

    public static function flatRateInstallmentAmount($loanDetails)
    {
        //$interest_rate = $loanDetails['loan_interest']/100;
        $interest_rate = self::periodicInterestPercentage($loanDetails);
        $interest = $interest_rate * $loanDetails["principal_amt"];
        return $interest;
    }

    public static function reducingBalanceEqualInstallmentsAmount($loanDetails, $RemainingPrinciple = 0)
    {

        if ($RemainingPrinciple > 0) {
            $principle = $RemainingPrinciple;
        } else {
            $principle = $loanDetails["principal_amt"];
        }

        $result = array();
        $num_of_installments = self::numberOfInstallments($loanDetails);
        //$loanInterest = (int)$loanDetails['loan_interest'] / 100;
        $loanInterest = self::periodicInterestPercentage($loanDetails);
        // $length_of_period = self::RepayemntPeriod($RepaymentCycle)['fraction_of_year'];

        // $loanInterest_per_pd = $loanInterest * $length_of_period;
        $EMI = ($loanInterest * $loanDetails["principal_amt"]) / (1 - pow((1 + $loanInterest), ($num_of_installments * -1)));

        $result['EMI'] = $EMI;
        $result['interest'] =  $principle * $loanInterest;
        $result['principle'] = $EMI - $result['interest'];

        return $result;
    }
    // Reducing Balance Equal Principal Amount
    public static function reducingBalanceEqualPrincipalAmount($loanDetails, $RemainingPrinciple = 0)
    {
        $result = array();
        if ($RemainingPrinciple > 0) {
            $principle = $RemainingPrinciple;
        } else {
            $principle = $loanDetails["principal_amt"];
        }

        $num_of_installments = self::numberOfInstallments($loanDetails);
        $monthlyPrinciple = $loanDetails["principal_amt"] / $num_of_installments;
        //$loanInterest = (int)$loanDetails['loan_interest'] / 100;
        $loanInterest = self::periodicInterestPercentage($loanDetails);

        $result['principle'] = $monthlyPrinciple;
        $result['interest'] =  $principle * $loanInterest;
        $result['due'] = $monthlyPrinciple + $result['interest'];

        return $result;
    }
    public static function reducingBalancekaziinterest($loanDetails, $RemainingPrinciple = 0)
    {
        $result = array();
        if ($RemainingPrinciple > 0) {
            $principle = $RemainingPrinciple;
        } else {
            $principle = $loanDetails["principal_amt"];
        }

        $num_of_installments = self::numberOfInstallments($loanDetails);
        $monthlyPrinciple = $loanDetails["principal_amt"] / $num_of_installments;
        //$loanInterest = (int)$loanDetails['loan_interest'] / 100;
        $loanInterest = self::periodicInterestPercentage($loanDetails);

        $result['principle'] = $monthlyPrinciple;
        $result['interest'] =  $principle * $loanInterest;
        $result['due'] = $monthlyPrinciple + $result['interest'];

        return $result;
    }
    // Intrest Only
    public static function InterestOnlyAmount($loanDetails, $Repayment_no = 0)
    {
        $result = array();
        $num_of_installments = self::numberOfInstallments($loanDetails);
        $monthlyPrinciple = 0;
        if ($num_of_installments == $Repayment_no) {
            $monthlyPrinciple = $loanDetails["principal_amt"];
        }

        //$loanInterest = (int)$loanDetails['loan_interest'] / 100;
        $loanInterest = self::periodicInterestPercentage($loanDetails);

        $result['principle'] = $monthlyPrinciple;
        $result['interest'] =  $loanDetails["principal_amt"] * $loanInterest;
        $result['due'] = $monthlyPrinciple + $result['interest'];

        return $result;
    }

    public static function compoundInterestInstallmentAmount($loanDetails)
    {

        $principle = $loanDetails["principal_amt"];
        $RepaymentCycle = $loanDetails["repayment_cycle"];

        $result = array();
        $num_of_installments = self::numberOfInstallments($loanDetails);
        // $length_of_period = self::RepayemntPeriod($RepaymentCycle)['yearly'];
        $no_payments_per_year = self::RepayemntPeriod($RepaymentCycle)['yearly'];
        $loanInterest = self::periodicInterestPercentage($loanDetails) * $no_payments_per_year;


        // $loanInterest_per_pd = $loanInterest * $length_of_period;
        $Amount = $principle * pow((1 + ($loanInterest / $no_payments_per_year)), (self::LoanDurationInYears($loanDetails) * $no_payments_per_year));

        $result['principle'] = $principle / $num_of_installments;
        $result['interest'] =  ($Amount - $principle) / $num_of_installments;
        $result['due'] = $result['interest'] + $result['principle'];

        return $result;
    }
    public static function repaymentDates($loanDetails)
    {
        $num = self::numberOfInstallments($loanDetails);
        $repaymentDates = array();
        for ($i = 1; $i <= $num; $i++) {
            $release_date = str_replace("/", "-", $loanDetails['release_date']);
            $repayment_cycle = $loanDetails['repayment_cycle'];

            //refer to RepaymentCycle to see what it returns
            $Cycle = self::RepaymentCycle($repayment_cycle);

            // $Cycle is a string with two parts, a number and a name of a month
            $Cycle_components = explode(" ", $Cycle);
            $num_cycle = $Cycle_components[0];
            $period_cycle = $Cycle_components[1];

            //what is to be added to the repayment cycle period to get the time length to increase the previous date by.
            $add_to_period = $i * $num_cycle;
            //$dateInre= ($loanDetails['repayment_cycle']=="Monthly")?" month":" year";
            $date = date('Y-m-d', strtotime('+' . $add_to_period . $period_cycle, strtotime($release_date)));
            $repaymentDates[] = $date;
        }
        return $repaymentDates;
    }
    public static function getMaturityDate($loanDetails)
    {

        $repaymentDates = Loan::repaymentDates($loanDetails);
        $maturityDate = $repaymentDates[array_key_last($repaymentDates)];
        return $maturityDate;
    }
}
