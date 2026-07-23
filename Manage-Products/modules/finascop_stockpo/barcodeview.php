<style>
img.barcode {
  border: 1px solid #ccc;
padding: 2px;
border-radius: 5px;
margin: 2px;
}
.bcimg{
  width: 40%;
  margin: 0 auto;
  padding: 5px;
  float:left;
}
.barcodeCont{
  width : 500px;
  background-color: grey;
}
</style>
<div class="barcodeCont">
	<div class="row">

		<div class="col-md-4">
		 <?php
				if($_REQUEST['stii_id'] ==""){
					$where = "where stii_id=".$stiiId['stii_id'];
				}
				else{
					$where = "where stii_id=".$_REQUEST['stii_id'];
				}
				$barcodeType="code128";
				$barcodeDisplay="horizontal";
				$barcodeSize="20";
				$printText="true";
				$barcodeText = $db->getMultipleData("SELECT stiid_barcode FROM finascop_stock_item_inventorydetails $where");
				 $quantity = count($barcodeText);
				if($barcodeText != '') {
					for($i=0;$i<$quantity;$i++){
					echo '<div class="bcimg"><img class="barcode" alt="'.$barcodeText[$i].'" src="barcode.php?text='.$barcodeText[$i].'&codetype='.$barcodeType.'&orientation='.$barcodeDisplay.'&size='.$barcodeSize.'&print='.$printText.'"/></div>';
					}

				} else {
					echo '<div class="alert alert-danger">Enter product name or number to generate barcode!</div>';
				}

		?>
		</div>
	</div>

</div>
