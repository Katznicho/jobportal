<?php

namespace Ssentezo\Loans;

use Ssentezo\Database\DbAccess;
use Ssentezo\Loans\Loan;

class SchedulePenalty
{
    private $db;
    function __construct()
    {
        $this->db = new DbAccess();
        //$this->sms = new SMS();
    }
    //$db = new DbAcess();



    public function new_date($old_date, $gp, $x)
    {
        $col_day = stringtodatesql($old_date, $gp);
    }

    public function stringtodatesql($collection_date, $graceperiod)
    {
        $var = $collection_date;
        $date = str_replace('/', '-', $var);
        $date = date('Y-m-d', strtotime($date));
        return date('Y-m-d', strtotime($date . ' + ' . $graceperiod . ' days'));
    }
    public function paidamountoncollectiondate($cdate, $loanid, $pdate)
    {
        // $db = new DbAcess();
        $datepayments = "SELECT amount,payment_date FROM `loan_installment_paid` where loan_id=$loanid and payment_date between '$cdate' and '$pdate' AND del_flag=0";
        //echo $datepayments;
        //$duepay="SELECT amount FROM loan_installment_paid where loan_id='".$loanid."'AND collection_date='".$cdate."'AND del_flag=0";

        $duepatamt = $this->db->selectQuery($datepayments);
        return $duepatamt;
    }
    public function paidamountonlastcollectiondate($cdate, $loanid)
    {

        $datepayments = "SELECT amount,payment_date FROM `loan_installment_paid` where loan_id=$loanid and payment_date>='" . $cdate . "' AND del_flag=0";
        $duepatamt = $this->db->selectQuery($datepayments);
        return $duepatamt;
    }

    public function paid_amount_percolectiondate($cdate, $loanid)
    {
        // $db = new DbAcess();

        $duepay = "SELECT sum(amount) total FROM loan_installment_paid where loan_id='" . $loanid . "'AND collection_date='" . $cdate . "'AND del_flag=0";

        $duepatamt = $this->db->selectQuery($duepay);

        $duepayVal = null;
        foreach ($duepatamt as $duepatamt1) {
            if (!is_null($duepatamt1[0])) {
                $duepayVal = $duepatamt1[0];
            } else {
                $duepayVal = 0;
            }
        }
        return  $duepayVal;
    }
    public function paid_amount_perdate($collection_date, $graceperiod, $loanid)
    {

        $condate = $this->stringtodatesql($collection_date, $graceperiod);
        //echo $condate;
        $duepay = "SELECT sum(amount) total FROM loan_installment_paid where loan_id='" . $loanid . "'AND payment_date<='" . $condate . "'AND del_flag=0";
        $duepatamt = $this->db->selectQuery($duepay);

        //print_r($duepatamt);
        $duepayVal = null;
        foreach ($duepatamt as $duepatamt1) {
            if (!is_null($duepatamt1[0])) {
                $duepayVal = $duepatamt1[0];
            } else {
                $duepayVal = 0;
            }
        }
        return  $duepayVal;
    }

