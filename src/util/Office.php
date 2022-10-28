<?php

namespace App\Util;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel
{
    protected $spreadsheet;
    function __construct($file)
    {
        $spreadsheet =  IOFactory::load($file);
    }
}
