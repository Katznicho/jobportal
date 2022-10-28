<?php   
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Database\DbAccess;
// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'run-query',
    description: 'Runs a query on a database or all databases.',
    hidden: false,
    aliases: ['app:run-query']
)]

class RunQueryCommand extends Command
{
    protected static $defaultName = 'run-query';
    protected static $defaultDescription = 'Runs a query on a database or all databases.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        
        $output->writeln("Connecting to manager database...");
        $manager_db = new DbAccess(MANAGER_DB);
        $output->writeln("Connected.");
        $query = $input->getArgument('query');
        if(strstr( strtolower( $query), "drop")){
            $output->writeln("You cannot drop tables using this command.");
            return Command::FAILURE;
        }
        if(strstr( strtolower( $query), "truncate")){
            $output->writeln("You cannot truncate tables using this command.");
            return Command::FAILURE;
        }
        $database = $input->getArgument('database');
        $output->writeln("Query: $query Database: $database");
        if($database){
            $output->writeln("Running query on $database...");
            try {
                $db = new DbAccess($database);
                $result = $db->sql($query);
                $output->writeln("Success: $result");
                return Command::SUCCESS;

            } catch (\Exception $e) {
                $output->writeln("Error: " . $e->getMessage());
                return Command::FAILURE;
            } 
        }
        else{
            $companies = $manager_db->select('company', [], ['active_flag'=>1,'del_flag'=>0]);
            $output->writeln("Running query on all databases...");
            foreach($companies as $company){
                try {
                    $output->writeln("Running query on {$company['name']}...");
                    $db = new DbAccess($company['Data_base']);
                    $result = $db->sql($query);
                    $output->writeln("Success: $result");
                    
                } catch (\Exception $e) {
                    $output->writeln("Error:".$e->getMessage());
                } 
            }
        }
        return Command::SUCCESS;
        
        
        return Command::SUCCESS;

       
    }
    // the command description shown when running "php bin/console list"

    // ...
    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'The query to run.')
            ->addArgument('database', InputArgument::OPTIONAL, 'The database to run the query on.')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to query databases in no time...');
    }
}
