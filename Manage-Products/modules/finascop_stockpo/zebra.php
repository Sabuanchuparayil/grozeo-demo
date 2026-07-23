<?php
//$outputType = 'viewsource';
if($_REQUEST['stii_id'] ==""){
  $where = "where stii_id=".$stiiId['stii_id'];
}
else{
  $where = "where stii_id=".$_REQUEST['stii_id']." AND stiid_barcode BETWEEN ".$_REQUEST['stiid_barcode']." AND ".$_REQUEST['stiid_Tobarcode'];
}
$barcodeText = $db->getMultipleData("SELECT stiid_barcode FROM finascop_stock_item_inventorydetails $where");
$quantity = count($barcodeText);
 $name =  'GOGO' ;
if($quantity>0){
$zebra = "";
$j=1;
for($i=0;$i<$quantity;$i++){

if($j%5==1){
$zebra.= 'CT~~CD,~CC^~CT~
^XA~TA000~JSN^LT0^MNW^MTD^PON^PMN^LH0,0^JMA^PR4,4~SD15^JUS^LRN^CI0^XZ
^XA
^MMT
^PW847
^LL0120
^LS0';
$zebra.='
^FT50,31^A0N,17,13^FH\^FD'.$name.'^FS
^BY1,3,59^FT47,95^BCN,,N,N
^FD>;'.$barcodeText[$i].'^FS
^FT57,115^A0N,20,15^FH\^FD'.$barcodeText[$i].'^FS';
}
else if($j%5==2){
$zebra.='
^FT210,28^A0N,17,13^FH\^FD'.$name.'^FS
^BY1,3,59^FT207,95^BCN,,N,N
^FD>;'.$barcodeText[$i].'^FS
^FT217,115^A0N,20,15^FH\^FD'.$barcodeText[$i].'^FS';
}
else if($j%5==3){
$zebra.='
^FT370,28^A0N,17,13^FH\^FD'.$name.'^FS
^BY1,3,59^FT367,95^BCN,,N,N
^FD>;'.$barcodeText[$i].'^FS
^FT377,115^A0N,20,15^FH\^FD'.$barcodeText[$i].'^FS';
}
else if($j%5==4){
$zebra.='
^FT530,28^A0N,17,13^FH\^FD'.$name.'^FS
^BY1,3,59^FT527,95^BCN,,N,N
^FD>;'.$barcodeText[$i].'^FS
^FT537,115^A0N,20,15^FH\^FD'.$barcodeText[$i].'^FS';
}
else if($j%5==0){
$zebra.='
^FT690,28^A0N,17,13^FH\^FD'.$name.'^FS
^BY1,3,59^FT687,95^BCN,,N,N
^FD>;'.$barcodeText[$i].'^FS
^FT697,115^A0N,20,15^FH\^FD'.$barcodeText[$i].'^FS';
$zebra.='
^PQ1,0,1,Y^XZ

';
}
if($j==$quantity){
if($j%5==1 || $j%5==2 ||$j%5==3 ||$j%5==4){
$zebra.='
^PQ1,0,1,Y^XZ

';
}
}
$j++;
}
echo $zebra;
}





?>
