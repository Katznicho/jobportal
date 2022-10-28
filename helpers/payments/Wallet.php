<?php

namespace Ssentezo\Payments;

class Wallet
{

    private $balance;
    private  $name;

    /**
     * @Constructor
     */


    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * This will add (or subtract with negitive numbers) the current balance.
     */
    public function add($num)
    {
        return $this->balance += $num;
    }

    /**
     * This will load the wallet based on it's name from a file.
     */
    private function load()
    {
        try {
        } catch (\Throwable $th) {
        }
    }

    /**
     * this will write the the object to a file named after itself.
     */
    public function save()
    {
        try {
        } catch (\Throwable $th) {
        }
    }
}
