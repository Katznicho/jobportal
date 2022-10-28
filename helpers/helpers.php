<?php

use Ssentezo\Billing\Billing;

class HelperFunctions
{

    public function checkEmptyFields($field)
    {
        //die("The field is " . $field);
        if (empty($field)) {

            return $field . "field is required";
        } else return NULL;
    }

    public function checkDesiredLength($data, $desiredLength, $field)
    {
        if (strlen($data) < $desiredLength) {
            return "The " . $data . " must be greater than " . $desiredLength . "characters";
        } else {
            return false;
        }
    }

    public function checkEmail($email)
    {
        //$emailRegex = /^(('[\w - \s] + ')|([\w-]+(?:\.[\w-]+)*)|('[\w - \s] + ')([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i;

        $newEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return $newEmail;
        } else {
            return NULL;
        }
    }



    public function confirmPassword($pass1, $pass2)
    {
        if (strcmp($pass1, $pass2) == 0) {
            return true;
        } else {
            return false;
        }
    }
    public function checkPassword($password)
    {
        $passwordRegex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$/';
        if (preg_match($password, $passwordRegex)) {
            return true;
        } else {
            return "Passsword must contain both lower case and upper case letters";
        }
    }

    public function checkNumber($number)
    {
        //die("am dying" . $number);
        //$numberRegex = "/^[0-9][0-9]$/";
        if (preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $number) && strlen($number) == 10) {
            return  "phone number is valid";
        } else {
            return NULL;
        }
        # code...
    }

    //generate random number based on current time
    public function randomNumber($low, $high)
    {
        return time() * rand($low, $high);
    }

    //get unique number
    private static function getuniquenumber()
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'purpose' => 'client', 'company_id' => AppUtil::companyId(), 'status' => 1]);

        if (count($results) == 0) {
            return "ABC";
        } else {
            return $results[0]['prefix'];
        }
    }
    private static function getGroupPrefixText()
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'purpose' => 'group', 'company_id' => AppUtil::companyId(), 'status' => 1]);

        if (count($results) == 0) {
            return "ABC";
        } else {
            return $results[0]['prefix'];
        }
    }

    public static function getClientPrefix($all = '')
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'purpose' => 'client', 'company_id' => AppUtil::companyId()]);

        if (count($results) == 0) {
            return "ABC";
        } else if ($all) {
            return $results;
        } else {
            return $results[0]['prefix'];
        }
    }
    public static function getPrefixById($id)
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'company_id' => AppUtil::companyId(), 'id' => $id]);
        return $results[0];
    }
    public static function getClientPrefixById($id)
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'purpose' => 'client', 'company_id' => AppUtil::companyId(), 'id' => $id]);
        return $results[0];
    }
    public static function deletePrefix($prefixId)
    {
        $db = new DbAcess('ssenhogv_manager');

        $updateId =   $db->update('unique_number_prefixes', ['active_flag' => 0, 'del_flag' => 1], ['id' => $prefixId]);
        if (is_numeric($updateId)) {
            return true;
        } else {
            return false;
        }
    }
    public static function activatePrefix($prefixId, $purpose)
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $db->update('unique_number_prefixes', ['status' => 0], ['company_id' => AppUtil::companyId(), 'purpose' => $purpose]);
        $updateId = $db->update('unique_number_prefixes', ['status' => 1], ['id' => $prefixId, 'company_id' => AppUtil::companyId(), 'purpose' => $purpose]);

        if (is_numeric($updateId)) {
            return true;
        } else {
            return false;
        }
    }
    public static function deactivatePrefix($prefixId, $purpose)
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        // $updateId =   $db->update('unique_number_prefixes', ['status' => 0], ['company_id' => AppUtil::companyId(), 'purpose' => $purpose]);
        $updateId = $db->update('unique_number_prefixes', ['status' => 0], ['id' => $prefixId, 'company_id' => AppUtil::companyId(), 'purpose' => $purpose]);

        if (is_numeric($updateId)) {
            return true;
        } else {
            return false;
        }
    }
    public static function getGroupPrefix($all = '')
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'purpose' => 'group', 'company_id' => AppUtil::companyId()]);

        if (count($results) == 0) {
            return "ABC";
        } else if ($all) {
            return $results;
        } else {
            return $results[0]['prefix'];
        }
    }
    public static function getAllPrefixes()
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $results =  $db->select('unique_number_prefixes', [], ['active_flag' => 1, 'del_flag' => 0, 'company_id' => AppUtil::companyId()]);


        return $results;
    }

    public static function savePrefix($prefix, $purpose, $status)
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $data = array(
            'prefix' => $prefix,
            'purpose' => $purpose,
            'company_id' => AppUtil::companyId(),
            'status' => $status

        );
        $insertId =  $db->insert('unique_number_prefixes', $data);
        if ($status) {
            static::activatePrefix($insertId, $purpose);
        }

        if (is_numeric($insertId)) {
            return true;
        } else {
            return false;
        }
    }

    public static function editPrefix($prefix, $purpose, $status, $prefixId)
    {
        $db = new DbAcess('ssenhogv_manager');
        // $results = $db->selectQuery($sql);
        $set = array(
            'prefix' => $prefix,
            'purpose' => $purpose,
            'company_id' => AppUtil::companyId(),
            'status' => $status

        );
        $insertId =  $db->update('unique_number_prefixes', $set, ['id' => $prefixId]);
        if ($status) {
            static::activatePrefix($insertId, $purpose);
        } else {
            static::deactivatePrefix($prefixId, $purpose);
        }

        if (is_numeric($insertId)) {
            return true;
        } else {
            return false;
        }
    }

    //get unique number

    //get last client id
    private static function getlastclientid()
    {
        $sql = "SELECT id FROM borrower ORDER BY id DESC LIMIT 1;";
        $db = new DbAcess();
        $results = $db->selectQuery($sql);
        if (count($results) == 0) {
            return intval(1);
        } else {
            return  intval($results[0]['id']) + 1;
        }
    }

    private static function getlastGroupId()
    {
        $sql = "SELECT id FROM 	borrowers_group ORDER BY id DESC LIMIT 1;";
        $db = new DbAcess();
        $results = $db->selectQuery($sql);
        if (count($results) == 0) {
            return intval(1);
        } else {
            return  intval($results[0]['id']) + 1;
        }
    }
    //get last client id

    public static function generatUniqueNumber()
    {
        $id = static::getlastclientid();
        $num = static::getuniquenumber();
        return $num . $id;
    }
    public static function generateGroupUniqueNumber()
    {
        // die();
        $id = static::getlastGroupId();
        $num = static::getGroupPrefixText();
        return $num . $id;
    }
}


