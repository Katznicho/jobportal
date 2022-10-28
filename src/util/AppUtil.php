<?php

namespace App\Util;

use App\Loans\Loan;
use App\Database\DbAccess;
use App\Loans\AftermaturityPenalty;
use App\Loans\SchedulePenalty;

class AppUtil
{

    public static $SITE_NAME;

    function __construct()
    {
        $SITE_NAME = $_SESSION['company'];
    }
    private static function stringtodatesql($collection_date, $graceperiod)
    {
        //$p=2;
        $var = $collection_date;
        $date = str_replace('/', '-', $var);
        $date = date('Y-m-d', strtotime($date));
        return date('Y-m-d', strtotime($date . ' + ' . $graceperiod . ' days'));
    }
    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public static function create_account($borrower_id)
    {
        $account_length = 9;
        $gaps = $account_length - strlen($borrower_id);
        $acct = "1";
        for ($i = 0; $i < $gaps; $i++) {
            $acct .= "0";
        }
        $acct .= $borrower_id;
        return $acct;
    }

    public static function user_can($page_role)
    {
        if (!isset($_SESSION['actions'])) {
            return false;
        }
        $actions1 = $_SESSION['actions'];
        $actions = json_decode($actions1);
        // print_r($actions);
        return in_array($page_role, $actions);
    }
    public static function generatePayRefNumber()
    {
        $id = static::companyId();
        $stamp = time();
        $reference = "$id" . "$stamp";
        return $reference;
    }
    public static function hashPwd($str)
    {
        //return password_hash($str, PASSWORD_DEFAULT);
        return md5($str);
    }

