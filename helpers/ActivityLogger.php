
<?php
// include "DbAcess.php";
class ActivityLogger
{
    /**
     * Wrapper for obtaining the ip address
     */
    private static function getIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    private static function compnayId()
    {
        $db = new DbAcess('ssenhogv_manager');
        if (isset($_SESSION['company'])) {
            $companyName = $_SESSION['company'];
            $compnay = $db->select('company', [], ['name' => $companyName]);
            return $compnay[0]['id'];
        } else {
            return 0;
        }
    }
    private static function getScriptName()
    {
        return $_SERVER['SCRIPT_FILENAME'];
    }
    private static function getCurrentTimestamp()
    {
        return date('Y-m-d h:m:s a');
    }
    public static function logActivity($user_id, $activity, $status, $description)
    {

        $db = new DbAcess('ssenhogv_manager');

        $ip_address = static::getIpAddress();

        $date = date('Y-m-d h:i:s');
        $compnay_id = static::compnayId();



        $ret = $db->insert('logs', [
            'date' => $date,
            "company_id" => $compnay_id,
            "ip_address" => $ip_address,
            'user_id' => $user_id,
            'activity' => $activity,
            'status' => $status,
            'description' => $description,
            'active_flag' => "1",
            'del_flag' => "0",

        ]);
    }
}
