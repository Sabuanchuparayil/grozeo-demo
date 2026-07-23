<?php 

if(isset($type) && ($type=="web") ){

?>
<script src="{{ asset('js/app.js') }}"></script>
<script >
		console.log('/order/complete/<?php echo $status;?>/<?php echo $paymentId;?>');
        jQuery(document).ready(function () {
            var delay = 2000;
            setTimeout(function () {
                  window.opener.location = '/order/complete/<?php echo $status;?>/<?php echo $paymentId;?>';
                    window.close();
            }, delay);

            
        });
    </script>
<?php }?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title><?php  echo config('siteinfo.app_client_project_name'); ?></title>
</head>
<body>
    <?php 

if(!isset($type)  ){

?>

    <h2>Payment {{ $status }}</h2>
    <h3>Returning to Application</h3>
    <?php } ?>
</body>
</html>
