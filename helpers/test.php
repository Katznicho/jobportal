
<script>

    /*  var doc = new jsPDF();
     
     doc.addPage('a6','l');  Add Page.
    
     *Auto print  
     // Set the document to automatically print via JS
     doc.autoPrint()
     *
     **/

    var doc = new jsPDF();

// We'll make our own renderer to skip this editor
    var specialElementHandlers = {
        '#editor': function (element, renderer) {
            return true;
        }
    };

// All units are in the set measurement for the document
// This can be changed to "pt" (points), "mm" (Default), "cm", "in"
    doc.fromHTML($('body').get(0), 15, 15, {
        'width': 170,
        'elementHandlers': specialElementHandlers
    });
</script>

<?php

include './DbAcess.php';
include_once '../helpers/AppUtil.php';

echo date("Y");
echo '</br>';

$principal = 50000;
//$interest=18;
//$term=30;
//Same as 
//$interest=0.05*12;//*30*12;
//$term=1;//*30;
//New
$interest = 10 * 12; //For Daily its Interest * 12; and Maintain the $term/duration
//Weeekly Its the same
$term = 10;

echo ($principal * .1) . '</br>';

$result = AppUtil::calculateMonthlyPayments($principal, $interest, $term);
print_r(number_format($result, 2));
echo '</br>';

$totalInt = AppUtil::getTotalInterest($principal, $interest, $term);

print_r(number_format($totalInt, 2));
echo '</br>';

$totalInt = AppUtil::other($principal, $interest, $term);
print_r(number_format($totalInt, 2));
echo '     Sre</br>';



/*echo 'Testing Connection ***';
echo date("Y-m-d");

$db = new DbAcess();
$table = "borrower";

//$result=$db->select($table);
//print_r($result);
echo 'Result ****///';

/*echo date('Y-m-d h:i:s');

echo '*** ' . time() . '</br>';
echo $_SERVER['DOCUMENT_ROOT'] . '</br>';
echo dirname(__DIR__);

/**
 * @desc    Calculates the monthly payments of a loan
 *             based on the APR and Term.
 *
 * @param    Float    $fLoanAmount    The loan amount.
 * @param    Float    $fAPR            The annual interest rate.
 * @param    Integer    $iTerm            The length of the loan in months.
 * @return    Float    Monthly Payment.
 */
/*function calculateMonthlyPayments($fLoanAmount, $fAPR, $iTerm) {
    return ($fLoanAmount / $iTerm) + (($fLoanAmount / $iTerm) / 100 * ($fAPR / 12 * $iTerm));
}

echo '</br>**** </br></br>';

echo (calculateMonthlyPayments(30000, (15*30*12), (7/30))/30);
echo ' HHH</br></br></br>';

/*
 * @param float $apr   Interest rate.
 * @param integer $term  Loan length in years. 
 * @param float $loan   The loan amount.

 */

/*function calPMT($apr, $term, $loan) {
    $term = $term * 12;
    $apr = $apr / 1200;
    $amount = $apr * -$loan * pow((1 + $apr), $term) / (1 - pow((1 + $apr), $term));
    return round($amount);
}

echo calPMT(20, 6, 60000);

/**
 * FV
 *
 * Returns the Future Value of a cash flow with constant payments and interest rate (annuities).
 *
 * @param   float   $rate   Interest rate per period
 * @param   int     $nper   Number of periods
 * @param   float   $pmt    Periodic payment (annuity)
 * @param   float   $pv     Present Value
 * @param   int     $type   Payment type: 0 = at the end of each period, 1 = at the beginning of each period
 * @return  float
 */
/*function FV($rate = 0, $nper = 0, $pmt = 0, $pv = 0, $type = 0) {

    // Validate parameters
    if ($type != 0 && $type != 1) {
        return False;
    }

    // Calculate
    if ($rate != 0.0) {
        return -$pv * pow(1 + $rate, $nper) - $pmt * (1 + $rate * $type) * (pow(1 + $rate, $nper) - 1) / $rate;
    } else {
        return -$pv - $pmt * $nper;
    }
}

//  function FV()

echo '</br></br></br>';


echo FV();

function PMT($i, $n, $p) {

    return $i * $p * pow((1 + $i), $n) / (1 - pow((1 + $i), $n));
}

function ROUNDUP($n) {

    return round(ceil($n * 1000) / 1000, 2);
}

function PV($R, $n, $pmt, $m = 1) {

    $Z = 1 / (1 + ($R / $m));

    return ($pmt * $Z * (1 - pow($Z, $n))) / (1 - $Z);
}

/**
  * $PMT = (-$fv - $pv * pow(1 + $rate, $nper)) /
    (1 + $rate * $type) /
    ((pow(1 + $rate, $nper) - 1) / $rate);
  * 
    $rate = interest rate
    $nper = number of periods
    $fv is future value
    $pv is present value
    $type is type

The formula that I use in PHPExcel to reflect MS Excel's formula is:

$PMT = (-$fv - $pv * pow(1 + $rate, $nper)) /
    (1 + $rate * $type) /
    ((pow(1 + $rate, $nper) - 1) / $rate);

where

    $rate = interest rate
    $nper = number of periods
    $fv is future value
    $pv is present value
    $type is type

Which returns the same result as MS Excel when I use

=PMT(6%/12, 360, 0, 833333, 0)

And which returns a result of -10540.755358736 (the same as MS Excel) when I use

=PMT(0.06,30,0,833333,0)
  */
