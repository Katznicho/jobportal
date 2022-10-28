<?php

namespace Ssentezo\Command;

use Exception;
use mysqli;
use Ssentezo\Database\BluePrint\ManagerBlueprint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'manager:create-database',
    description: 'Creates the ssentezo manager database.',
    hidden: false,
    aliases: ['manager:migrate']
)]

class MigrateManagerCommand extends Command
{
    protected static $defaultName = 'manager:create-database';
    protected static $defaultDescription = 'Creates the ssentezo manager database';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {

            $host = "localhost";
            $user = "root";
            $pass = "!Log19tan88";
            $manager_database = 'ssenhogv_manager';
            $conn = new mysqli($host, $user, $pass);
            $blueprint = new ManagerBlueprint($conn);
            $blueprint->migrate();
            $output->writeln("Migration Successful");
            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->write($e->getMessage());
            return Command::SUCCESS;
        }
    }
    // the command description shown when running "php bin/console list"

    // ...
    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command creates the ssentezo manager database');
    }
}
