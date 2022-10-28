<?php
class BulkUpload
{
    private static $clientsUploadFile = "";
    private static $depositsUploadFile = "";
    private static $loanRepaymentsUploadFile = "";
    private static $clientUploadFileHeadings = array(
        "Unique Number",
        "Title",
        "First Name",
        "Middle/Last Name",
        "Gender",
        "Country",
        "Mobile",
        "Email",
        "Date of Birth",
        "Address",
        "District",
        "Subcounty",
        "Village",
        "Landline",
        "Business Name",
        "Working Status",
        "Description",

    );

    private static $borrowerTableMappings = array(
        "Unique Number" => "unique_no",
        "Title" => "title",
        "First Name" => "fname",
        "Middle/Last Name" => "lname",
        "Gender" => "gender",
        "Country" => "country",
        "Mobile" => "mobile_no",
        "Email" => "email",
        "Date of Birth" => "dob",
        "Address" => "address",
        "District" => "district",
        "Subcounty" => "subcounty",
        "Village" => "village",
        "Landline" => "landline",
        "Business Name" => "bussiness_name",
        "Working Status" => "working_status",
        "Description" => "description",

    );

    private static $savingsUploadFileHeadings = array(
        "id",
        "Unique Number",
        "Full Name",
        "Date",
        "Time",
        "Amount",
        // "Transaction Fees",
        "Description",
        "Deposited By"

    );
    private static $savingsSummaryFileHeadings = array(
        "Unique Number",
        "Full Name",
        "Phone",
        "Email",
        "Account Balance",

    );

