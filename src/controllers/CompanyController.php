<?php

namespace App\Controllers;

use App\Company\Company;

class CompanyController
{
    public static function create_company()
    {
        if (isset($_POST['step']) && $_POST['step'] == 1 && isset($_POST['name'])) {
            $companyName = $_POST['name'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            $email = $_POST['email'];
            $senderId = $_POST['senderId'];
            //echo$fname;
            $company = new Company($companyName);
            // Set the company details
            $company->setEmail($email);
            $company->setSenderId($senderId);
            $company->setCompanyTel($phone);
            $company->setAddress($address);
            // Before doing anything first make sure there is company with
            // same name and database
            if ($company->checkIfCompanyExists()) {
                $message =  "Company already exists";
                $status = "failed";
            } else {
                // die();
                // Create the database for the company
                // The name and other details are handled internally by the compnay class
                // $company->createDatabase();

                // Register the company details
                $company_id = $company->registerCompany();

                $_SESSION['__company_id'] = $company_id; //Store a copy in the session for security perposes

                if (is_numeric($company_id)) {
                    $status = "success";
                    $message =  "Company Registered Successfully";
                } else {
                    $status = "failed";
                    // $message = "Failed with reason $result";
                }
            }
        }
    }
}
