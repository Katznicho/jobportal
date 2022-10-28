<?php

namespace Ssentezo\Util;

class AutoRepair
{
    protected $newDbChanges = array(
        "ALTER TABLE `loan_installment_paid`  ADD `principal_installment` DOUBLE NULL  AFTER `amount`,  ADD `interest_installment` DOUBLE NULL  AFTER `principal_installment`;",
        "ALTER TABLE `general_ledger` ADD `is_gl` INT NULL DEFAULT '1' AFTER `trans_id`;",
        "ALTER TABLE `loan_product`  ADD `min_principal` FLOAT NULL  AFTER `current_month_accrual_account_id`,  ADD `max_principal` FLOAT NULL  AFTER `min_principal`,  ADD `default_principal` FLOAT NULL  AFTER `max_princiapal`;",
        "ALTER TABLE `loan_product` ADD `current_month_accrual_account_id` INT NULL AFTER `accrual_account_id`;",
        "ALTER TABLE `general_ledger` CHANGE `amount` `amount` FLOAT NOT NULL, CHANGE `balance` `balance` FLOAT NOT NULL;",
        "CREATE TABLE `ssenhogv_manager`.`config` ( `id` INT NOT NULL AUTO_INCREMENT ,  `name` TEXT NOT NULL ,  `value` TEXT NOT NULL ,  `active_flag` INT NOT NULL DEFAULT '1' ,  `del_flag` INT NOT NULL DEFAULT '0' ,    PRIMARY KEY  (`id`)) ENGINE = InnoDB;",
        "ALTER TABLE `loans` ADD `total_accrued` FLOAT NULL DEFAULT '0' AFTER `overriden_maturity_date`, ADD `last_accrued_on` DATETIME NULL AFTER `total_accrued`, ADD `is_accruable` INT NULL DEFAULT '0' AFTER `last_accrued_on`;",
        "ALTER TABLE `loan_applications` ADD `duration` INT NULL AFTER `approval_types`;",
        "ALTER TABLE `client_loan_product` ADD `min_duration` INT NULL AFTER `min_cancellations`, ADD `max_duration` INT NULL AFTER `min_duration`;",
        "ALTER TABLE `loan_product` ADD `main_account_id` INT NULL DEFAULT '0' AFTER `min_approvals`, ADD `interest_account_id` INT NULL DEFAULT '0' AFTER `main_account_id`, ADD `fees_account_id` INT NULL DEFAULT '0' AFTER `interest_account_id`, ADD `accrual_account_id` INT NULL DEFAULT '0' AFTER `fees_account_id`;",
        "ALTER TABLE `savings_account` CHANGE `fforzen_amount` `frozen_amount` DECIMAL(19,4) NULL DEFAULT NULL;"
    );
    public static $newQueries = array(
        "ALTER TABLE `make_deposit`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",
        "ALTER TABLE `clients` ADD `otp_token` VARCHAR(255) NULL DEFAULT NULL AFTER `account_activation_token`, ADD `login_attempts` INT NULL DEFAULT NULL AFTER `otp_token`, ADD `is_suspended` BOOLEAN NOT NULL DEFAULT FALSE AFTER `login_attempts`, ADD `phone_number` VARCHAR(255) NULL DEFAULT NULL AFTER `is_suspended`, ADD `password_change_attempts` VARCHAR(255) NULL DEFAULT NULL AFTER `phone_number`;", "ALTER TABLE `clients` ADD `change_password_otp` VARCHAR(255) NULL DEFAULT NULL AFTER `password_change_attempts`, ADD `change_password_expires_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `change_password_otp`, ADD `expires_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `change_password_expires_at`;", "ALTER TABLE `clients` ADD `token` VARCHAR(255) NULL DEFAULT NULL AFTER `expires_at`, ADD `is_otp_verified` BOOLEAN NOT NULL DEFAULT FALSE AFTER `token`;", "ALTER TABLE `borrower` ADD `ussd_phonenumber` VARCHAR(255) NULL DEFAULT NULL AFTER `staff_id`;",

        "ALTER TABLE `savings_fees` ADD `channel` VARCHAR(255) NULL DEFAULT NULL AFTER `updated_at`, ADD `mode_of_application` VARCHAR(255) NULL DEFAULT NULL AFTER `channel`, ADD `transaction_type` VARCHAR(255) NULL DEFAULT NULL AFTER `mode_of_application`;",
        "ALTER TABLE `savings_disbursement_methods` ADD PRIMARY KEY(`id`);",
        "ALTER TABLE `savings_disbursement_methods` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;"


    );
    public static $newTables = [];

    protected $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function diagnose()
    {
    }
    public function dbRepair()
    {
        // echo "We don't know";
        // die();
        // Try running all the new Db changes and see
        foreach ($this->newDbChanges as $query) {
            $this->db->selectQuery($query);
        }
    }
    public function isRepairSuccessful()
    {
    }
}
