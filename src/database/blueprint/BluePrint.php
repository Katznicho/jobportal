<?php

namespace Ssentezo\Database\BluePrint;

use Exception;
use mysqli;
use Ssentezo\Util\Logger;

class BluePrint
{

    /**
     * @var string 
     * The name of the database the blueprint belongs
     */
    public $database_name;

    /**
     * @var mysqli
     * The connection  
     */


    public $conn;
    /**
     * @param mysqli $conn A mysql database connection object
     */
    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }
    
    /**
     * Tables for a ssentezo company
     */
    public function tables()
    {
        return [
            /**Accounts Table */
            new Table("CREATE TABLE `accounts` (  `id` int NOT NULL,        `name` varchar(255) NOT NULL,        `balance` int NOT NULL DEFAULT '0',        `account_no` varchar(255) NOT NULL,    `category` int NOT NULL,    `initial_balance` int DEFAULT '0',    `initial_balance_date` date DEFAULT NULL,    `is_leaf` int NOT NULL DEFAULT '1',    `created_by` int NOT NULL,    `created_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,`modified_by` int DEFAULT NULL,`modified_on` text,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`sub_category_id` int DEFAULT '0') ENGINE=InnoDB  ;"),
            /**Account Categories */
            new Table("CREATE TABLE `account_categories` (`id` int NOT NULL,`name` varchar(255) NOT NULL,`prefix` varchar(255) NOT NULL,`created_by` int NOT NULL,`created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`modified_on` timestamp NULL DEFAULT NULL,`modified_by` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),
            /**Balance sheet */
            new Table("CREATE TABLE `balance_sheet` (`id` int NOT NULL,`reg_date` varchar(20) DEFAULT NULL,`cr_dr` varchar(20) DEFAULT NULL,`name` varchar(60) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`type` varchar(100) DEFAULT NULL,`category` varchar(100) DEFAULT NULL,`amount` double DEFAULT NULL,`balance` double DEFAULT NULL,`trans_details` varchar(100) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`field1` varchar(50) DEFAULT NULL,`field2` varchar(50) DEFAULT NULL,`transaction_reference` varchar(255) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `borrower` (`id` int NOT NULL,`fname` varchar(50) NOT NULL,`lname` varchar(50) NOT NULL,`gender` varchar(20) NOT NULL,`country` varchar(40) DEFAULT NULL,`title` varchar(20) DEFAULT NULL,`mobile_no` varchar(50) DEFAULT NULL,`email` varchar(60) DEFAULT NULL,`unique_no` varchar(50) DEFAULT NULL,`dob` varchar(100) DEFAULT NULL,`address` varchar(50) DEFAULT NULL,`district` varchar(200) DEFAULT NULL,`subcounty` varchar(200) DEFAULT NULL,`village` varchar(200) DEFAULT NULL,`landline` varchar(30) DEFAULT NULL,`business_name` varchar(100) DEFAULT NULL,`working_status` varchar(100) DEFAULT NULL,`photo` mediumblob,`description` varchar(100) DEFAULT NULL,`staff_id` int DEFAULT NULL,`ussd_phonenumber` varchar(255) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL,`field3` varchar(100) DEFAULT NULL,`field4` varchar(100) DEFAULT NULL,`field5` varchar(100) DEFAULT NULL,`field6` varchar(100) DEFAULT NULL,`fiel7` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `borrowers_group` (`Id` int NOT NULL,`name` varchar(200) NOT NULL,`unique_number` varchar(100) NOT NULL DEFAULT '0',`borrowers` varchar(500) NOT NULL,`leader` varchar(200) NOT NULL,`collector` varchar(200) NOT NULL,`meeting_schedule` varchar(400) NOT NULL,`description` text NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `borrower_files` (`id` int NOT NULL,`borrower_id` int NOT NULL,`name` varchar(200) DEFAULT NULL,`description` varchar(200) DEFAULT NULL,`content` mediumblob,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`filepath` text,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL,`is_group` tinyint(1) NOT NULL DEFAULT '0') ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `branch_capital` (`id` int NOT NULL,`branch_id` int DEFAULT NULL,`amount` double DEFAULT NULL,`capital_date` varchar(20) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `clients` (`id` int NOT NULL,`username` text NOT NULL,`email` text NOT NULL,`password` text,`created_at` text,`password_reset_token` text,`last_seen` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`user_id` int DEFAULT NULL,`account_activation_token` text,`otp_token` varchar(255) DEFAULT NULL,`login_attempts` int DEFAULT NULL,`is_suspended` tinyint(1) NOT NULL DEFAULT '0',`phone_number` varchar(255) DEFAULT NULL,`password_change_attempts` varchar(255) DEFAULT NULL,`change_password_otp` varchar(255) DEFAULT NULL,`change_password_expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`token` varchar(255) DEFAULT NULL,`is_otp_verified` tinyint(1) NOT NULL DEFAULT '0',`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `client_loan_product` (`id` int NOT NULL,`loan_product_id` int DEFAULT NULL,`disbursement_mtd` text,`min_principal` decimal(10,0) DEFAULT NULL,`max_principal` decimal(10,0) DEFAULT NULL,`interest_method` text,`interest_rate` decimal(10,0) DEFAULT NULL,`interest_rate_pd` text,`duration` text,`duration_pd` text,`description` text,`active_flag` int DEFAULT NULL,`del_flag` int DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`min_approvals` int DEFAULT NULL,`min_cancellations` int DEFAULT NULL,`min_duration` int DEFAULT NULL,`max_duration` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `config` (`id` int NOT NULL,`name` text,`value` text,`has_many` int NOT NULL DEFAULT '0',`delimiter` text,`default_value` text,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `deposit_status` (`id` int NOT NULL,`transaction_reference` varchar(255) DEFAULT NULL,`status` varchar(100) DEFAULT NULL,`narrative` varchar(255) DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `disbursement_methods` (`id` int NOT NULL,`name` varchar(50) NOT NULL,`description` varchar(50) DEFAULT NULL,`created_by` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `expenses` (`id` int NOT NULL,`expense_type_id` int NOT NULL,`amount` double DEFAULT NULL,`expense_date` varchar(50) NOT NULL,`description` varchar(100) NOT NULL,`attachments` varchar(200) DEFAULT NULL COMMENT 'json encode all images of attachments',`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`last_modified_date` datetime DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `expense_type` (`id` int NOT NULL,`name` varchar(100) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loans` (`id` int NOT NULL,`loan_product_id` int NOT NULL,`borrower_id` int NOT NULL,`loan_no` varchar(100) DEFAULT NULL,`disbursement_mtd` varchar(100) DEFAULT NULL,`principal_amt` int DEFAULT NULL,`release_date` varchar(100) DEFAULT NULL,`interest_mtd` varchar(40) DEFAULT NULL,`loan_interest` float DEFAULT NULL,`loan_interest_pd` varchar(30) DEFAULT NULL,`loan_duration` int DEFAULT NULL,`loan_duration_pd` varchar(30) DEFAULT NULL,`repayment_cycle` varchar(25) DEFAULT NULL,`no_repayment_cycle` int DEFAULT NULL,`description` text,`status` varchar(50) DEFAULT NULL COMMENT 'processing, open, denied, defaulted/fraud,closed, disbursed, approved',`disbursement_date` datetime DEFAULT NULL,`application_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`is_group` tinyint(1) NOT NULL DEFAULT '0',`collector` int DEFAULT NULL,`field3` varchar(100) DEFAULT NULL,`overriden_due` int DEFAULT NULL,`overriden_maturity_date` date DEFAULT NULL,`total_accrued` float DEFAULT '0',`last_accrued_on` datetime DEFAULT NULL,`is_accruable` int DEFAULT '0') ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_applications` (`id` int NOT NULL,`borrower_id` int DEFAULT NULL,`loan_product_id` int DEFAULT NULL,`amount` decimal(10,0) DEFAULT NULL,`status` text,`active_flag` int DEFAULT NULL,`del_flag` int DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`approved_on` timestamp NULL DEFAULT NULL,`approved_by` text,`approval_dates` text,`approval_comments` text,`approval_types` text,`duration` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_application_applied_charges` (`id` int NOT NULL,`loan_charge_id` int NOT NULL,`amount` int NOT NULL,`creation_user` int NOT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`status` varchar(100) DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`loan_application_id` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_application_files` (`id` int NOT NULL,`loan_application_id` int NOT NULL,`name` varchar(100) NOT NULL,`path` text NOT NULL,`extension` varchar(30) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_applied_charges` (`id` int NOT NULL,`loan_id` int NOT NULL,`loan_charge_id` int NOT NULL,`amount` int NOT NULL,`creation_user` int NOT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`status` varchar(100) DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_charges` (`id` int NOT NULL,`name` varchar(50) NOT NULL,`description` varchar(100) DEFAULT NULL,`charge_mtd` varchar(100) DEFAULT NULL,`charge_amount` int DEFAULT NULL,`charge_rate` int DEFAULT NULL,`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`last_modified_by` int DEFAULT NULL,`deductable` varchar(10) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_collateral` (`id` int NOT NULL,`loan_id` int DEFAULT NULL,`name` varchar(100) DEFAULT NULL,`collateral_type` varchar(100) DEFAULT NULL,`reg_date` varchar(50) DEFAULT NULL,`value` double DEFAULT NULL,`status` varchar(100) DEFAULT NULL,`status_date` varchar(100) DEFAULT NULL,`model_name` varchar(100) DEFAULT NULL,`model_number` varchar(100) DEFAULT NULL,`serial_no` varchar(100) DEFAULT NULL,`manufacture_date` varchar(100) DEFAULT NULL,`collateral_condition` varchar(100) DEFAULT NULL,`address` varchar(100) DEFAULT NULL,`description` varchar(300) DEFAULT NULL,`photo` varchar(300) DEFAULT NULL,`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`last_modified_by` int DEFAULT NULL,`last_modification_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int DEFAULT '0',`feild1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL,`loan_application_id` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_collateral_files` (`id` int NOT NULL,`collateral_id` int NOT NULL,`name` varchar(200) DEFAULT NULL,`path` varchar(200) DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_collateral_type` (`id` int NOT NULL,`name` varchar(100) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(50) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_comments` (`id` int NOT NULL,`loan_id` int NOT NULL,`staff_id` int DEFAULT NULL,`comment` text,`com_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_duration` (`id` int NOT NULL,`loan_product_id` int NOT NULL,`min_duration` int NOT NULL,`min_duration_rate` varchar(100) NOT NULL,`default_duration` int NOT NULL,`default_duration_rate` varchar(100) NOT NULL,`max_duration` int NOT NULL,`max_duration_rate` varchar(100) NOT NULL,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_files` (`id` int NOT NULL,`loan_id` int NOT NULL,`name` varchar(100) NOT NULL,`path` text NOT NULL,`extension` varchar(30) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_guarantee_fund_profiles` (`id` int NOT NULL,`borrower_id` int NOT NULL,`balance` double NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`created_by` int NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_guarantee_fund_transactions` (`id` int NOT NULL,`borrower_id` int NOT NULL,`amount` double NOT NULL,`type` varchar(100) NOT NULL,`date` date NOT NULL,`time` int NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_guarantors` (`id` int NOT NULL,`name` varchar(100) DEFAULT NULL,`phone` varchar(20) DEFAULT NULL,`description` text,`created_by` int NOT NULL DEFAULT '0',`loan_id` int DEFAULT NULL,`loan_application_id` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_installments` (`id` int NOT NULL,`loan_id` int DEFAULT NULL,`collection_date` date DEFAULT NULL,`amount` decimal(19,4) DEFAULT NULL,`principal_installment` decimal(19,4) DEFAULT '0.0000',`interest_installment` decimal(19,4) DEFAULT '0.0000',`principal_paid` decimal(19,4) DEFAULT '0.0000',`interest_paid` decimal(19,4) DEFAULT '0.0000',`reducing_bal` decimal(19,4) DEFAULT '0.0000',`creation_date` datetime DEFAULT CURRENT_TIMESTAMP,`created_by` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`active_flag` int DEFAULT '1',`del_flag` int DEFAULT '0',`trans_id` int DEFAULT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_installment_paid` (`id` int NOT NULL,`loan_id` int NOT NULL,`borrower_id` int NOT NULL,`amount` double DEFAULT '0',`principal_installment` double DEFAULT NULL,`interest_installment` double DEFAULT NULL,`payment_date` date DEFAULT NULL,`reducing_bal` int DEFAULT NULL,`creation_user` int NOT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`collection_date` varchar(20) DEFAULT NULL,`repayment_mtd` varchar(30) DEFAULT NULL,`description` varchar(200) DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1',`is_group` tinyint(1) NOT NULL DEFAULT '0',`deposited_by` varchar(200) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_installment_repayments` (`id` int NOT NULL,`installment_id` int NOT NULL,`repayment_id` int NOT NULL,`trans_id` int NOT NULL,`principal_amount` float NOT NULL,`interest_amount` float NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_interest_accruals` (`id` int NOT NULL,`loan_id` int NOT NULL,`trans_id` int NOT NULL,`amount` int NOT NULL,`date` date NOT NULL,`total_amount` int NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_interest_deferrals` (`id` int NOT NULL,`loan_id` int NOT NULL,`trans_id` int NOT NULL,`status` int NOT NULL DEFAULT '0',`account_id` int NOT NULL,`amount` float NOT NULL,`created_by` int NOT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_penalty` (`id` int NOT NULL,`loan_product_id` int NOT NULL,`charge_type` varchar(100) NOT NULL,`amount` double DEFAULT NULL,`percentage` varchar(30) DEFAULT NULL,`grace_period` varchar(10) DEFAULT NULL,`recurring_days` varchar(100) DEFAULT '1',`penalty_type` varchar(100) DEFAULT NULL,`applies_to` varchar(100) DEFAULT NULL,`calculated_on` varchar(100) DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`field1` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_product` (`id` int NOT NULL,`name` varchar(100) NOT NULL,`decimal_places` int NOT NULL DEFAULT '2',`repayment_order` varchar(200) DEFAULT NULL,`disbursement_mtds` varchar(200) DEFAULT NULL,`creation_user` int NOT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1' COMMENT '0-Inactive 1-active',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`last_modified_by` int DEFAULT NULL,`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL,`min_approvals` int NOT NULL DEFAULT '1',`main_account_id` int DEFAULT '0',`interest_account_id` int DEFAULT '0',`fees_account_id` int DEFAULT '0',`accrual_account_id` int DEFAULT '0',`min_principal` float DEFAULT NULL,`max_principal` float DEFAULT NULL,`default_principal` float DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_product_interest` (`id` int NOT NULL,`loan_product_id` int NOT NULL,`method` varchar(100) NOT NULL,`mini_interest` int NOT NULL,`mini_interest_rate` varchar(100) NOT NULL,`default_interest` int NOT NULL,`default_interest_date` varchar(100) NOT NULL,`max_interest` int NOT NULL,`max_interest_rate` varchar(100) NOT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`field1` varchar(100) DEFAULT NULL,`feild2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_repayments` (`id` int NOT NULL,`loan_product_id` int NOT NULL,`repayment_cycle` varchar(50) NOT NULL,`mini_no_repayments` int NOT NULL,`default_no_repayments` int NOT NULL,`max_no_repayments` int NOT NULL,`creation_user` int NOT NULL,`last_modified_by` int DEFAULT NULL,`field1` varchar(50) DEFAULT NULL,`field2` varchar(50) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_schedule` (`id` int NOT NULL,`loan_id` int NOT NULL,`type` varchar(50) DEFAULT NULL COMMENT 'fees, penalty,principal,interest etc',`description` varchar(80) DEFAULT NULL,`principal` varchar(20) DEFAULT NULL,`interest` varchar(50) DEFAULT NULL,`fees` varchar(20) DEFAULT NULL,`penalty` varchar(20) DEFAULT NULL,`due` varchar(50) DEFAULT NULL,`paid` varchar(23) DEFAULT NULL,`pending_due` varchar(50) DEFAULT NULL,`principal_bal_owed` varchar(30) DEFAULT NULL,`principal_paid` varchar(20) DEFAULT NULL,`interest_paid` varchar(30) DEFAULT NULL,`fees_paid` varchar(25) DEFAULT NULL,`penalty_paid` varchar(100) DEFAULT NULL,`due_date` varchar(50) DEFAULT NULL,`record_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`collector_id` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL,`field3` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `loan_status_table` (`id` int NOT NULL,`due` decimal(15,3) NOT NULL DEFAULT '0.000',`penalty` decimal(15,3) NOT NULL DEFAULT '0.000',`paid` decimal(15,3) NOT NULL DEFAULT '0.000',`balance` decimal(15,3) NOT NULL DEFAULT '0.000',`maturity_date` varchar(20) NOT NULL,`collector` int NOT NULL,`status` varchar(20) NOT NULL,`missed_days` varchar(20) NOT NULL DEFAULT '0',`loan_id` int NOT NULL,`last_payment_date` varchar(20) NOT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`missed_day` varchar(20) DEFAULT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `loan_status_update_log` (`id` int NOT NULL,`old_data` text,`message` text,`loan_id` int DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `make_deposit` (`id` int NOT NULL,`borrower_id` int DEFAULT NULL,`status` varchar(100) DEFAULT NULL,`transaction_reference` varchar(255) DEFAULT NULL,`transaction_type` varchar(255) NOT NULL DEFAULT 'deposit',`amount` varchar(255) DEFAULT NULL,`phone_number` varchar(255) DEFAULT NULL,`narrative` varchar(255) DEFAULT NULL,`otp_code` varchar(255) DEFAULT NULL,`otp_expiry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int DEFAULT NULL,`del_flag` int DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `other_income` (`id` int NOT NULL,`other_income_type_id` int NOT NULL,`amount` double NOT NULL,`transaction_date` varchar(50) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`files` text,`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`last_modified_date` datetime DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(50) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `other_income_type` (`id` int NOT NULL,`name` varchar(50) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(50) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `payrole_transactions` (`id` int NOT NULL,`staff_id` int NOT NULL,`account_no` varchar(255) NOT NULL,`amount` float NOT NULL,`payroll_date` date NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `payroll` (`id` int NOT NULL,`staff_id` int DEFAULT NULL,`payroll_date` varchar(20) DEFAULT NULL,`basic_pay` double DEFAULT NULL,`net_pay` varchar(20) DEFAULT NULL,`payment_method` varchar(100) DEFAULT NULL,`bank_name` varchar(100) DEFAULT NULL,`account_no` varchar(50) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`comment` varchar(100) DEFAULT NULL,`is_recurring` int DEFAULT '0',`recurring_pd` varchar(20) DEFAULT NULL,`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(50) DEFAULT NULL,`field2` varchar(50) DEFAULT NULL,`field3` varchar(50) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `payroll_details` (`id` int NOT NULL,`payroll_id` int DEFAULT NULL,`payment_name` varchar(50) DEFAULT NULL,`type` varchar(10) DEFAULT NULL COMMENT 'C or D',`amount` varchar(20) DEFAULT NULL,`payment_id` int DEFAULT NULL,`creation_user` int DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`field1` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `payroll_extras` (`id` int NOT NULL,`type` varchar(10) DEFAULT NULL COMMENT 'C or D',`name` varchar(100) NOT NULL,`description` varchar(100) NOT NULL,`creation_date` datetime DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(50) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `platform_activations` (`id` int NOT NULL,`platform_id` int NOT NULL,`user_id` int NOT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,`staff_id` int NOT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `principal_amount` (`id` int NOT NULL,`loan_product_id` int NOT NULL,`mini` int NOT NULL,`default_amount` int NOT NULL,`max` int NOT NULL,`field1` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `querycomplex` (`no` bigint NOT NULL DEFAULT '0',`month` varchar(9) DEFAULT NULL,`year` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `querymonth` (`no` double DEFAULT NULL,`month` varchar(9)  DEFAULT NULL,`year` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `querythrid` (`loan_id` int NOT NULL,`month` int DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `sample` (`id` int NOT NULL,`external_reference` varchar(200)  DEFAULT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `savings_account` (`id` int NOT NULL,`savings_product_id` int NOT NULL,`account_no` varchar(100) NOT NULL,`account_name` varchar(100) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`borrower_id` int NOT NULL,`balance` double NOT NULL DEFAULT '0',`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int NOT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`is_group` tinyint(1) NOT NULL DEFAULT '0',`field2` varchar(100) DEFAULT NULL,`field3` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `savings_applied_charges` (`id` int NOT NULL,`saving_id` int NOT NULL,`saving_fee_id` int NOT NULL,`amount` int NOT NULL,`creation_user` int NOT NULL,`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`active_flag` int DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`transaction_reference` varchar(255) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `savings_disbursement_methods` (`id` int NOT NULL,`name` varchar(50) NOT NULL,`description` varchar(50) DEFAULT NULL,`created_by` varchar(50)  DEFAULT NULL,`active_flag` varchar(50) DEFAULT NULL,`del_flag` varchar(50) DEFAULT NULL,`created_at` varchar(50) DEFAULT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `savings_fees` (`id` int NOT NULL,`name` varchar(200) NOT NULL,`charge_amount` int NOT NULL,`charge_mtd` varchar(100) NOT NULL,`charge_rate` int NOT NULL,`deductable` varchar(10) NOT NULL,`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` varchar(100) NOT NULL,`description` text NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`channel` varchar(255) DEFAULT NULL,`mode_of_application` varchar(255) DEFAULT NULL,`transaction_type` varchar(255) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `savings_product` (`id` int NOT NULL,`name` varchar(100) NOT NULL,`interest` double NOT NULL,`posting_freq` varchar(100) DEFAULT NULL,`minimum_amount` double NOT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modifed_date` datetime DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `savings_transcations` (`id` int NOT NULL,`savings_account_id` int NOT NULL,`savings_account_to` int NOT NULL DEFAULT '0',`amount` double NOT NULL,`type` varchar(100) NOT NULL COMMENT 'credit or debit',`transaction_date` varchar(100) DEFAULT NULL,`transaction_time` varchar(100) DEFAULT NULL,`trans_type` varchar(100) NOT NULL COMMENT 'deposit, withdrawal,transfer',`description` varchar(100) NOT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int NOT NULL,`last_modified_by` int DEFAULT NULL,`last_modified_date` datetime DEFAULT NULL,`incremental_balance` double DEFAULT NULL,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`deposited_by` varchar(200) DEFAULT NULL,`transaction_reference` varchar(255) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `staff` (`id` int NOT NULL,`branch_id` int DEFAULT NULL,`role_id` int DEFAULT NULL,`fname` varchar(100) DEFAULT NULL,`lname` varchar(100) DEFAULT NULL,`email` varchar(100) DEFAULT NULL,`password` text,`gender` varchar(20) DEFAULT NULL,`country` varchar(100) DEFAULT NULL,`phone_no` varchar(100) DEFAULT NULL,`dob` varchar(20) DEFAULT NULL,`address` varchar(50) DEFAULT NULL,`city` varchar(50) DEFAULT NULL,`province_state` varchar(50) DEFAULT NULL,`zipcode` varchar(30) DEFAULT NULL,`landline` varchar(30) DEFAULT NULL,`skype` varchar(30) DEFAULT NULL,`pic` text,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `staff_access` (`id` int NOT NULL,`role_id` int DEFAULT NULL,`actions` text,`creation_date` datetime DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`last_modified_by` int DEFAULT NULL,`field1` varchar(100) DEFAULT NULL,`field2` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `staff_roles` (`id` int NOT NULL,`name` varchar(100) DEFAULT NULL,`description` varchar(100) DEFAULT NULL,`creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`creation_user` int DEFAULT NULL,`actions` text,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0',`field1` varchar(100) DEFAULT NULL) ENGINE=InnoDB ;"),

            new Table("CREATE TABLE `transactions` (`id` int NOT NULL,`type` varchar(255) DEFAULT '',`amount` float DEFAULT NULL,`description` text,`date` date DEFAULT NULL,`time` varchar(255) DEFAULT NULL,`accounts` text,`status` text,`active_flag` int NOT NULL DEFAULT '1',`del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB  ;"),
            new Table("CREATE TABLE `transaction_status` (`id` int NOT NULL,`transaction_reference` varchar(255) DEFAULT NULL,`status` varchar(100) DEFAULT NULL,`narrative` varchar(255) NOT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `unique_numbers` (`id` int NOT NULL,`unique_numbers` varchar(100) NOT NULL DEFAULT 'ABC',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `ussd_clients` (`id` int NOT NULL,`msisdn` varchar(20) NOT NULL,`company_id` int NOT NULL,`ussd_mpin` varchar(40) DEFAULT NULL,`old_mpin` varchar(40) DEFAULT NULL,`security_code` varchar(255) DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`staff_id` int NOT NULL,`del_flag` int NOT NULL DEFAULT '0',`borrower_id` int NOT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `withdrawal_transactions` (`id` int NOT NULL,`borrower_id` int DEFAULT NULL,`status` varchar(100) DEFAULT NULL,`transaction_reference` varchar(255) DEFAULT NULL,`transaction_type` varchar(255) DEFAULT 'withdraw',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`amount` varchar(255) DEFAULT NULL,`narrative` varchar(255) DEFAULT NULL,`otp_code` varchar(255) DEFAULT NULL,`active_flag` int DEFAULT NULL,`del_flag` int DEFAULT NULL,`otp_expiry_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,`phone_number` varchar(255) DEFAULT NULL) ENGINE=InnoDB  ;"),

            new Table("CREATE TABLE `withdraw_requests` (`id` int NOT NULL,`borrower_id` int NOT NULL,`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,`settled` int NOT NULL,`status` text,`amount` int NOT NULL,`settled_at` datetime NOT NULL) ENGINE=InnoDB ;"),

        ];
    }
    /**
     * The constraints to the tables 
     * This function returns an array of queries to alter the structure of the tables
     */
    public function alters()
    {
        return [
            "ALTER TABLE `savings_transcations ADD `channel` VARCHAR(255) NULL DEFAULT 'cash' AFTER `incremental_balance`, ADD `platform` VARCHAR(255) NULL DEFAULT NULL AFTER channel`, ADD `fee_applied` VARCHAR(255) NULL DEFAULT NULL AFTER `platform`;",
            "ALTER TABLE `accounts` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `account_categories` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `balance_sheet` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `borrower` ADD PRIMARY KEY (`id`), ADD KEY `staff_id` (`staff_id`);",

            "ALTER TABLE `borrowers_group` ADD PRIMARY KEY (`Id`);",

            "ALTER TABLE `borrower_files` ADD PRIMARY KEY (`id`),ADD KEY `borrower_id` (`borrower_id`);",

            "ALTER TABLE `branch_capital` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `clients` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `client_loan_product` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `config` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `deposit_status` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `disbursement_methods` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `expenses` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `expense_type` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loans` ADD PRIMARY KEY (`id`),ADD KEY `loan_product_id_in_loan` (`loan_product_id`), ADD KEY `borrower_id_in_loan` (`borrower_id`);",

            "ALTER TABLE `loan_applications` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_application_applied_charges` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_application_files` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_applied_charges` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_charges` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_collateral` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_collateral_files` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_collateral_type` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_comments` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_duration` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_files` ADD PRIMARY KEY (`id`), ADD KEY `loan_id` (`loan_id`);",

            "ALTER TABLE `loan_guarantee_fund_profiles` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_guarantee_fund_transactions` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_guarantors` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_installments` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_installment_paid` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_installment_repayments` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_interest_accruals` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_interest_deferrals` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_penalty` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_product`  ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_product_interest` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_repayments` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_schedule` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_status_table` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `loan_status_update_log` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `make_deposit` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `transaction_reference` (`transaction_reference`);",

            "ALTER TABLE `other_income` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `other_income_type` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `payrole_transactions` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `payroll` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `payroll_details` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `payroll_extras` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `platform_activations` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `principal_amount` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `sample`  ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `savings_account`  ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `savings_applied_charges` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `savings_disbursement_methods` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `savings_product`  ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `savings_transcations` ADD PRIMARY KEY (`id`);",
            "ALTER TABLE `savings_fees` MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            "ALTER TABLE `savings_fees` ADD PRIMARY KEY (`id`);",
            "ALTER TABLE `staff` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `staff_access` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `staff_roles` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `transactions` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `transaction_status` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `unique_numbers` ADD PRIMARY KEY (`id`);",
            "ALTER TABLE `ussd_clients` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `withdrawal_transactions` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `transaction_reference` (`transaction_reference`), ADD UNIQUE KEY `otp_code` (`otp_code`);",

            "ALTER TABLE `withdraw_requests` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `accounts` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `account_categories` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `balance_sheet` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `borrower`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `borrowers_group` MODIFY `Id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `borrower_files` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `branch_capital` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `clients` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `client_loan_product`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `config` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `deposit_status` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `disbursement_methods` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `expenses` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `expense_type` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loans` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_applications` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_application_applied_charges` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_application_files` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_applied_charges` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_charges` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_collateral` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_collateral_files` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_collateral_type` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_comments` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_duration` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_files` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_guarantee_fund_profiles` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_guarantee_fund_transactions` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_guarantors` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_installments` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_installment_paid` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_installment_repayments` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_interest_accruals` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_interest_deferrals` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_penalty` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_product` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_product_interest` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_repayments` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_schedule` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_status_table` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `loan_status_update_log` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `make_deposit` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `other_income` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `other_income_type` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `payrole_transactions` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `payroll` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `payroll_details` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `payroll_extras` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `platform_activations` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `principal_amount` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `sample` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_account` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_applied_charges` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_disbursement_methods` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_product` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_transcations` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `staff` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `staff_access` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `staff_roles` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `transactions` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `transaction_status` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `unique_numbers` MODIFY `id` int NOT NULL AUTO_INCREMENT;",          "ALTER TABLE `ussd_clients` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `withdrawal_transactions` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `withdraw_requests` MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            "ALTER TABLE `savings_account` ADD `freeze_amount` VARCHAR(255) NULL DEFAULT NULL AFTER `updated_at`;"

        ];
    }
    /**
     * The views for a ssentezo company databse
     */
    public function views()
    {
        return [
            new View("CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `transactions_status` AS select `withdrawal_transactions`.`borrower_id` AS `borrower_id`,`withdrawal_transactions`.`amount` AS `amount`,`withdrawal_transactions`.`transaction_type` AS `transaction_type`,`withdrawal_transactions`.`status` AS `status`,`withdrawal_transactions`.`transaction_reference` AS `transaction_reference`,`withdrawal_transactions`.`narrative` AS `narrative`,`withdrawal_transactions`.`created_at` AS `created_at`,`withdrawal_transactions`.`phone_number` AS `phone_number` from `withdrawal_transactions` union all select `make_deposit`.`borrower_id` AS `borrower_id`,`make_deposit`.`amount` AS `amount`,`make_deposit`.`transaction_type` AS `transaction_type`,`make_deposit`.`status` AS `status`,`make_deposit`.`transaction_reference` AS `transaction_reference`,`make_deposit`.`narrative` AS `narrative`,`make_deposit`.`created_at` AS `created_at`,`make_deposit`.`phone_number` AS `phone_number` from `make_deposit` order by `created_at` ;"),
            new View("CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_loan_borrower_list`  AS  select `borrower`.`fname` AS `fname`,`borrower`.`lname` AS `lname`,`borrower`.`title` AS `title`,`loans`.`loan_no` AS `loan_no`,`loans`.`id` AS `loan_id` from (`loans` join `borrower` on((`loans`.`borrower_id` = `borrower`.`id`))) where ((`loans`.`active_flag` = 1) and (`loans`.`del_flag` = 0)) ;"),
            new View("CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_loan_collectors`  AS  select `staff`.`fname` AS `c_fname`,`staff`.`lname` AS `c_lname`,`staff`.`id` AS `collector_id`,`loans`.`id` AS `loan_id` from (`loans` join `staff` on((`loans`.`collector` = `staff`.`id`))) where ((`loans`.`active_flag` = 1) and (`loans`.`del_flag` = 0)) ;"),
            new View("CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `optimization_table`  AS  select `loan_status_table`.`id` AS `id`,`loan_status_table`.`due` AS `due`,`loan_status_table`.`penalty` AS `penalty`,`loan_status_table`.`missed_day` AS `missed_day`,`loan_status_table`.`paid` AS `paid`,`loan_status_table`.`balance` AS `balance`,`loan_status_table`.`maturity_date` AS `maturity_date`,`loan_status_table`.`collector` AS `collector`,`loan_status_table`.`status` AS `status`,`loan_status_table`.`missed_days` AS `missed_days`,`loan_status_table`.`loan_id` AS `loan_id`,`loan_status_table`.`last_payment_date` AS `last_payment_date`,`loan_status_table`.`created_at` AS `created_at`,`loan_status_table`.`updated_at` AS `updated_at`,`active_loan_collectors`.`c_fname` AS `c_fname`,`active_loan_collectors`.`c_lname` AS `c_lname`,`active_loan_borrower_list`.`fname` AS `fname`,`active_loan_borrower_list`.`lname` AS `lname`,`active_loan_borrower_list`.`title` AS `title`,`loans`.`principal_amt` AS `principal_amt`,`loans`.`loan_interest` AS `loan_interest`,`loans`.`loan_no` AS `loan_no`,`loans`.`release_date` AS `release_date`,`loans`.`interest_mtd` AS `interest_mtd`,`loans`.`borrower_id` AS `borrower_id`,`loans`.`is_group` AS `is_group`,`loans`.`loan_duration` AS `loan_duration`,`loans`.`loan_duration_pd` AS `loan_duration_pd`,`loans`.`repayment_cycle` AS `repayment_cycle`,`loans`.`loan_interest_pd` AS `loan_interest_pd`,`loans`.`overriden_due` AS `overriden_due`,`loans`.`overriden_maturity_date` AS `overriden_maturity_date`,`loans`.`status` AS `c_status` from (((`loan_status_table` join `active_loan_collectors` on((`active_loan_collectors`.`loan_id` = `loan_status_table`.`loan_id`))) join `active_loan_borrower_list` on((`active_loan_borrower_list`.`loan_id` = `loan_status_table`.`loan_id`))) join `loans` on((`loans`.`id` = `loan_status_table`.`loan_id`))) ;"),
            new View("CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `yearloans`  AS  select `loans`.`id` AS `id`,`loans`.`loan_product_id` AS `loan_product_id`,`loans`.`borrower_id` AS `borrower_id`,`loans`.`loan_no` AS `loan_no`,`loans`.`disbursement_mtd` AS `disbursement_mtd`,`loans`.`principal_amt` AS `principal_amt`,`loans`.`release_date` AS `release_date`,`loans`.`interest_mtd` AS `interest_mtd`,`loans`.`loan_interest` AS `loan_interest`,`loans`.`loan_interest_pd` AS `loan_interest_pd`,`loans`.`loan_duration` AS `loan_duration`,`loans`.`loan_duration_pd` AS `loan_duration_pd`,`loans`.`repayment_cycle` AS `repayment_cycle`,`loans`.`no_repayment_cycle` AS `no_repayment_cycle`,`loans`.`description` AS `description`,`loans`.`status` AS `status`,`loans`.`disbursement_date` AS `disbursement_date`,`loans`.`application_date` AS `application_date`,`loans`.`creation_user` AS `creation_user`,`loans`.`creation_date` AS `creation_date`,`loans`.`last_modified_by` AS `last_modified_by`,`loans`.`last_modified_date` AS `last_modified_date`,`loans`.`active_flag` AS `active_flag`,`loans`.`del_flag` AS `del_flag`,`loans`.`collector` AS `collector`,`loans`.`field3` AS `field3`,`loans`.`is_group` AS `is_group` from `loans` where (year(str_to_date(`loans`.`release_date`,'%d/%m/%Y')) = year(curdate())) ;")
        ];
    }
    public function create_database()
    {
        $q = "CREATE DATABASE " . $this->database_name . "";
        $ret = mysqli_query($this->conn, $q);
    }

    public function migrate()
    {

        Logger::info("Attempting to create database $this->database_name");
        try {
            $this->create_database();
            Logger::info("Database created successfully");
        } catch (Exception $e) {
            Logger::error("Exception: " . $e->getMessage());
        }
        // Switch to that database
        $useQuery = "USE $this->database_name";
        mysqli_query($this->conn, $useQuery);
        Logger::info("Selected database: $this->database_name");

        Logger::info("Creating a blueprint object");
        //First create tables
        Logger::info("Now creating the tables");
        $tables = $this->tables();
        foreach ($tables as $table) {
            $schema = $table->schema;
            try {
                $result = mysqli_query($this->conn, $schema);
                Logger::info("Success", array('query' => $schema, 'result' => $result, 'error' => mysqli_error($this->conn)));
            } catch (Exception $e) {
                Logger::error("Exception: " . $e->getMessage(), array('query' => $schema, 'error' => mysqli_error($this->conn)));
            }
        }
        Logger::info("Adding Constraints");
        //Now add constraints
        $alter_queries = $this->alters();
        foreach ($alter_queries as $query) {
            try {
                $result  = mysqli_query($this->conn, $query);
                Logger::info("Success", array('query' => $query, 'result' => $result, 'error' => mysqli_error($this->conn)));
            } catch (Exception $e) {
                Logger::error("Exception: " . $e->getMessage(), array('query' => $query, 'error' => mysqli_error($this->conn)));
            }
        }
        //Create views
        Logger::info("Now creating the views");
        $views = $this->views();
        foreach ($views as $view) {
            $schema = $view->schema;
            try {
                $result =  mysqli_query($this->conn, $schema);
                Logger::info("Success", array('query' => $schema, 'result' => $result, 'error' => mysqli_error($this->conn)));
            } catch (Exception $e) {
                Logger::error("Exception: " . $e->getMessage(), array('query' => $schema, 'error' => mysqli_error($this->conn)));
            }
        }
       
    }
}