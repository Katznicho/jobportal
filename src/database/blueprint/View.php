<?php

namespace App\Database\BluePrint;

class View
{
    public $schema;
    public function __construct($schema)
    {
        $this->schema = $schema;
    }
}
