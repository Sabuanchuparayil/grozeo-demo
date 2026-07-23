<?php 
if($_REQUEST['type'] == 1){
$images = $db->getFromSafe("SELECT image_front,image_back,image_top,image_bottom,image_left,image_right,image_top_left,image_top_right from gs1_products_extension where id = ?", "i", [$_REQUEST['gs1Id']], true);
}else{
	$images = $db->getFromSafe("SELECT image_front,image_back,image_top,image_bottom,image_left,image_right,image_top_left,image_top_right from gs1_products_source where id = ?", "i", [$_REQUEST['gs1Id']], true);
}
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title></title>
        <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="resources/css/orderinvoice-style.css" media="all" />
    </head>

    <body>
        <header class="clearfix">

        </header>
        <main>
            <div class="left-area-block">
                
                    <table border="1" cellspacing="0" cellpadding="0" class="ship-detail-table">
                        <tbody>
<?php if(!empty($images['image_front'])){?>
                        <tr><td><a href ="<?php echo $images['image_front'];?>" target="_blank">Image Front</a></td>
						<td><img  src="<?php echo $images['image_front'];?>" alt="Image Front" height = "250" width = "270" /></td></tr>
<?php }?>
<?php if(!empty($images['image_back'])){?>
                        <tr><td><a href ="<?php echo $images['image_back'];?>" target="_blank">Image Back</a></td>
						<td><img  src="<?php echo $images['image_back'];?>" alt="Image Back" height = "250" width = "270"/></td></tr>
<?php }?>
<?php if(!empty($images['image_top'])){?>
                        <tr><td><a href ="<?php echo $images['image_top'];?>" target="_blank">Image Top</a></td>
						<td><img  src="<?php echo $images['image_top'];?>" alt="Image Top" height = "250" width = "270"/></td></tr>
<?php }?>
<?php if(!empty($images['image_bottom'])){?>
                        <tr><td><a href ="<?php echo $images['image_bottom'];?>" target="_blank">Image Bottom</a></td>
						<td><img  src="<?php echo $images['image_bottom'];?>" alt="Image Bottom"  height = "250" width = "270"/></td></tr>
<?php }?>
<?php if(!empty($images['image_left'])){?>
                        <tr><td><a href ="<?php echo $images['image_left'];?>" target="_blank">Image Left</a></td>
						<td><img  src="<?php echo $images['image_left'];?>" alt="Image Left" height = "250" width = "270"/></td></tr>
<?php }?>
<?php if(!empty($images['image_right'])){?>
                        <tr><td><a href ="<?php echo $images['image_right'];?>" target="_blank">Image Right</a></td>
						<td><img  src="<?php echo $images['image_right'];?>" alt="Image Right" height = "250" width = "270" /></td></tr>
<?php }?>
<?php if(!empty($images['image_top_left'])){?>
                        <tr><td><a href ="<?php echo $images['image_top_left'];?>" target="_blank">Image Top Left</a></td>
						<td><img  src="<?php echo $images['image_top_left'];?>" alt="Image Top Left" height = "250" width = "270" /></td></tr>
<?php }?>
<?php if(!empty($images['image_top_right'])){?>
                        <tr><td><a href ="<?php echo $images['image_top_right'];?>" target="_blank">Image Top Right</a></td>
						<td><img  src="<?php echo $images['image_top_right'];?>" alt="Image Top Right" height = "250" width = "270" /></td></tr>
<?php }?>

                        </tbody>
                        </table>
                
            </div>
        </main>
    </body>

</html>