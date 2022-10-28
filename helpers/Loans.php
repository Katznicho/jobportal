<?php



class Loans
{

    function __construct()
    {
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

        // if (!is_null($loanDetails["overriden_due"])) {
        //     $Remaining_principle = $loanDetails["overriden_due"];
        // }
        for ($i = 1; $i <= $num; $i++) {
            $installments = self::getInstallmentAmount($loanDetails, $Remaining_principle, $i);

            if ($loanDetails['interest_mtd'] == "Reducing Balance - Equal Installments" || $loanDetails['interest_mtd'] == "Reducing Balance - Equal Principal") {
                $Remaining_principle -= $installments[0];
            }

            $totalDue += $installments[2];
            $totalInterest += $installments[1];
        }
        // if (!is_null($loanDetails["overriden_due"])) {
        //     $totalDue = $loanDetails["overriden_due"];
        // }
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
    public function addClientLoanProduct(
        $db,
        $disbursed_by,
        $min_principlal,
        $max_principle,
        $interest_method,
        $interest_rate,
        $repayment_cycle,
        $description
    ) {
        $db->insert('client_loan_product', [
            'disbursed_by' => $disbursed_by, 'min_principlal' => $min_principlal,
            'max_principle' => $max_principle, 'interest_method' => $interest_method,
            'interest_rate' => $interest_rate, 'repayment_cycle' => $repayment_cycle,
            'description' => $description
        ]);
    }
    public static function applyForLoan($db, $borrower_id, $loan_product_id, $principal_amount, $loan_files = array())
    {
        // First Check to see if Principle is within the loans principal range
        $loan_product = $db->select('client_loan_product', [], ['id' => $loan_product_id])[0];
        if (empty($loan_product)) {
            $error = true;
            $message = "Invalid Loan Product";
        } else {
            // test the range
            $min_principal = $loan_product['min_principal'];
            $max_principal = $loan_product['max_principal'];
            if ($principal_amount < $min_principal || $principal_amount > $max_principal) {
                $error = true;
                $message = "The principal is out of range";
            } else {
                // save the request to the database.


                $data = [
                    'borrower_id' => $borrower_id,
                    'loan_product_id' => $loan_product_id,
                    'amount' => $principal_amount,
                    'status' => 'pending',
                    'active_flag' => "1",
                    'del_flag' => "0"
                ];
                $ret = $db->insert('loan_applications', $data);
                // $ret = (int)$ret;
                if (is_numeric($ret)) {
                    $insertId = $ret;
                    // Add the loan Application files
                    if (!empty($loan_files)) {
                        // print_r($allOtherFiles);
                        $namesArray = $loan_files['name'];
                        $tempNameArray = $loan_files['tmp_name'];
                        $ii = 0;
                        foreach ($tempNameArray as $value) {
                            if (strlen(trim($namesArray[$ii])) > 0) {
                                $saveFileH = AppUtil::saveFile($tempNameArray[$ii], $namesArray[$ii]);
                                $tableHer = 'loan_application_files';
                                // 	id	loan_application_id	name	
                                //path	extension	creation_date	
                                //creation_user	active_flag	del_flag
                                $datahere = [
                                    "loan_application_id" => $insertId,
                                    "name" => $namesArray[$ii],
                                    "path" => $saveFileH,
                                    "creation_user" => AppUtil::userId()
                                ];

                                $insertIdHere = $db->insert($tableHer, $datahere);
                                $ii++;
                            }
                        }
                    }
                    $error = false;
                    $message = "Success";
                } else {
                    $error = true;
                    $message = "Db error";
                }
            }
        }
        return array(
            "error" => $error,
            "message" => $message,
            'id' => $ret
        );
    }
    public static function editLoanApplication($db, $loan_app_id, $borrower_id, $loan_product_id, $principal_amount, $loan_files = array())
    {
        // First Check to see if Principle is within the loans principal range
        $loan_product = $db->select('client_loan_product', [], ['id' => $loan_product_id])[0];
        if (!$loan_product) {
            $error = true;
            $message = "Invalid Loan Product";
        } else {
            // test the range
            $min_principal = $loan_product['min_principal'];
            $max_principal = $loan_product['max_principal'];
            if ($principal_amount < $min_principal || $principal_amount > $max_principal) {
                $error = true;
                $message = "The principal is out of range";
            } else {
                // save the request to the database.


                $data = [
                    'borrower_id' => $borrower_id,
                    'loan_product_id' => $loan_product_id,
                    'amount' => $principal_amount,
                    'status' => 'pending',
                    'active_flag' => "1",
                    'del_flag' => "0"
                ];
                $ret = $db->update('loan_applications', $data, ['id' => $loan_app_id]);
                // $ret = (int)$ret;
                if (is_numeric($ret)) {
                    $insertId = $ret;
                    // Add the loan Application files
                    if (!empty($loan_files)) {
                        // print_r($allOtherFiles);

                        $namesArray = $loan_files['name'];
                        $tempNameArray = $loan_files['tmp_name'];
                        $ii = 0;
                        foreach ($tempNameArray as $value) {
                            if (strlen(trim($namesArray[$ii])) > 0) {
                                $saveFileH = AppUtil::saveFile($tempNameArray[$ii], $namesArray[$ii]);
                                $tableHer = 'loan_application_files';
                                // 	id	loan_application_id	name	
                                //path	extension	creation_date	
                                //creation_user	active_flag	del_flag
                                $datahere = [
                                    "loan_application_id" => $loan_app_id,
                                    "name" => $namesArray[$ii],
                                    "path" => $saveFileH,
                                    "creation_user" => AppUtil::userId()
                                ];

                                $insertIdHere = $db->insert($tableHer, $datahere);
                                $ii++;
                            }
                        }
                    }
                    $error = false;
                    $message = "Success";
                } else {
                    $error = true;
                    $message = "Db error";
                }
            }
        }
        return array(
            "error" => $error,
            "message" => $message,
            'id' => $ret
        );
    }


    public static function applyForLoanNew($db, $borrower_id, $loan_product_id, $principal_amount, $loan_files = array())
    {
        // First Check to see if Principle is within the loans principal range
        $loan_product = $db->select('client_loan_product', [], ['id' => $loan_product_id])[0];
        if (!$loan_product) {
            $error = true;
            $message = "Invalid Loan Product";
        } else {
            // test the range
            $min_principal = $loan_product['min_principal'];
            $max_principal = $loan_product['max_principal'];
            if ($principal_amount < $min_principal || $principal_amount > $max_principal) {
                $error = true;
                $message = "The principal is out of range";
            } else {
                // save the request to the database.


                $data = [
                    'borrower_id' => $borrower_id,
                    'loan_product_id' => $loan_product_id,
                    'amount' => $principal_amount,
                    'status' => 'pending',
                    'active_flag' => "1",
                    'del_flag' => "0"
                ];
                $ret = $db->insert('loan_applications', $data);

                // $ret = (int)$ret;
                if (is_numeric($ret)) {
                    $insertId = $ret;
                    // Add the loan Application files
                    if (!empty($loan_files)) {
                        // print_r($allOtherFiles);
                        $namesArray = $loan_files['name'];
                        $tempNameArray = $loan_files['tmp_name'];
                        $ii = 0;
                        foreach ($tempNameArray as $value) {
                            if (strlen(trim($namesArray[$ii])) > 0) {
                                $saveFileH = AppUtil::saveFile($tempNameArray[$ii], $namesArray[$ii]);
                                $tableHer = 'loan_application_files';
                                // 	id	loan_application_id	name	
                                //path	extension	creation_date	
                                //creation_user	active_flag	del_flag
                                $datahere = [
                                    "loan_application_id" => $insertId,
                                    "name" => $namesArray[$ii],
                                    "path" => $saveFileH,
                                    "creation_user" => AppUtil::userId()
                                ];

                                $insertIdHere = $db->insert($tableHer, $datahere);
                                $ii++;
                            }
                        }
                    }
                    $error = false;
                    $message = "Success";
                } else {
                    $error = true;
                    $message = "Db error";
                }
            }
        }
        return array(
            "error" => $error,
            "message" => $message,
            'id' => $ret
        );
    }
    public static function loanApplicationsAll($db, $status = false)
    {

        // We fetch all loan applications for the client

        // fetch all the loan requests in the table
        if ($status) {
            $loan_applications = $db->select('loan_applications', [], ['active_flag' => 1, 'status' => $status]);
        } else {
            $loan_applications = $db->select('loan_applications', [], ['active_flag' => 1]);
        }

        return $loan_applications;
    }
    public static function approveLoan($db, $loan_app_id, $staff_id, $approved = false, $approve_type = 0, $approve_comment = "")
    {

        // First make sure the loan comment has no commas
        $approve_comment = str_replace(",", " ", $approve_comment);
        $loan_application = $db->select('loan_applications', [], ['id' => $loan_app_id])[0];
        $approved_by = $loan_application['approved_by'];
        $approval_dates = $loan_application['approval_dates'];
        $approval_types = $loan_application['approval_types'];
        $approval_comments = $loan_application['approval_comments'];

        $required_approvals = static::requiredApprovals($db, $loan_app_id);
        $approved_by = explode(',', $approved_by);
        $approval_dates = explode(',', $approval_dates);
        $approval_types = explode(',', $approval_types);
        $approval_comments = explode(',', $approval_comments);

        $total_approvals = count($approved_by);
        $error = true;
        $message = '';
        // Check to see if the person approving is not among the list of approvers
        if (!(array_search($staff_id, $approved_by) == false)) {
            $error = true;
            $message = "You Can't Approve the same Loan Twice";
        } else {
            $approved_by[] = $staff_id;
            $approval_dates[] = date('Y-m-d');
            $approval_types[] = $approve_type;
            $approval_comments[] = $approve_comment;

            $approved_by = implode(',', $approved_by);
            $approval_dates = implode(',', $approval_dates);
            $approval_comments = implode(',', $approval_comments);
            $approval_types = implode(',', $approval_types);

            //Apoproved is true for the final approval of thr loan
            if ($approved) {
                $result = $db->update(
                    'loan_applications',
                    [
                        'approval_dates' => $approval_dates,
                        'approval_comments' => $approval_comments,
                        'approval_types' => $approval_types,
                        'approved_by' => $approved_by,
                        'status' => 'approved',
                        'approved_on' => date('Y-m-d H:i:s')
                    ],
                    ['id' => $loan_app_id]
                );
                if (is_numeric($result)) {
                    $error = false;
                    $message = "Success";
                } else {
                    $error = true;
                    $message = "Failed with $result";
                }

                // Also use this chance to delete the temporary client loan products.
                // $client_loan_product_id = $loan_application['loan_product_id'];
                // $temp_client_loan_product = $db->select('client_loan_product', [], ['id' => $client_loan_product_id, 'del_flag' => 1])[0];
                // if ($temp_client_loan_product) {
                // $sql = "DELETE from client_loan_product WHERE id = $client_loan_product_id";
                // $db->delete($sql);
                // Done!        
                // }
            } else {
                $result = $db->update(
                    'loan_applications',
                    [
                        'approval_dates' => $approval_dates,
                        'approved_by' => $approved_by,
                        'approval_comments' => $approval_comments,
                        'approval_types' => $approval_types,

                    ],
                    [
                        'id' => $loan_app_id
                    ]
                );

                if (is_numeric($result)) {
                    $error = false;
                    $message = "Success";
                } else {
                    $error = true;
                    $message = "Failed with $result";
                }
            }
        }
        return array(
            'error' => $error,
            'message' => $message
        );
    }
    public static function updateLoanGuarantors($db, $loan_id, $loan_app_id)
    {
        $set = array(
            "loan_id" => $loan_id
        );
        $where = array(
            "loan_application_id" => $loan_app_id
        );
        $ret = $db->update("loan_guarantors", $set, $where);
        return $ret;
    }
    public static function isApplicationApproved($db, $loan_app_id)
    {
        $loan_application = $db->select('loan_applications', [], ['id' => $loan_app_id])[0];
        $status = $loan_application['status'];
        if ($status == 'approved') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Approval type of 1 means approval and 0 means rejection
     */
    public static function loanApprovalSummary($db, $approved_by, $approval_dates, $approval_types = '', $approval_comments = '', $approval_type = null)
    {
        $result = [];
        // split both arguments into arrays
        // if(strlen($approved_by)<1){
        //     return $result;
        // }
        $approved_by = explode(',', $approved_by);
        $approval_dates = explode(',', $approval_dates);
        $approval_comments = explode(',', $approval_comments);
        $approval_types = explode(',', $approval_types);

        for ($i = 0; $i < count($approved_by); $i++) {
            $staff_id = $approved_by[$i];
            // make sure it's an integer.
            $staff_id = (int)$staff_id;
            if ($staff_id <= 0) {
                continue;
            }
            $staff = $db->select('staff', [], ['id' => $staff_id])[0];
            $name = $staff['fname'] . ' ' . $staff['lname'];
            $date = $approval_dates[$i];
            $comment = $approval_comments[$i];
            $type = $approval_types[$i];
            if (strcmp($type, $approval_type) == 0) {
                $result[] = [
                    'name' => $name,
                    'date' => $date,
                    'type' => $type,
                    'comment' => $comment,

                ];
            }
        }
        return $result;
    }
    public static function getMaturityDate($loanDetails)
    {

        $repaymentDates = Loans::repaymentDates($loanDetails);
        $maturityDate = $repaymentDates[array_key_last($repaymentDates)];
        return $maturityDate;
    }
    public static function requiredApprovals($db, $loan_product_id)
    {
        // Get loan product
        $client_loan_product = $db->select('client_loan_product', [], ['id' => $loan_product_id])[0];
        // get the minimun approvals
        $min_approvals = $client_loan_product['min_approvals'];
        return $min_approvals;
    }
    public static function didStaffApprove($db, $loan_app_id, $staff_id)
    {
        $loan_application = $db->select('loan_applications', [], ['id' => $loan_app_id])[0];
        $approved_by = $loan_application['approved_by'];
        $approval_dates = $loan_application['approval_dates'];
        $approved_by = explode(',', $approved_by);
        $approval_dates = explode(',', $approval_dates);
        $total_approvals = count($approved_by);
        $error = true;
        $message = '';
        // Check to see if the person approving is not among the list of approvers
        if (!(array_search($staff_id, $approved_by) == false)) {
            return true;
        } else {
            return false;
        }
    }
    public static function didStaffReject($db, $loan_app_id, $staff_id)
    {
        $loan_application = $db->select('loan_applications', [], ['id' => $loan_app_id])[0];
        $approved_by = $loan_application['approved_by'];
        $approval_types = $loan_application['approval_types'];
        $approval_dates = $loan_application['approval_dates'];
        $approved_by = explode(',', $approved_by);
        $approval_dates = explode(',', $approval_dates);
        $approval_types = explode(",", $approval_types);
        $total_approvals = count($approved_by);
        $error = true;
        $message = '';
        // Check to see if the person approving is not among the list of approvers
        if (!(array_search($staff_id, $approved_by) == false)) {
            $key = array_search($staff_id, $approved_by);
            $type = $approval_types[$key];
            if (strcmp($type, "1") == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public static function loanApplicationStatusSummary($loan_application)
    {
        //Get the loan
        $result = static::howManyApprovals($loan_application);
    }

    public static function howManyApprovals($loan_application)
    {
        $approved_by = $loan_application['approved_by'];
        $approval_dates = $loan_application['approval_dates'];
        $approval_types = $loan_application['approval_types'];
        $approved_by = explode(',', $approved_by);
        $approval_dates = explode(',', $approval_dates);
        $approval_types = explode(',', $approval_types);
        $approves = 0;
        $rejections = 0;
        for ($i = 0; $i < count($approved_by); $i++) {
            $approver = $approved_by[$i];
            if (is_numeric($approver)) {
                if ($approval_types[$i] == 0) {
                    $rejections++;
                } else {
                    $approves++;
                }
            }
        }
        return array(
            $approves,
            $rejections
        );
        // Check to see if the person approving is not among the list of approvers

    }
}
