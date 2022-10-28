<?php

namespace Ssentezo\Util;

class BulkUpload
{

    /**
     * @param string $fileName The name of the csv file that has you want to load into an associative array
     * @param array $columnMappings An associative array whose keys are the column headings in the csv file and values will be the keys of the returned associative array.
     * @return array An array of associative arrays for each row whose keys are the column names of the csv files with the corresponding values
     */
    public static function fetchAssocFromCsvFile($fileName, $columnMappings)
    {
        $dataAssoc = array();
        $heading = array();
        $flag = 0; //We want to skip the heading
        // Attempt to open the file for reading
        if (($handle = fopen($fileName, 'r')) !== FALSE) {

            // Read the csv file line by line
            $row = [];
            while (($data = fgetcsv($handle, 0, ',', '\\')) !== FALSE) {
                // Skip the csv file heading.

                if ($flag) {
                    for ($i = 0; $i < count($heading); $i++) {
                        $key = $columnMappings[trim($heading[$i])];
                        $value = trim($data[$i]);
                        $row[$key] = $value;
                    }
                    $dataAssoc[] = $row;
                } else {
                    $heading = $data;
                    $flag = 1;
                }
            }
            // Close the file 
            fclose($handle);

            return $dataAssoc;
        } else {
            return array(
                "error" => true,
                "message" => "Can't open file $fileName"
            );
        }
    }

    public static function createCsvFile($headings,$purpose)
    {
        ob_clean();
        $fileName = "$purpose Ssentezo-" . date("l F d Y h-i-s a", time()) . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.ms-excel");
        echo  implode(",", $headings);
        exit;
    }
}
