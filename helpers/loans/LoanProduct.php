<?php

namespace Ssentezo\Loans;

use Ssentezo\Util\ActivityLogger;
use Ssentezo\Util\AppUtil;

class LoanProduct
{

    protected $id;
    protected $name;
    protected $requiredApprovals;
    protected $status;

    public static function createWithAccounting($db)
    {
        $loan_product_name = $_POST['loan_product_name'];
        $disbursedMtds = isset($_POST['loan_disbursed_by_id']) ? $_POST['loan_disbursed_by_id'] : 0;
        $min_loan_principal_amount =  isset($_POST['min_loan_principal_amount']) ? $_POST['min_loan_principal_amount'] : 0;
        $default_loan_principal_amount =  isset($_POST["default_loan_principal_amount"]) ? $_POST["default_loan_principal_amount"] : 0;
        $max_loan_principal_amount =  isset($_POST['max_loan_principal_amount']) ? $_POST['max_loan_principal_amount'] : 0;

        $loan_decimal_places = isset($_POST['loan_decimal_places']) ? $_POST['loan_decimal_places'] : 0;
        $repayment_order = isset($_POST['repayment_order']) ? $_POST['repayment_order'] : 0;
        $required_approvals = isset($_POST['required_approvals']) ? $_POST['required_approvals'] : 1;
        // Accounting information
        $mainAccount = $_POST['main_account'];
        $feesAccount = $_POST['fees_account'];
        $interestAccount = $_POST['interest_account'];
        $accrualAccount = $_POST['accrual_account'];
        $currentMonthAccount = $_POST['current_month_account'];
        if ($disbursedMtds != 0 && 0) {
            $data = [
                "name" => $loan_product_name,
                "decimal_places" => $loan_decimal_places,
                "repayment_order" => json_encode($repayment_order),
                "disbursement_mtds" => json_encode($disbursedMtds),
                "creation_user" => AppUtil::userId(),
                "main_account_id" => $mainAccount,
                "interest_account_id" => $interestAccount,
                "fees_account_id" => $feesAccount,
                "accrual_account_id" => $accrualAccount,
                "current_month_accrual_account_id" => $currentMonthAccount
            ];
        } else {

            $data = [
                "name" => $loan_product_name,
                "creation_user" => AppUtil::userId(),
                "min_approvals" => $required_approvals,
                "main_account_id" => $mainAccount,
                "interest_account_id" => $interestAccount,
                "fees_account_id" => $feesAccount,
                "accrual_account_id" => $accrualAccount,
                "current_month_accrual_account_id" => $currentMonthAccount,
                "min_principal" => $min_loan_principal_amount,
                "default_principal" => $default_loan_principal_amount,
                "max_principal" => $max_loan_principal_amount

            ];
        }

        // print_r($data);
        // die();
        $savePdctId = $db->insert("loan_product", $data);
        // print_r($savePdctId);
        // die();
        if (is_numeric($savePdctId)) {
            ActivityLogger::logActivity(AppUtil::userId(), "Add Loan Product", "Success", "Insert Id #$savePdctId");
            header("location:view_loan_products.php");
            die();
        } else {
            ActivityLogger::logActivity(AppUtil::userId(), "Add Loan Product", "failed", "Add loan product failed with reason: $savePdctId");
            return "Error Failed to create Loan Product, Reason $savePdctId";
        }
    }

