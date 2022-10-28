<?php

namespace Ssentezo\Util;

use Ssentezo\Database\DbAccess;

class Feature
{
    public static function isActivated()
    {
    }
    /**
     *  Determines whether a company uses the given feature
     * @param DbAccess $db connection to the manager database
     * @return bool true if the accounting feature is in use, false otherwise
     * 
     */
    public static function usesAccounting($db)
    {

        if (isset($_SESSION['features'][ACCOUNTING_FEATURE])) {
            return $_SESSION['features'][ACCOUNTING_FEATURE];
        } else {

            $accounting_config = $db->select('config', [], ['name' => ACCOUNTING_FEATURE])[0];
            // print_r($accounting_config);
            $status = $accounting_config['value']; //0 means deactive and 1 means active

            if ($status == 1) {
                //Set the session for a faster check next time.
                $_SESSION['features'][ACCOUNTING_FEATURE] = true;
                return true;
            }
            //Set the session for a faster check next time.
            $_SESSION['features'][ACCOUNTING_FEATURE] = false;
            return false;
        }

        //$manageDb = new DbAccess("");
    }
    public static function getValue($db, $featureName)
    {
        $config = $db->select('config', [], ['name' => $featureName])[0];

        return $config['value'];
    }
    public static function setValue($db, $featureName, $value)
    {

        // First check if feature exists
        $config = $db->select('config', [], ['name' => $featureName]);
        $_SESSION['features'][$featureName] = $value;
        if ($config) { //Just update the feature as it's already there 
            $update = $db->update('config', ['value' => $value], ['name' => $featureName]);
            if (is_numeric($update)) {
                ActivityLogger::logActivity(AppUtil::userId(), "Toggle Feature: $featureName => $value", "success", "Update successfully");
            } else {
                ActivityLogger::logActivity(AppUtil::userId(), "Toggle Feature: $featureName => $value", "failed", "Update failed with reason $update");
            }
            return $update;
        } else { //Create the feature

            $insert_id = $db->insert('config', ['name' => $featureName, 'value' => $value]);
            if (is_numeric($insert_id)) {
                ActivityLogger::logActivity(AppUtil::userId(), "Toggle Feature: $featureName => $value", "success", "Feature Activated successfully");
            } else {
                ActivityLogger::logActivity(AppUtil::userId(), "Toggle Feature: $featureName => $value", "failed", "Activation failed with reason $insert_id");
            }

            return $insert_id;
        }
    }
    public static function usesLoans()
    {
        return true;
    }
    public static function usesSavings()
    {
        return true;
    }
}