    private static $repaymentsUploadFileHeadings = array(
        "Loan Id",
        "Loan Name",
        "Client Name",
        "Collection Date",
        "Date",
        "Amount",
        "Deposited By",
        "Description",

    );
    public static $balanceSeetTableMappings = array(
        "Loan Id" => 'field1',
        "Loan Name" => 'loan_name',
        "Client Name" => 'full_name',
        "Collection Date" => 'reg_date',
        "Date" => 'creation_date',
        "Amount" => 'amount',
        "Deposited By" => 'deposited_by',
        "Description" => 'description',
    );
    private static $savingsTransactionTableMappings = array(
        "id" => "id",
        "Unique Number" => "unique_no",
        "Full Name" => "fullname",
        "Time" => "transaction_time",
        "Date" => "transaction_date",
        "Amount" => "amount",
        // "Fees" => "transaction_fees",
        "Description" => "description",

        "Deposited By" => "deposited_by"
    );
    public static $loansSummaryFileHeadings = array(
        "UniqueNo",
        "Client Name",
        "Mobile",
        "Loan Product",
        "Principal",
        "Interest",
        "Total Due",
        "Paid",
        "Balance"
    );
    public static $allLoansSummaryFileHeadings = array(
        "UniqueNo",
        "Client Name",
        "Mobile",
        "Loan Product",
        "Principal",
        "Interest",
        "Total Due",
        "Paid",
        "Balance",
        "Maturity Date",
        "Status"
    );
    public static $repaymentsSummaryFileHeadings = array(
        "UniqueNo",
        "Client Name",
        "Mobile",
        "Loan Product",
        "Amount Paid",
        "Collection Date",
        "Paid On",
        "Paid By",
        "Collected By"
    );
    /**
     * @param string $fileName The name of the csv file containing the client's data.
     * @return mixed $clientsData | $error  Returns error true if there is invalid data
     * 
     */
    public static function verifyClientsFile($fileName)
    {
        $result = static::fetchAssocFromCsvFile($fileName, static::$borrowerTableMappings);
        return $result;
    }
    public static function saveToBorrowersTable($db, $clientsData, $staffId)
    {
        $table = 'borrower';
        $errors = false;
        // Array to hold exixting clients in case they are found in the process
        // of inseting into the database.
        $exists = array();
        // Failed insertions may be due to database errors, maybe invalid data
        // or missing required data.
        $failed = array();
        // Successful insertions will be stored here
        // With these three arrays we can have clear feedback 
        // at the end of the process.
        $success = array();
        // Specifically for invalid data.
        $invalid = array();
        // This will hold the data temporarily before it's inserted into the database
        $data = array();

        foreach ($clientsData as $clientData) {
            // echo json_encode($clientData);
            // die();
            // error_reporting(1);
            // initiallize data to empty for every iteration
            $data = [];
            $clientData['dob'] = str_replace("/", '-', $clientData['dob']);
            $formatedPhone = static::formatPhoneNumber($clientData['mobile_no']);
            $fname = '';
            $lname = '';
            if (strlen($clientData['lname']) > 2) {
                $fname = $clientData['fname'];
                $lname = $clientData['lname'];
            } else {
                $fullname = $clientData['fname'];
                $splitted = static::splitName($fullname);
                $fname = $splitted['fname'];
                $lname = $splitted['lname'];
            }

            $data = array(
                'fname' => $fname,
                'lname' => $lname,
                'gender' => $clientData['gender'],
                'country' => $clientData['country'],
                'title' => $clientData['title'],
                'mobile_no' => $formatedPhone,
                'email' => $clientData['email'],
                'unique_no' => $clientData['unique_no'],
                'dob' => $clientData['dob'],
                'address' => $clientData['address'],
                'district' => $clientData['district'],
                'subcounty' => $clientData['subcounty'],
                'village' => $clientData['village'],
                'landline' => $clientData['landline'],
                'business_name' => $clientData['business_name'],
                'working_status' => $clientData['working_status'],
                'description' => $clientData['description'],
                'staff_id' => $staffId,
                'creation_user' => $staffId
            );
            $valid = static::validateRequiredFields($data, ['fname']);
            // echo json_encode( $data);
            // die();
            // Add the data to the failed array and skip it.
            if (!$valid) {
                $data['message'] = "Failed Validation Check";
                $invalid[] = $data;
                $errors  = true;
                $_SESSION['bulkUploadStatus']['data']['invalid'] += 1;
                $_SESSION['bulkUploadStatus']['data']['finished'] += 1;
                continue;
            }

            //Try to select using the current data
            // incase we get any result, we conclude
            // that the record already exists
            // die();
            // $result = $db->select($table, [], $data);
            // try to select using unique number 
            $resultUnique = $db->select($table, [], ["unique_no" => $data['unique_no']]);
            $resultEmail = $db->select($table, [], ["email" => $data['email']]);
            $resultPhone = $db->select($table, [], ["mobile_no" => $data['mobile_no']]);
            // echo "Selection";

            // Data is considered existent if a match with any if the 
            // unique_no, email, phone or the entire data is found in the db.
            if ($resultUnique || $resultPhone || $resultEmail) {
                $data['message'] = $resultUnique ? "Unique number already Taken, " : "";
                $data['message'] .= $resultEmail ? "\nA client with the same email exists, " : "";
                $data['message'] .= $resultPhone ? "\nA client with the same phone number exists, " : "";

                $exists[] = $data;
                $errors  = true;
                $_SESSION['bulkUploadStatus']['data']['finished'] += 1;
                $_SESSION['bulkUploadStatus']['data']['failed'] += 1;
                continue;
            }
            // echo "<br> Attempting to insert";
            $insertId = $db->insert($table, $data);

            // echo "<br> Inseterted siuccessfully";
            // echo json_encode( $insertId);
            // die();
            if (is_numeric($insertId)) {
                // echo $insertId;
                $client_id = $insertId;
                $_SESSION['bulkUploadStatus']['data']['finished'] += 1;
                $data["borrower_id"] = $insertId;

                $result1 = static::createSavingsAccount($db, $staffId, $client_id);

                // Incase we fail to create a savings account 
                // Delete the clients from clients table and add their data
                // to the failed array.

                if ($result1['error']) {
                    $db->delete('borrower', ['id' => $insertId]);
                    $data['message'] = "Failed to create Savings Account";
                    $failed[] = $data;
                } else {
                    $data['message'] = "Added Successfylly";
                    // Only activate login if savings account creation was successful
                    // $result2 =  static::activateClientLogin($db, $client_id);
                    // $data['activated_login'] = $result2['error'];
                    // $data['message_login'] = $result2['message'];

                    $success[] = $data;
                }
            } else {
                // echo json_encode($data);
                $errors  = true;
                $data['message'] = "This was a database insertion error. Contact System Admin";
                $failed[] = $data;
            }
        }
        $message = $errors ? "Some Errors Occurred" : "Success";
        return array(
            "error" => $errors,
            "message" => $message,
            "success" => $success,
            "exists" => $exists,
            "failed" => $failed,
            "invalid" => $invalid
        );
    }
    public static function formatPhoneNumber($mobile)
    {
        // Number should be 10 or  9 digits long
        // if it's 10, remove the leading 0 before you start.
        if (strlen($mobile) <= 10 && strlen($mobile) >= 9) {

            if (strlen($mobile) == 10) {
                $mobile = substr($mobile, 1, 9);
            }
            $array_format = strtok($mobile, '');
            $mobile = '+256';
            for ($i = 0; $i < 9; $i++) {
                if ($i == 3) {
                    $mobile .= "";
                }
                if ($i == 6) {
                    $mobile .= "";
                }
                $mobile .= $array_format[$i];
            }
        }
        return $mobile;
    }
    public static function createSavingsAccount($db, $staffId, $client_id)
    {
        $minimum_balance = 0;

        $default_saving_product_id = 0;
        $savings_product = $db->select("savings_product", [], ['active_flag' => 1, 'del_flag' => 0]);
        if (count($savings_product) > 0) {
            //$default_saving_product_id = $savings_product[0]['id'];
            $default_saving_product_id = $savings_product[0]['id'];
            $minimum_balance = $savings_product[0]['minimum_amount'];
        } else {
            //Create a default and get its ID
            $data = array(
                'name' => "Personal savings",
                'interest' => 10,
                'posting_freq' => 12,
                'minimum_amount' => 20000,
                'creation_user' => $staffId
            );

            $saveID = $db->insert("savings_product", $data);
            $default_saving_product_id = $saveID;
        }
        $tablehre = 'savings_account';
        // $acct = AppUtil::create_account($insertId);
        $lastacctno = $db->selectQuery("SELECT account_no FROM `savings_account` where id=(SELECT Max(id) from savings_account)");
        $lastacctno = $lastacctno[0]['account_no'];
        $lastacctno++;
        $dataInsert = array(
            'savings_product_id' => $default_saving_product_id,
            'account_no' => $lastacctno,
            'borrower_id' => $client_id,
            'creation_user' => $staffId
        );

        $saveAcct = $db->insert($tablehre, $dataInsert);
        if (is_numeric($saveAcct)) {
            return array(
                "error" => false,
                "message" => "Success"
            );
        } else {
            return array(
                "error" => true,
                "message" => "Failed"
            );
        }
    }

