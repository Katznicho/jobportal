<?php

namespace Ssentezo\Command;

use ClientActivation;
use Exception;
use Ssentezo\Company\Company;
use Ssentezo\Controllers\ClientActivationController;
use Ssentezo\Database\BluePrint\BluePrint;
use Ssentezo\Database\DbAccess;
use Ssentezo\Mailer\MyMail;
use Ssentezo\Util\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'manager:activate-clients',
    description: 'Activates clients for a certain company',
    hidden: false,
    aliases: ['manager:activate-clients']
)]

class ActivateClientsCommand extends Command
{
    protected static $defaultName = 'manager:activate-clients';
    protected static $defaultDescription = 'Activates clients for a company';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Logger::info("==========Bulk web activation -CLI==============");
        Logger::info("Connecting to manager...");
        $manager_db = new DbAccess(MANAGER_DB);
        Logger::info("Connected");
        Logger::info("Getting mailer...");
        $mailer = new MyMail();
        Logger::info("Success");
        Logger::info("Parsing Arguments...");
        $company_id = $input->getArgument('company_id');
        Logger::info("Company_id $company_id");

        $from  = $input->getArgument('from');
        Logger::info("From $from");

        $to = $input->getArgument('to');
        Logger::info("To $to");

        Logger::info("Selecting company info");

        $company = $manager_db->select('company', [], ['id' => $company_id])[0];
        Logger::info("Success");

        if (!$company) {
            Logger::error("Company information Not Found");

            $output->writeln("Company Not Found");
            return Command::FAILURE;
        }
        Logger::info("Company info found");

        $client_domain = "client.ssentezo.com/client/";
        Logger::info("Connecting to database");

        $db = new DbAccess($company['Data_base']);
        Logger::info("Connected");

        Logger::info("Preparing query");

        $sql  = "SELECT * from borrower where id>=$from and id<=$to";
        Logger::info("Query ready $sql");

        Logger::info("Running query");

        $users = $db->selectQuery($sql);
        Logger::info("Success");

        Logger::info("Processing user activations");

        $csv_headings = ["Name", "Email", "Status"];
        $csv_data = [];
        $csv_file_name = "client-activations-from-$from-to-$to.csv";
        foreach ($users as $user) {
            $full_name = $user['fname'] . " " . $user['lname'];
            Logger::info($full_name . "," . $user['email']);
            $user_id = $user['id'];
            $user_email = $user['email'];

            try {
                Logger::info("Generating Username");

                $username = generate_username($user_email, $company_id);
                Logger::info("Username generated $username");

                $domain = $client_domain;
                $force = false;
                Logger::info("Attempting to activate user for ssentezo web");

                $result = ClientActivationController::activateWeb($db, $user_id, $user_email, $username, $company['name'], $mailer, $domain, $company_id, $force);
                if ($result['error']) {
                    Logger::error("Error: " . $result['message']);
                } else {
                    Logger::error("Success: " . $result['message']);
                }
                $csv_data[] = [$full_name, $user_email, $result['error'] ? "Failed" : "Success", $result['message']];
                $output->writeln("Result: " . json_encode($result));
            } catch (Exception $e) {
                $csv_data[] = [$full_name, $user_email, "Failed", $e->getMessage()];

                $output->writeln("Exception " . $e->getMessage());
            }
        }
        Logger::info("Done with activating users");
        Logger::info("Attempting to generate a csv file");
        $file_name = write_csv($csv_file_name,$csv_headings,$csv_data);
        Logger::info("File save successfully: $file_name");
        $output->writeln("A summary of the results can be found at: https://app.ssentezo.com$file_name");
        
        return Command::SUCCESS;
    }
    protected function configure(): void
    {
        $this
            ->addArgument('company_id', InputArgument::REQUIRED, "The id of the company to activate clients for")
            ->addArgument('from', InputArgument::REQUIRED, "The starting id of the client")
            ->addArgument("to", InputArgument::REQUIRED, "The stopping id of the client")
            ->setHelp('This command allows you a manager to activate clients for web for a company');
    }
}
