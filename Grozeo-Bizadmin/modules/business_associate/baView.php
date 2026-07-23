<html>
<style>
    table {
        font-size: 15px;
    }

    .details_view_table {
        border-collapse: collapse;
    }

    .details_view_table td,
    .details_view_table th {
        border-bottom: 1px solid #EDEDED;
        font-family: Source Sans Pro, Verdana, Geneva, sans-serif;
        font-size: 12px;
        padding: 3px;
    }

    .details_view_table th {
        padding-right: 21px;
        padding-left: 14px;
        width: 35%;
        vertical-align: top;
        font-weight: inherit;
        text-align: left;
        line-height: 24px;
    }

    .details_view_table td {
        font-weight: bold;
    }

    .details_view_table td span {
        float: left;
        width: 95%;
        text-align: justify;
        font-weight: normal;
    }

    .details_view_table th[colspan="2"] {
        font-size: 13px;
        background: #f0f0f0 none repeat scroll 0 0;
        border-color: #d7d7d7;
    }

    #map {
        height: 400px;
        width: 100%;
    }
</style>

<?php if (!empty($data)) { ?>
    <div class="details-outer">
        <table cellspacing="2" cellpadding="2" border="0" width="100%" class="details_view_table">
            <tbody>
                <?php if ($data['areaName'] != '') { ?>
                    <tr>
                        <th width="275">Name</th>
                        <td width="675"><?php echo $data['areaName']; ?></td>
                    </tr>
                <?php }
                if ($data['areaLocation'] != '') { ?>
                    <tr>
                        <th width="275">Location</th>
                        <td width="675"><?php echo $data['areaLocation']; ?></td>
                    </tr>
                <?php }
                if ($data['areaSpan'] != '') { ?>
                    <tr>
                        <th width="275">Area Span</th>
                        <td width="675"><?php echo $data['areaSpan'] . DISTANCE; ?> </td>
                    </tr>
                <?php }
                if ($data['areaLatitude'] != '') { ?>
                    <tr>
                        <th width="275">Latitude</th>
                        <td width="675"><?php echo $data['areaLatitude']; ?></td>
                    </tr>
                <?php }
                if ($data['areaLongitude'] != '') { ?>
                    <tr>
                        <th width="275">Longitude</th>
                        <td width="675"><?php echo $data['areaLongitude']; ?></td>
                    </tr>
                <?php }
                if ($data['areaBusinessAssociate'] != '') { ?>
                    <tr>
                        <th width="275">Business Associate</th>
                        <td width="675"><?php echo $data['areaBusinessAssociateName']; ?></td>
                    </tr>
                <?php }
                if ($data['areaState'] > 0) { ?>
                    <tr>
                        <th width="275">State</th>
                        <td width="675"><?php echo $data['st_name']; ?></td>
                    </tr>
                <?php }
                if ($data['areaDistrict'] > 0) { ?>
                    <tr>
                        <th width="275">District</th>
                        <td width="675"><?php echo $data['dst_Name']; ?></td>
                    </tr>
                <?php }
                if ($data['divisionId'] > 0) { ?>
                    <tr>
                        <th width="275">Territory</th>
                        <td width="675"><?php echo $data['areaTerritoryName']; ?></td>
                    </tr>
                <?php }
                if ($data['areaLockedFor'] > 0 && $data['areaBusinessAssociate'] == '') {
                    $lockedFor = $supportdb->getItemFromDB("SELECT name FROM crm_area_associate WHERE id = {$data['areaLockedFor']}");
                ?>
                    <tr>
                        <th width="275">Locked Till</th>
                        <td width="675"><?php echo $data['areaLockedTill']; ?></td>
                    </tr>
                    <tr>
                        <th width="275">Locked For</th>
                        <td width="675"><?php echo $lockedFor; ?></td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    </div>
    <div id="map"></div>
<?php } else { ?>
    sorry there is no available data to display
<?php } ?>
<script>
    var citymap = {
        <?php echo $data['areaName']; ?>: {
            center: {
                lat: <?php echo $data['areaLatitude']; ?>,
                lng: <?php echo $data['areaLongitude']; ?>
            },
            areaSpan: <?php echo $data['areaSpan']; ?>,
        }
    };
    console.log('citymap', citymap);

    function initMap() {
        var map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: {
                lat: <?php echo $data['areaLatitude']; ?>,
                lng: <?php echo $data['areaLongitude']; ?>
            },
            mapTypeId: "terrain",
        });

        for (var city in citymap) {
            new google.maps.Circle({
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.35,
                map: map,
                center: citymap[city].center,
                radius: citymap[city].areaSpan * 1000,
            });
            
            // Add a marker at the city's center
            new google.maps.Marker({
                position: citymap[city].center,
                map: map,
                title: city,
            });
        }
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?&libraries=places&key=<?= GOOGLE_MAP_API_KEY; ?>&callback=initMap"></script>

</html>