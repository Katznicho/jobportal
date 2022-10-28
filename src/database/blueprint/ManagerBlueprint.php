<?php

namespace App\Database\BluePrint;

use mysqli;
use App\Database\BluePrint\Table;

class ManagerBlueprint extends BluePrint
{
    
    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
        $this->database_name = "ssenhogv_manager";
    }

    function tables()
    {
        return [

            new Table("CREATE TABLE `api_domains` (`id` int NOT NULL,  `url` varchar(255) DEFAULT NULL,  `platform` varchar(255) DEFAULT NULL COMMENT 'app or ussd or web', `type` varchar(255) DEFAULT NULL,  `del_flag` int DEFAULT NULL,  `active_flag` int DEFAULT NULL,  `description` varchar(255) DEFAULT NULL,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `applies_to` varchar(255) DEFAULT NULL) ENGINE=InnoDB;"),
            new Table("CREATE TABLE `company` (  `id` int NOT NULL,  `name` varchar(200) NOT NULL,  `senderId` varchar(10) DEFAULT NULL,  `Licence_type` varchar(255) NOT NULL DEFAULT 'Transactional',  `address` varchar(255) DEFAULT NULL,  `email` varchar(255) DEFAULT NULL,  `phone` varchar(255) DEFAULT NULL,  `Data_base` varchar(200) NOT NULL,  `unit_charge` int NOT NULL DEFAULT '100',  `units` int NOT NULL,  `total_units` int NOT NULL,  `sms_status` int NOT NULL,  `status` int NOT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0',  `payment_token` varchar(255) DEFAULT NULL,  `two_factor_auth_status` int NOT NULL DEFAULT '0',  `client_domain` text,  `main_domain` text,  `Active_Licence_Key` varchar(255) DEFAULT NULL,  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;"),
            new Table("CREATE TABLE `company_payments` (`id` int NOT NULL,  `company_id` int NOT NULL,  `date` date NOT NULL,  `amount` int NOT NULL,  `rate` int NOT NULL,  `units` int NOT NULL,  `paid_by` varchar(100) NOT NULL,  `channel` varchar(100) NOT NULL,  `account_no` varchar(100) NOT NULL DEFAULT ' ',  `transaction_reference` varchar(100) NOT NULL DEFAULT ' ',  `status_message` varchar(300) NOT NULL DEFAULT ' ',  `active_flag` int NOT NULL,  `del_flag` int NOT NULL) ENGINE=InnoDB "),
            new Table("CREATE TABLE `config` (  `id` int NOT NULL,  `name` text NOT NULL,  `value` text NOT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB;"),
            new Table("CREATE TABLE `demo_users` (  `id` int NOT NULL,  `name` varchar(200) NOT NULL,  `phone` varchar(200) NOT NULL,  `email` varchar(200) NOT NULL,  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `license` (  `id` int NOT NULL,  `companyid` int NOT NULL,  `license_token` varchar(255) NOT NULL,  `license_creation_date` date NOT NULL,  `status` int NOT NULL DEFAULT '1',  `license_expirely_date` date NOT NULL,  `packageid` int DEFAULT NULL,  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `del_flag` int NOT NULL DEFAULT '0',  `active_flag` int NOT NULL DEFAULT '1') ENGINE=InnoDB "),
            new Table("CREATE TABLE `logs` (  `id` int NOT NULL,  `ip_address` varchar(50) NOT NULL DEFAULT '127.0.0.1',  `date` datetime DEFAULT NULL,  `user_id` int DEFAULT NULL,  `activity` varchar(255) DEFAULT 'none',  `status` varchar(255) DEFAULT 'none',  `description` varchar(255) DEFAULT 'none',  `active_flag` int DEFAULT NULL,  `del_flag` int DEFAULT NULL,  `company_id` int DEFAULT NULL,  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `manager` (  `id` int NOT NULL,  `email` varchar(200) NOT NULL,  `password` varchar(200) NOT NULL) ENGINE=InnoDB "),
            new Table("CREATE TABLE `packages` (  `id` int NOT NULL,  `package_name` varchar(255) NOT NULL,  `number_of_users` int NOT NULL DEFAULT '0',  `price` decimal(10,0) NOT NULL,  `createdat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `updatedat` timestamp NULL ON UPDATE CURRENT_TIMESTAMP,  `status` int NOT NULL DEFAULT '0',  `del_flag` int NOT NULL DEFAULT '0',  `active_flag` int NOT NULL DEFAULT '1') ENGINE=InnoDB "),
            new Table("CREATE TABLE `platforms` (  `id` int NOT NULL,  `name` varchar(100) NOT NULL,  `description` varchar(100) NOT NULL,  `is_deleted` int NOT NULL DEFAULT '0',  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `reminders` (  `id` int NOT NULL,  `company_id` int NOT NULL,  `client_group` varchar(100) NOT NULL,  `action_type` varchar(100) NOT NULL,  `message_template` text,  `days_to_execute` text,  `period` varchar(100) DEFAULT NULL,  `cycle` varchar(100) DEFAULT NULL,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `is_deleted` int NOT NULL DEFAULT '0',  `user_id` int NOT NULL) ENGINE=InnoDB "),
            new Table("CREATE TABLE `reminder_logs` (  `id` int NOT NULL,  `reminder_id` int NOT NULL,  `log_message` text NOT NULL,  `error_message` varchar(200) NOT NULL,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `savings_disbursement_methods` (  `id` int NOT NULL,  `name` varchar(50) NOT NULL,  `description` varchar(50) DEFAULT NULL,  `created_by` varchar(50)  DEFAULT NULL,  `active_flag` varchar(50)  DEFAULT NULL,  `del_flag` varchar(50)  DEFAULT NULL,  `created_at` varchar(50)  DEFAULT NULL) ENGINE=InnoDB "),
            new Table("CREATE TABLE `savings_fees_cron_job` (  `id` int NOT NULL,  `company` int NOT NULL,  `savings_account_id` int NOT NULL,  `savings_fee_id` int NOT NULL,  `amount` double NOT NULL,  `date` varchar(100) NOT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB "),
            new Table("CREATE TABLE `savings_interest_cron_job` (  `id` int NOT NULL,  `company_id` int NOT NULL,  `savings_account_id` int NOT NULL,  `savings_product_id` int NOT NULL,  `amount` double NOT NULL,  `date` date NOT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB "),
            new Table("CREATE TABLE `staff` (  `id` int NOT NULL,  `email` varchar(200) NOT NULL,  `company` varchar(200) NOT NULL,  `createdon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `support_tickets` (  `id` int NOT NULL,  `company_id` int DEFAULT NULL,  `user_id` int DEFAULT NULL,  `created_at` text,  `status` text,  `token` text,  `active_flag` int DEFAULT '1',  `del_flag` int DEFAULT '0',  `title` text,  `details` text) ENGINE=InnoDB "),
            new Table("CREATE TABLE `ticket_comments` (  `id` int NOT NULL,  `ticket_id` int NOT NULL,  `comment` text NOT NULL,  `role` text NOT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0',  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `transaction_crons` (  `id` int NOT NULL,  `company_name` varchar(255) DEFAULT NULL,  `client_name` varchar(255) DEFAULT NULL,  `transaction_reference` varchar(255) DEFAULT NULL,  `amount` varchar(255) DEFAULT NULL,  `status` varchar(255) DEFAULT NULL,  `description` varchar(255) DEFAULT NULL,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `unique_number_prefixes` (  `id` int NOT NULL,  `prefix` text NOT NULL,  `company_id` int NOT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0',  `purpose` text NOT NULL,  `status` int NOT NULL DEFAULT '0') ENGINE=InnoDB "),
            new Table("CREATE TABLE `ussd_clients` (  `id` int NOT NULL,  `phone_number` varchar(255) NOT NULL,  `company_id` int NOT NULL,  `pin` text,  `created_at` text,  `pin_reset_token` text,  `user_id` int NOT NULL,  `otp_token` varchar(255) DEFAULT NULL,  `login_attempts` int NOT NULL DEFAULT '0',  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',  `is_otp_verified` tinyint(1) NOT NULL DEFAULT '0',  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB "),
            new Table("CREATE TABLE `wallets` (  `id` int NOT NULL,  `company_id` int NOT NULL,  `name` varchar(255) NOT NULL,  `provider` varchar(255) NOT NULL,  `username` varchar(255) NOT NULL,  `password` varchar(255) NOT NULL,  `status` varchar(255) NOT NULL,  `last_modified_at` date DEFAULT NULL,  `active_flag` int NOT NULL DEFAULT '1',  `del_flag` int NOT NULL DEFAULT '0') ENGINE=InnoDB")
        ];
    }
    function alters()
    {
        return [

            "ALTER TABLE `api_domains`ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `company`ADD PRIMARY KEY (`id`),ADD UNIQUE KEY `id` (`id`);",


            "ALTER TABLE `company_payments`ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `config`ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `demo_users`ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `license` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `logs` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `manager` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `packages` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `platforms` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `reminders` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `reminder_logs` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `savings_fees_cron_job` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `savings_interest_cron_job` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `staff` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `support_tickets` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `ticket_comments` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `transaction_crons` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `unique_number_prefixes` ADD PRIMARY KEY (`id`);",

            "ALTER TABLE `ussd_clients` ADD PRIMARY KEY (`id`),ADD UNIQUE KEY `phone_number` (`phone_number`);",

            "ALTER TABLE `wallets` ADD PRIMARY KEY (`id`);",


            "ALTER TABLE `api_domains` MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            "ALTER TABLE `company` MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            "ALTER TABLE `company_payments` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `config` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `demo_users` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `license` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `logs` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `manager` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `packages` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `platforms` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `reminders` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `reminder_logs` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_fees_cron_job` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `savings_interest_cron_job` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `staff` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `support_tickets` MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `ticket_comments`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `transaction_crons`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `unique_number_prefixes`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",
            "ALTER TABLE `ussd_clients`  MODIFY `id` int NOT NULL AUTO_INCREMENT;",

            "ALTER TABLE `wallets`  MODIFY `id` int NOT NULL AUTO_INCREMENT;"

        ];
    }
    function views()
    {
        return [];
    }
}