    public static function activateClientLogin($db, $client_id)
    {
        // echo json_encode($client_id);
        // Need to get company id
        $db_manager = new DbAcess('ssenhogv_manager');
        $company_db = $_SESSION['company_db'];
        $company = $db_manager->select('company', [], ['Data_base' => $company_db]);
        $company_id = $company[0]['id'];

        $client = $db->select('borrower', [], ['id' => $client_id]);
        if ($client) {
            $details = $client[0];
            $email = $details['email'];
            $user_id = $details[0]['id'];
            if (AppUtil::isValidEmail($email)) {

                // Check to see if the email is brand new or it belongs to someone else
                $email_status = ClientActivation::isEmailHis($db, $email, $client_id);

                $username = ClientActivation::generateUsername($email, $company_id);
                if ($email_status['error'] == true) {
                    $error = true;
                    $message = $email_status['message'];
                } else if (ClientActivation::activated($db, $client_id)) {

                    $error = true;
                    $message = "Account already Activated";
                    // die();
                    // ));
                    // ClientActivation::sendFeedback(true,"Accou")
                } else if (ClientActivation::usernameAlreadyExists($db, $username) && !ClientActivation::activationLinkExpired($db, $username)) {

                    $error = true;
                    $message = "Account activation already in progress";
                } else {


                    // If client already exists and activation link expired
                    // Delete the client data and try to creete new one
                    if (ClientActivation::usernameAlreadyExists($db, $username) && ClientActivation::activationLinkExpired($db, $username)) {
                        $sql = "DELETE FROM clients WHERE username='$username'";

                        $db->delete($sql);
                    }
                    $activation_token = ClientActivation::generateToken();

                    //  Encrypted token to be stored in database
                    $hashed_token = md5($activation_token);
                    // $current_time = DateTime::getTimestamp();
                    $date = new DateTime();
                    // Current time to track expiry of link
                    $date = $date->getTimestamp();

                    $company = $_SESSION['company'];

                    // A new mailer to send emails
                    $mailer = new MyMail(false);

                    // Send the activation email to client
                    $result = ClientActivation::sendActivationEmailWeb($mailer, $email, $username, $company, $activation_token, "client.ssentezo.com/client");
                    // $result = ClientActivation::sendActivationEmail($mailer, $email, $username, $company, $activation_token);

                    // Insert into the database only when email is successfully sent.
                    if ($result == 'success') {
                        // echo json_encode(array(
                        $db->update('borrower', ['email' => $email], ['id' => $client_id]);
                        $data = array(
                            'username' => $username,
                            'email' => $email,
                            // 'password',
                            'created_at' => $date,
                            // 'password_reset_token',
                            // 'last_seen',
                            'user_id' => $client_id,
                            'account_activation_token' => $hashed_token
                        );

                        $db->insert(
                            'clients',
                            $data
                        );
                        $error = false;

                        $message = "Success, An activation email has been sent to $email. It expires in 10 minutes";
                        // echo json_encode([$data,$message,$error]);
                        // die();

                    } else {

                        $error = true;
                        $message = "Failed to send activation link, Check to see if the email is valid or Contact system Admin for support";
                    }
                }
            } else {

                $error = true;
                $message = "Invalid Email";
            }
            // Check if account is already activated 

        }
        $status = $error ? "Failed" : "Success";
        ActivityLogger::logActivity($_SESSION['user_id'], 'Activate Client Login', $status, $message);

        return array(
            "error" => $error,
            "message" => $message
        );
    }
    public static function validateRequiredFields($clientData, $requiredFileds)
    {
        $totalRequired = count($requiredFileds);
        $totalPassed = 0;
        foreach ($requiredFileds as $field) {
            if (strlen($clientData[$field]) >= 3) {
                $totalPassed++;
            }
        }
        if ($totalPassed == $totalRequired) {
            return true;
        } else {
            return false;
        }
    }
    public static function createClientsFile()
    {
        ob_clean();
        $fileName = "Add Clients Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$clientUploadFileHeadings);
        exit;
    }
    public static function createSavingsFile($db)
    {
        $all_clients = $db->select('borrower', [], []);
        /***
         * For this case we need to load the clients into the csv file to ease the work of 
         * of the person to do the filling in of the form.
         */

        ob_clean();
        $fileName = "Add Savings Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$savingsUploadFileHeadings);
        echo "\n";
        foreach ($all_clients as $client) {
            // Construct an array that will be imploded to match the headings
            // of the csv file
            $csv_record = array(
                $client['id'],
                $client['unique_no'],
                $client['title'] . " " . $client['fname'] . " " . $client['lname'],
                "",
                "",
                "",
                "",
                ""

            );
            echo implode(",", $csv_record);
            echo "\n";
        }

        exit;
    }
    public static function createRepaymentsSummaryFile($db)
    {
        $repayments = $db->select("loan_installment_paid", [], ["active_flag" => 1, "del_flag" => 0]);
        $is_group = FALSE;
        ob_clean();
        $fileName = "Repayments Summary Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$repaymentsSummaryFileHeadings);
        echo "\n";

        foreach ($repayments as $repaymnt) {
            $loan = $db->select("loans", [], ['id' => $repaymnt['loan_id']])[0];
            $borrower = $db->select("borrower", [], ['id' => $repaymnt['borrower_id']])[0];
            // print_r($loan);
            // die();
            $loan_product = $db->select('loan_product', [], ['id' => $loan['loan_product_id']])[0];
            $collector = $db->select("staff", [], ["id" => $repaymnt['creation_user']])[0];
            $loan_name = $loan_product['name'];
            // print_r($loan_product);
            // echo $loan_name;
            // die();
            $addedOn = $repaymnt['payment_date'];
            $collectionDate = $repaymnt['collection_date'];
            $loanNo = $loan['loan_no'];
            $loanCollector = $collector['fname'] . " " . $collector["lname"];
            $repaymentMtd = $repaymnt['repayment_mtd'];
            $amount = $repaymnt['amount'];
            $depositedBy = $repaymnt['deposited_by'];
            $repaymentId = $repaymnt['id'];
            /**
             *"UniqueNo",
             *"Client Name",
             *"Mobile",
             *"Loan Product",
             *"Amount Paid",
             *"Collection Date",
             *"Paid On",  
             *"Paid By",
             *"Collected By"
             */
            $csv_record = array(
                $borrower['unique_no'],
                $borrower['title'] . " " . $borrower['fname'] . " " . $borrower['lname'],
                $borrower['mobile_no'],
                $loan_name,
                $amount,
                $collectionDate,
                $addedOn,
                $depositedBy,
                $loanCollector



            );
            echo implode(",", $csv_record);
            echo "\n";
        }
    }

