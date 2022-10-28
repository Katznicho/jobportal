<?php

class AccessControl
{

    function __construct()
    {
    }

    public static $admin = [
        "Dashboard" => [
            "View Dashboard",
            // "View Only Assigned",

        ],
        "SMS Settings" => [
            "SMS Templates" => ["Add SMS Template", "Edit SMS Template", "Delete SMS Template"],
            "Sender Id" => ["Sender Id"],
            "SMS Credits" => [],
            "Auto Send SMS" => [],
            "Reminders" => ["Add SMS Reminder","Delete SMS Reminders"],
            "SMS History"=>['View Sms History']
        ],
        "Payroll Templates" => [],
        "Admin Settings" => ["Account Settings", "Format Borrower Reports", "Purchase Units"],
        "Email Settings" => [
            "Email Accounts" => [" Email Settings", "Add Email Account", "Edit Email Account", "Delete Email Account", "Verify Email Account"],
            "Email Templates" => ["Add Email Template", "Edit Email Template", "Delete Email Template"],
            "Auto Send Emails" => [],
        ],
        "Other Income Types" => ["Delete Other Income Type", "Edit Other Income Type", "Add Other Income Type", "Other Income Types", "View Other Income",  "Add Other Income"],
        "Custom Fields" => ["Edit Custom Field", "Add Custom Field", "Delete Custom Field"],

        "Expense Types" => ["Expense Types", "Add Expense Type", "Edit Expense Type", "Delete Expense Type", "View Expenses", "Add Expense", "Edit Expense", "Delete Expense"],
        "Staff" => ["Add Staff", "Edit Staff", "Reset Password", "Delete Staff"],
        "Staff Roles" => [" Add Staff Role", "Edit Staff Roles Permissions", "Edit Staff Role", "Delete Staff Role", "Staff saving summery"],
        "Collateral Types" => ["Add Collateral Type", "Edit Collateral Type", "Delete Collateral Type"],
        "Savings Products" => ["Add Savings Product", "Edit Savings Product", "Delete Savings Product"],
        "Savings Fees" => ["Add Savings Fee", "Edit Savings Fee", "Delete Savings Fee", ],
        "Loan Products" => ["Loan Products", "Add Loan Product", "Edit Loan Product", "Delete Loan Product", "Add Client Loan Product", "Edit Client Loan Product", "Delete Client Loan Product", "View Client Loan Product"],
        "Loan Penalty Settings" => ["Edit Loan Penalty Settings"],
        "Bulk Upload" => ["Bulk Add Clients", "Bulk Add Savings", "Bulk Add Repayments"],
        "Loan Fees" => ["Add Loan Fee", "Edit Loan Fee", "Delete Loan Fee"],
        "Loan Repayment Methods" => ["Loan Repayment Methods", "Add Loan Repayment Method", "Edit Loan Repayment Method", "Delete Loan Repayment Method"],
        "Not Registered Collectors" => ["Not Registered Collectors", "Add Collector", "Edit Collector", "Delete Collector"],
        "Not Registered Loan Officers" => ["Add Loan Officer", "Edit Loan Officer", "Delete Loan Officer"],
        "Loan Disbursed By" => ["Add Loan Disbursed By", "Edit Loan Disbursed By", "Delete Loan Disbursed By"],
        "Payroll" => ["View Payroll", "Add Payroll"]
    ];
    public static $accounting = [

        "Accounts" => [
            "Add Account",
            "Delete Account",
            "Edit Account",
            "Add Account Subcategory",
            "Edit Account Subcategory",
            "Add Account Mappings",
            "Edit Account Mappings",
            "Delete Acoount Mappings"
        ],
        "General Ledger" => [
            "View General Ledger"
        ],
        "Transactions" => [
            "Add Transaction",
            "View Transactions",
            "Edit Transaction",
            "Delete Transaction"

        ],


    ];
    public static $borrowers = [
        "Send SMS to Borrower" => [],
        "Send Email to Borrower" => ["Send Email to Borrower"],
        "View Borrowers" => ["Edit Borrower", "Delete Borrower"],
        "Add Borrower" => [],
        "View Borrower Groups" => ["View Group Details", "Edit Borrowers Group", "Delete Borrowers Group", "Add Group Comments", "Edit Group Comments", " Delete Group Comments"],
        "Add Borrowers Group" => [],
        // New option
        "Activate Client Login" => [],
        "Send SMS to All Borrowers" => [],
        "Send Email to All Borrowers" => [],
        "Invite Borrowers" => ["Invite Borrowers", "Reset Borrower Password", "Delete Borrower Invitation"],
    ];
    public static $savings = [
        "View Savings" => [],
        "Withdraw Requests" => ["View Withdraw Requests", "Approve Withdraw Request", "Cancel Withdraw Request", "Freeze Amount"],
        "Savings Transactions" => ["Add Transaction", "View Savings Transactions", "Transaction Report"],
    ];
    public static $loans = [
        "View Loans Mode" => [

            "Only Assigned Loans",
            "Download Active Loans Summary",
            // "Use Advanced Search"
        ],

        "View Loans Borrower" => [
            "Add Loan" => [
                "Processing", "Open", "Restructure", "Defaulted", "Fraud", "Denied", "Fully Paid"
            ],
            "View Loan Details" => [
                "Edit Loan", "Delete Loan", "Add Repayment", "Bulk Add Repayments", "Edit Repayment", "Delete Repayment",
                "Add Comments", "Edit Comments", "Delete Comments", "View Loan Statement", "View Loan Repayments Schedule", "Edit Collection Sheet"
            ],
            "Delete Loan" => [
                "Edit Loan", "Delete Loan", "Add Repayment", "Bulk Add Repayments", "Edit Repayment", "Delete Repayment",
                "Add Comments", "Edit Comments", "Delete Comments", "View Loan Statement", "View Loan Repayments Schedule", "Edit Collection Sheet"
            ],
            "Edit Loan" => [],
            "Cancel Loan" => [],
            "Close Off Loan" => []
        ],


        "View Loan Applications" => [
            "Approve Loan Application", "Delete Loan Application"
        ],
        "Due Loans" => [],
        "Missed Repayments" => [],
        "Past Maturity Date" => [],
        "1 Month Late Loans" => [],
        "3 Months Late Loans" => [],
        "Loan Calculator" => []
    ];

