<?php

namespace Ssentezo\Util;

class SecurityCheck
{
    /**
     * These are the scripts that don't need any session to access them.
     */
    private static $noPermissionScripts = array(
        "/login.php",
        "/ssentezo/index.php",
        "/ssentezo/login.php",
        "/ssentezo/admin-view.php",
        "/ssentezo/Manager_Login.php",
        "/enter_verification_code.php",
        "/ssentezo/staff/setEmail.php",
        "/ssentezo/passwordResetEmail.php",
        "/ssentezo/enter_verification_code.php",
        "/ssentezo/payroll/generate_payslip.php",
        '/ssentezo/admin/credit_purchase_failed.php',
        '/ssentezo/admin/credit_purchase_successful.php',
    );

    /**
     * Verifies if the current script is accessible without logging in
     * @return true|redirect to the login page. Returns true if it's accessible without logging in 
     * and automatically redirects to the login page otherwise.
     */
    public static function verifyNoPermissionScript()
    {

        $scriptName = $_SERVER['SCRIPT_NAME'];
        if (in_array($scriptName, static::$noPermissionScripts)) {
            return true;
        } else {
            self::redirectToLogin();
            exit;
        }
    }

    /**
     * Redirects to the login page
     */
    private static function redirectToLogin()
    {
        $login_script = "../index.php";
        if (file_exists($login_script)) {
            header("location: ../index.php");
        } else {
            header("location:../../index.php");
        }
    }

    /**
     * Verfies that the person accessing thescript has a session and is logged is is.
     */
    public static function verifySession()
    {
        if (!isset($_SESSION["email"]) || !isset($_SESSION['user_id']) || !isset($_SESSION["role"]) ||  !isset($_SESSION["fullname"]) || !isset($_SESSION["actions"]) || !isset($_SESSION["company"]) || !isset($_SESSION["company_db"])) {
            self::redirectToLogin();
        } else return true;
    }
    /**
     * Check if the currently logged in user has the permissions
     * @param array $permissions An array of the permissions to verify
     * @param string $mode (AND and OR are the available modes) AND means all permissions while OR means at least 1
     * @return bool true if user has all the provoded permissions and false otherwise
     */
    public static function verifyPermissions($permissions, $mode = 'and')
    {
        $user_permissions = $_SESSION['permissions'];
        if (!is_array($user_permissions)) {
            return false;
        }
        $intersection = array_intersect($permissions, $user_permissions);
        switch ($mode) {
            case 'AND':
            case 'and':

                if ($intersection == $permissions) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'OR':
            case 'or':
            case '':
                if (count($intersection) > 0) {
                    return true;
                }
                return false;
                break;
            default:
                return false;
        }
    }
    public static function VerifyAccessRights()
    {
    }
}
