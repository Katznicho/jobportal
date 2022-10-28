<?php

namespace Ssentezo\Savings;

use Ssentezo\Database\DbAccess;
use Ssentezo\Util\Logger;

class SavingsAccount
{
    protected $id;
    protected $savingsProductId;
    protected $accountNumber;
    protected $accountName;
    protected $description;
    protected $borrowerId;
    protected $balance;
    protected $creationDate;
    protected $creationUser;
    protected $lastModifiedBy;
    protected $lastDodifiedDate;
    protected $activeFlag;
    protected $delFlag;
    protected $isGroup;

    /**
     *  Instantiates a savings account object 
     * @param array $savings_account An associative array whose keys are the column names of the table savings_account
     * and values are the corresponding values for that record.
     */
    function __construct($db, $user_id)
    {
        $savings_account = $db->select('savings_account', [], ['borrower_id' => $user_id])[0];
        $this->id = $savings_account["id"];
        $this->savingsProductId = $savings_account["savings_product_id"];
        $this->accountNumber = $savings_account["account_no"];
        $this->accountName = $savings_account["account_name"];
        $this->description = $savings_account["description"];
        $this->borrowerId = $savings_account["borrower_id"];
        $this->balance = $savings_account["balance"];
        $this->creationDate = $savings_account["creation_date"];
        $this->creationUser = $savings_account["creation_user"];
        $this->lastDodifiedBy = $savings_account["last_modified_by"];
        $this->lastDodifiedDate = $savings_account["last_modified_date"];
        $this->activeFlag = $savings_account["active_flag"];
        $this->delFlag = $savings_account["del_flag"];
        $this->isGroup = $savings_account["is_group"];
    }

    public function withdraw($amount, DbAccess $db)
    {
        $query = "UPDATE savings_account SET balance = balance-$amount WHERE id=" . $this->id;
        $result =  $db->updateQuery($query);
        return $result;
    }

    //update aacount
    public function updateClientAccount($db, $borrowerId, $amount)
    {
        $query = "UPDATE savings_account SET balance = balance-$amount WHERE borrower_id=" . $borrowerId;
        $result =  $db->updateQuery($query);
        return $result;
    }

    //update a deposit
    public function updateClientAccountDeposit($db, $borrowerId, $amount)
    {
        $query = "UPDATE savings_account SET balance = balance+$amount WHERE borrower_id=" . $borrowerId;
        $result =  $db->updateQuery($query);
        return $result;
    }

    /**
     * Fixes issues to do with account balance not matching the transactions carried out
     * @param DbAccess $db The database connection of the company
     * @param int $savings_id The id of the savings account to fix.
     * @return true|false  Returns true if the fix has been successful and false if error have aoccured
     * In case of error you can check the logs to find out the issue.
     * Also ensure that the /uploads/allfiles/logs folder is writeable, otherwise it will throw internal server error.
     * To avoid this wrap it in a try- ctach .
     */
    public static function updateTransactions($db, $savings_id)
    {
        Logger::log_header("Fixing Errors in transactions ");
        //Get all the transactions
        Logger::info("Fetching all transactions...");
        $transactions = $db->select("savings_transcations", [], ['savings_account_id' => $savings_id, 'active_flag' => 1, 'del_flag' => 0, "order by" => "id asc"]);
        Logger::info("Finished getting all the transactions ", ["total" => count($transactions)]);

        $incremental_balance = 0;

        $error = false;
        Logger::info("Looping through all the transactions");
        foreach ($transactions as $transaction) { //Transactions are processed in the order they were made
            Logger::info("Working on transaction #" . $transaction['id']);
            $amount = $transaction["amount"];
            $type = $transaction["type"];
            $date = $transaction["transaction_date"];

            if ($type == "C") {
                Logger::info("Detected a credit transaction");
                $incremental_balance += $amount;
            } else {
                Logger::info("Detected a debit transaction");
                $incremental_balance -= $amount;
            }

            Logger::info("Checking if it's inconsistent");
            if ($incremental_balance != $transaction["incremental_balance"]) { //This is where the error is 
                Logger::info('Inconsistent transaction detected');
                //Here we have to update the incremental balance.
                Logger::info("fixing the inconsitence");
                $count = $db->update('savings_transcations', ['incremental_balance' => $incremental_balance], ['id' => $transaction['id']]);
                if (is_numeric($count) and $count > 0) {
                    Logger::info("Inconsistence fixed successfully");
                } else {
                    $error = true;
                    Logger::error("Failed to fix inconsitence with reason: $count");
                    Logger::info("Giving up now we don't want to cause more errors");
                    break;
                }
            } else {
                Logger::info("The transaction is fully consistent");
            }
        }
        if ($error) {
            Logger::info("Process finished, Ignoring updating the account balance due to errors");
            return false;
        } else {
            Logger::info("Success!!!, Now updating the account balance to the right amount");
            $balance = $incremental_balance; //this is the right balance
            $count = $db->update('savings_account', ['balance' => $balance], ['id' => $savings_id]);
            if (is_numeric($count)) {
                if ($count > 0)
                    Logger::info("Account balance Updated successfully");
                else
                    Logger::info("No need to update account balance since it's the same");
            } else {
                Logger::error("Account balance Update failed With reason: $count");
                return false;
            }
            return true;
        }
    }

