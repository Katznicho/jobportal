<?php

namespace Ssentezo\Transaction;

use Exception;
use Ssentezo\Accounting\ExisitingAccount;
use call_user_method;

class GLTransaction extends Transaction
{
    protected $accountsDetails = [];

    function __construct($db)
    {
        parent::__construct($db);
    }
    /**
     * We add an account to a transaction, this adds to the details to the account details array that is used to make a complete transaction
     * @param ExistingAccount $account an instance of the account on which the operations will be made
     * @param string $method A method of the Existing account class that will be invoked to complete the transaction
     * @param float $amount The amount that will be debited or credited on the account
     * @param string $type The type of the transaction D means debit and C means credit (Make sure you know the meanings of credit and debit)
     * @return void
     */
    public function addAccountDetails(ExisitingAccount $account, string $method, float $amount, string $type)
    {
        $this->accountsDetails[] = array(
            'account' => $account,
            'method' => $method,
            'amount' => $amount,
            'type' => $type
        );
    }
    protected function effectTransactions()
    {
        foreach ($this->accountsDetails as $accountDetail) {

            // do an increase or decrease
            // call_user_method($accountDetail['method'], $accountDetail['account']);
            // call_user_method_array();
        }
    }
    public function save()
    {
        $this->effectTransactions();
    }
}