/**
 * Sends a json response
 * @param string $statusCode The http status code i.e 200
 * @param string $message A summarized message e.g success, failed
 * @param array $data The data or detailed message 
 */
function sendResponse($statusCode, $message, $data)
{
    echo json_encode(
        array(
            "statusCode" => $statusCode,
            "message" => $message,
            "data" => $data
        )
    );
    die();
}


function default_format_phone($mobile)
{
    $length = strlen($mobile);
    $m = '0';
    //format 1: +256752665888
    if ($length == 13)
        return $m .= substr($mobile, 4);
    elseif ($length == 12)
        return $m .= substr($mobile, 3);
    elseif ($length == 9)
        return $m .= $mobile;

    return $mobile;
}

/**
 * Save an uploaded file
 */
function save_file($tempLocation, $fileName)
{
    $relativePath = "/uploads/allfiles/" . time() . "_" . basename($fileName);
    $target_file = dirname(__DIR__) . $relativePath;

    $ret = move_uploaded_file($tempLocation, $target_file);

    if ($ret)
        return $relativePath;
    return false;
}
function generate_username($userEmail, $company_id)
{
    $first_email_part = strtok($userEmail, '@');
    $server_username = $company_id . '@' . $first_email_part;
    return $server_username;
}
function write_csv($fileName, $headings, $data)
{
    $relativePath = "/uploads/allfiles/" . time() . "_" . basename($fileName);
    $target_file = dirname(__DIR__) . $relativePath;

    $f = fopen($target_file, 'w');
    fputcsv($f, $headings);
    foreach ($data as $row) {
        fputcsv($f, $row);
    }
    fclose($f);
    return $relativePath;
}

/**
 * Reduce one unit from a company
 * This is a wrapper method that used the underlying implementation by the Billing class to reduce units
 * @param int $company_id The id of the company
 */
function one_unit_less($company_id){
   return  Billing::one_less_unit($company_id);
}
