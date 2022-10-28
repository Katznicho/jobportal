<?php

namespace Ssentezo\Controllers;

use Ssentezo\Database\DbAccess;
use Ssentezo\Util\AppUtil;

class LoansController
{
    /**
     * 
     */
    public static function download($db, $request)
    {
    }
    /**
     * Create a csv file for download containing loans for a given company
     * @param DbAccess $db The connection to the database
     * @param bool $activeOnly A flag to indicate whether active loans only or all loans.
     * @return never This function never returns. 
     */
    public static function createLoansSummaryFile($db, $activeOnly = false)
    {
        $headings = [
            "Client" => 'full_name',
            "Loan#" => 'loan_no',
            "Principal" => 'principal',
            "Release Date" => 'release_date',
            "Interest" => 'interest',
            "Due" => 'due',
            "Penalty" => 'penalty',
            "Paid" => 'paid',
            "Balance" => 'balance',
            "Last Payment" => 'last_payment_date',
            "Maturity Date" => 'maturity_date',
            "Collector" => 'collector',
            "Status" => 'status'
        ];

        $loans = static::getLoans($db);

        foreach ($loans as $loan) {
            if ($activeOnly && $loan['status'] == "Fully Paid") {
                continue;
            }
            $row = [];
            $row['full_name'] = ($loan['title'] ? $loan['title'] : "") . ' ' . $loan['fname'] . ' ' . $loan['lname'];
            $row['loan_no'] = $loan['loan_no'];
            $row['principal'] = $loan['principal_amt'];
            $row['release_date'] = $loan['release_date'];
            $interest = $loan['loan_interest'] . '%/' . $loan['loan_interest_pd'];
            $interest .= '(' . $loan['loan_duration'] . ' ' . $loan['loan_duration_pd'] . ')';
            $row['interest'] = $interest;
            $row['due'] = is_null($loan['overriden_due']) ? $loan['due'] : $loan['overriden_due'];
            $row['penalty'] = $loan['penalty'];
            $row['paid'] = $loan['paid'];
            // $row[] = 0; // to be calculated and adopted by Mr. Daaki Benjamin
            $row['balance'] = $loan['balance'];
            $row['last_payment_date'] = $loan['last_payment_date'];

            $maturity_date = is_null($loan['overriden_matutity_date']) ? $loan['maturity_date'] : $loan['overriden_matutity_date'];
            $row['maturity_date'] = date("d M, Y", strtotime($maturity_date));
            $row['collector'] = $loan['collector'] == AppUtil::userId() ? 'You' : $loan['c_fname'] . ' ' . $loan['c_lname'];
            switch ($loan['status']) {
                case 'Missed Repayment':
                    $row['status'] = 'Missed Repayment (' . $loan['missed_days'] . 'Days)';
                    break;
                case 'Fully Paid':
                    $row['status'] = 'Fully Paid';
                    break;
                case 'Past Maturity':
                    $row['status'] = 'Past Maturity';
                    break;
                case 'Open':
                    $row['status'] =  'Open';
                    break;
                default:
                    $row['status'] = $loan["status"];
                    break;
            }
            $data[] = $row;
        }
        ob_clean();
        $fileName = "Ssentezo-Loans Summary As of" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");

        echo  implode(",", array_keys($headings));
        echo "\n";

        foreach ($data as $loan) {
            $csv_record = [];
            foreach ($headings as $heading) {
                $csv_record[] = $loan[$heading];
            }
            echo implode(",", $csv_record);
            echo "\n";
        }
        exit;
    }
    private static function getLoans($db)
    {
        $sql = "select * from optimization_table ";
        $order = ' ORDER BY id DESC';
        if (AppUtil::user_can("Only Assigned Loans")) {
            $sql .= 'where collector =' . AppUtil::userId();
        }
        $sql .= $order;
        $loans = $db->selectQuery($sql);
        return $loans;
    }
    
}