    public static function create($db)
    {
        $loan_product_name = $_POST['loan_product_name'];
        $disbursedMtds = isset($_POST['loan_disbursed_by_id']) ? $_POST['loan_disbursed_by_id'] : 0;
        $min_loan_principal_amount =  isset($_POST['min_loan_principal_amount']) ? $_POST['min_loan_principal_amount'] : 0;
        $default_loan_principal_amount =  isset($_POST["default_loan_principal_amount"]) ? $_POST["default_loan_principal_amount"] : 0;
        $max_loan_principal_amount =  isset($_POST['max_loan_principal_amount']) ? $_POST['max_loan_principal_amount'] : 0;

        $loan_decimal_places = isset($_POST['loan_decimal_places']) ? $_POST['loan_decimal_places'] : 0;
        $repayment_order = isset($_POST['repayment_order']) ? $_POST['repayment_order'] : 0;
        $required_approvals = isset($_POST['required_approvals']) ? $_POST['required_approvals'] : 1;
        if ($disbursedMtds != 0) {
            $data = [
                "name" => $loan_product_name,
                "decimal_places" => $loan_decimal_places,
                "repayment_order" => json_encode($repayment_order),
                "disbursement_mtds" => json_encode($disbursedMtds),
                "creation_user" => AppUtil::userId(),
                "min_approvals" => $required_approvals,
                "min_principal" => $min_loan_principal_amount,
                "default_principal" => $default_loan_principal_amount,
                "max_principal" => $max_loan_principal_amount

            ];
        } else {

            $data = [
                "name" => $loan_product_name,
                "creation_user" => AppUtil::userId(),
                "min_approvals" => $required_approvals,
                "name" => $loan_product_name,
                "creation_user" => AppUtil::userId(),
                "min_approvals" => $required_approvals,
                "min_principal" => $min_loan_principal_amount,
                "default_principal" => $default_loan_principal_amount,
                "max_principal" => $max_loan_principal_amount


            ];
        }


        $savePdctId = $db->insert("loan_product", $data);
        if (is_numeric($savePdctId)) {
            ActivityLogger::logActivity(AppUtil::userId(), "Add Loan Product", "Success", "Insert Id #$savePdctId");
            header("location:view_loan_products.php");
            die();
        } else {
            ActivityLogger::logActivity(AppUtil::userId(), "Add Loan Product", "Failed", "Failed with reason $savePdctId");
            return "Add Loan Product Failed with Reason: $savePdctId";
        }
    }
    public static function editWithAccounting($db)
    {
        $min_loan_principal_amount =  isset($_POST['min_loan_principal_amount']) ? $_POST['min_loan_principal_amount'] : 0;
        $default_loan_principal_amount =  isset($_POST["default_loan_principal_amount"]) ? $_POST["default_loan_principal_amount"] : 0;
        $max_loan_principal_amount =  isset($_POST['max_loan_principal_amount']) ? $_POST['max_loan_principal_amount'] : 0;

        $id = $_POST['product_id'];
        $loan_product_name = $_POST['loan_product_name'];
        $required_approvals = isset($_POST['required_approvals']) ? $_POST['required_approvals'] : 1;
        $required_approvals = ($required_approvals < 1) ? 1 : $required_approvals;

        // Accounting information
        $mainAccount = $_POST['main_account'];
        $feesAccount = $_POST['fees_account'];
        $interestAccount = $_POST['interest_account'];
        $accrualAccount = $_POST['accrual_account'];
        $currentMonthAccount = $_POST['current_month_account'];
        $data = [
            "name" => $loan_product_name,
            "min_approvals" => $required_approvals,
            "main_account_id" => $mainAccount,
            "interest_account_id" => $interestAccount,
            "fees_account_id" => $feesAccount,
            "accrual_account_id" => $accrualAccount,
            "current_month_accrual_account_id" => $currentMonthAccount,
            "min_principal" => $min_loan_principal_amount,
            "default_principal" => $default_loan_principal_amount,
            "max_principal" => $max_loan_principal_amount



        ];

        $updateId = $db->update("loan_product", $data, ["id" => $id]);
        // echo json_encode(error_get_last());
        // die();
        if (is_numeric($updateId)) {
            ActivityLogger::logActivity(AppUtil::userId(), "Edit Loan Product", "Success", "Edited Id #$updateId");
        } else {
            ActivityLogger::logActivity(AppUtil::userId(), "Add Loan Product", "failed", "Add loan product failed with reason: $updateId");
        }

        if (is_numeric($updateId)) {
            ActivityLogger::logActivity(AppUtil::userId(), "Edit Loan Product #$id", "Success", "Query Affected $updateId rows");

            header("location:view_loan_products.php");
            die();
        } else {
            ActivityLogger::logActivity(AppUtil::userId(), "Edit Loan Product #$id", "Failed", "Failed with reason $updateId");
            return "Edit Loan Product Failed with reason $updateId";
        }
    }
    public static function edit($db)
    {
        $min_loan_principal_amount =  isset($_POST['min_loan_principal_amount']) ? $_POST['min_loan_principal_amount'] : 0;
        $default_loan_principal_amount =  isset($_POST["default_loan_principal_amount"]) ? $_POST["default_loan_principal_amount"] : 0;
        $max_loan_principal_amount =  isset($_POST['max_loan_principal_amount']) ? $_POST['max_loan_principal_amount'] : 0;

        $id = $_POST['product_id'];
        $loan_product_name = $_POST['loan_product_name'];
        $required_approvals = isset($_POST['required_approvals']) ? $_POST['required_approvals'] : 1;
        $required_approvals = ($required_approvals < 1) ? 1 : $required_approvals;
        $data = [
            "name" => $loan_product_name,
            "min_approvals" => $required_approvals,
            "min_principal" => $min_loan_principal_amount,
            "default_principal" => $default_loan_principal_amount,
            "max_principal" => $max_loan_principal_amount

        ];
        $updateId = $db->update("loan_product", $data, ["id" => $id]);

        if (is_numeric($updateId)) {
            ActivityLogger::logActivity(AppUtil::userId(), "Edit Loan Product #$id", "Success", "Query Affected $updateId rows");
            header("location:view_loan_products.php");
            die();
        } else {
            ActivityLogger::logActivity(AppUtil::userId(), "Edit Loan Product #$id", "Failed", "Failed with reason $updateId");
            return "Edit Loan Product falied with reason $updateId";
        }
    }
}