    public static $repayments = [
        "Add Repayments" => [],
        "View Repayments" => [],
        "Add Bulk Repayments" => [],
        "Edit Loan Repayments" => [],
        "Delete Loan Repayments" => []
    ];
    public static $collateral = ["Add Collateral" => [], "Edit Collateral" => [], "Delete Collateral" => []];

    public static $collectionSheet = [
        "Daily Collection Sheet" => [],
        "Missed Repayment Sheet" => [],
        "Past Maturity Date Loans" => [],
        "Send SMS" => [],
        "Send Email" => []
    ];

    public static $reports = [
        "Collections Report" => [],
        "Collector Report (Staff)" => [],
        "Loan Products Report" => [],
        "Cash flow" => [],
        "Cash Flow Projection" => [],
        "Deferred Income" => [],
        "Profit / Loss" => [],
        "MFRS Ratios" => [],
        "Portfolio At Risk (PAR)" => [],
        "All Entries" => []
    ];
    public static $shares = [
        "Add shares" => [],
        "View shares" => [],
        "View share transactions" => [],
        "withdraw shares" => []

    ];

    public static $wallet_settings = [
      "Add Wallet Details"=>[],
      "Delete Wallet Details"=>[],
      "Edit Wallet Details"=>[],
      "View Wallet Details"=>[],
    ];
    public static function spreadArrayKeys($assocArray)
    {
        $result = [];
        foreach ($assocArray as $key => $value) {
            if (is_string($key)) {
                $result[] = $key;
            }
            if (is_array($value)) {

                $result = array_merge($result, static::spreadArrayKeys($value));
            } else {
                $result[] = $value;
            }
        }
        return  $result;
    }
    public static function getAllPermissions()
    {
        $roles = static::spreadArrayKeys(
            array_merge(
                static::$admin,
                static::$borrowers,
                static::$collateral,
                static::$collectionSheet,
                static::$loans,
                static::$repayments,
                static::$reports,
                static::$shares,
                static::$accounting,
                static::$wallet_settings

            )
        );
        return $roles;
    }
    public static function getAccessControl()
    {

        $access = [
            "View Another Branch" => [],
             "Wallet Settings"=>self::$wallet_settings,
            "Billing" => [],
            "Admin" => self::$admin,
            "Home Branch" => [],
            "Borrowers" => self::$borrowers,
            "Savings" => self::$savings,
            "Loans" =>  self::$loans,
            "Repayments" =>  self::$repayments,
            "Collateral Register" =>  self::$collateral,
            "Collection Sheets" =>  self::$collectionSheet,
            "Reports" =>  self::$reports,
            "Shares" => self::$shares,
            "Accounting" => static::$accounting
        ];
        return $access;
    }


    public static function userCan($pageRole, $actions = [])
    {
        return in_array($pageRole, $actions);
    }
}