    /**
     * Method to print the data into a csv files with the headings supplied
     * @param string $fileName The name of the file that will be printed
     * @param array $data An associative array of the data to be printed
     * @param array $headings An associative array of the Column headings whose keys are similar to keys in the $data array.
     * 
     */
    private static function printFile($fileName, $headings, $data = [])
    {
        // Extract the keys
        $allKeys = array_keys($headings);
        $fileHeadings = array_values($headings);
        // Tell the browser to save  the file
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", $fileHeadings);
        echo "\n";
        foreach ($data as $row) {
            $row = array_values(array_intersect_key($allKeys, $row));
            echo  implode(",", $row);
            echo "\n";
        }
    }
    public static function createSavingsSummaryFile($db)
    {
        $all_clients = $db->select('borrower', [], []);
        /***
         * For this case we need to load the clients into the csv file to ease the work of 
         * of the person to do the filling in of the form.
         */

        ob_clean();
        $fileName = "Savings Summary Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$savingsSummaryFileHeadings);
        echo "\n";
        foreach ($all_clients as $client) {
            // Construct an array that will be imploded to match the headings
            // of the csv file
            $balance = "Has no savings Account";
            $savings_account = $db->select('savings_account', [], ['borrower_id' => $client['id']]);
            if ($savings_account) {
                $savings_account = $savings_account[0];
                $balance = number_format($savings_account['balance'], 0, '.', '');
            }

            $csv_record = array(
                $client['unique_no'],
                $client['title'] . " " . $client['fname'] . " " . $client['lname'],
                $client['mobile_no'],
                $client['email'],
                $balance
            );
            echo implode(",", $csv_record);
            echo "\n";
        }

        exit;
    }
    public static function createLoansSummaryFile($db)
    {
        $loans = [];

        $loans = $db->select("loans", [], ['active_flag' => 1, "del_flag" => 0]);

        // only interested in active loans
        $active_loans = [];
        foreach ($loans as $loan) {
            $collection_date = AppUtil::nextDueDate($db, $loan);
            if ($collection_date) {

                $loan['collectionDate'] = $collection_date;
                $active_loans[] = $loan;
            }
        }

        /***
         * For this case we need to load the active loans into the csv file to ease the work of 
         * of the person to do the filling in of the form and also easy retrival of data.
         */
        $date = date('Y-m-d');
        $time = date('h:i a');
        ob_clean();
        $fileName = "All Active Loans Summary Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$loansSummaryFileHeadings);
        echo "\n";

        foreach ($active_loans as $loan) {

            $loan_product = $db->select('loan_product', [], ['id' => $loan['loan_product_id']])[0];
            $loan_name = $loan_product['name'];

            $client = $db->select('borrower', [], ['id' => $loan['borrower_id']])[0];
            $client_name  = $client['fname'] . ' ' . $client['lname'];
            $collection_date = $loan['collectionDate'];
            $collection_date = date('Y-m-d', strtotime(str_replace('/', '-', $collection_date)));

            $status = AppUtil::Loan_status($db, $loan);
            $dates = Loans::repaymentDates($loan);
            $release_date = AppUtil::Comparable_date_format($loan['release_date']);
            $maturity_date = AppUtil::Comparable_date_format($dates[count($dates) - 1]);

            $borrowerDetails = "";
            if ($loan['is_group'] == 1) {
                $borrowerDetails = $db->select("borrowers_group", [], ['id' => $loan['borrower_id']])[0];
            } else {
                $borrowerDetails = $db->select("borrower", [], ['id' => $loan['borrower_id']])[0];
            }


            $loanFees = $db->select("loan_applied_charges", [], ["loan_id" => $loan['id'], "active_flag" => 1, "del_flag" => 0]);
            $payments = $db->select("loan_installment_paid", [], ["loan_id" => $loan['id'], "active_flag" => 1, "del_flag" => 0]);

            $Installments = Loans::totalAmountsToPay($loan, $payments);
            $installments = $Installments[2];
            $num = Loans::numberOfInstallments($loan);
            $borrower_name = ($loan['is_group'] == 1) ? $borrowerDetails['name'] : ($borrowerDetails['title'] ? $borrowerDetails['title'] : '') . ' ' . $borrowerDetails['fname'] . ' ' . $borrowerDetails['lname'];
            $unique_no = $borrowerDetails['unique_no'];
            $phone = $borrowerDetails['mobile_no'];
            // $phone .="";

            $loan_number  = $loan['loan_no'];
            $principal = $loan['principal_amt'];
            $disbursed_on = $loan['release_date'];
            $interest = $loan['loan_interest'] . '%/' . $loan['loan_interest_pd'];

            $totalDue = $Installments[0];

            $afterMP = new aftermaturitypenalty();
            $afterMP = $afterMP->totalaftermpenalty($loan['id']);
            // echo $afterMP;
            //schedule penalty
            $sumPenalty = new schedulepenalty();
            $sumPenalty = $sumPenalty->totalschedulpenalty($loan['id']);
            $penalty = $afterMP + $sumPenalty;

            $paid = $Installments[2];

            $balance = $Installments[3] + $afterMP + $sumPenalty;


            if ($loan["status"] == "Open") {

                if ($status['missed_repayment'] == 1) {
                } else if ($status['open'] == 1) {
                } else if ($status['fully_paid'] == 1) {
                } else if ($status['past_maturity'] == 1) {
                }
            } else {
            }
            /**
             *UniqueNo*,
             *Client Name*,
             *Mobile*,
             *Loan Product*,
             *Principal*,
             *Total Due*,
             *Paid*,
             *Balance*,
             */

            if ((int)$balance <= 0) {
                continue;
            }
            $csv_record = array(
                $unique_no,
                $borrower_name,
                $phone,
                $loan_name,
                $principal,
                $interest,
                $totalDue,
                $paid,
                $balance,
                // $time,
            );
            echo implode(",", $csv_record);
            echo "\n";
        }

        exit;
    }