    public function closeSuccessfulWithraw(DbAccess $db, $transaction_ref, $message)
    {
        $insert_id = $db->insert(
            'transaction_status',
            [
                'transaction_reference' => $transaction_ref,
                'status' => 'Completed',
                'narrative' => $message,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        );

        if (is_numeric($insert_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function closeFailedWithraw(DbAccess $db, $transaction_ref, $message = '')
    {
        $insert_id = $db->insert(
            'transaction_status',
            [
                'transaction_reference' => $transaction_ref,
                'status' => 'Failed',
                'narrative' => $message,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        );

        if (is_numeric($insert_id)) {
            return true;
        } else {
            return false;
        }
    }
    public function initWithdraw($db, $transaction_ref, $status = 'Pending', $message)
    {
         
        $insert_id = $db->insert(
            'transaction_status',
            [
                'transaction_reference' => $transaction_ref,
                'status' => $status,
                'narrative' => $message
            ]
        );


        if (is_numeric($insert_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function initDepositDraw($db, $transaction_ref, $status = 'Pending', $message)
    {
        $insert_id = $db->insert(
            'deposit_status',
            [
                'transaction_reference' => $transaction_ref,
                'status' => $status,
                'narrative' => $message
            ]
        );


        if (is_numeric($insert_id)) {
            return true;
        } else {
            return false;
        }
    }



    public function updateWithDrawTable($db, $transaction_ref, $amount, $status = 'Failed')
    {
        $update_id = $db->update(
            'withdrawal_transactions',
            [
                'status' => $status,

            ],
            [
                'transaction_reference' => $transaction_ref,
                'amount' => $amount
            ]
        );
        if (is_numeric($update_id)) {
            return true;
        } else {
            return false;
        }
    }



    public function updateDepositTable($db, $transaction_ref, $amount, $status = 'Failed')
    {
        $update_id = $db->update(
            'make_deposit',
            [
                'status' => $status,

            ],
            [
                'transaction_reference' => $transaction_ref,

            ]
        );
        if (is_numeric($update_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPendingTransactions(DbAccess $db)
    {
        $pending_transactions = $db->select('withdrawal_transactions', [], ['borrower_id' => $this->borrowerId, 'status' => 'Pending']);

        //must be greater than 1 because they must have atleast one pending transaction
        if (count($pending_transactions) > 1) {
            return true;
        }
        return false;
    }
    public function findTransactionByReferenceNumber($db, $ref_number)
    {
        $transaction = $db->select('withdrawal_transactions', [], ["transaction_reference" => $ref_number, 'borrower_id' => $this->borrowerId]);

        return $transaction;
    }

    public function findDepositByReferenceNumber($db, $ref_number)
    {
        $transaction = $db->select('make_deposit', [], ["transaction_reference" => $ref_number, 'borrower_id' => $this->borrowerId]);

        return $transaction;
    }



    public function canWithdraw($amount)
    {
        if ($this->balance > $amount) {
            return true;
        }
        return false;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getSavingsProductId()
    {
        return $this->savingsProductId;
    }
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
    public function getAccountName()
    {
        return $this->accountName;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getBorrowerId()
    {
        return $this->borrowerId;
    }
    public function getBalance()
    {
        return $this->balance;
    }
    public function getCreationDate()
    {
        return $this->creationDate;
    }
    public function getCreationUser()
    {
        return $this->creationUser;
    }
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }
    public function getLastDodifiedDate()
    {
        return $this->lastDodifiedDate;
    }
    public function getActiveFlag()
    {
        return $this->activeFlag;
    }
    public function getDelFlag()
    {
        return $this->delFlag;
    }
    public function getIsGroup()
    {
        return $this->isGroup;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    public function setSavingsProductId($savingsProductId)
    {
        $this->savingsProductId = $savingsProductId;
    }
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function setBorrowerId($borrowerId)
    {
        $this->borrowerId = $borrowerId;
    }
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
    public function setCreationUser($creationUser)
    {
        $this->creationUser = $creationUser;
    }
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }
    public function setLastDodifiedDate($lastDodifiedDate)
    {
        $this->lastDodifiedDate = $lastDodifiedDate;
    }
    public function setActiveFlag($activeFlag)
    {
        $this->activeFlag = $activeFlag;
    }
    public function setDelFlag($delFlag)
    {
        $this->delFlag = $delFlag;
    }
    public function setIsGroup($isGroup)
    {
        $this->isGroup = $isGroup;
    }
}
