<?php

namespace App\Command;

use Exception;
use App\Company\Company;
use App\Database\BluePrint\BluePrint;
use App\Database\DbAccess;
use App\Util\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'manager:add-units',
    description: 'Adds Units to a company',
    hidden: false,
    aliases: ['manager:add-units']
)]

class AddUnitsCommand extends Command
{
    protected static $defaultName = 'manager:add-units';
    protected static $defaultDescription = 'Adds units to a company';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager_db = new DbAccess(MANAGER_DB);
        $company_id = $input->getArgument('company_id');
        $units = $input->getArgument('units');
        $company = $manager_db->select('company', [], ['id' => $company_id]);
        $output->writeln("Checking for company if exists");
        if (!$company) {
            $output->writeln("Company Not Found");
            return Command::FAILURE;
        }
        $output->writeln("Company Exists");
        $output->writeln("Attempting to add units");
        $output->writeln("Preparing query to update units");
        $update_query = "UPDATE company set units=units+$units, total_units=total_units+$units WHERE id=$company_id";
        $output->writeln("Query ready: $update_query");
        $output->writeln("Querying database");
        try {
            $result = $manager_db->updateQuery($update_query);
            $output->writeln("Success Update Count: $result");
            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln("Exception Occurred: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
    protected function configure(): void
    {
        $this
            ->addArgument('company_id', InputArgument::REQUIRED, "The id of the company to add units to")
            ->addArgument('units', InputArgument::REQUIRED, "The number of units to give to the company")
            ->setHelp('This command allows you a manager to add units to a company');
    }
}