    public static function createAllLoansSummaryFile($db)
    {
        $loans = [];

        $loans = $db->select("loans", [], ['active_flag' => 1, "del_flag" => 0]);

        // only interested in active loans
        $active_loans = [];
        $all_loans = [];
        foreach ($loans as $loan) {
            $collection_date = AppUtil::nextDueDate($db, $loan);
            $status = AppUtil::Loan_status($db, $loan);



            $repayment_dates = Loans::repaymentDates($loan);
            $size = count($repayment_dates) - 1;
            $maturity_date = $repayment_dates[$size];
            $loan['maturity_date'] = $maturity_date;
            if ($collection_date) {
                $loan['collectionDate'] = $collection_date;
                $active_loans[] = $loan;
            } else {
                $loan['status'] = 'Fully Paid';
            }

            if ($status['open'] == 1) {
                $loan['status'] = 'Open';
            }
            if ($status['missed_repayment'] == 1) {
                $loan['status'] = 'Missed Repayment';
            }
            if ($status['past_maturity'] == 1) {
                $loan['status'] = 'Passed Maturity';
            }
            if ($status['fully_paid'] == 1) {
                $loan['status'] = 'Fully Paid';
            }

            $all_loans[] = $loan;
        }

        /***
         * For this case we need to load the active loans into the csv file to ease the work of 
         * of the person to do the filling in of the form and also easy retrival of data.
         */
        $date = date('Y-m-d');
        $time = date('h:i a');
        ob_clean();
        $fileName = "All Loans Summary Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$allLoansSummaryFileHeadings);
        echo "\n";

        foreach ($all_loans as $loan) {

            $loan_product = $db->select('loan_product', [], ['id' => $loan['loan_product_id']])[0];
            $loan_name = $loan_product['name'];

            $client = $db->select('borrower', [], ['id' => $loan['borrower_id']])[0];
            $client_name  = $client['fname'] . ' ' . $client['lname'];
            $collection_date = $loan['collectionDate'];
            $collection_date = date('Y-m-d', strtotime(str_replace('/', '-', $collection_date)));

            $status = AppUtil::Loan_status($db, $loan);
            $dates = Loans::repaymentDates($loan);
            $release_date = AppUtil::Comparable_date_format($loan['release_date']);
            $maturity_date = AppUtil::Comparable_date_format($dates[count($dates) - 1]);

            $borrowerDetails = "";
            if ($loan['is_group'] == 1) {
                $borrowerDetails = $db->select("borrowers_group", [], ['id' => $loan['borrower_id']])[0];
            } else {
                $borrowerDetails = $db->select("borrower", [], ['id' => $loan['borrower_id']])[0];
            }


            $loanFees = $db->select("loan_applied_charges", [], ["loan_id" => $loan['id'], "active_flag" => 1, "del_flag" => 0]);
            $payments = $db->select("loan_installment_paid", [], ["loan_id" => $loan['id'], "active_flag" => 1, "del_flag" => 0]);

            $Installments = Loans::totalAmountsToPay($loan, $payments);
            $installments = $Installments[2];
            $num = Loans::numberOfInstallments($loan);
            $borrower_name = ($loan['is_group'] == 1) ? $borrowerDetails['name'] : ($borrowerDetails['title'] ? $borrowerDetails['title'] : '') . ' ' . $borrowerDetails['fname'] . ' ' . $borrowerDetails['lname'];
            $unique_no = $borrowerDetails['unique_no'];
            $phone = $borrowerDetails['mobile_no'];
            // $phone .="";

            $loan_number  = $loan['loan_no'];
            $principal = $loan['principal_amt'];
            $disbursed_on = $loan['release_date'];
            $interest = $loan['loan_interest'] . '%/' . $loan['loan_interest_pd'];

            $totalDue = $Installments[0];

            $afterMP = new aftermaturitypenalty();
            $afterMP = $afterMP->totalaftermpenalty($loan['id']);
            // echo $afterMP;
            //schedule penalty
            $sumPenalty = new schedulepenalty();
            $sumPenalty = $sumPenalty->totalschedulpenalty($loan['id']);
            $penalty = $afterMP + $sumPenalty;

            $paid = $Installments[2];

            $balance = $Installments[3] + $afterMP + $sumPenalty;
            $status = $loan['status'];
            $csv_record = array(
                $unique_no,
                $borrower_name,
                $phone,
                $loan_name,
                $principal,
                $interest,
                $totalDue,
                $paid,
                $balance,
                $loan['maturity_date'],
                $status,
            );
            echo implode(",", $csv_record);
            echo "\n";
        }

        exit;
    }



    public static function createRepaymentsFile($db)
    {
        $loans = $db->select("loans", [], ['active_flag' => 1, "del_flag" => 0]);

        // only interested in active loans
        $active_loans = [];
        foreach ($loans as $loan) {
            $collection_date = AppUtil::nextDueDate($db, $loan);
            if ($collection_date) {

                $loan['collectionDate'] = $collection_date;
                $active_loans[] = $loan;
            }
        }

        /***
         * For this case we need to load the active loans into the csv file to ease the work of 
         * of the person to do the filling in of the form and also easy retrival of data.
         */
        $date = date('Y-m-d');
        $time = date('h:i a');
        ob_clean();
        $fileName = "Add Repayments Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", static::$repaymentsUploadFileHeadings);
        echo "\n";

