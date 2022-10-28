<?php

namespace Ssentezo\Loans;

use AppUtil;
use Ssentezo\Util\Date;

class LoanSchedule
{
    public static function tabular($db, $loanDetails)
    {


        $loan_id = $loanDetails['id'];
        $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loan_id, "active_flag" => 1, "del_flag" => 0]);

        $installments = Loan::totalAmountsToPay($loanDetails, $repayments);
        $totalFees = 0;
        $fees = $db->select("loan_applied_charges", [], ["loan_id" => $loanDetails['id']]);
        if (!empty($fees)) {
            foreach ($fees as $fee) {
                $totalFees += $fee['amount'];
            }
        }

        $loan_product = $db->select("loans", [], ["id" => $loan_id]);
        $lateRep = "Late Repayment";
        $afterM = "After Maturity";
        foreach ($loan_product as $lproduct) {
            //retrieving loan product ID from loan table
            $lproductid = $lproduct['loan_product_id'];
            //echo ($lproduct['loan_product_id']);
            //using the product id to retrieve penalty details froom loan_penalty table of penalty type Late repayment
            $loanPenalty = $db->select("loan_penalty", [], ["loan_product_id" => $lproduct['loan_product_id']], ["penalty_type" => $lateRep]);
            $counting = 0;
            foreach ($loanPenalty as $loanPe) {
                if ($counting < 1) {

                    $gracep = $loanPe['grace_period'];
                    $chargetype = $loanPe['charge_type'];
                    $amountcharged = $loanPe['amount'];
                    $calculatedon = $loanPe['calculated_on'];
                    $recurring_days = $loanPe['recurring_days'];
                    $counting++;
                }
            }
        }

        $afterMP = new AftermaturityPenalty();

        $afterMP = $afterMP->totalaftermpenalty($loanDetails['id']);

        $sumPenalty = new SchedulePenalty();

        $sumPenalty = $sumPenalty->totalschedulpenalty($loanDetails['id']);
        $total_due = $installments[0] + $afterMP + $sumPenalty;
        if ($loanDetails['overriden_due']) {
            $total_due = $loanDetails['overriden_due'] + $afterMP + $sumPenalty;
        }

