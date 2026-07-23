<?php
//print_r($_REQUEST);
$count = $_REQUEST['count'];
$packets = $_REQUEST['packets'];
$ordePackets = explode(',', $packets)
?>

<html>
    <style>
        .cesstable {
            border: 1px solid #CECECE;
            text-align: left;
        }
        table {
            font-family: arial;
            font-size: 11px;
            border-collapse: collapse;
            border-spacing: 0;
        }
        h4 {
            font-family: arial;
            font-size: 13px;
            font-weight: bold;
            padding: 3px 0;
        }
        .cesstable td {
            border-color: -moz-use-text-color #CECECE #CECECE -moz-use-text-color;
            border-style: none solid solid none;
            border-width: 0 1px 1px 0;
            height: 22px;
            padding: 0 10px 0 12px;
            vertical-align: middle;
        }
    </style>

    <?php
    if ($count > 0) {
        ?>


        <h4>This order has <?php echo $count; ?> box</h4>  
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>


                <?php for ($p = 0; $p < $count; $p++) {
                    $si = $p + 1;
                    ?>
                    <tr>
                        <td>
                            Packet <?php echo $si; ?>
                        </td>
                        <td>
                            <b> <?php echo $ordePackets[$p]; ?> </b>
                        </td>
                    </tr>
    <?php } ?>

            </tbody>
        </table>
        <?php
    } else {
        ?>
        sorry there is no available data to display
<?php } ?>




</html>

