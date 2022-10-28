<?php

namespace Ssentezo\Command;

use Exception;
use Ssentezo\Company\Company;
use Ssentezo\Database\BluePrint\BluePrint;
use Ssentezo\Database\DbAccess;
use Ssentezo\Mailer\MyMail;
use Ssentezo\Util\Auth;
use Ssentezo\Util\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'manager:create-company',
    description: 'Creates a new company',
    hidden: false,
    aliases: ['manager:create-company']
)]

class CreateCompanyCommand extends Command
{
    protected static $defaultName = 'manager:create-company';
    protected static $defaultDescription = 'Creates a new company';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager_db = new DbAccess(MANAGER_DB);
        $companyName = "Ssentezo Ada";
        $phone = "0771397135";
        $address = "Nalya Kyaliwajjala";
        $email = "admin@ssentezo.com";
        $senderId = '12345678';

        $company = new Company($companyName);
        $company->setEmail($email);
        $company->setSenderId($senderId);
        $company->setCompanyTel($phone);
        $company->setAddress($address);

        if ($company->checkIfCompanyExists()) {
            $output->writeln("Company " . $company->getCompanyName() . " already exists");
            return Command::FAILURE;
        }

        $company_id = $company->registerCompany();


        $co = $manager_db->select('company', [], ['id' => $company_id])[0];

        $status = $co['status'];
        if ($status != 0) {
            $output->writeln("Company already activated.");
            $output->writeln("Active Company, Possibly such a company already has a database");
            $output->writeln(" Exiting... ");
            return Command::FAILURE;
        }
        $conn = $company->getConnection();
        $companyName = $co['name'];
        $database = $co['Data_base'];
        $q = "CREATE DATABASE " . $database . "";
        $output->writeln("Running Query: " . $q);
        $ret = mysqli_query($conn, $q);

        $output->writeln("Query returned " . $ret);
        if ($ret) {

            $output->writeln("Switching to the created database");

            $useQuery = "USE $database";

            $output->writeln("Running Query " . $useQuery);

            mysqli_query($conn, $useQuery);

            $output->writeln("Switched to database $database");
            $output->writeln("Creating a database blueprint object");

            $blueprint = new BluePrint($conn);

            $output->writeln("Now creating the tables");

            $tables = $blueprint->tables();
            foreach ($tables as $table) {
                try {
                    $schema = $table->schema;
                    $output->writeln("Running query: $schema");
                    $result = mysqli_query($conn, $schema);
                    $output->writeln("Query Executed successfully. Return value " . $result);
                } catch (Exception $e) {
                    $output->writeln("What the hell, An Exception has occurred: " . $e->getMessage());
                }
            }

            $output->writeln("Now Adding Constraints");
            $alter_queries = $blueprint->alters();
            foreach ($alter_queries as $query) {
                try {
                    $output->writeln("Running query: $query");
                    $result = mysqli_query($conn, $query);
                    $output->writeln("Query Executed successfully. Return value " . $result);
                } catch (Exception $e) {
                    $output->writeln("What the hell, An Exception has occurred: " . $e->getMessage());
                }
            }

            //Create views
            Logger::info("Now creating the views");
            $views = $blueprint->views();
            foreach ($views as $view) {
                try {
                    $schema = $view->schema;
                    $output->writeln("Running query: $schema");
                    $result = mysqli_query($conn, $schema);
                    $output->writeln("Query Executed successfully. Return value " . $result);
                } catch (Exception $e) {
                    $output->writeln("What the hell, An Exception has occurred: " . $e->getMessage());
                }
            }
            // $query = "SELECT count(*) AS total FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_SCHEMA = '$dbName'";
        }


        $fname = "Ssentezo";
        $lname = "v1.0.0 Ada";
        $email = "admin@ssentezo.com";
        $gender = "male";
        $password = md5("@55ente20");

        $data = array("email" => "$email", "company" => "$company_id");
        try {
            $output->writeln("Adding staff to manager database...");
            $ret = $manager_db->insert("staff", $data);
            $output->writeln("Staff Added successfully");
        } catch (Exception $e) {
            $output->writeln("Failed to save staff to manager DB Reason: $ret");
        }

