<?php

namespace Ssentezo\Transaction;

use AppUtil;
use Ssentezo\Savings\Account;

class SavingTransaction
{
    /**
     * @var DbAccess $db The database connection of the company
     */
    public $db;
    /**
     * @var string The date on which the transaction occurred
     */
    public $date;

    /**
     * @var string The time at which the transaction happened
     */
    public $time;

    /**
     * @var string The type of transaction
     */
    public $type;

    /**
     * @var double The amount to transact
     */
    public $amount;

    /**
     * @var array Saving fees
     */
    public $fees;

    /**
     * @var string The person who deposited the money
     */
    public $deposited_by;

    /**
     * @var string The description of the transaction
     */
    public $narrative;

    /**
     * @var Account The main account participating in the transaction
     */
    public $sourceAccount;

    /**
     * @var Account The other account in case of a transfer this represents the account 
     * to which funds will be transferred to.
     */
    public $destAccount;

    /**
     * Creates a savings transaction handle
     * @param DbAccess $db The database connection of the company
     * @param int $source_account_id The id of the main savings account in the transaction
     * @param array $data An associative array with The data needed to make transaction.
     * The array should have the following keys: 
     * transaction_date, 
     * transaction_time,
     * transaction_type,
     * transaction_amount, 
     * transaction_fees, 
     * deposited_by, 
     * dest_account
     * @return this
     */
    public function __construct($db, $source_account_id, $data)
    {
        $this->sourceAccount = new Account($db, $source_account_id);
        if (strlen($data['dest_account']) > 0) { //Check for dest_account id 
            $dest_account = $data['dest_account'];
            $this->destAccount = new Account($db, $dest_account);
        }
        $this->db = $db;
        $this->date = $data['transaction_date'];
        $this->time = $data['transaction_time'];
        $this->type = $data['transaction_type'];
        $this->amount = $data['transaction_amount'];
        $this->fees = $data['transaction_fees'];
        $this->deposited_by = $data['deposited_by'];
        $this->narrative = $data['narrative'];
        return $this;
    }
    public function withdraw()
    {
        $ret = $this->sourceAccount->decrease($this->db, $this->amount);
        return $ret;
    }
    public function deposit()
    {
        $ret = $this->sourceAccount->increase($this->db, $this->amount);
        return $ret;
    }
    public function transfer()
    {
        $this->sourceAccount->decrease($this->db, $this->amount);
        $this->destAccount->increase($this->db, $this->amount);
    }
    public function totalFees()
    {
        $total_fees = 0;
        if (count($this->fees) > 0) {
            foreach ($this->fees as $fee_id) {
                $feeDetails = $this->db->select("savings_fees", [], ["id" => $fee_id])[0];
                if ($feeDetails['charge_mtd'] == "fixed") {
                    $total_fees += $feeDetails['charge_amount'];
                }
                if ($feeDetails['charge_mtd'] == "percentage") {
                    $total_fees += ($feeDetails['charge_rate'] / 100) * $this->amount;
                }
            }
        }
        return $total_fees;
    }
    public function validate()
    {
        // //get Old Balance to have Incremental balance...
        // $saving_account = $db->select("savings_account", [], ["id" => $savings_id])[0];
        // $oldBalance = $saving_account['balance'];
        // $saving_product_id = $saving_account['savings_product_id'];
        // /**
        //  * Checks for minimum balance
        //  */
        // $savings_product = $db->select('savings_product', [], ['id' => $saving_product_id])[0];
        // $min_balance = $savings_product['minimum_amount'];


        return  $this->amount > 0 && //Avoid negative amounts

            $this->sourceAccount->can_transact($this->amount, $this->type);

        // if ($transaction_amount < 0) { //Reject any amount less than zero
        //     Alert::setSessionAlert("Amount can't be negative", 'danger');
        // } else if ($type != 'C' && ($oldBalance - $total_amount) < $min_balance) { //As long as it's not a deposit we have to check the minimum balance rule
        //     ActivityLogger::logActivity(AppUtil::userId(), "Withdrawal/Transfer", "aborted", "Violation of minimum balance rule");
        //     $possible = $oldBalance - ($min_balance + $fees);
        //     Alert::setSessionAlert("Account balance is low to complete the transaction 
        //             <br> You can only withdraw upto UGX " . number_format($possible > 0 ? $possible : 0, 2) . ". Thank you", "danger");
        // } else {

    }
    public function save()
    {
        $data = [
            "savings_account_id" => $this->sourceAccount->id,
            "savings_account_to" => $this->destAccount->id,
            "amount" => $this->amount,
            "type" => $this->type,
            "transaction_date" => $this->date,
            "transaction_time" => $this->time,
            "trans_type" => $this->trans_type,
            "description" => $this->narrative,
            "creation_user" =>  AppUtil::userId(),
            'incremental_balance' => $this->sourceAccount->balance,
            "deposited_by" => $this->deposited_by
        ];
        $insertId = $this->db->insert("savings_transcations", $data);

        $data1 = [
            'saving_id' => $insertId,
            'saving_fee_id' => $this->fees[0], 'amount' => $this->totalFees, "creation_user" => AppUtil::userId()
        ];
        $chargeId = $this->db->insert("savings_applied_charges", $data1);


        $data1 = [
            "savings_account_id" => $this->sourceAccount->id,
            "savings_account_to" => $this->destAccount->id,
            "amount" => $this->totalFees(),
            "type" => "D",
            "transaction_date" => $this->date,
            "transaction_time" => $this->time,
            "trans_type" => "Fees",
            "description" => "transaction fees",
            "creation_user" =>  AppUtil::userId(),
            'incremental_balance' => $this->sourceAccount->balance,
            "deposited_by" => $this->deposited_by
        ];
        $dataSheet = [
            'reg_date' => $this->date, 'cr_dr' => $this->type, "type" => "Savings",
            "description" => $this->narrative, "amount" => $this->amount, "field1" => $insertId,
            "trans_details" => $this->narrative, "creation_user" => AppUtil::userId()
        ];
        $dataSheet1 = [
            'reg_date' => $this->date, 'cr_dr' => "D", "type" => "Saving Fees",
            "description" => $this->narrative, "amount" => $this->amount, "field1" => $insertId,
            "trans_details" => $this->narrative, "creation_user" => AppUtil::userId()
        ];
        $sheetId = $this->db->insert("balance_sheet", $dataSheet);
        $sheetId1 = $this->db->insert("balance_sheet", $dataSheet1);

    }
}
