<?php

namespace Ssentezo\Database\BluePrint;

class Table
{
    public $schema;
    public function __construct($schema)
    {
        $this->schema = $schema;
    }
}