        try {
            $output->writeln("Getting connection to the company database");
            $db = new DbAccess($database);
            $output->writeln("Connection successful");
        } catch (Exception $e) {
            $output->writeln("Failed to connect to database :" . $e->getMessage());
        }
        $roles = ["View Another Branch", "Wallet Settings", "Add Wallet Details", "Delete Wallet Details", "Edit Wallet Details", "View Wallet Details", "Billing", "Admin", "Dashboard", "View Dashboard", "SMS Settings", "SMS Templates", "Sender Id", "SMS Credits", "Auto Send SMS", "Reminders", "SMS History", "Payroll Templates", "Admin Settings", "Account Settings", "Format Borrower Reports", "Purchase Units", "Email Settings", "Email Accounts", "Email Templates", "Auto Send Emails", "Other Income Types", "Delete Other Income Type", "Add Other Income Type", "Other Income Types", "View Other Income", "Add Other Income", "Custom Fields", "Edit Custom Field", "Add Custom Field", "Delete Custom Field", "Expense Types", "Expense Types", "Add Expense Type", "Edit Expense Type", "View Expenses", "Add Expense", "Staff", "Add Staff", "Edit Staff", "Reset Password", "Delete Staff", "Staff Roles", " Add Staff Role", "Edit Staff Roles Permissions", "Edit Staff Role", "Delete Staff Role", "Staff saving summery", "Collateral Types", "Add Collateral Type", "Edit Collateral Type", "Delete Collateral Type", "Savings Products", "Add Savings Product", "Edit Savings Product", "Delete Savings Product", "Savings Fees", "Add Savings Fee", "Edit Savings Fee", "Delete Savings Fee", "Loan Products", "Loan Products", "Add Loan Product", "Edit Loan Product", "Delete Loan Product", "Add Client Loan Product", "Edit Client Loan Product", "Delete Client Loan Product", "View Client Loan Product", "Loan Penalty Settings", "Edit Loan Penalty Settings", "Bulk Upload", "Bulk Add Clients", "Bulk Add Savings", "Bulk Add Repayments", "Loan Fees", "Add Loan Fee", "Edit Loan Fee", "Delete Loan Fee", "Loan Repayment Methods", "Loan Repayment Methods", "Add Loan Repayment Method", "Edit Loan Repayment Method", "Delete Loan Repayment Method", "Not Registered Collectors", "Not Registered Collectors", "Add Collector", "Edit Collector", "Delete Collector", "Not Registered Loan Officers", "Add Loan Officer", "Edit Loan Officer", "Delete Loan Officer", "Loan Disbursed By", "Add Loan Disbursed By", "Edit Loan Disbursed By", "Delete Loan Disbursed By", "Payroll", "View Payroll", "Add Payroll", "Home Branch", "Borrowers", "Send SMS to Borrower", "Send Email to Borrower", "Send Email to Borrower", "View Borrowers", "Edit Borrower", "Delete Borrower", "Add Borrower", "View Borrower Groups", "View Group Details", "Edit Borrowers Group", "Delete Borrowers Group", "Add Group Comments", "Edit Group Comments", " Delete Group Comments", "Add Borrowers Group", "Activate Client Login", "Send SMS to All Borrowers", "Send Email to All Borrowers", "Invite Borrowers", "Invite Borrowers", "Reset Borrower Password", "Delete Borrower Invitation", "View Savings", "Withdraw Requests", "View Withdraw Requests", "Approve Withdraw Request", "Cancel Withdraw Request", "Savings Transactions", "Add Transaction", "View Savings Transactions", "Transaction Report", "Loans", "View Loans Mode", "Download Active Loans Summary", "View Loans Borrower", "Add Loan", "View Loan Details", "Delete Loan", "Edit Loan", "Cancel Loan", "View Loan Applications", "Approve Loan Application", "Due Loans", "Missed Repayments", "Past Maturity Date", "1 Month Late Loans", "3 Months Late Loans", "Loan Calculator", "Repayments", "Add Repayments", "View Repayments", "Add Bulk Repayments", "Edit Loan Repayments", "Delete Loan Repayments", "Collateral Register", "Add Collateral", "Edit Collateral", "Delete Collateral", "Collection Sheets", "Daily Collection Sheet", "Missed Repayment Sheet", "Past Maturity Date Loans", "Send SMS", "Send Email", "Reports", "Collections Report", "Collector Report (Staff)", "Loan Products Report", "Cash flow", "Cash Flow Projection", "Deferred Income", "Profit \/ Loss", "MFRS Ratios", "Portfolio At Risk (PAR)", "All Entries", "Shares", "Add shares", "View shares", "View share transactions", "withdraw shares"];
        $output->writeln("Creating a super user role...");
        $data  = array(
            "name" => "Owner",
            "description" => "Has all the permissions",
            "creation_user" => "0",
            "actions" => json_encode($roles),
        );
        // Save the role and note it's id
        $insertId = $db->insert("staff_roles", $data);


        if (is_numeric($insertId)) {
            $output->writeln("Role created successfully");
            $password = "@55ente20";
            $data = array(
                "branch_id" => "1",
                "role_id" => "$insertId",
                "fname" => "$fname",
                "lname" => "$lname",
                "email" => "$email",
                "password" => md5($password),
                "gender" => "$gender",

            );

            try {
                $output->writeln("Creating staff for the company");
                $insertId = $db->insert("staff", $data);
                $output->writeln("Staff created successfully");
                $output->writeln("Activating company to allow login...");
                $updateCount = $manager_db->update("company",['status'=>1],['id'=>$company_id]);
                if(is_numeric($updateCount)){
                    $output->writeln("Company activated successfully");
                    $output->writeln("Congratulation!!! Here are the login details");
                    $output->writeln("Email: $email");
                    $output->writeln("Password: $password");
                    $output->writeln("Visit http://localhost/ssentezo/ or http://ssentezo.local/ssentezo");
                    $output->writeln("Enjoy!!!");

                }
                else{
                    $output->writeln("Failed to activate company: $updateCount");
                }
            } catch (Exception $e) {
                $output->writeln("Creating staff failed with message: " . $e->getMessage());
            }

        } else {
            
            $output->writeln(" Failed create role  reason $insertId");
        }




        return Command::SUCCESS;
    }
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you a manager to create a company');
    }
}
