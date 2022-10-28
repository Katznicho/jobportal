<?php

class Reports
{

    function __construct()
    {
    }
    public static function LoanTotals($db, $person = 0, $from = 0, $to = 0)
    {
        $result = array();
        $totalPrinciple = 0;
        $totalIntrest = 0;
        $totalFees = 0;
        $total = 0;
        $loans = "";
        if ($person > 0) {
            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0, "collector" => $person]);
        } else if ($from > 0 && $to > 0) {
            $to_date = date("Y-m-d", strtotime($to));
            $from_date = date("Y-m-d", strtotime($from));
            $query = "SELECT * FROM  loans WHERE 
            (CASE 
                WHEN STR_TO_DATE(release_date,'%d/%m/%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d/%m/%Y')  
                WHEN STR_TO_DATE(release_date,'%Y/%m/%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y/%m/%d')   
                WHEN STR_TO_DATE(release_date,'%d-%m-%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d-%m-%Y')
                WHEN STR_TO_DATE(release_date,'%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y-%m-%d') 
            END)>='$from_date' AND  
            (CASE 
                WHEN STR_TO_DATE(release_date,'%d/%m/%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d/%m/%Y')
                WHEN STR_TO_DATE(release_date,'%Y/%m/%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y/%m/%d')     
                WHEN STR_TO_DATE(release_date,'%d-%m-%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d-%m-%Y') 
                WHEN STR_TO_DATE(release_date,'%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y-%m-%d') 
            END)<='$to_date' AND active_flag=1 and del_flag=0";

            // $query = "SELECT * FROM loans WHERE STR_TO_DATE(release_date, '%d/%m/%Y') > '$from' AND STR_TO_DATE(release_date, '%d/%m/%Y') < '$to' AND active_flag = 1  AND del_flag= 0";
            $loans = $db->selectQuery($query);
        } else {

            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        }
        foreach ($loans as $loan) {
            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loan['id']]);

            //            if ($from > 0) {
            //                if (!self::date_filter($from, $loan['release_date'], $to)) {
            //                    continue;
            //                }
            //            }

            $fees = $db->select("loan_applied_charges", [], ["loan_id" => $loan['id']]);
            //Loan principle and interest
            $sums = Loans::totalAmountsToPay($loan, $repayments);
            $principle = $sums[0] - $sums[1];
            $totalPrinciple += $principle;
            $totalIntrest += $sums[1];

            //Loan fees
            if (!empty($fees)) {
                foreach ($fees as $fee) {
                    $totalFees += $fee['amount'];
                    $total += $fee['amount'];
                }
            }
            $total += $sums[0];
        }


        $result[] = $totalPrinciple;
        $result[] = $totalIntrest;
        $result[] = $totalFees;
        $result[] = $total;

        return $result;
    }
    public static function allLoanTotals($db, $from = 0, $to = 0, $person = 0)
    {
        $result = array();
        $totalPrinciple = 0;
        $totalIntrest = 0;
        $totalFees = 0;
        $total = 0;
        $loans = "";
        $tot = 0;
        if ($person > 0) {
            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        } else {
            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        }

        foreach ($loans as $loan) {
            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loan['id']]);

            if ($from > 0) {
                if (!self::date_filter($from, $loan['release_date'], $to)) {
                    continue;
                }
            }

            $fees = $db->select("loan_applied_charges", [], ["loan_id" => $loan['id']]);
            //Loan principle and interest
            $sums = Loans::totalAmountsToPay($loan, $repayments);
            $principle = $sums[0] - $sums[1];
            $totalPrinciple += $principle;
            $totalIntrest += $sums[1];

            //Loan fees
            if (!empty($fees)) {
                foreach ($fees as $fee) {
                    $totalFees += $fee['amount'];
                    $total += $fee['amount'];
                }
            }
            $total += $sums[0];
            $tot++;
        }


        $result[] = $totalPrinciple;
        $result[] = $totalIntrest;
        $result[] = $totalFees;
        $result[] = $total;
        $result["total"] = $tot;

        return $result;
    }


    public static function LoanTotalsPaid($db, $person = 0, $from = 0, $to = 0)
    {
        $result = array();
        $totalPrinciplePaid = 0;
        $totalIntrestPaid = 0;
        $totalFeesPaid = 0;
        $totalPaid = 0;
        $loans = "";

        if ($person > 0) {
            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0, "collector" => $person]);
        } else if ($from > 0 && $to > 0) {
            $to_date = date("Y-m-d", strtotime($to));
            $from_date = date("Y-m-d", strtotime($from));
            $query = "SELECT * FROM  loans WHERE 
            (CASE  
                WHEN STR_TO_DATE(release_date,'%d/%m/%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d/%m/%Y')   
                WHEN STR_TO_DATE(release_date,'%Y/%m/%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y/%m/%d') 
                WHEN STR_TO_DATE(release_date,'%d-%m-%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d-%m-%Y') 
                WHEN STR_TO_DATE(release_date,'%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y-%m-%d') 
            END)>='$from_date' AND  
            (CASE   
                WHEN STR_TO_DATE(release_date,'%d/%m/%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d/%m/%Y')
                WHEN STR_TO_DATE(release_date,'%Y/%m/%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y/%m/%d')   
                WHEN STR_TO_DATE(release_date,'%d-%m-%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d-%m-%Y') 
                WHEN STR_TO_DATE(release_date,'%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y-%m-%d') 
            END)<='$to_date' AND active_flag=1 AND del_flag=0";

            // $query = "SELECT * FROM loans WHERE STR_TO_DATE(release_date, '%d/%m/%Y') > '$from' AND STR_TO_DATE(release_date, '%d/%m/%Y') < '$to'";
            // echo $query;
            $loans = $db->selectQuery($query);
        } else {
            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        }

        foreach ($loans as $loan) {
            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loan['id'], "active_flag" => 1, "del_flag" => 0]);
            $fees = $db->select("loan_applied_charges", [], ["loan_id" => $loan['id']]);
            $installment = Loans::getInstallmentAmount($loan); //what is supposed to be paid every month


            $reviewed_dates = array();
            $payments = array();
            if (!empty($repayments)) {

                foreach ($repayments as $repayment) {

                    if ($from > 0) {
                       
                        if (!self::date_filter($from, $repayment['creation_date'], $to)) {
                            continue;
                        }
                    }

                    if (!in_array($repayment['collection_date'], $reviewed_dates)) {
                        $reviewed_dates[] = $repayment['collection_date'];
                        $payments[] = $repayment['amount'];
                    } else {
                        $index = array_search($repayment['collection_date'], $reviewed_dates);
                        $payments[$index] += $repayment['amount'];
                    }
                }

                // For the purposes of catering for loans with different interest methods, these two are needed as parameters
                $Remaining_principle = $loan['principal_amt'];
                $counter = 1;

                $installment_to_be_paid = $installment[2];
                $PrincipalInstallment =  $installment[0]; //principal to be paid every month

                foreach ($payments as $payment) {
                    if ($payment > $installment_to_be_paid) { //if the repayment was bigger than what was supposed to be paid for a month 
                        $remaining_repayment = $payment; // a value to hold the repayment temporarily, this value will be reduced in this loop

                        while ($remaining_repayment > 0) {
                            // this variable helps to determine how much is to be considered a repayment in this iteration
                            if ($remaining_repayment >= $installment_to_be_paid) {
                                $amounts = self::ToPay($installment_to_be_paid, $PrincipalInstallment);
                                $totalPrinciplePaid += $amounts[0];
                                $totalIntrestPaid += $amounts[1];

                                $remaining_repayment -= $installment_to_be_paid; //reduce the value of the repayment remaining

                                //Again because of loans having different interest methods, these parameters have to change for the next iteration to give accurate values
                                $Remaining_principle -= $PrincipalInstallment;
                                $counter++;
                                $installment_to_be_paid = Loans::getInstallmentAmount($loan, $Remaining_principle, $counter)[2];
                                $PrincipalInstallment = Loans::getInstallmentAmount($loan, $Remaining_principle, $counter)[0];
                            } else {
                                $amounts = self::ToPay($remaining_repayment, $PrincipalInstallment);
                                $totalPrinciplePaid += $amounts[0];
                                $totalIntrestPaid += $amounts[1];
                                $remaining_repayment -= $installment[2];
                            }
                        }
                    } else { // if the repayment is equal of less than what is supposed to be paid for a month.
                        $amounts = self::ToPay($payment, $PrincipalInstallment);
                        $totalPrinciplePaid += $amounts[0];
                        $totalIntrestPaid += $amounts[1];
                    }

                    $totalPaid += $payment;

                    $Remaining_principle -= $PrincipalInstallment;
                    $counter++;
                    $installment_to_be_paid = Loans::getInstallmentAmount($loan, $Remaining_principle, $counter)[2];
                    $PrincipalInstallment = Loans::getInstallmentAmount($loan, $Remaining_principle, $counter)[0];
                }
            }

            if (!empty($fees)) {
                foreach ($fees as $fee) {

                    $fee_date = $fee['creation_date'];
                    $fee_date = explode(" ", $fee_date)[0];

                    if ($from > 0) {
                        if (!self::date_filter($from, $fee_date, $to)) {
                            continue;
                        }
                    }

                    $totalFeesPaid += $fee['amount'];
                    $totalPaid += $fee['amount'];
                }
            }
        }

        $result[] = $totalPrinciplePaid;
        $result[] = $totalIntrestPaid;
        $result[] = $totalFeesPaid;
        $result[] = $totalPaid;

        return $result;
    }

    public static function ToPay($paid, $principal)
    {
        $result = array();
        $totalPrinciplePaid = 0;
        $totalIntrestPaid = 0;
        $reminder = $paid - $principal;
        if ($reminder < 0) {
            $totalPrinciplePaid += $paid;
        } else {
            $totalPrinciplePaid += $principal;
            $totalIntrestPaid += $reminder;
        }
        $result[] = $totalPrinciplePaid;
        $result[] = $totalIntrestPaid;

        return $result;
    }

    public static function Due_Paid_Amount($db)
    {
        $month = self::Days_in_Month();
        $result = array(); //our final array..

        foreach ($month as $day) { //we first intialise it to all days in the current month...
            $result[$day] = array();
            $result[$day][0] = 0; //this index will hold the due amounts
            $result[$day][1] = 0; //this index will hold the total payments made on this date;
        }

        $loans = $db->select("yearloans", [], ["active_flag" => 1, "del_flag" => 0]);

        foreach ($loans as $loan) {
            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loan['id'], "active_flag" => 1, "del_flag" => 0]);
            $installment = Loans::getInstallmentAmount($loan);
            $repayment_dates = Loans::repaymentDates($loan);

            //if a collection date is witthin the month, we add the total monthly installment for this loan to our total of all due collections to be made on this day
            foreach ($repayment_dates as $date) {
                $date = AppUtil::Comparable_date_format($date);
                foreach ($month as $value) {
                    $value = AppUtil::Comparable_date_format($value);
                    if ($date == $value) {
                        $result[$value][0] += $installment[2];
                    }
                }
            }

            //if a repayment date is witthin the month, we add the amount paid for this loan to our total of all payments made on this day
            foreach ($repayments as $paid) {
                $collection_date = AppUtil::Comparable_date_format($paid['collection_date']);
                foreach ($month as $value) {
                    $value = AppUtil::Comparable_date_format($value);
                    if ($collection_date == $value) {
                        $result[$value][1] += $paid['amount'];
                    }
                }
            }
        }

        return $result;
    }

    public static function Monthly_Stats($db)
    {
        $result = array();
        $months = self::Months();
        $Person = 0;
        foreach ($months as $key => $value) {
            $result[$key] = array(); // our final array will be indexed using months of the year...........
            $first_date = $value[0];
            $last_date = $value[1];
            $monthly_stats = self::Stats($db, $first_date, $last_date);
            $monthly_loan_stats = self::Loan_Stats($db, $first_date, $last_date);
            $monthly_loan_collections =  self::LoanTotalsPaid($db, $Person = 0, $first_date, $last_date);
            //keep the total proit and expenses for each month next to them........................
            $result[$key][] = $monthly_stats['ProfitTotal'];
            $result[$key][] = $monthly_stats['ExpensesTotal'];
            $result[$key][] = $monthly_loan_stats[0]; // loans released
            $result[$key][] = $monthly_loan_stats[3]; //past Maturity loans
            $result[$key][] = $monthly_loan_collections[3];
        }
        return $result;
    }
    public static function Monthly_Stats1($db)
    {
        $result = array();
        $months = self::Months();
        $Person = 0;
        foreach ($months as $key => $value) {
            $result[$key] = array(); // our final array will be indexed using months of the year...........
            $first_date = $value[0];
            $last_date = $value[1];
            //$monthly_stats = self::Stats($db, $first_date, $last_date);
            $monthly_stats = [];
            $monthly_stats['ProfitTotal'] = 0;
            $monthly_stats['ExpensesTotal'] = 0;
            $monthly_loan_stats = self::Loan_Stats($db, $first_date, $last_date);
            $monthly_loan_collections =  self::LoanTotalsPaid($db, $Person = 0, $first_date, $last_date);
            //keep the total proit and expenses for each month next to them........................
            $result[$key][] = $monthly_stats['ProfitTotal'];
            $result[$key][] = $monthly_stats['ExpensesTotal'];
            $result[$key][] = $monthly_loan_stats[0]; // loans released
            $result[$key][] = $monthly_loan_stats[3]; //past Maturity loans
            $result[$key][] = $monthly_loan_collections[3];
        }
        return $result;
    }

    public static function Loan_Stats($db, $start = 0, $end = 0)
    {

        $result = array();
        $loans_released = 0;
        $open_Loans = 0;
        $Fully_Paid = 0;
        $past_Maturity_Loans = 0;

        $haveDateDiff = FALSE;
        if ($start > 0) {
            $haveDateDiff = TRUE;
        }

        $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        foreach ($loans as $loanDetails) {
            $status = AppUtil::Loan_status($db, $loanDetails);
            if ($haveDateDiff) {
                if (self::date_filter($start, $loanDetails['release_date'], $end)) {
                    $loans_released++;
                }
            } else {
                $loans_released++;
            }

            $loan_id = $loanDetails['id'];

            $repayment_dates = Loans::repaymentDates($loanDetails);
            $size = count($repayment_dates) - 1;
            $maturity_date = $repayment_dates[$size];
            $maturity_date = str_replace("/", "-", $maturity_date);

            $maturity_date = date('Y-m-d', strtotime($maturity_date));

            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id'], "active_flag" => 1, "del_flag" => 0]);
            $Installments = Loans::totalAmountsToPay($loanDetails, $repayments);

            $totalPaid = $Installments[2];

            if ($Installments[0] > $totalPaid) {
                if (date('Y-m-d') > $maturity_date) {

                    if ($haveDateDiff) {
                        if (self::date_filter($start, $maturity_date, $end)) {
                            $past_Maturity_Loans++;
                        }
                    } else {
                        $past_Maturity_Loans++;
                    }
                } else {
                    $open_Loans++;
                }
            } else {
                $Fully_Paid++;
            }
        }

        $result[] = $loans_released;
        $result[] = $open_Loans;
        $result[] = $Fully_Paid;
        $result[] = $past_Maturity_Loans;

        return $result;
    }

    public static function Stats($db, $start = 0, $end = 0)
    {
        $result = array();
        $id = 0;
        $profitsTotal = 0;
        $totalExpenses = 0;
        $haveDateDiff = FALSE;
        if ($start > 0) {
            $haveDateDiff = TRUE;
        }
        $TotalsPaid = self::LoanTotalsPaid($db, $id, $start, $end);
        // $TotalsPaid = static::allLoanTotals($db, $start, $end);
        //Total Loans Intrest...........................................................................
        $result["totalLoansIntrest"] = $TotalsPaid[1];
        $profitsTotal += $TotalsPaid[1];
        //End Total loans Intrest.......................................................................     

        //Other Income type totals..................................................
        $otherIncomeTypes = $db->select("other_income_type");
        foreach ($otherIncomeTypes as $incomeType) {
            $name = $incomeType['name'];
            $sum = 0;
            $where = ['active_flag' => 1, 'del_flag' => 0, 'other_income_type_id' => $incomeType['id']];
            $other_income = $db->select("other_income", [], $where);
            foreach ($other_income as $income) {
                if ($haveDateDiff) {
                    if (!Reports::date_filter($start, $income['transaction_date'], $end)) {
                        continue;
                    }
                }
                $sum += $income['amount'];
            }
            $profitsTotal += $sum;
            $result[$name] = $sum;
        }
        // End of other income type totals.....................................................

        //Expenses totals......................................................................
        $otherExpenseTypes = $db->select("expense_type");
        foreach ($otherExpenseTypes as $expense) {
            $name = $expense['name'];
            $sum = 0;
            $where = ['active_flag' => 1, 'del_flag' => 0, 'expense_type_id' => $expense['id']];
            $expenses = $db->select("expenses", [], $where);
            foreach ($expenses as $exp) {
                if ($haveDateDiff) {
                    if (!Reports::date_filter($start, $exp['expense_date'], $end)) {
                        continue;
                    }
                }
                $sum += $exp['amount'];
            }
            $totalExpenses += $sum;
            $result[$name] = $sum;
        }
        //End of expenses.....................................................................

        $result['ProfitTotal'] = $profitsTotal;
        $result['ExpensesTotal'] = $totalExpenses;

        return $result;
    }

    public static function Months()
    {
        $now = date('d-m-Y');
        $year = date("Y", strtotime($now));
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $dateObj   = DateTime::createFromFormat('m', $i);
            $monthName = $dateObj->format('M'); // the 'M' abbreviates the month name i.e Jan
            $month = date("m", strtotime($monthName));
            $months[$monthName] = array();
            $months[$monthName][0] = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
            $months[$monthName][1] = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        }
        return $months;
    }
    public static function Days_in_Month()
    {
        $Month = array();
        $now = date('d-m-Y');
        $month = date("m", strtotime($now));
        $year = date("Y", strtotime($now));


        $first = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $last = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

        $thisTime = strtotime($first);
        $endTime = strtotime($last);
        while ($thisTime <= $endTime) {
            $thisDate = date('d-m-Y', $thisTime);
            $Month[] = $thisDate;
            $thisTime = strtotime('+1 day', $thisTime);
        }

        return $Month;
    }

    public static function date_filter($from, $date, $to)
    {
        $flag = TRUE;
        if (strlen($from) > 0) {

            $from = str_replace("/", "-", $from);
            $from = date('Y-m-d', strtotime($from));

            if (strlen($to) > 0) {
                $to = str_replace("/", "-", $to);
                $to = date('Y-m-d', strtotime($to));
            }

            $date = str_replace("/", "-", $date);
            $date = date('Y-m-d', strtotime($date));

            if ($date < $from) {
                $flag = FALSE;
            } else {
                if ($date > $to && strlen($to) > 0) {
                    $flag = FALSE;
                }
            }
        }
        return $flag;
    }

    public static function AverageTenureDays($db)
    {
        $Fully_Paid_Loan_days = 0;
        $Average_Number_of_Days = 0;
        $Fully_Paid_Loans = 0;

        $loans = $db->select("yearloans", [], ["active_flag" => 1, "del_flag" => 0]);
        foreach ($loans as $loanDetails) {

            $loan_id = $loanDetails['id'];

            $repayment_dates = Loans::repaymentDates($loanDetails);
            $size = count($repayment_dates) - 1;
            $maturity_date = $repayment_dates[$size];
            $maturity_date = str_replace("/", "-", $maturity_date);
            $maturity_date = new DateTime($maturity_date);

            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id']]);
            $Installments = Loans::totalAmountsToPay($loanDetails, $repayments);

            $totalPaid = $Installments[2];

            if ($Installments[0] <= $totalPaid) {
                $Fully_Paid_Loans++;
                $release_date = $loanDetails['release_date'];
                $release_date = str_replace("/", "-", $release_date);
                $release_date = new DateTime($release_date);
                $Fully_Paid_Loan_days = $maturity_date->diff($release_date)->format("%a");
            }
        }
        if ($Fully_Paid_Loans < 1) {
            $Average_Number_of_Days = 0;
        } else {
            $Average_Number_of_Days = $Fully_Paid_Loan_days / $Fully_Paid_Loans;
        }

        return $Average_Number_of_Days;
    }

    public static function PAR($db, $days)
    {
        $result = array();
        $result['loans'] = array();
        $result['amount'] = 0;
        $result['number_loans'] = 0;
        $totalPrinciple = self::LoanTotals($db)[0];
        $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        foreach ($loans as $loanDetails) {

            $repayments = $db->select("loan_installment_paid", [], ["loan_id" => $loanDetails['id'], "active_flag" => 1, "del_flag" => 0]);
            $Installments = Loans::totalAmountsToPay($loanDetails, $repayments);

            $totalPaid = $Installments[2];

            if ($Installments[0] > $totalPaid) {
                $sql = "SELECT * FROM loan_installment_paid where id= (SELECT MAX(id) from loan_installment_paid WHERE loan_id='" . $loanDetails['id'] . "')";
                $collection_date = $db->selectQuery($sql);
                $release_date = str_replace("/", "-", $loanDetails['release_date']);
                if (!empty($collection_date)) {
                    $last_payment = str_replace("/", "-", $collection_date[0]['collection_date']);
                } else {
                    $last_payment = $release_date;
                }
                $repayment_dates = Loans::repaymentDates($loanDetails);
                $size = count($repayment_dates) - 1;
                $maturity_date = strtotime(str_replace("/", "-", $repayment_dates[$size]));
                $loan_duration_days = date_diff(date_create(date("d-m-Y", $maturity_date)), date_create($release_date))->format('%a');
                $daily_principle = $loanDetails['principal_amt'] / $loan_duration_days;
                if ($days > 0) {
                    if (self::overdue($days, $last_payment)) {
                        $result['amount'] += $daily_principle * $days;
                        $result['number_loans']++;
                        $loanDetails['amount_at_risk'] = $daily_principle * $days;
                        $result['loans'][] = $loanDetails;
                    }
                }
            }
        }
        if ($result['amount'] > 0 && $totalPrinciple > 0) {
            $result['percentage'] = ($result['amount'] / $totalPrinciple) * 100;
        } else {
            $result['percentage'] = 0;
        }
        return $result;
    }

    public static function overdue($days, $date)
    {
        $flag = FALSE;
        $interval = date_diff(date_create(date("Y-m-d")), date_create($date));
        $num = str_replace(" days", "", $days);
        $date2 = (int)$num;
        if ($interval->format('%a') >= $date2) {
            $flag = TRUE;
        }
        return $flag;
    }
    public static function borrowerSummary()
    {
    }



    public static function getLoanFees($db, $start, $end, $deductable = 0)
    {
        $loans = [];
        if ($start != 0 && $end != 0) {
            // $query = "SELECT id FROM loans WHERE STR_TO_DATE(release_date, '%d/%m/%Y') > '$start' AND STR_TO_DATE(release_date, '%d/%m/%Y') < '$end'";
            $to_date = date("Y-m-d", strtotime($start));
            $from_date = date("Y-m-d", strtotime($end));
            $query = "SELECT * FROM  loans WHERE 
            (CASE  
                WHEN STR_TO_DATE(release_date,'%d/%m/%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d/%m/%Y') 
                WHEN STR_TO_DATE(release_date,'%Y/%m/%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y/%m/%d')   
                WHEN STR_TO_DATE(release_date,'%d-%m-%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d-%m-%Y') 
                WHEN STR_TO_DATE(release_date,'%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y-%m-%d') 
            END)>='$from_date' AND  
            (CASE 
                WHEN STR_TO_DATE(release_date,'%d/%m/%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d/%m/%Y') 
                WHEN STR_TO_DATE(release_date,'%Y/%m/%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y/%m/%d')   
                WHEN STR_TO_DATE(release_date,'%d-%m-%Y') IS NOT NULL THEN STR_TO_DATE(release_date,'%d-%m-%Y') 
                WHEN STR_TO_DATE(release_date,'%Y-%m-%d') IS NOT NULL THEN STR_TO_DATE(release_date,'%Y-%m-%d') 
            END)<='$to_date' AND active_flag=1 and del_flag=0";

            $loans = $db->selectQuery($query);
        } else {
            $loans = $db->select("loans", [], ["active_flag" => 1, "del_flag" => 0]);
        }
        //echo $query;

        $totalFeesPaid = 0;
        //  var_dump($loans);

        foreach ($loans as $loan) {


            $fees = $db->select("loan_application_fees", [], ["loan_id" => $loan['id'], 'deductable' => $deductable]);

            if (!empty($fees)) {
                foreach ($fees as $fee) {
                    $totalFeesPaid  += $fee['amount'];
                }
            }
        }

        return $totalFeesPaid;
    }
}
