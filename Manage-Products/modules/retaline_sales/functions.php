<?php

function generateOrderNo($cpdId, $lastOrderNo) {
    return 'PKT' . str_pad($cpdId, 3, '0', STR_PAD_LEFT) . str_pad($lastOrderNo, 7, '0', STR_PAD_LEFT);
}

function getIndianCurrencyToWords(float $number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $counter = count($str);
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. ' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    if($decimal > 10 and $decimal < 20){
        $paise = ($decimal > 0) ? "And " . ($words[$decimal]) . ' Paise' : '';
    }else{
        $paise = ($decimal > 0) ? "And " . ($words[intval($decimal/10,10)*10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    }
    
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}

