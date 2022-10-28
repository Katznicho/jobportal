<?php

namespace Ssentezo\Loans;

use Ssentezo\Database\DbAccess;

class LoanGuaranteeFund
{
    static $profiles_table = "loan_guarantee_fund_profiles";
    static $transactions_table = "loan_guarantee_fund_transactions";
    /**
     * Creates a loan guarantee fund profile for a borrower
     * @param DbAccess $db  Database connection to the company's database
     * @param int $borrowerId The id of the borrower who will be the owner of the profile
     * @param int #staffId The id of the staff who creates or leads to the creation of the profile
     * @param double $balance The initial balance of the profile.
     */
    public static function create_profile($db, $borrowerId, $staffId)
    {
        $balance = 0;
        $ret = $db->insert(static::$profiles_table, ['borrower_id' => $borrowerId, 'created_by' => $staffId, 'balance' => $balance]);
        return $ret;
    }
    public static function add_money($db, $borrowerId, $amount, $staffId)
    {
        $date = date("Y-m-d");
        $time = time();
        /*Record the transaction in the LGF transaction table */
        $insert_id = $db->insert(static::$transactions_table, ['amount' => $amount, 'borrower_id' => $borrowerId, 'time' => $time, 'date' => $date, 'type' => 'C']);

        /*Get the latest balance of the profile */
        $profile = $db->select(static::$profiles_table, [], ['borrower_id' => $borrowerId])[0];
        $current_balance = $profile['balance'];
        $new_balance = $current_balance + $amount;
        $update = $db->update(static::$profiles_table, ['balance' => $new_balance], ['borrower_id' => $borrowerId]);
        return $update;
    }
    public static function remove_money($db, $borrowerId, $amount)
    {
        /*Get the latest balance of the profile */
        $profile = $db->select(static::$profiles_table, [], ['borrower_id' => $borrowerId])[0];
        $current_balance = $profile['balance'];
        $new_balance = $current_balance - $amount;
        $update = $db->update(static::$profiles_table, ['balance' => $new_balance], ['borrower_id' => $borrowerId]);
        return $update;
    }
    public static function check_balance($db, $borrowerId)
    {
        $profile = $db->select(static::$profiles_table, [], ['borrower_id' => $borrowerId])[0];
        $current_balance = $profile['balance'];
        return $current_balance;
    }
    public static function delete_account(DbAccess $db, $borrowerId)
    {
    }
    public static function getLGFAccountId($db)
    {
        $LGF_config = $db->select('config', [], ['name' => LOAN_GUARANTEE_FUND_ACCOUNT])[0];
        $account_id = $LGF_config['value'];
        return $account_id;
    }
    public static function check_profie($db, $borrowerId)
    {
        $profile = $db->select(static::$profiles_table, [], ['borrower_id' => $borrowerId])[0];
        if ($profile) {
            return true;
        } else {
            return false;
        }
    }
}
