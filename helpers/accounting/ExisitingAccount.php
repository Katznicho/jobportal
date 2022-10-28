<?php

namespace Ssentezo\Accounting;

class ExisitingAccount extends Account
{
    /**
     * @param array $account an associative array of the account details as directly fetched from the db
     * @return void
     */
    function __construct($account)
    {
        $this->setId($account['id']);
        $this->name = $account['name'];
        $this->category = $account['category'];
        $this->accNumber = $account['account_no'];
        $this->balance = $account['balance'];
        $this->createdBy = $account['created_by'];
        $this->isLeaf = $account['is_leaf'];
        $this->initialBalance = $account['initial_balance'];
        $this->initialBalanceDate = $account['initial_balance_date'];
        $this->createdOn = $account['created_on'];
        $this->modifedBy =  $account['modified_by'];
        $this->modifiedOn =  $account['modified_on'];
        $this->activeFlag = $account['active_flag'];
        $this->delFlag =  $account['del_flag'];
        $this->subCategoryId = $account['sub_category_id'];
    }
    public function describe()
    {
        return $this->accNumber . " - " . $this->name;
    }
}
