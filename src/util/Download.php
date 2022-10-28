<?php

namespace App\Util;

trait Download
{
    function createCsvFile($headings, $purpose)
    {
        echo "Hello";
        ob_clean();
        $fileName = "$purpose Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", $headings);
    }
}
