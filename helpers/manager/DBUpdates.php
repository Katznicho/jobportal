<?php

namespace Ssentezo\Manager;

use Ssentezo\Database\DbAccess;

class DBUpdates
{
    /**
     * The database connection to the manager database.
     * @var $db DbAccess 
     * 
     */
    public static $db;

    /**
     * This is the sql to create the table in case it doen't exist
     */
    public static $table_sql = "CREATE TABLE IF NOT EXISTS `database_changes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `query` varchar(10000) NOT NULL,
            `created_by` varchar(255) NOT NULL,
            `ran_by` varchar(255) NOT NULL,
            `updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
            `status` varchar(255) NOT NULL,
            `active_flag` varchar(255) NOT NULL,
            `del_flag` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    /**
     * Get all the that haven't been deleted and are active
     *
     */
    public static function getDBChanges()
    {
        self::init(); //Ensure the database connection is set
        $db = new DbAccess('ssenhogv_manager');
        $changes = $db->select('database_changes', [], ['active_flag' => 1, 'del_flag' => 0, 'order by' => 'id DESC']);
        return $changes;
    }

    /**
     * Ensure that the table exists
     *
     */
    public static function init()
    {
        if (!self::$db) {
            self::$db = new DbAccess('ssenhogv_manager');
        } else {
            self::$db->switchDb('ssenhogv_manager');
        }
        self::$db->sql(self::$table_sql); //Always try out this query to ensure that the table exists
        return self::$db;
    }

    /**
     * Add a new database change
     */
    public static function addQuery($query)
    {
        self::init(); //Ensure we are connected to the manager database
        self::$db->insert('database_changes', ['query' => $query, 'created_by' => 'System', 'ran_by' => 'System', 'status' => 'pending', 'active_flag' => 1, 'del_flag' => 0]);
    }
    public static function runQuery($query, $database)
    {
        self::init()->switchDb($database);
        self::$db->sql($query);
    }
    public static function runPendingQueries($databases)
    {
        $changes = self::getDBChanges(); //Get all the pending changes
        if (is_array($databases)) {
            foreach ($databases as $database) {
                foreach ($changes as $change) {
                    self::runQuery($change['query'], $database);
                    self::$db->update('database_changes', ['status' => 'ran'], ['id' => $change['id']]);
                }
            }
        }
    }

    public static function runPendingQueriesForCompany($company_database)
    {
        $changes = self::getDBChanges();
        foreach ($changes as $change) {
            $query = $change['query'];
            self::runQuery($query, $company_database);
            self::$db->update('database_changes', ['status' => 'done'], ['id' => $change['id']]);
        }
    }
}
?>
ALTER TABLE `savings_fees` ADD PRIMARY KEY(`id`);
ALTER TABLE `savings_fees` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;