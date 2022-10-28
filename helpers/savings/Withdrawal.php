<?php

namespace Ssentezo\Savings;



use Ssentezo\Database\DbAccess;
use Ssentezo\Util\AppUtil;
use Ssentezo\Util\SmsClass;
use Ssentezo\Util\ActivityLogger;
use Ssentezo\Payments\SsentezoWallet;
use Ssentezo\Savings\SavingsAccount;
use Ssentezo\Savings\CompleteTransaction;


class Withdrawal
{
  //function to withdraw money from a savings account
  public static function withdrawal_incomplete(){
    //variables that return data
    $statusCode;
    $message;
    $data;

    $username = $_POST['username'];
    $password = $_POST['password'];
    $amount = $_POST['amount'];
    $narrative = $_POST['reason'] ?? "";
    $transaction_ref = $_POST['transaction_ref'];
    $phoneNumber =  $_POST['phone_number'];
    $company_id = strtok($username, '@');
    $db = new DbAccess(MANAGER_DB);
    $company = $db->select('company', [], ['id' => $company_id])[0];
    $company_name=  $company['name'];
    if (!empty($company)) { //check if company exisits
        $database = $company['Data_base'];
        $db = new DbAccess($database);
        // Switch to the database of the company
        $hashed_password = $password;
        $client = $db->select('clients', [], ['username' => $username, 'password' => $hashed_password])[0];
        $user_id = $client['user_id'];
        $client = $db->select('borrower', [], ['id' => $user_id])[0];
        $client_names  = $client['fname'] . " " . $client['lname'];
        $email = $client['email'];
        
        $complete = new CompleteTransaction($database);
        $complete->onSuccess(
        "Withdrawal", 
        $amount,
        $client_names,
         $narrative,
          $user_id,
         $company_name, 
            $transaction_ref,
        "Mobile Money", 
        "app"
    );

        ActivityLogger::logActivity($user_id, "Send Money Confirmed", "success", "Client has sent money of amount  
        {$amount} to {$phoneNumber}");
        $statusCode = 200;
        $message = "success";
        $data = "money has been sent successfully";

    }
    else{
        $statusCode = '500';
        $message = 'failure';
        $data = "We couldn't understand your request";
    }  
    
    return sendResponse($statusCode, $message, $data);

  }

  public static function  complete_withdrawal(){

    $username = $_POST['username'];
    $password = $_POST['password'];
    $amount = $_POST['amount'];
    // $narrative = $_POST['reason'] ?? "";
    $transaction_ref = $_POST['transaction_ref'];
    $phoneNumber =  $_POST['phone_number'];
    $company_id = strtok($username, '@');
    $db = new DbAccess(MANAGER_DB);
    $company = $db->select('company', [], ['id' => $company_id])[0];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $amount = $_POST['amount'];
    $narrative = $_POST['reason'] ?? "";
    $transaction_ref = $_POST['transaction_ref'];
    $phoneNumber =  $_POST['phone_number'];
    $company_id = strtok($username, '@');
    $db = new DbAccess(MANAGER_DB);
    $company = $db->select('company', [], ['id' => $company_id])[0];
    $company_name=  $company['name'];



    if (!empty($company)) { //check if company exisits
        $database = $company['Data_base'];
        $db = new DbAccess($database);

        // Switch to the database of the company
        $hashed_password = $password;
        $client = $db->select('clients', [], ['username' => $username, 'password' => $hashed_password])[0];
        
        if ($client) { //if client exisits
            $user_id = $client['user_id'];
            $client = $db->select('borrower', [], ['id' => $user_id])[0];
            $client_names  = $client['fname'] . " " . $client['lname'];
            $email = $client['email'];
            $savingsAccount = new SavingsAccount($db, $user_id);
            $complete = new CompleteTransaction($database);
            // check if there is no pending transaction and there is enough money
            

            if ($savingsAccount->findTransactionByReferenceNumber($db, $transaction_ref) and $savingsAccount->canWithdraw($amount)) {
                $wallet = new SsentezoWallet(MANAGER_DB, $company_id);
                $details = $savingsAccount->findTransactionByReferenceNumber($db, $transaction_ref)[0];
                $transaction_narrative = $details['narrative'];
                $narrative =  $transaction_narrative;
                
                $ret = $wallet->withdraw($phoneNumber, $amount, $narrative, $transaction_ref, $db);

                $manager_db = new DbAccess("ssenhogv_manager");
                $res = $manager_db->insert("wallet_logs", [
                    'company_name'=>$company_name ,
                 "details"=>$ret->code, 
                "description"=>"Wallet Response"]);

                
                if ($ret->code == 202) {

                    $database = $company['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                    $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'Completed', 'Payment process completed');
                    $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'Completed');

                    //send sms to client
                     ActivityLogger::logActivity($user_id, "Send Money Confirmed", "success", "Client has sent money of amount 
                        {$amount} to {$phoneNumber}");
                    
                    $statusCode = 200;
                    $message = "success";
                    $data = "money sent successfully";
                    return sendResponse($statusCode, $message, $data);
                } else if ($ret->code == 400) {

                    $database = $company['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                    $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'An error occured');
                    $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                    $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                                            //send sms to client
                     ActivityLogger::logActivity($user_id, "Send Money Confirmed", "Failure", 
                                            "Client has failed to send money of amount 
                                            {$amount} to {$phoneNumber}");
                                         
                    
                    $statusCode = 400;
                    $message = "failure";
                    $data = "Withdrawal failed";
                    return sendResponse($statusCode, $message, $data);
                } elseif ($ret->code == 403) {
                      
                    $database = $company['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);


                    $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'Insufficient Funds');
                    $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                    $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                    //send sms to client
                    ActivityLogger::logActivity($user_id, "Send Money Confirmed", "Failure", 
                    "Client has failed to send money of amount 
                    {$amount} to {$phoneNumber}");

                    //send message to client
                    $statusCode = 500;
                    $message = "failure";
                    $data = "Invalid wallet credentails";
                    return sendResponse($statusCode, $message, $data);
                } elseif ($ret->code == 500) {
                    $database = $company['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                    $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'Insufficient Funds');
                    $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                    $statusCode = 500;
                    $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                     ActivityLogger::logActivity($user_id, "Send Money Confirmed", "Failure", 
                    "Client has failed to send money of amount 
                    {$amount} to {$phoneNumber}");
                    $message = "failure";
                    $data = "Insufficient funds";
                    return sendResponse($statusCode, $message, $data);
                } else {
                    $database = $company['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                    $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'An error occured');
                    $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                    $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                     ActivityLogger::logActivity($user_id, "Send Money Confirmed", "Failure", 
                    "Client has failed to send money of amount 
                    {$amount} to {$phoneNumber}");

                    $statusCode  = 501;
                    $message = "failure";
                    $data = "Something went wrong please try again later";
                    return sendResponse($statusCode, $message, $data);
                }
            }
        } else {
            $statusCode = '500';
            $message = "failure";
            $data = "Wrong Username or Password";
            return sendResponse($statusCode, $message, $data);
        }
    } else {

        $statusCode = 401;
        $message = "failure";
        $data = "User not found. Make sure you send the correct username";
        return sendResponse($statusCode, $message, $data);
    }

    

  }

  public static function withdrawal_incomplete_usdd(){
  
    $amount = $_POST['amount'];
    $narrative = $_POST['reason'];
    $transaction_ref = $_POST['transaction_ref'];
    $phoneNumber =  $_POST['phone_number'];
     $recipient = $_POST['recipient'];
     $pin = $_POST['pin'];
    
    $db = new DbAccess(MANAGER_DB);
    $user_details = $db->select('ussd_clients', [], ['pin' =>md5($pin) , 'phone_number'=>$phoneNumber]);


    
    $company_id = $user_details[0]['company_id'];
    $company_details = $db->select('company', [], ['id' => $company_id])[0];

    if (count($user_details)) { 
        $user_id = $user_details[0]['user_id'];
       $database = $company_details['Data_base'];
       $company_name = $company_details['name'];
       
       $db = new DbAccess($database);
        $client = $db->select('borrower', [], ['id' => $user_id])[0];
        $names = $client['fname']. " " .$client['lname'];
        $savingsAccount = new SavingsAccount($db, $user_id);
        $complete = new CompleteTransaction($database);
        
        $complete->onSuccess("Withdrawal", $amount,$names, $narrative, $user_id, $company_name, $transaction_ref, 
        "Money Sent Successfully", "ussd");
        ActivityLogger::logActivity($user_id, "Send Money Confirmed", "success", "Client has sent money of amount 
         {$amount} to {$recipient}");
        $statusCode = 200;
        $message = "success";
        $data = "money sent successfully";
        return sendResponse($statusCode, $message, $data);
    } else {
        $statusCode = 401;
        $message = "failure";
        $data = "User not found. Make sure you send the correct username";
        return sendResponse($statusCode, $message, $data);
    }       


  }
    public static function withdrawal_complete_usdd(){



        $amount = $_POST['amount'];
    $narrative = "client withdrawal";
    $transaction_ref = $_POST['transaction_ref'];
    $phoneNumber =  $_POST['phone_number'];
     $recipient = $_POST['recipient'];
     $pin = $_POST['pin'];
    $db = new DbAccess(MANAGER_DB);
    $user_details = $db->select('ussd_clients', [], ['pin' =>md5($pin) , 'phone_number'=>$phoneNumber]);
    $company_id = $user_details[0]['company_id'];
    $company_details = $db->select('company', [], ['id' => $company_id])[0];
        

         if (count($user_details)) { 
            $user_id = $user_details[0]['user_id'];
           $database = $company_details['Data_base'];
           $company_name = $company_details['name'];
           $db = new DbAccess($database);
               $client = $db->select('borrower', [], ['id' => $user_id])[0];
               $names = $client['fname']. " " .$client['lname'];
               $savingsAccount = new SavingsAccount($db, $user_id);
             
               
               // check if there is no pending transaction and there is enough money
               if ($savingsAccount->findTransactionByReferenceNumber($db, $transaction_ref)) {
               
                   //switch to the wallet of the company
               
                   $wallet = new SsentezoWallet(MANAGER_DB, $company_id);
                   $ret = $wallet->withdraw($recipient, $amount, $narrative, $transaction_ref, $db);
                   
                   $manager_db = new DbAccess("ssenhogv_manager");
                   $res = $manager_db->insert("wallet_logs", [
                       'company_name'=>$company_name ,
                    "details"=>$ret->code, 
                   "description"=>"Wallet Response"]);
                   if ($ret->code == 202) {
                    $database = $company_details['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);
                    $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'Completed', 'Payment process completed');
                    $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'Completed');
                     ActivityLogger::logActivity($user_id, "Send Money Confirmed", "success", "Client has sent money of amount 
                        {$amount} to {$recipient}");
                    
                       $statusCode = 200;
                       $message = "success";
                       $data = "money sent successfully";
                   } else if ($ret->code == 400) {

                      $database = $company_details['Data_base'];
                      $db = new DbAccess($database);
                      $complete = new CompleteTransaction($database);

                       $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'An error occured');   
                       $res = $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                       
                    
                        $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                          
                       $statusCode = 500;
                       $message = "failure";
                       $data = "with draw failed";

                   } elseif ($ret->code == 403) {

                    $database = $company_details['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                       $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'Insufficient Funds');
                       $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                        $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                               
                       $statusCode = 500;
                       $message = "failure";
                       $data = "with draw failed";
                   } elseif ($ret->code == 500) {
                    $database = $company_details['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                       $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'Insufficient Funds');
                       $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                        $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);           
                       $statusCode = 500;
                       $message = "failure";
                       $data = "Insufficient funds";
                   } else {
                    $database = $company_details['Data_base'];
                    $db = new DbAccess($database);
                    $complete = new CompleteTransaction($database);

                       $ret = $savingsAccount->initWithdraw($db, $transaction_ref, 'failure', 'An error occured');
                       $savingsAccount->updateWithDrawTable($db, $transaction_ref, $amount, 'failure');
                        $complete->onFailure("Withdrawal", $amount, $transaction_ref, $user_id, $phoneNumber);
                                
                       $statusCode  = 501;
                       $message = "failure";
                       $data = "Something went wrong please try again later";
                   }
               }
               else{
                   $statusCode = 500;
                   $message = "failure";
                   $data = "No transaction found";
               }
           
       } else {
   
           $statusCode = 401;
           $message = "failure";
           $data = "User not found. Make sure you send the correct username";
       }
         return sendResponse($statusCode, $message, $data);

    
    }

  

  
    
}