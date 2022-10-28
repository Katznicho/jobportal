<?php

namespace App\Controllers;

use ClientActivation;
use App\Mailer\MyMail;
use App\Clients\Activators\MobileActivator;
use App\Clients\Activators\USSDActivator;
use App\Clients\Activators\WebActivator;
use App\Database\DbAccess;
use App\Util\ActivityLogger;
use App\Util\AppUtil;

class ClientActivationController
{
    /**
     * Activate clients for web, mobile and ussd
     */
    public static function activate($request)
    {
        $company = $_SESSION['company'];
        // access the manager db for the company id
        $manager_db = new DbAccess('ssenhogv_manager');
        $company = $manager_db->select('company', [], ['name' => $company])[0];
        $company_id = $company['id'];
        $database = $_SESSION['company_db'];
        // switch from manager db to the company database
        $db  = new DbAccess($database);
        $user_id = $request['user_id'];
        $user_email = $request['email'];
        $username = $request['username'];
        $activation = $request['activation'];
        $phone_number = $request['phonenumber'];
        $force = false;
        if (isset($_GET['force'])) {
            $force = true;
        }

        // A new mailer to send emails
        $mailer = new MyMail(false);


        $domain = $company["client_domain"];

        if (strlen($domain) < 3) {
            $domain = "client.ssentezo.com/client";
        }
        $domain .= "/";
        $server_username = ClientActivation::generateUsername($user_email, $company_id);
        $username = $server_username;
        switch ($activation) {
            case "ussd":

                $result = self::activateUSSD($db, $user_id, $phone_number, $company['name'], $company_id, $force);
                break;
            case "web":

                $result = self::activateWeb($db, $user_id, $user_email, $username, $company['name'], $mailer, $domain, $company_id, $force);

                break;
            case "mobile":
                $result = self::activateMobile($db, $user_id, $user_email, $username, $company['name'], $mailer, $force);
                break;

            default:
                $result = self::activateMobile($db, $user_id, $user_email, $username, $company['name'], $mailer, $force);
        }
        $error = $result['error'];
        $message = $result['message'];
        $status = $error ? "Failed" : "Success";
        ActivityLogger::logActivity(AppUtil::userId(), "Activate Client Login #$user_id", $status, $message);
        ClientActivation::sendFeedback($error, $message);
    }
    /**
     * Activate a client for ssentezo client web app
     */
    public static function activateWeb($db, $client_id, $client_email, $username, $company_name, $mailer, $domain, $company_id, $force)
    {
        $activator = new WebActivator($client_id, $client_email, $username, $company_name, $force);
        if ($activator->verify($db)) {
            return $activator->activate($db, $mailer, $domain, $company_id);
        } else {
            return $activator->getErrors();
        }
    }
    public static function activateUSSD($db, $client_id, $phone_number, $company_name, $company_id, $force)
    {
        $activator = new USSDActivator($client_id, $phone_number, $company_name, $force, $company_id);
        if ($activator->verify()) {
            return $activator->activate($db);
        } else {
            return $activator->getErrors();
        }
    }

    /**
     * Activate client for ssentezo mobile app
     */
    public static function activateMobile($db, $client_id, $client_email, $username, $company_name, $mailer, $force)
    {
        $activator = new MobileActivator($client_id, $client_email, $username, $company_name, $force);
        if ($activator->verify($db)) {
            return $activator->activate($db, $mailer);
        } else {
            return $activator->errors;
        }
    }
}