    public static function is_assoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);
        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }


    public static function getAppliedChargesSql($loan_id)
    {
        $sql = "SELECT c.id,c.loan_id,c.loan_charge_id,c.amount,c.status,l.name,l.charge_mtd,l.charge_amount,l.charge_rate from loan_applied_charges c, loan_charges l where c.loan_charge_id=l.id and c.loan_id='" . $loan_id . "'";
        return $sql;
    }

    public static function lastRepaymentQuery($loan_id)
    {
        $one = 1;
        $zero = 0;
        $sql = "SELECT * FROM loan_installment_paid where id= (SELECT MAX(id) from loan_installment_paid WHERE loan_id='" . $loan_id . "' and active_flag = '$one' and del_flag = '$zero')";
        return $sql;
    }

    public static function loanummaryTable($loans, $db, $borrower)
    {
?>
        <table class="table table-bordered table-condensed  table-hover" id="table_allLoans">
            <thead>
                <tr style="background-color: #FFF8F2">

                    <th>Loan #</th>
                    <th><b>Released</b></th>
                    <th><b>Maturity</b></th>
                    <th><b>Repayment</b></th>
                    <th><b>Principal</b></th>
                    <th><b>Interest %</b></th>
                    <th>Penalty</th>
                    <th><b>Interest</b></th>
                    <th><b>Interest Accrued</b></th>
                    <th><b>Due</b></th>
                    <th><b>Paid</b></th>
                    <th><b>Balance</b></th>
                    <th><b>Status</b></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                //get Loans for the Borrower.
                /*  $where = [
                  "borrower_id" => $borrowerID,
                  "status" => "!closed"
                  ]; */
                //  $loans = $db->select("loans", [], $where);
                foreach ($loans as $loan) {
                    $status = self::Loan_status($db, $loan);
                    $loanFees = $db->select("loan_applied_charges", [], ["loan_id" => $loan['id']]);

                    $borr_id = "";
                    if ($loan['is_group'] == 1) {
                        $borr_id = $borrower['Id'];
                    } else {
                        $borr_id = $borrower['id'];
                    }

                    $whereh = ["loan_id" => $loan['id'], "borrower_id" => $borr_id, 'active_flag' => 1, "del_flag" => 0];
                    $paidInstallments = $db->select("loan_installment_paid", [], $whereh);

                ?>
                    <tr>
                        <td>
                            <?= $loan['loan_no'] ?>
                        </td>
                        <td>
                            <?= $loan['release_date'] ?>
                        </td>
                        <td>
                            <?php
                            $repay_dates = Loan::repaymentDates($loan);
                            // $mdate = $repay_dates[count($repay_dates) - 1];
                            // print_r($loan);
                            if (!is_null($loan['overriden_maturity_date'])) {
                                echo  $loan['overriden_maturity_date'];
                            } else {
                                echo $repay_dates[count($repay_dates) - 1];
                            }

                            //getting this from Loan Schedule.
                            // $q1 = "select max(id) from loan_schedule where type='repayment' and loan_id='" . $loan['id'] . "'";
                            // $sql = "select due_date from loan_schedule where type='repayment' and id=($q1)";
                            // $resulth = $db->selectQuery($sql);
                            ?>
                            <a href="./override_maturity_date.php?loan_id=<?= $loan['id'] ?>">Override</a>
                        </td>
                        <td>
                            <?= $loan['repayment_cycle'] ?>
                        </td>
                        <td><?= number_format($loan['principal_amt'], 2) ?>
                        </td>
                        <td>
                            <?php
                            $installments = Loan::totalAmountsToPay($loan, $paidInstallments);
                            $num = Loan::numberOfInstallments($loan);
                            ?>
                            <?php echo $loan['loan_interest'] . "% Per " . $loan['loan_interest_pd'] ?>
                        </td>
                        <td>
                            <?php

                            //after maturity penalty
                            $afterMP = new AftermaturityPenalty();
                            $afterMP = $afterMP->totalaftermpenalty($loan['id']);
                            // echo $afterMP;
                            //schedule penalty
                            $sumPenalty = new SchedulePenalty();
                            $sumPenalty = $sumPenalty->totalschedulpenalty($loan['id']);
                            // echo $sumPenalty;
                            echo number_format($afterMP + $sumPenalty, 2);

                            ?>
                        </td>
                        <td>
                            <?php
                            echo number_format($installments[1], 2);
                            ?>

                        </td>



                        <td>
                            <?= number_format($loan['total_accrued'], 2) ?>
                        </td>
                        <td>
                            <?php
                            $totalDue = $installments[0];


                            if (!is_null($loan['overriden_due'])) {
                                echo "<strike>" . number_format($totalDue, 2) . "</strike> <br>";
                                echo number_format($loan['overriden_due'], 2);
                            } else {
                                echo number_format($totalDue, 2);
                            }


                            //get Paid Monney


                            $totalPaid = $installments[2];

                            ?>
                            </b>
                            <a href="override_total_due.php?loan_id=<?= $loan['id'] ?>">Override</a>

                        </td>
                        <td>
                            <b><?php

                                echo number_format($totalPaid, 2);
                                ?>
                                <!--  Piad  6,233.00</b> -->
                        </td>
                        <td>
                            <?php
                            if (!is_null($loan['overriden_due'])) {
                                echo number_format(($loan['overriden_due'] + $afterMP + $sumPenalty - $totalPaid), 2);
                            } else {
                                echo number_format(($totalDue + $afterMP + $sumPenalty - $totalPaid), 2);
                            }

                            ?>
                            <br>

                            <!-- <a href="./make_loan_fully_paid.php?loan_id=<?= $loan['id'] ?>">Make it zero</a> -->
                        </td>
                        <td>
                            <?php

                            if (!is_null($loan['overriden_due']) && ((int)($loan['overriden_due'] + $afterMP + $sumPenalty - $totalPaid) <= 0) || (int)($totalDue + $afterMP + $sumPenalty - $totalPaid) <= 0 || $status['fully_paid'] == 1) {
                            ?>
                                <span class="label label-success">Fully Paid</span>
                                <?php
                            } else  if ($loan["status"] == "Open") {
                                if ($status['missed_repayment'] == 1) {
                                ?>
                                    <span class="label label-info">Missed Repayment</span>
                                <?php
                                } else if ($status['open'] == 1) {
                                ?>
                                    <span class="label label-info">Open</span>
                                <?php
                                } else if ($status['past_maturity'] == 1) {
                                ?>
                                    <span class="label label-danger">Past Maturity</span>
                                <?php
                                }
                            } else {
                                ?>
                                <span class="label label-danger"><?= $loan["status"] ?></span>
                            <?php }
                            ?>

                        </td>
                        <td>
                            <?php
                            $link = "";
                            if ($loan['is_group'] == 1) {
                                $link = "./../loans/view_loan_details.php?loan_id=" . $loan['id'];
                            } else {
                                $link = "./../loans/view_loan_details.php?loan_id=" . $loan['id'];
                            }
                            ?>
                            <a class="label label-primary" href="<?= $link ?>"> View/Modify</a>
                        </td>

                    </tr>
                <?php
                }
                ?>


            </tbody>

        </table>
    <?php
    }
    public static function Group_borrower_header($borrower, $db)
    { ?>
        <div class="row">
            <div class="col-sm-4">

                <div class="user-block">
                    <img class="img-circle" src="" alt="user image">
                    <span class="username">
                        <?= $borrower['name'] ?>
                    </span>
                    <span class="description" style="font-size:13px; color:#000000">
                        <a href="../borrowers/groups/add_borrowers_group.php?group_id=<?= $borrower['Id'] ?>">Edit</a><br>

                    </span>
                </div><!-- /.user-block -->

                <div class="btn-group-horizontal">
                    <a type="button" class="btn bg-olive margin" href="view_loans_borrower.php?borrower=<?= $borrower['Id'] ?>&group=yes">View All Loans</a>
                    <a type="button" class="btn bg-blue margin" href="../savings/view_savings_borrower.php?saver=<?= $borrower['Id'] ?>&is_group=yes">View Savings</a>
                </div>

            </div><!-- /.col -->


            <div class="col-sm-4">
                <ul class="list-unstyled">
                    <a data-toggle="collapse" data-parent="#accordion" href="#viewFiles">
                        View Borrower Files
                    </a>
                    <div id="viewFiles" class="panel-collapse collapse">
                        <div class="box-body">
                            <ul class="no-margin" style="font-size:12px; padding-left:10px">
                                <?php
                                $borrowerFiles = $db->select("borrower_files", [], ["borrower_id" => $borrower['Id'], "active_flag" => 1, "del_flag" => 0, "is_group" => 1]);
                                foreach ($borrowerFiles as $ff) {
                                ?>
                                    <li><a href="./<?= $ff['filepath'] ?>" target="_blank">‪<?= $ff['name'] ?></a></li>
                                <?php
                                }
                                ?>

                            </ul>
                        </div>
                    </div>
                </ul>
            </div>
        </div><!-- /.row -->
    <?php   }

    public static function borrower_header($borrower, $db)
    {
    ?>
        <div class="row">
            <div class="col-sm-4">

                <div class="user-block">
                    <img class="img-circle" src="..<?= $borrower['field2'] ?>" alt="user image">
                    <span class="username">
                        <?= $borrower['title'] . ' ' . $borrower['fname'] . ' ' . $borrower['lname'] ?>
                    </span>
                    <span class="description" style="font-size:13px; color:#000000">
                        <!--                                            <a href="../borrowers/groups/view_group_details.php">MAZAS</a><br>4556666<br>-->
                        <a href="../borrowers/add_borrower_edit.php?edit_borrower=<?= $borrower['id'] ?>&amp;borrower_id=<?= $borrower['id'] ?>">Edit</a><br>
                        <?= $borrower['business_name'] ?><br><?= $borrower['gender'] . ', ' ?>
                        <?php
                        echo date_diff(date_create($borrower['dob']), date_create('today'))->y;
                        ?>
                    </span>
                </div><!-- /.user-block -->

                <div class="btn-group-horizontal">
                    <a type="button" class="btn bg-olive margin" href="view_loans_borrower.php?borrower=<?= $borrower['id'] ?>">View All Loans</a>
                    <a type="button" class="btn bg-blue margin" href="../savings/view_savings_borrower.php?saver=<?= $borrower['id'] ?>">View Savings</a>
                </div>

            </div><!-- /.col -->


            <div class="col-sm-4">
                <ul class="list-unstyled">
                    <li><b>Address:</b> <?= $borrower['address'] ?></li>
                    <li><b>District:</b> <?= $borrower['district'] ?></li>
                    <li><b>Sub county:</b> <?= $borrower['subcounty'] ?></li>
                    <li><b>Village:</b> <?= $borrower['village'] ?></li>

                    <a data-toggle="collapse" data-parent="#accordion" href="#viewFiles">
                        View Borrower Files
                    </a>
                    <div id="viewFiles" class="panel-collapse collapse">
                        <div class="box-body">
                            <ul class="no-margin" style="font-size:12px; padding-left:10px">
                                <?php
                                $borrowerFiles = $db->select("borrower_files", [], ["borrower_id" => $borrower['id'], "active_flag" => 1, "del_flag" => 0]);
                                foreach ($borrowerFiles as $ff) {
                                ?>
                                    <li><a href="../<?= $ff['filepath'] ?>" target="_blank">‪<?= $ff['name'] ?></a></li>
                                <?php
                                }
                                ?>

                            </ul>
                        </div>
                    </div>
                </ul>
            </div>

            <div class="col-sm-4">
                <ul class="list-unstyled">
                    <li><b>Landline:</b> <?= $borrower['landline'] ?></li>
                    <li><b>Email:</b> <a onClick="javascript:window.open('mailto:<?= $borrower['email'] ?>', 'mail');
                            event.preventDefault()" href="mailto::<?= $borrower['email'] ?>">:<?= $borrower['email'] ?></a>
                        <div class="btn-group-horizontal"><a type="button" class="btn-xs bg-red" href="mailto:<?= $borrower['email'] ?>">Send Email</a></div>
                    </li>
                    <li><b>Mobile:</b> :<?= $borrower['mobile_no'] ?>
                        <div class="btn-group-horizontal">
                            <a type="button" class="btn-xs bg-red" href="#">Send SMS</a>
                        </div>
                    </li>

                </ul>
            </div>
        </div><!-- /.row -->
    <?php
    }

    public static function userId()
    {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0';
    }

    public static function userFullName()
    {
        return isset($_SESSION['fullname']) ? $_SESSION['fullname'] : "No name";
    }

    public static function isValidEmail($string)
    {
        if (filter_var($string, FILTER_VALIDATE_EMAIL)) {
            //echo "This ($email_a) email address is considered valid.";
            return TRUE;
        }
        return FALSE;
    }

    public static function json_encodeWithCheck($arrayMulti)
    {
        for ($i = 0; $i < count($arrayMulti); $i++) {
            $array = $arrayMulti[$i];
            foreach ($array as $key => $value) {
                //print_r($value);
                $decoded = json_decode($value);
                if (json_last_error() == JSON_ERROR_NONE) {
                    $array[$key] = $decoded;
                    $arrayMulti[$i] = $array;
                }
            }
        }
        return json_encode($arrayMulti);
    }

    public static function saveFile($tempLocation, $fileName)
    {
        $relativePath = "/uploads/allfiles/" . time() . "_" . basename($fileName);
        $target_file = dirname(__DIR__) . $relativePath;
        
        $ret = move_uploaded_file($tempLocation, $target_file);

        if ($ret)
            return $relativePath;
        return false;
    }

    public static function nextDueDate($db, $loanDetails)
    {

        $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id'], 'active_flag' => 1, "del_flag" => 0]);
        $loan_totals = Loan::totalAmountsToPay($loanDetails, $repayments);
        $total_pending_amount = $loan_totals[3];
        $total_amount_paid = $loan_totals[2];
        $collection_date = NULL;

        if ($total_pending_amount > 0) {
            $Repayment_dates = Loan::repaymentDates($loanDetails);

            //to get the installments that are to be made on each schedule date
            $Remaining_principle = $loanDetails['principal_amt'];
            for ($i = 1; $i <= count($Repayment_dates); $i++) {
                $installment = Loan::getInstallmentAmount($loanDetails, $Remaining_principle, $i)[2];
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

    public static function prevDueDate($db, $loanDetails)
    {

        $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id'], 'active_flag' => 1, "del_flag" => 0]);
        $loan_totals = Loan::totalAmountsToPay($loanDetails, $repayments);
        $total_pending_amount = $loan_totals[3];
        $total_amount_paid = $loan_totals[2];
        $collection_date = '';
        //If no repayment as made we have to return the realease date
        if ($total_amount_paid == 0) {
            $release_date = $loanDetails['release_date'];
            // print_r($release_date);
            $date = str_replace("/", "-", $release_date);
            $collection_date = date('d/m/Y', strtotime($date));
            // die();
        } else if ($total_pending_amount > 0) {
            $Repayment_dates = Loan::repaymentDates($loanDetails);


            //to get the installments that are to be made on each schedule date
            $Remaining_principle = $loanDetails['principal_amt'];
            for ($i = 1; $i <= count($Repayment_dates); $i++) {
                $installment = Loan::getInstallmentAmount($loanDetails, $Remaining_principle, $i)[2];
                if ($installment > $total_amount_paid) {
                    $index = ($i - 2);
                    if ($index < 0) {
                        $index = ($i - 1);
                    }
                    $date = str_replace("/", "-", $Repayment_dates[$index]);
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
    public static function MissedRepayments($db, $loanDetails)
    {

        $result = array();
        $result['day'] = "";
        $result['flag'] = FALSE;

        $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id'], 'active_flag' => 1, "del_flag" => 0]);
        $loan_totals = Loan::totalAmountsToPay($loanDetails, $repayments);
        $total_pending_amount = $loan_totals[3];
        $total_amount_paid = $loan_totals[2];

        if ($total_pending_amount > 0) {
            $Repayment_dates = Loan::repaymentDates($loanDetails);

            //to get the installments that are to be made on each schedule date
            $Remaining_principle = $loanDetails['principal_amt'];
            for ($i = 1; $i <= count($Repayment_dates); $i++) {
                $installment = Loan::getInstallmentAmount($loanDetails, $Remaining_principle, $i)[2];

                $date = self::Comparable_date_format($Repayment_dates[($i - 1)]);

                if (date('Y-m-d') > $date) {
                    if ($installment > $total_amount_paid) {
                        $result['day'] = $date;
                        $result['flag'] = TRUE;
                        break;
                    } else {
                        $total_amount_paid -= $installment;
                    }
                }
                $Remaining_principle -= $installment[0];
            }
        }
        return $result;
    }
    public static function getDateDiff($date1, $date2)
    {
        $datetime1 = strtotime($date1);
        $datetime2 = strtotime($date2);

        $secs = $datetime2 - $datetime1; // == <seconds between the two times>
        $days = $secs / 86400;

        $days =  abs($days);
        // echo number_format($days);
        // die();
        return number_format($days);
    }

    public static function formatDays($days)
    {
        // This will be worked on later
        // Let it not bother you 


        $d = $days;
        $y = NULL;
        $m = NULL;
        $w = NULL;

        // d = days
        // y = years
        // w = weeks

        // Conversion of days in to years, Months, weeks and days

        $y = (int)($d / 365);

        $w = (int)(($d % 365) / 7);
        $d = (int)($d - (($y * 365) + ($w)));

        // Output
        echo $y . " Year, " . $w . " Weeks, and " . $d . " Days\n";
    }

    public static function Loan_status($db, $loanDetails)
    {
        $status = array();
        $status['missed_repayment'] = 0;
        $status['missed_day'] = "";
        $status['past_maturity'] = 0;
        $status['maturity_date'] = "";
        $status['open'] = 0;
        $status['next_date'] = self::nextDueDate($db, $loanDetails);
        $status['fully_paid'] = 0;

        if (self::MissedRepayments($db, $loanDetails)['flag']) {
            $status['missed_repayment'] = 1;
            $status['missed_day'] = self::MissedRepayments($db, $loanDetails)['day'];
        }

        $repayment_dates = Loan::repaymentDates($loanDetails);
        $size = count($repayment_dates) - 1;
        $maturity_date = $repayment_dates[$size];
        $maturity_date = str_replace("/", "-", $maturity_date);

        $maturity_date = date('Y-m-d', strtotime($maturity_date));

        $allRepayments = $db->select("loan_installment_paid", [], ['loan_id' => $loanDetails['id'], "active_flag" => 1, "del_flag" => 0]);
        $loan_totals = Loan::totalAmountsToPay($loanDetails, $allRepayments);

        $num_of_installments = Loan::numberOfInstallments($loanDetails);

        $totalPaid = $loan_totals[2];


        if ($loan_totals[0] > $totalPaid) {
            if (date('Y-m-d') > $maturity_date) {

                $status['past_maturity'] = 1;
                $status['maturity_date'] = $maturity_date;
            } else {
                $status['open'] = 1;
            }
        } else {
            $status['fully_paid'] = 1;
        }
        return $status;
    }

    public static function getInterestAccrued()
    {
    }
    public static function Comparable_date_format($date)
    {
        $date = str_replace("/", "-", $date);
        $date = date('Y-m-d', strtotime($date));
        return $date;
    }

    public static function sms_check($db, $company)
    {
        $result = $db->select("company", [], ["name" => $company,  "active_flag" => 1, "del_flag" => 0])[0]['sms_status'];
        return $result;
    }
    public static function two_factor_auth_status($db, $company)
    {
        $result = $db->select("company", [], ["name" => $company,  "active_flag" => 1, "del_flag" => 0])[0]['two_factor_auth_status'];
        return $result;
    }

    public static function units_check($db, $company)
    {
        $flag = FALSE;
        $units = $db->select("company", [], ["name" => $company,  "active_flag" => 1, "del_flag" => 0])[0]['units'];
        if ($units > 0) {
            $flag = TRUE;
        }
        return $flag;
    }

    public static function one_less_unit($db, $company)
    {

        $result = 0;
        $units = $db->select("company", [], ["name" => $company,  "active_flag" => 1, "del_flag" => 0])[0]['units'];


        $new_units = ($units - 1);

        if ($units > 0) {
            $where = ['active_flag' => 1, 'del_flag' => 0, 'name' => $company];
            $updateId = $db->update("company", ['units' => $new_units], $where);
            if (is_numeric($updateId)) {
                $result = 1;
            }
        }
        return $result;
    }
    public static function n_less_units($db, $company, $n)
    {
        $result = 0;
        $units = $db->select("company", [], ["id" => $company,  "active_flag" => 1, "del_flag" => 0])[0]['units'];
        $new_units = ($units - $n);

        if ($units > 0) {
            $where = ['active_flag' => 1, 'del_flag' => 0, 'id' => $company];
            $updateId = $db->update("company", ['units' => $new_units], $where);
            if (is_numeric($updateId)) {
                $result = 1;
            }
        }
        return $result;
    }


    public static function sms_switch($db, $company_id, $Status)
    {
        $result = 0;
        $where = ['active_flag' => 1, 'del_flag' => 0, 'id' => $company_id];
        $updateId = $db->update("company", ['sms_status' => $Status], $where);
        if (is_numeric($updateId)) {
            $result = 1;
        }
        return $result;
    }

    public static function two_factor_auth_switch($db, $company_id, $Status)
    {
        $result = 0;
        $where = ['active_flag' => 1, 'del_flag' => 0, 'id' => $company_id];
        $updateId = $db->update("company", ['two_factor_auth_status' => $Status], $where);
        if (is_numeric($updateId)) {
            $result = 1;
        }
        return $result;
    }
    public static function companyId()
    {
        $company_db =  isset($_SESSION['company_db']) ? $_SESSION['company_db'] : '';
        $manager_db = new DbAccess('ssenhogv_manager');
        $result = $manager_db->select('company', [], ['Data_base' => $company_db]);
        if (is_array($result) && !empty($result)) {
            $company = $result[0];
            return $company['id'];
        }
        return 0;
    }
    public static function getCompanyInfo()
    {
        $company_db =  isset($_SESSION['company_db']) ? $_SESSION['company_db'] : '';
        $manager_db = new DbAccess('ssenhogv_manager');
        $result = $manager_db->select('company', [], ['Data_base' => $company_db])[0];

        $company = $manager_db->select('company', [], ['id' => $result['id']])[0];
        return $company;
    }
    public static function getCcEmails($companyId)
    {
        return array(
            [
                "email" => "richarddaaki4@gmail.com",
                "name" => "Daaki Benjamin Richard"
            ],
            [
                "name" => "Carlmer Amumpaire",
                "email" => "ajoan@thinkxsoftware.com"
            ],
            [
                "name" => "Joan Amumpaire",
                "email" => "jamumpaire19@gmail.com"
            ],
            [
                "name" => "Dennis Natugasha A",
                "email" => "anishinani@thinkxsoftware.com"
            ]

        );
    }
    public static function getCompanyName()
    {
        return static::getCompanyInfo()['name'];
    }
    public static function getCompanyMainDomain()
    {
        return static::getCompanyInfo()['main_domain'];
    }
    public static function getCompanyClientDomain()
    {
        return static::getCompanyInfo()['client_domain'];
    }
    //end of class
    public static function countrySelectOptions()
    {
    ?>
        <option value="AF">Afghanistan</option>
        <option value="AX">Aland Islands</option>
        <option value="AL">Albania</option>
        <option value="DZ">Algeria</option>
        <option value="AS">American Samoa</option>
        <option value="AD">Andorra</option>
        <option value="AO">Angola</option>
        <option value="AI">Anguilla</option>
        <option value="AQ">Antarctica</option>
        <option value="AG">Antigua and Barbuda</option>
        <option value="AR">Argentina</option>
        <option value="AM">Armenia</option>
        <option value="AW">Aruba</option>
        <option value="AU">Australia</option>
        <option value="AT">Austria</option>
        <option value="AZ">Azerbaijan</option>
        <option value="BS">Bahamas</option>
        <option value="BH">Bahrain</option>
        <option value="BD">Bangladesh</option>
        <option value="BB">Barbados</option>
        <option value="BY">Belarus</option>
        <option value="BE">Belgium</option>
        <option value="BZ">Belize</option>
        <option value="BJ">Benin</option>
        <option value="BM">Bermuda</option>
        <option value="BT">Bhutan</option>
        <option value="BO">Bolivia</option>
        <option value="BQ">Bonaire</option>
        <option value="BA">Bosnia and Herzegovina</option>
        <option value="BW">Botswana</option>
        <option value="BV">Bouvet Island</option>
        <option value="BR">Brazil</option>
        <option value="IO">British Indian Ocean Territory</option>
        <option value="BN">Brunei Darussalam</option>
        <option value="BG">Bulgaria</option>
        <option value="BF">Burkina Faso</option>
        <option value="BI">Burundi</option>
        <option value="KH">Cambodia</option>
        <option value="CM">Cameroon</option>
        <option value="CA">Canada</option>
        <option value="CV">Cape Verde</option>
        <option value="KY">Cayman Islands</option>
        <option value="CF">Central African Republic</option>
        <option value="TD">Chad</option>
        <option value="CL">Chile</option>
        <option value="CN">China</option>
        <option value="CX">Christmas Island</option>
        <option value="CC">Cocos (Keeling) Islands</option>
        <option value="CO">Colombia</option>
        <option value="KM">Comoros</option>
        <option value="CG">Congo</option>
        <option value="CD">Congo</option>
        <option value="CK">Cook Islands</option>
        <option value="CR">Costa Rica</option>
        <option value="CI">Cote dIvoire</option>
        <option value="HR">Croatia</option>
        <option value="CU">Cuba</option>
        <option value="CY">Cyprus</option>
        <option value="CZ">Czech Republic</option>
        <option value="DK">Denmark</option>
        <option value="DJ">Djibouti</option>
        <option value="DM">Dominica</option>
        <option value="DO">Dominican Republic</option>
        <option value="EC">Ecuador</option>
        <option value="EG">Egypt</option>
        <option value="SV">El Salvador</option>
        <option value="GQ">Equatorial Guinea</option>
        <option value="ER">Eritrea</option>
        <option value="EE">Estonia</option>
        <option value="ET">Ethiopia</option>
        <option value="FK">Falkland Islands (Malvinas)</option>
        <option value="FO">Faroe Islands</option>
        <option value="FJ">Fiji</option>

        <option value="ID">Indonesia</option>
        <option value="IR">Iran, Islamic Republic of</option>
        <option value="IQ">Iraq</option>
        <option value="IE">Ireland</option>
        <option value="IM">Isle of Man</option>
        <option value="IL">Israel</option>
        <option value="IT">Italy</option>
        <option value="JM">Jamaica</option>
        <option value="JP">Japan</option>
        <option value="JE">Jersey</option>
        <option value="JO">Jordan</option>
        <option value="KZ">Kazakhstan</option>
        <option value="KE">Kenya</option>
        <option value="KI">Kiribati</option>
        <option value="KP">Korea, Democratic People's Republic of</option>
        <option value="KR">Korea, Republic of</option>
        <option value="KW">Kuwait</option>

        <option value="FM">Micronesia, Federated States of</option>
        <option value="MD">Moldova, Republic of</option>
        <option value="MC">Monaco</option>
        <option value="MN">Mongolia</option>
        <option value="ME">Montenegro</option>
        <option value="MS">Montserrat</option>
        <option value="MA">Morocco</option>
        <option value="MZ">Mozambique</option>
        <option value="MM">Myanmar</option>
        <option value="NA">Namibia</option>
        <option value="NR">Nauru</option>
        <option value="NP">Nepal</option>
        <option value="NL">Netherlands</option>
        <option value="NC">New Caledonia</option>
        <option value="NZ">New Zealand</option>
        <option value="NI">Nicaragua</option>
        <option value="NE">Niger</option>
        <option value="NG">Nigeria</option>
        <option value="NU">Niue</option>
        <option value="NF">Norfolk Island</option>
        <option value="MP">Northern Mariana Islands</option>
        <option value="NO">Norway</option>
        <option value="OM">Oman</option>
        <option value="PK">Pakistan</option>
        <option value="PW">Palau</option>
        <option value="PS">Palestine</option>
        <option value="PA">Panama</option>
        <option value="PG">Papua New Guinea</option>
        <option value="PY">Paraguay</option>
        <option value="PE">Peru</option>
        <option value="PH">Philippines</option>
        <option value="PN">Pitcairn</option>
        <option value="PL">Poland</option>
        <option value="PT">Portugal</option>
        <option value="PR">Puerto Rico</option>
        <option value="QA">Qatar</option>
        <option value="RE">Reunion</option>
        <option value="RO">Romania</option>
        <option value="RU">Russian Federation</option>
        <option value="RW">Rwanda</option>
        <option value="BL">Saint Barthelemy</option>
        <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
        <option value="KN">Saint Kitts and Nevis</option>
        <option value="LC">Saint Lucia</option>
        <option value="MF">Saint Martin (French part)</option>
        <option value="PM">Saint Pierre and Miquelon</option>
        <option value="VC">Saint Vincent and the Grenadines</option>
        <option value="WS">Samoa</option>
        <option value="SM">San Marino</option>
        <option value="ST">Sao Tome and Principe</option>
        <option value="SA">Saudi Arabia</option>
        <option value="SN">Senegal</option>
        <option value="RS">Serbia</option>
        <option value="SC">Seychelles</option>

        <option value="TC">Turks and Caicos Islands</option>
        <option value="TV">Tuvalu</option>
        <option value="UG" selected>Uganda</option>

        <option value="ZW">Zimbabwe</option>
        <?php
    }
    public static function errorPage($errorMessage, $descrption, $homeLink)
    {
    }
    public static function selectAccountOptions($db)
    {
        $accounts = $db->select('accounts', [], ['active_flag' => 1, 'del_flag' => 0, 'is_leaf' => 1]);
        foreach ($accounts as $account) {
        ?>
            <option value="<?= $account['id'] ?>"> <?= $account['account_no'] ?> <?= $account['name'] ?></option>
<?php
        }
    }
}


if (!empty($_SESSION)) {
    AppUtil::$SITE_NAME = $_SESSION['company'];
}



// Constants to be used in accounting
define("INCOME", 1);
define('REVENUE', 1);
define("EXPENSES", 2);
define("ASSETS", 3);
define("LIABILITIES", 4);
define("EQUITY", 5);
