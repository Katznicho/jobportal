<?php

namespace Ssentezo\Accounting;

class NewAccount extends Account
{
    function __construct($name, $category, $accNumber, $balance)
    {
        $this->name = $name;
        $this->category = $category;
        $this->accNumber = $accNumber;
        $this->balance = $balance;
    }
}
