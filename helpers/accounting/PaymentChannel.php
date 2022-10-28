<?php

namespace Ssentezo\Accounting;

use AppUtil;

class PaymentChannel
{
    protected $id;
    protected $name;
    protected $initialBalance;
    protected $initialBalanceDate;
    protected $balance;
    protected $amountIn;
    protected $amountOut;
    protected $createdBy;
    protected $createdOn;
    protected $lastModifiedOn;
    protected $lastModifiedBy;
    protected $activeFlag;
    protected $delFlag;
    function __construct($channel)
    {
        $this->id = $channel['id'];
        $this->name =  $channel['name'];
        $this->balance = $channel['balance'];
        $this->amountIn = $channel['amount_in'];
        $this->amountOut =  $channel['amount_out'];
        $this->initialBalance =  $channel['initial_balance'];
        $this->initialBalanceDate = $channel['initial_balance_date'];
        $this->createdBy =  $channel['created_by'];
        $this->createdOn =  $channel['created_on'];
        $this->lastModifiedOn = $channel['last_modified_on'];
        $this->lastModifiedBy = $channel['last_modified_by'];
        $this->activeFlag = $channel['active_flag'];
        $this->delFlag = $channel['del_flag'];
    }
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getInitialBalance()
    {
        return $this->initialBalance;
    }
    public function getInitialBalanceDate()
    {
        return $this->initialBalanceDate;
    }
    public function getBalance()
    {
        return $this->balance;
    }
    public function getAmountIn()
    {
        return $this->amountIn;
    }
    public function getAmountOut()
    {
        return $this->amountOut;
    }
    public function getcreatedBy()
    {
        return $this->createdBy;
    }
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
    public function getLastModifiedOn()
    {
        return $this->lastModifiedOn;
    }
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }
    public function getActiveFlag()
    {
        return $this->activeFlag;
    }
    public function getDelFlag()
    {
        return $this->delFlag;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setInitialBalance($initialBalance)
    {
        $this->initialBalance = $initialBalance;
    }
    public function setInitialBalanceDate($initialBalanceDate)
    {
        $this->initialBalanceDate = $initialBalanceDate;
    }
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }
    public function setAmountIn($amountIn)
    {
        $this->amountIn = $amountIn;
    }
    public function setAmountOut($amountOut)
    {
        $this->amountOut = $amountOut;
    }
    public function setcreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }
    public function setLastModifiedOn($lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;
    }
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }
    public function setActiveFlag($activeFlag)
    {
        $this->activeFlag = $activeFlag;
    }
    public function setDelFlag($delFlag)
    {
        $this->delFlag = $delFlag;
    }
    public function update($db)
    {
        $data = array(
            // 'id'=>$this->id,
            'name' => $this->name,
            'balance' => $this->balance,
            'amount_in' => $this->amountIn,
            'amount_out' => $this->amountOut,
            'initial_balance' => $this->initialBalance,
            'initial_balance_date' => $this->initialBalanceDate,
            'created_by' => $this->createdBy,
            'created_on' => $this->createdOn,
            'last_modified_on' => $this->lastModifiedOn,
            'last_modified_by' => $this->lastModifiedBy,
            'active_flag' => $this->activeFlag,
            'del_flag' => $this->delFlag
        );
        $result = $db->update('payment_channels', $data, ['id' => $this->id]);

        return $result;
    }
    public function payOut($amount, $db)
    {
        // Update the balance to reflect the new amount
        // A payout reduces the balance
        $this->balance -= $amount;
        // The amount out
        $this->amountOut += $amount;
        $data = array(
            // 'id'=>$this->id,
            // 'name' => $this->name,
            'balance' => $this->balance,
            // 'amount_in' => $this->amountIn,
            'amount_out' => $this->amountOut,
            // 'initial_balance' => $this->initialBalance,
            // 'initial_balance_date' => $this->initialBalanceDate,
            // 'created_by' => $this->createdBy,
            // 'created_on' => $this->createdOn,
            'last_modified_on' => $this->lastModifiedOn,
            'last_modified_by' => $this->lastModifiedBy,
            // 'active_flag' => $this->activeFlag,
            // 'del_flag' => $this->delFlag
        );
        $result = $db->update('payment_channels', $data, ['id' => $this->id]);
        if (is_numeric($result)) {
            // This is for success
            return true;
        }
        return false;
    }
    public function receive($amount, $db)
    {
        // Update the balance to reflect the new amount
        // this increase the balance
        $this->balance += $amount;
        // The amount in increases
        $this->amountIn += $amount;
        $data = array(
            // 'id'=>$this->id,
            // 'name' => $this->name,
            'balance' => $this->balance,
            'amount_in' => $this->amountIn,
            // 'amount_out' => $this->amountOut,
            // 'initial_balance' => $this->initialBalance,
            // 'initial_balance_date' => $this->initialBalanceDate,
            // 'created_by' => $this->createdBy,
            // 'created_on' => $this->createdOn,
            'last_modified_on' => $this->lastModifiedOn,
            'last_modified_by' => $this->lastModifiedBy,
            // 'active_flag' => $this->activeFlag,
            // 'del_flag' => $this->delFlag
        );
        $result = $db->update('payment_channels', $data, ['id' => $this->id]);
        if (is_numeric($result)) {
            // This is for success
            return true;
        }
        return false;
    }
    public static function createChannel($db, $name, $balance, $balanceDate)
    {

        // Update the balance to reflect the new amount
        // A payout reduces the balance
        $data = array(
            'name' => $name,
            'balance' => $balance,
            'initial_balance' => $balance,
            'initial_balance_date' => $balanceDate,
            'created_by' => AppUtil::userId(),
        );
        $result = $db->insert('payment_channels', $data);
        return $result;
    }
}