    public function totalschedulpenalty($loan_id)
    {

        if (!isset($loan_id)) {
            die("No Loan specified");
        }
        //loan product id
        $loanid = $loan_id;
        $totalPrincipalowed = $totalInterestowed = $totalDue = $incrementalDue = $paid = $paidSum = $paidSum1 = $paidSum2 = $penalty = $totalpenalty = $pending = $duepayValTot = 0;
        $loan_product = $this->db->select("loans", [], ["id" => $loan_id]);
        $lateRep = "Late Repayment";
        $afterM = "After Maturity";
        foreach ($loan_product as $lproduct) {
            //retrieving loan product ID from loan table
            $lproductid = $lproduct['loan_product_id'];

            //using the product id to retrieve penalty details froom loan_penalty table of penalty type Late repayment
            $loanPenalty = $this->db->select("loan_penalty", [], ["loan_product_id" => $lproduct['loan_product_id']], ["penalty_type" => $lateRep]);
            $counting = 0;
            foreach ($loanPenalty as $loanPe) {
                if ($counting < 1) {
                    //grace period
                    $gracep = $loanPe['grace_period'];
                    $chargetype = $loanPe['charge_type'];
                    $amountcharged = $loanPe['amount'];
                    $calculatedon = $loanPe['calculated_on'];
                    $recurring_days = $loanPe['recurring_days'];

                    $counting++;
                }
            }
        }
        //$loan_id = (int) $_GET['loan_id'];
        $is_canceled  = FALSE;
        $loanDetails = $this->db->select("loans", [], ['id' => $loan_id, "active_flag" => 1, "del_flag" => 0])[0];
        if ($loanDetails['status'] == "Canceled") {
            $is_canceled = TRUE;
        }
        //print_r($loanDetails);                                      
        $no = 0;
        $num_repayments = Loan::numberOfInstallments($loanDetails);
        $totalPrincipalowed = $totalInterestowed = $totalDue = $incrementalDue = $paid = $paidSum = $paidSum1 = $paidSum2 = $penalty = $totalpenalty = $pending = $duepayValTot = 0;

        $Repayment_dates = Loan::repaymentDates($loanDetails);

        $totalInstallments = 0;
        $totalpenaltycharged = 0;
        $incrementalPrincile = 0;
        $Remaining_principle = $loanDetails['principal_amt'];
        $temp = 0;
        $counter = 1;
        for ($m = 0; $m < $num_repayments; $m++) {

            $installments = Loan::getInstallmentAmount($loanDetails, $Remaining_principle, $counter);
            $counter++;
            $no++;
            $date = $Repayment_dates[$m];
            if ($loanDetails['interest_mtd'] == "Reducing Balance - Equal Installments" || $loanDetails['interest_mtd'] == "Reducing Balance - Equal Principal") {
                $Remaining_principle -= $installments[0];
            }


            $pbowd = $loanDetails['principal_amt'];
            $interest_charged = $installments[1];
            $incrementalPrincile += $installments[0];
            $totalDue += $installments[2];
            $incrementalDue += $installments[0];
            $incrementalDue += $installments[1];
            $repd_gp = $this->stringtodatesql($Repayment_dates[$m], $gracep);
            //echo $repd_gp;
            $cdate = date("Y-m-d");

            $datediff = ("SELECT DATEDIFF('" . $cdate . "', '" . $repd_gp . "')");
            $datediff_res = $this->db->selectQuery($datediff);
            // print_r($datediff_res);
            foreach ($datediff_res as $datedi) {
                //date difference between today and repayment date+grace period
                $datedifre = $datedi[0];
            }


            $newpenalty = 0;
            $incrementalDue = intval($incrementalDue);
            for ($x = 0; $x < $recurring_days; $x++) {
                $duepayVal = intval($this->paid_amount_perdate($date, $gracep + $x, $loan_id));

                $caldate = $this->stringtodatesql($Repayment_dates[$m], $gracep + $x);
                ///echo $caldate;
                if ($caldate < date("Y-m-d")) {

                    if ($chargetype == "percentage") {
                        //penalty on total overdue
                        if ($calculatedon == "Total") {
                            if ($duepayVal < $incrementalDue) {
                              //  $newpenalty = 0;
                            //} else {
                                $extraDue = $incrementalDue;
                                $penaltyval = $extraDue * ($amountcharged / 100);
                                $newpenalty += $penaltyval;
                                // echo number_format($penaltyval,2);
                            }
                        }

                      //penalty on overdue principle
                      if ($calculatedon == "Principal") {
                        //testing if money paid is less than owed
                        if ($duepayVal < $incrementalDue) {
                        
                            //checking if the schedule payment is the first one
                           if($m==0)
                           {
                            $balanceprinciple=$incrementalPrincile-$duepayVal;
                            
                           }
                           //applied to any other schedule which is not the first one
                           else
                           {
                            //checking if amount paid is less or equal to  principle paid is less than incrimimental principle due of a previous schedule
                            if($duepayVal<=$installments[0]*($m))
                            {
                                $balanceprinciple=$installments[0];
                                
                            }
                            else
                            {
                                $balanceprinciple=$incrementalPrincile-$duepayVal;
                                
                            }
                           
                            
                           }
                           $penaltyval = $balanceprinciple * ($amountcharged / 100);
                           $newpenalty += $penaltyval;
                       
                            
                            
                        }
                    }
                        //penalty on overdue principle+interest
                        if ($calculatedon == "Principal_Interest") {
                            if ($duepayVal < $incrementalDue) {
                              //  $newpenalty = 0;
                           // } else {
                                if (intval($installments[0]) == 0) {
                                    $extraDue = $pbowd;
                                } else {
                                    $extraDue = $installments[0] + $installments[1];
                                }

                                $penaltyval = $extraDue * ($amountcharged / 100);
                                $newpenalty = $penaltyval * $recurring_days;
                            }
                        }

                        //penalty on overdue principle+total overdue
                        if ($calculatedon == "Principal_Interest_Fees") {
                            if ($duepayVal < $incrementalDue) {
                             //   $newpenalty = 0;
                           // } else {
                                if (intval($installments[0]) == 0) {
                                    $extraDue = $pbowd;
                                } else {
                                    $extraDue = $installments[0] + $incrementalDue;
                                }

                                $penaltyval = $extraDue * ($amountcharged / 100);
                                $newpenalty = $penaltyval * $recurring_days;
                            }
                        }
                    }
                    if ($chargetype == "fixed") {
                        if ($duepayVal < $incrementalDue) {
                          //  $newpenalty = 0;
                       // } else {
                            $penaltyval = $amountcharged;
                            $newpenalty = $penaltyval * $recurring_days;
                        }
                    }
                }
                //                else{
                //                    $newpenalty=0;
                //    
                //                    }
                //$penalty+=$newpenalty;
                //$penalty+=$newpenalty;
            }
            $penalty += $newpenalty;
            //echo number_format($penalty,2);





        }
        return $penalty;
    }
}