?>
        <table class="table table-bordered table-condensed table-hover">
            <tr style="background-color: #F2F8FF">
                <th style="width: 10px">
                    <b>#</b>
                </th>
                <th>
                    <b>Date</b>
                </th>
                <th>
                    <b>Description</b>
                </th>
                <th style="text-align:right;">
                    <b>Principal</b>
                </th>
                <th style="text-align:right;">
                    <b>Interest</b>
                </th>
                <th style="text-align:right;">
                    <b>Due</b>
                </th>
                <th style="text-align:right;">
                    <b>Penalty</b>
                </th>
                <th style="text-align:right;">
                    Total Due
                </th>
                <th style="text-align:right;">
                    Paid
                </th>
                <th style="text-align:right;">
                    Pending Due
                </th>
                <th style="text-align:right;">
                    Principal Balance Owed
                </th>
            </tr>

            <tr>
                <td colspan="11" style="text-align:right; font-weight:bold">
                    <?php
                    // echo json_encode($loanDetails);
                    echo  number_format($loanDetails['principal_amt'], 2);
                    ?>
                </td>
            </tr>

            <?php
            $num_repayments = Loan::numberOfInstallments($loanDetails);
            $totalPrincipalowed = $totalInterestowed = $totalDue = $incrementalDue = $paid = $paidSum = $paidSum1 = $paidSum2 = $penalty = $totalpenalty = $pending = $duepayValTot = 0;
            $Repayment_dates = Loan::repaymentDates($loanDetails);
            $totalInstallments = 0;
            $incrementalPrincile = 0;
            $Remaining_principle = $loanDetails['principal_amt'];
            $counter = 1;
            $no = 0;
            $undefined = false;
            for ($m = 0; $m < $num_repayments; $m++) {

                $installments = Loan::getInstallmentAmount($loanDetails, $Remaining_principle, $counter);
                $counter++;
                $no++;

                if ($loanDetails['interest_mtd'] == "Reducing Balance - Equal Installments" || $loanDetails['interest_mtd'] == "Reducing Balance - Equal Principal") {
                    $Remaining_principle -= $installments[0];
                }

                if ($incrementalDue + $installments[2] > $total_due && $loanDetails['overriden_due']) {
                    $date =  isset($Repayment_dates[$m]) ? $Repayment_dates[$m] : null;
                    $undefined = true;
            ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= isset($Repayment_dates[$m]) ? $Repayment_dates[$m] : "Unknown" ?></td>
                        <td>Repayment</td>
                        <td style="text-align:center" colspan="2">Undefined</td>
                        <td style="text-align:right"><?= number_format($total_due - $incrementalDue, 2)  ?></td>
                        <td>Undefined</td>
                        <td style="text-align:right"><?= number_format($total_due, 2) ?></td>
                        <td style="text-align:right;">
                            <?php
                            $date = AppUtil::Comparable_date_format($date);
                            foreach ($repayments as $repay) {
                                $collection_date = AppUtil::Comparable_date_format($repay["collection_date"]);

                                if ($collection_date == $date) {
                                    $paid = $repay['amount'];
                                    $paidSum += (int)$paid;
                                    echo number_format($paid) . " ";
                                }
                            }
                            ?>

                        </td>

                        <td style="text-align:right">
                            <b>
                                <?= number_format(($total_due + $penalty), 2) //- $paidSum, 2) 
                                ?>

                            </b>
                        </td>
                        <td style="text-align:right;">
                            <?php
                            $Proposed_remaining_principle = 0;
                            if ($loanDetails['interest_mtd'] == "Flat Rate") {
                                $Proposed_remaining_principle = number_format($loanDetails['principal_amt'] - ($installments[0] * ($m + 1)), 2, '.', ',');
                            } else {
                                $Proposed_remaining_principle = number_format(($loanDetails['principal_amt'] - ($incrementalPrincile)), 2, '.', ',');
                            }
                            if (substr($Proposed_remaining_principle, 0, 1) == "-") {
                                $ex = explode("-", $Proposed_remaining_principle);
                                $Proposed_remaining_principle = $ex[1];
                            }

                            echo $Proposed_remaining_principle;
                            ?>
                        </td>
                    </tr>
                <?php
                    $incrementalDue = $total_due;
                    break;
                } //This is the case when we have a lower override
                ?>
                <tr>
                    <td>
                        <?= $no ?>
                    </td>
                    <td>
                        <?php
                        $date = $Repayment_dates[$m];
                        echo $date;
                        ?>
                    </td>
                    <td>
                        Repayment
                    </td>
                    <td style="text-align:right">
                        <?= number_format($installments[0], 2) ?>
                    </td>
                    <td style="text-align:right">
                        <?php
                        $totalInstallments += $installments[1];
                        ?>
                        <?= number_format($installments[1], 2) ?>

                    </td>
                    <td style="text-align:right;">
                        <?php
                        echo number_format(($installments[1] + $installments[0]), 2);
                        ?>
                    </td>
                    <td>
                        <?php
                        $pbowd = $loanDetails['principal_amt'];
                        $interest_charged = $installments[1];
                        $incrementalPrincile += $installments[0];
                        $totalDue += $installments[2];
                        $incrementalDue += $installments[0];
                        $incrementalDue += $installments[1];
                        $repd_gp = stringtodatesql($Repayment_dates[$m], $gracep);
                        $cdate = date("Y-m-d");
                        $datediff = ("SELECT DATEDIFF('" . $cdate . "', '" . $repd_gp . "')");
                        $datediff_res = $db->selectQuery($datediff);
                        foreach ($datediff_res as $datedi) {
                            //date difference between today and repayment date+grace period
                            $datedifre = $datedi[0];
                        }
                        $newpenalty = 0;
                        $incrementalDue = intval($incrementalDue);
                        for ($x = 0; $x < $recurring_days; $x++) {
                            $duepayVal = intval(paid_amount_perdate($date, $gracep + $x, $loan_id));
                            $caldate = stringtodatesql($Repayment_dates[$m], $gracep + $x);
                            if ($caldate < date("Y-m-d")) {

                                if ($chargetype == "percentage") {
                                    if ($calculatedon == "Total") {
                                        if ($duepayVal < $incrementalDue) {
                                            $extraDue = $incrementalDue;
                                            $penaltyval = $extraDue * ($amountcharged / 100);
                                            $newpenalty += $penaltyval;
                                        }
                                    }
                                    if ($calculatedon == "Principal") {

                                        if ($duepayVal < $incrementalDue) {
                                            if (intval($installments[0]) == 0) {
                                                $extraDue = $pbowd;
                                            } else {
                                                $extraDue = $installments[0];
                                            }


                                            $penaltyval = $extraDue * ($amountcharged / 100);
                                            $newpenalty += $penaltyval;
                                        }
                                    }
                                    //penalty on overdue principle+interest
                                    if ($calculatedon == "Principal_Interest") {
                                        if ($duepayVal < $incrementalDue) {
                                            // $newpenalty = 0;
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
                                        $penaltyval = $amountcharged;
                                        $newpenalty = $penaltyval * $recurring_days;
                                    }
                                }
                            }
                        }
                        $penalty += $newpenalty;
                        echo number_format($newpenalty, 2);

                        ?>
                    </td>

                    <td style="text-align:right;">
                        <?php
                        $vartot = $incrementalDue + $penalty;
                        echo number_format($vartot, 2);
                        ?>
                    </td>
                    <td style="text-align:right;">
                        <?php

                        $date = AppUtil::Comparable_date_format($date);

                        foreach ($repayments as $repay) {
                            $collection_date = AppUtil::Comparable_date_format($repay["collection_date"]);

                            if ($collection_date == $date) {
                                $paid = $repay['amount'];
                                $paidSum += (int)$paid;
                                echo number_format($paid) . " ";
                            }
                        }
                        ?>

                    </td>
                    <td style="font-weight:bold;text-align:right;">
                        <?php
                        $pending = ($incrementalDue + $penalty); // - $paidSum; Removed this as it causes negative values in this column
                        echo number_format($pending, 2, '.', ',');
                        ?>
                    </td>
                    <td style="text-align:right;">
                        <?php
                        $Proposed_remaining_principle = 0;
                        if ($loanDetails['interest_mtd'] == "Flat Rate") {
                            $Proposed_remaining_principle = number_format($loanDetails['principal_amt'] - ($installments[0] * ($m + 1)), 2, '.', ',');
                        } else {
                            $Proposed_remaining_principle = number_format(($loanDetails['principal_amt'] - ($incrementalPrincile)), 2, '.', ',');
                        }
                        if (substr($Proposed_remaining_principle, 0, 1) == "-") {
                            $ex = explode("-", $Proposed_remaining_principle);
                            $Proposed_remaining_principle = $ex[1];
                        }

                        echo $Proposed_remaining_principle;
                        ?>
                    </td>
                </tr>

            <?php
            }
            if ($loanDetails['overriden_due'] && $loanDetails['overriden_due'] > $incrementalDue) {
                $count = count($Repayment_dates);
                $next_due_date = Date::predictNextDate($Repayment_dates[$count - 2], $Repayment_dates[$count - 1]);
                $undefined = true;
            ?>
                <tr>
                    <td><?= $no ?></td>

                    <td><?= $next_due_date ? $next_due_date : "Unknown" ?></td>
                    <td>Repayment</td>
                    <td style="text-align:center" colspan="2">Undefined</td>
                    <td style="text-align:right"><?= number_format($total_due - $incrementalDue, 2)  ?></td>
                    <td>Undefined</td>
                    <td style="text-align:right"><?= number_format($total_due, 2) ?></td>
                    <td style="text-align:right;">
                        <?php

                        $date = AppUtil::Comparable_date_format($date);

                        foreach ($repayments as $repay) {
                            $collection_date = AppUtil::Comparable_date_format($repay["collection_date"]);

                            if ($collection_date == $date) {
                                $paid = $repay['amount'];
                                $paidSum += (int)$paid;
                                echo number_format($paid) . " ";
                            }
                        }
                        ?>

                    </td>

                    <td style="text-align:right">
                        <b>

                            <?= number_format(($total_due + $penalty), 2) //- $paidSum, 2) 
                            ?>

                        </b>
                    </td>
                    <td style="text-align:right;">
                        <?php
                        $Proposed_remaining_principle = 0;
                        if ($loanDetails['interest_mtd'] == "Flat Rate") {
                            $Proposed_remaining_principle = number_format($loanDetails['principal_amt'] - ($installments[0] * ($m + 1)), 2, '.', ',');
                        } else {
                            $Proposed_remaining_principle = number_format(($loanDetails['principal_amt'] - ($incrementalPrincile)), 2, '.', ',');
                        }
                        if (substr($Proposed_remaining_principle, 0, 1) == "-") {
                            $ex = explode("-", $Proposed_remaining_principle);
                            $Proposed_remaining_principle = $ex[1];
                        }

                        echo $Proposed_remaining_principle;
                        ?>
                    </td>
                </tr>
            <?php
                $incrementalDue = $total_due;
            } //This is the case when we have a higher override

            if ($afterMP) {
                $pending += $afterMP;

            ?>
                <tr>
                    <td colspan="9" class="text text-center"> After maturity Penalty</td>
                    <td class="text text-right"><b><?= number_format($pending, 2) ?> </b></td>
                    <td></td>
                </tr>
            <?php
            }

            ?>
            <tr>
                <td>

                </td>
                <td>

                </td>
                <td style="font-weight:bold">
                    Total Due
                </td>
                <td style="text-align:right">
                    <?= number_format($loanDetails['principal_amt'], 2, '.', ',') ?>
                </td>
                <td style="text-align:right">
                    <?= number_format($totalInstallments, 2, '.', ',') ?>
                </td>
                <td style="text-align:right; font-weight:bold">

                    <?php
                    if ($undefined) {
                    ?>
                        <strike>
                            <?= number_format($loanDetails['principal_amt'] + $totalInstallments, 2) ?>

                        </strike>
                        <br>
                    <?php
                    }
                    ?>
                    <?= number_format(($incrementalDue), 2, '.', ',') ?>
                </td>
                <td style="text-align:right">
                    <?= number_format(($penalty), 2, '.', ',') ?>
                </td>

                <td style="text-align:right;">

                </td>
                <td style=" font-weight:bold;text-align:right;">
                    <?= number_format(($paidSum), 2, '.', ',') ?>
                </td>
                <td>
                </td>
                <td style="text-align:right;">

                </td>
            </tr>


        </table>
        <?php
        if ($undefined) {
        ?>
            <p style="color:red;">
                NB: <br>
                1.The fields with Undefined value mean that the exact amount for that field couldn't be accurately calculated due changes caused by the override</p>

            <br>

        <?php
        }
        ?>
<?php
    }
}