        foreach ($active_loans as $loan) {

            $loan_product = $db->select('loan_product', [], ['id' => $loan['loan_product_id']])[0];
            $loan_name = $loan_product['name'];

            $client = $db->select('borrower', [], ['id' => $loan['borrower_id']])[0];
            $client_name  = $client['fname'] . ' ' . $client['lname'];
            $collection_date = $loan['collectionDate'];
            $collection_date = date('Y-m-d', strtotime(str_replace('/', '-', $collection_date)));

            $csv_record = array(
                $loan['id'],
                $loan_name,
                $client_name,
                $collection_date,
                $date,
                // $time,
                "",
                "",
                ""

            );
            echo implode(",", $csv_record);
            echo "\n";
        }

        exit;
    }
    public static function verifySavingsFile($fileName)
    {
        $result = static::fetchAssocFromCsvFile($fileName, static::$savingsTransactionTableMappings);
        return $result;
    }

    /**
     * @param string $fileName The name of the csv file that has you want to load into an associative array
     * @param array $columnMappings An associative array whose keys are the column headings in the csv file and values will be the keys of the returned associative array.
     * @return array An array of associative arrays for each row whose keys are the column names of the csv files with the corresponding values
     */
    public static function fetchAssocFromCsvFile($fileName, $columnMappings)
    {
        $dataAssoc = array();
        $heading = array();
        $flag = 0; //We want to skip the heading
        // Attempt to open the file for reading
        if (($handle = fopen($fileName, 'r')) !== FALSE) {

            // Read the csv file line by line
            $row = [];
            while (($data = fgetcsv($handle, 0, ',', '\\')) !== FALSE) {
                // Skip the csv file heading.

                if ($flag) {
                    for ($i = 0; $i < count($heading); $i++) {
                        $key = $columnMappings[trim($heading[$i])];
                        $value = trim($data[$i]);
                        $row[$key] = $value;
                    }
                    $dataAssoc[] = $row;
                } else {
                    $heading = $data;
                    $flag = 1;
                }
            }
            // Close the file 
            fclose($handle);

            return $dataAssoc;
        } else {
            return array(
                "error" => true,
                "message" => "Can't open file $fileName"
            );
        }
    }

    public static function verifyRepaymentsFile($fileName)
    {
        $result = static::fetchAssocFromCsvFile($fileName, static::$balanceSeetTableMappings);
        return $result;
    }
    public static function saveSavingsToDatabase($db, $savingsData, $staffId)
    {
        $table = 'borrower';
        $errors = false;
        // Failed insertions may be due to database errors, maybe invalid data
        // or missing required data.
        $failed = array();
        // return array();
        // Successful insertions will be stored here
        // With these three arrays we can have clear feedback 
        // at the end of the process.
        $success = array();
        // Specifically for invalid data.
        $invalid = array();
        // This will hold the data temporarily before it's inserted into the database
        $data = array();

        foreach ($savingsData as $transaction) {
            // echo($clientData);
            // error_reporting(1);
            // initiallize data to empty for every iteration
            $data = [];
            $data = array(
                'fullname' => $transaction['fullname'],
                'id' => $transaction['id'],
                'unique_no' => $transaction['unique_no'],
                'transaction_date' => $transaction['transaction_date'],
                'transaction_time' => $transaction['transaction_time'],
                'amount' => $transaction['amount'],
                'description' => $transaction['description'],
                'deposited_by' => $transaction['deposited_by']

            );
            $valid = static::validateRequiredFields($data, ['amount', 'fullname']);

            $resultUnique = $db->select($table, [], ["unique_no" => $data['unique_no']]);
            // Add the data to the failed array and skip it.
            if (!$valid) {
                $data['message'] = "Failed Validation Check";
                $data['failed'] = true;
                $data['details'] = "Validate your data, We failed to  undertand it";
                $invalid[] = $data;

                $errors  = true;
                continue;
            }
            if (!$resultUnique) {
                $data['message'] = "Client has no unique number";
                $data['failed'] = true;
                $data['details'] = "Unique numbers are a must for all savings accounts";
                $failed[] = $data;
                $errors  = true;
                continue;
            }
            $data['id'] = $resultUnique[0]['id'];
            $ret = static::registerSavingsTransaction($db, $data);
            if ($ret['error']) {
                $data['message'] = "Transaction unexpectedly failed";
                $data['details'] = $ret['message'];
                $data['failed'] = true;
                $failed[] = $data;
                $errors  = true;
                continue;
            }

            $data['message'] = "Transaction Successful";
            $data['details'] = $ret['message'];
            $data['failed'] = false;
            $success[] = $data;
        }
        $message = $errors ? "Some Errors Occurred" : "Success";

        return array(
            "error" => $errors,
            "message" => $message,
            "success" => $success,
            "exists" => [],
            "failed" => $failed,
            "invalid" => $invalid
        );
    }
    public static function deleteFile($fileName)
    {
        return unlink($fileName);
    }

    public static function registerSavingsTransaction($db, $transData)
    {

        $borrower_id = $transData['id'];
        $response = [];
        $withrawal = 0;
        // if borrower id is empty
        if ($borrower_id) {

            // $borrower_details = $db->select('borrower', [], ['id' => $borrower_id]);
            // echo json_encode($borrower_details);
            $account_details = $db->select('savings_account', [], ['borrower_id' => $borrower_id]);
            // print_r($account_details);
            $account_balance = $account_details[0]['balance'];
            // echo "The account balance is ".$account_balance;
            $deposit_amount = $transData['amount'];
            if ($deposit_amount < 0) {
                $withrawal = 1;
            }
            // echo "<br>We are depositing ".$deposit_amount;
            // die();
            $savings_id = $account_details[0]['id'];
            // echo json_encode($account_details);
            // echo $withdraw_amount;
            // echo $account_balance;
            // $manager_db = new DbAcess("ssenhogv_manager");
            // $db = new DbAcess();
            // $company_name = $_SESSION['company'];

            // $sms_status = AppUtil::sms_check($manager_db, $company_name);
            // $can_transact = AppUtil::units_check($manager_db, $company_name);

            // $sms = new SMS();

            // $table = "savings_transcations";
            // print_r($request);
            // die();


            // if ($can_transact) {
            // $timestamp = time();
            $transaction_date = $transData['transaction_date'];
            $transaction_time = $transData['transaction_time'];

            $transaction_type = ($withrawal == 1) ? "Withdrawal" : "Deposit";
            $transaction_amount = $deposit_amount;
            // $transaction_fees = [];
            $deposited_by = "System"; //AppUtil::userFullName();
            $totalIncreBal = $account_balance + $deposit_amount;
            // echo "Total incremental balance is $totalIncreBal";
            // echo "It's obtained from Acc balance ($account_balance) plus  amount ($deposit_amount)";
            // die();
            // if (!isset($transaction_fees)) {
            // $transaction_fees = [];
            // }
            $transaction_description = $transData['description'] . "via Bulk Upload";

            // $data = [
            // "savings_account_id" => $savings_id,
            // "amount" => ($withrawal == 1) ? ($transaction_amount*-1) : $transaction_amount,"type" => ($withrawal == 1) ? "D" : "C",
            $amount = ($withrawal == 1) ? ($transaction_amount * -1) : $transaction_amount;
            $type = ($withrawal == 1) ? "D" : "C";

            // "transaction_date" => $transaction_date,
            // "transaction_time" => $transaction_time,
            // "trans_type" => $transaction_type,
            // "description" => $transaction_description,
            // "creation_user" =>  AppUtil::userId(),
            // "incremental_balance" => $totalIncreBal,
            // "deposited_by" => $deposited_by,
            // ];
            $sql =   "INSERT INTO savings_transcations(`savings_account_id`, `amount`, `type`, `transaction_date`, `transaction_time`,`trans_type`, `description`, `creation_user`,`incremental_balance`, `deposited_by`) VALUES ('$savings_id','$amount','$type','$transaction_date','$transaction_time','$transaction_type','$transaction_description','1','$totalIncreBal','System')";
            // print_r($data);
            // die();
            // $table = 'savings_transcations';
            // $insertId = $db->insert($table, $data);
            $insertId = $db->sql($sql);
            // print_r($insertId);

            if (is_numeric($insertId)) {
                // $num = AppUtil::one_less_unit($manager_db, $company_name);
                // This breaks the script incase the transation field is not given
                // a value in the form.
                // if (count($transaction_fees) > 0) {
                // foreach ($transaction_fees as $fee) {
                // 
                // $valueFee = 0;
                // $ids = $fee;
                // $feeDetails = $db->select("savings_fees", [], ["id" => $ids])[0];
                // if ($feeDetails['charge_mtd'] == "fixed") {
                // $valueFee = $feeDetails['charge_amount'];
                // }
                // if ($feeDetails['charge_mtd'] == "percentage") {
                // $valueFee = ($feeDetails['charge_rate'] / 100) * $transaction_amount;
                // }
                // }
                // }

                // compulsory chareges
                // $feecompulsory = $db->select("savings_fees", ["charge_amount"], ["deductable" => 4])[0];

                // $smsfee = $feecompulsory["charge_amount"];
                // if ($valueFee > 0) {
                // $valueFee = $valueFee + $smsfee;
                // } else {
                // $valueFee = 0 + $smsfee;
                // }
                // echo $valueFee;
                // die();
                // $data1 = ['saving_id' => $insertId, 'saving_fee_id' => $fee, 'amount' => $valueFee, "creation_user" => AppUtil::userId()];
                // $chargeId = $db->insert("savings_applied_charges", $data1);
                // $data1 = [
                //     "savings_account_id" => $savings_id,
                //     // "savings_account_to" => $transaction_to,
                //     "amount" => $valueFee,
                //     "type" => "D",
                //     "transaction_date" => $transaction_date,
                //     "transaction_time" => $transaction_time,
                //     "trans_type" => "Fees",
                //     "description" => "transaction fees",
                //     "creation_user" =>  AppUtil::userId(),
                //     'incremental_balance' => $totalIncreBal - $valueFee,
                //     "deposited_by" => $deposited_by
                // ];
                // $insertId1 = $db->insert($table, $data1);
                // // $borrower_id = $db->select("savings_account", ['borrower_id'], ["id" => $savings_id])[0];
                //$unique_no = $$db->select("borrower",[], ["id" => $borrower_id['borrower_id']])[0];
                // $borrower = $db->select("borrower", [], ["id" => $borrower_id['borrower_id']])[0];
                // $message = "Dear Customer, You have made a " . $transaction_type . " of Ugx " . $transaction_amount . " on your savings account with " . $company_name;
                // $receiver = array();
                // $receiver[] = $borrower['mobile_no'];
                // $unique_no = $borrower['unique_no'];
                // $result = $sms->sms($message, $receiver, $sms_status);

                //add balance sheet ***
                $type = ($withrawal == 1) ? "D" : "C";
                $text = ($type == "C") ? "savings $type" : "Withdrawal $type";
                $transaction_amount = ($withrawal == 0) ? $transaction_amount : (-1 * $transaction_amount);
                $dataSheet = [
                    'reg_date' => $transaction_date, 'cr_dr' => $type, "type" => ($withrawal == 0) ? "Savings" : "Withdrawal",
                    "description" => $text, "amount" => $transaction_amount, "field1" => $insertId,
                    "trans_details" => $text, "creation_user" => AppUtil::userId()
                ];
                // $dataSheet1 = [
                //     'reg_date' => $transaction_date, 'cr_dr' => "D", "type" => "Saving Fees",
                //     "description" => $text, "amount" => $valueFee, "field1" => $insertId1,
                //     "trans_details" => $text, "creation_user" => AppUtil::userId()
                // ];
                $sheetId = $db->insert("balance_sheet", $dataSheet);
                // $sheetId1 = $db->insert("balance_sheet", $dataSheet1);




                $update = $db->update("savings_account", ['balance' => $totalIncreBal], ["id" => $savings_id]);

                // $update1 = $db->update("savings_account", ['balance' => $totalIncreBal - $valueFee], ["id" => $savings_id]);

                $response =  ["error" => false, "message" => "Success"];
                //print_r($update);
            } else {
                // echo json_encode($insertId);
                // print_r(error_get_last());
                // die();
                $response  = array(
                    "error" => true,
                    "message" => "Database insertion error"
                );
            }
            // } else {
            // $response = ["error" => true, "message" => "You have zero transaction units and hence cannot make this transaction."];
            // }
        } else {
            $response =  array(
                "error" => true,
                "message" => "Unknown client made this request"
            );
        }

        return $response;
    }
    public static function registerRepayment($db, $repaymentData, $staffId)
    {
        $sms = new SMS();
        $manager_db = new DbAcess('ssenhogv_manager');
        $company_name = $_SESSION['company'];
        $loan_id = $repaymentData['field1'];
        $repayment_amount = $repaymentData['amount'];
        // echo json_encode($repaymentData);
        // die();
        $repayment_method_id = 'Cash';
        $repayment_collected_date = $repaymentData['reg_date'];
        $repayment_collected_date = static::converDate($repayment_collected_date);
        $repayment_description = $repaymentData['description'];
        $deposited_by = $repaymentData['deposited_by'];
        $paid_on = $repaymentData['creation_date'];
        $paid_on = static::converDate($paid_on);

        $message = '';
        $error = true;
        $loanDetails = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0];
        $collection_date = AppUtil::nextDueDate($db, $loanDetails);
        $sms_status = AppUtil::sms_check($manager_db, $company_name);

        if ($collection_date == NULL) {
            $message = "Info: The loan has been fully paid and further payments cannot be made";
        } else if ($repayment_amount) {
            $borrower_ID = $db->select("loans", [], ['id' => $loan_id, 'active_flag' => 1, "del_flag" => 0])[0];
            $borrowerDetails = $db->select("borrower", [], ["id" => $borrower_ID['borrower_id']])[0];

            // $repayment_amount=$_POST['repayment_amount'];
            //save Loan Repayment ********
            $data = "";

            $data = [
                'loan_id' => $loan_id,
                'borrower_id' => $borrower_ID['borrower_id'],
                'amount' => $repayment_amount,
                'creation_user' => AppUtil::userId(),
                'collection_date' => $repayment_collected_date,
                'repayment_mtd' => $repayment_method_id,
                'description' => $repayment_description,
                "deposited_by" => $deposited_by,
                "payment_date" => $paid_on
            ];

            $repayment_collected_date =  AppUtil::Comparable_date_format($repayment_collected_date);


            if ($repayment_amount > 0) {
                $insertId = $db->insert("loan_installment_paid", $data);
                // $message .= "repayment amount >0";

                if (is_numeric($insertId)) {
                    $num = AppUtil::one_less_unit($manager_db, $company_name);
                    // $message .= "Added to  loan_installemts";
                    $message = "Dear Customer, You have made a repayment of Ugx " . $repayment_amount . " on your loan with " . $company_name;
                    $receiver = array();
                    $receiver[] = $borrowerDetails['mobile_no'];
                    $result = $sms->sms($message, $receiver, $sms_status);

                    //Add to balance Sheet *** 
                    //Loan Details            

                    $text = 'Loan Repayment #' . $loanDetails['loan_no'];
                    $dataSheet = array(
                        'reg_date' => $repayment_collected_date, 'cr_dr' => "C", "type" => "Repayment",
                        "description" => $text, "amount" => $repayment_amount, "field1" => $insertId,
                        "trans_details" => $repayment_description, "creation_user" => AppUtil::userId()
                    );
                    $sheetId = $db->insert("balance_sheet", $dataSheet);
                    $error = false;
                    $message = "Repayment Successful";
                } else {

                    $message = "Error: Failed to register repayments. Reason: " . substr($insertId, 0, 35);;
                }
            } else {
                $message = "Error: Repayment amount cannot be equal to or less than zero";
            }
        } else {
            $message = "Error: You have missing repayment amount";
        }
        return array(
            'error' => $error,
            'message' => $message
        );
    }
    public static function saveRepaymentsToDatabase($db, $repaymentsData, $staffId)
    {
        $success = array();
        $failed = array();
        $invalid = array();
        $errors = false;
        foreach ($repaymentsData as $repayment) {
            if (!$repayment['amount'] || !static::validateRequiredFields($repayment, ['full_name', 'reg_date'])) {
                $repayment['failed'] = true;
                $repayment['message'] = "Invalid data detected";
                $invalid[] = $repayment;
                $errors = true;

                continue;
            }
            $ret = static::registerRepayment($db, $repayment, $staffId);

            if ($ret['error']) {
                $errors = true;
                $repayment['failed'] = true;
                $repayment['message'] = "Failed to make repayment.";
                $repayment['details'] = $ret['message'];
                $failed[] = $repayment;
            } else {
                $repayment['failed'] = false;

                $success[] = $repayment;
            }
            // echo json_encode($ret);
        }
        $message = $errors ? "Some Errors Occurred" : "Success";

        return array(
            "error" => $errors,
            "message" => $message,
            "success" => $success,
            "exists" => [],
            "failed" => $failed,
            "invalid" => $invalid
        );
    }
    public static function converDate($date)
    {
        return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
    }
    public static  function splitName($fullname)
    {

        $fullname = explode(' ', $fullname);
        $fname = $fullname[0];
        $lname = implode(' ', array_slice($fullname, 1));
        return array(
            'fname' => $fname,
            'lname' => $lname
        );
    }
}
