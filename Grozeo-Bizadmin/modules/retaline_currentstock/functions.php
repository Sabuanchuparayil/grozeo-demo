<?php

function dispatchCount($branchId,$stit_ID){
     global $db;
     $query = "SELECT GROUP_CONCAT(order_id) AS orderIds FROM retaline_branch_outward_order WHERE cpd_id = {$branchId} AND order_status = 6 ";
     $orderCount = $db->getItemFromDB($query);
     if($orderCount != ''){
         $neQuery = "SELECT bcod_count FROM retaline_branch_outward_order_items where bcod_id IN ({$orderCount}) and stit_ID = {$stit_ID}";
         $dispCount = $db->getItemFromDB($neQuery);
         return intval($dispCount);
     } 
     
}

