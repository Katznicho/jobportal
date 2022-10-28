<?php

namespace App\Command;

use Exception;

use App\Database\DbAccess;
use App\Util\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'manager:list-companies',
    description: 'Lists available companies',
    hidden: false,
    aliases: ['manager:list-companies']
)]

class ListCompaniesCommand extends Command
{
    protected static $defaultName = 'manager:list-companies';
    protected static $defaultDescription = 'Lists the available companies in the system';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager_db = new DbAccess(MANAGER_DB);
        $companies = $manager_db->select('company',);
        $output->writeln("id ---> Name");
        foreach($companies as $company){
            $output->writeln($company['id']."  ---> ".$company['name']);
        }
        return Command::SUCCESS;
    }
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you a manager to view available companies');
    }
}
