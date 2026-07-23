<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {

    case 'getpartyData':
        $data = $_POST;
        $br_id = $_SESSION['admin']->finascop_current_branch_id;

        if (!empty($data['query'])){
            $qry = "SELECT stpa_id AS partyId, CONCAT(stpa_Fname, ' ',stpa_Lname) AS partyName  FROM " . FINASCOP_DB . " finascop_stock_party a "
            . "WHERE br_id = {$br_id} AND a.stpa_IsVendor = 1 AND CONCAT(stpa_Fname, ' ',stpa_Lname) LIKE '%{$data['query']}%'";
        }else{
            $qry = "SELECT 'Combined' AS partyId, 'Combined' AS partyName FROM finascop_stock_party LIMIT 0, 1";   
        }

        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
    break;

    case 'getSKUs':
        $data = $_POST;
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        if (!empty($data['query'])){
            $qry = "SELECT stit_ID,stit_SKU FROM finascop_stock_itemmaster WHERE stit_SKU LIKE '%{$data['query']}%'";
        }else{
            $qry = "SELECT 'All' AS stit_ID, 'All' AS stit_SKU FROM finascop_stock_itemmaster LIMIT 0, 1";   
        }


        $result = $db->getMulipleData($qry, true);
        if (!empty($result)) {
            echo json_encode($result);
        } else
            echo [];
    break;

    case 'getViewableBranches':
        $data = $_POST;
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        if (!empty($data['query'])){
            $qry = "SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' "
            ." AND (br_cpd={$branch_id} OR br_ID={$branch_id}) AND br_Name LIKE '%{$data['query']}%'";
        }else{
            $qry = "SELECT 'Combined' AS br_ID, 'Combined' AS br_Name FROM finascop_branch LIMIT 0, 1";   
        }

        $result = $db->getMulipleData($qry, true);
        
        if (!empty($result)) {
            echo json_encode($result);
        } else
            echo [];
        break;

    case 'getAggreateDetails':

    $fromAggreateDetails = true;
    case 'getLiveStock':
        
        $data = $_POST;    
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_ID' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $db->query('begin');

        $innQryWhereCondElm = [];
        $SelectElm = [];
        $WhereCondElm = [];
        $HavingCondElm = [];
        $groupByElm [0] = 1;
        $comma = '';
        $innWHERE = $WHERE='';
        $HAVING = '';

        $SelectSumElm[0] = 'COUNT(fsb.stit_id) as totCount';

        $filter = array();

        if (isset($_POST['filter']) && $_POST['filter'] != '') {

            foreach ($_POST['filter'] as $key => $v) {
                $field = $v['field'];
                unset($v['field']);
           
                switch ($v['data']['type']) {
                    case 'string':
                        $filter[$field]['data']['value'] =  $v['data']['value'];
                    case 'numeric':
                       
                        switch ($v['data']['comparison']) {
                            case 'gt' :
                                $filter[$field]['data']['comparison']['gt'] = 'gt';
                                $filter[$field]['data']['value']['gt'] =  $v['data']['value'];
                                 break;
                            case 'lt':
                                $filter[$field]['data']['comparison']['lt'] = 'lt';
                                $filter[$field]['data']['value']['lt'] =  $v['data']['value'];
                                break;
                            case 'eq':
                                $filter[$field]['data']['comparison']['eq'] = 'eq';
                                $filter[$field]['data']['value']['eq'] =  $v['data']['value'];
                                break;
                        }

                        break;
                    case 'date':
                        switch ($v['data']['comparison']) {
                            case 'gt' :
                                $filter[$field]['data']['comparison']['gt'] = 'gt';
                                $filter[$field]['data']['value']['gt'] =  $v['data']['value'];
                                 break;
                            case 'lt':
                                $filter[$field]['data']['comparison']['lt'] = 'lt';
                                $filter[$field]['data']['value']['lt'] =  $v['data']['value'];
                                break;
                            case 'eq':
                                $filter[$field]['data']['comparison']['eq'] = 'eq';
                                $filter[$field]['data']['value']['eq'] =  $v['data']['value'];
                                break;
                        }

                        break;
                }
            }
        }

       
        if($data['stit_IDs'] != 'All'){
            array_push($SelectElm," fsb.stit_id");
            $itemNameQry = " AND stit_SKU like '%" . $filter['stit_SKU']['data']['value'] . "%'";
            $data['stit_IDs'] =  $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_id IN ({$data['stit_IDs']}) {$itemNameQry}");
            ////print_r( $data['stit_IDs']);
            array_push($WhereCondElm,"fsb.stit_id IN ({$data['stit_IDs']}) "); 
            array_push($innQryWhereCondElm, " inn.stit_id = fsb.stit_id ");
            array_push($groupByElm,"fsb.stit_id");
            $comma = ',';
            $innWHERE = $WHERE=' WHERE ';
        }else{
            array_push($SelectElm," fsb.stit_id");
            array_push($WhereCondElm," 1 = 1 "); 
            array_push($innQryWhereCondElm, " inn.stit_id = fsb.stit_id ");
            array_push($groupByElm,"fsb.stit_id");
            $comma = ',';
            $innWHERE = $WHERE=' WHERE ';   
        }

        if($data['br_IDs'] != 'Combined'){
            array_push($SelectElm,"fsb.branch_id");
            array_push($WhereCondElm,"fsb.branch_id IN ({$data['br_IDs']}) "); 
            array_push($innQryWhereCondElm, " inn.branch_id = fsb.branch_id");
            array_push($groupByElm,"fsb.branch_id");
            $comma = ',';
            $innWHERE = $WHERE=' WHERE ';
        }

        if($data['mrp'] != 'Combined'){
            array_push($SelectElm,"fsbg.fsbg_mrp as mrp");
            array_push($SelectSumElm,"SUM(fsb.item_count * fsbg.fsbg_mrp) totMRP");
            array_push($WhereCondElm,"1 = 1"); 
            array_push($innQryWhereCondElm, " inn.stit_id = fsb.stit_id ");
            array_push($groupByElm,"fsbg.fsbg_mrp");
            if (isset($filter['mrp']) &&  $filter['mrp'] != '') {
                $HAVING = ' HAVING ';
                if($filter['mrp']['data']['comparison']['gt'] == 'gt')
                        array_push($HavingCondElm ,"mrp > " . $filter['mrp']['data']['value']['gt']);
                if($filter['mrp']['data']['comparison']['lt'] == 'lt')
                        array_push($HavingCondElm ,"mrp < " . $filter['mrp']['data']['value']['lt']);
                if($filter['mrp']['data']['comparison']['eq'] == 'eq')
                        array_push($HavingCondElm ,"mrp = " . $filter['mrp']['data']['value']['lt']);
            }  
            $comma = ',';
            $innWHERE = $WHERE=' WHERE ';
        }else{
            if (isset($filter['mrp']) &&  $filter['mrp'] != '') {
                $HAVING = ' HAVING ';
                if($filter['mrp']['data']['comparison']['gt'] == 'gt')
                        array_push($HavingCondElm ,"mrp > " . $filter['mrp']['data']['value']['gt']);
                if($filter['mrp']['data']['comparison']['lt'] == 'lt')
                        array_push($HavingCondElm ,"mrp < " . $filter['mrp']['data']['value']['lt']);
                if($filter['mrp']['data']['comparison']['eq'] == 'eq')
                        array_push($HavingCondElm ,"mrp = " . $filter['mrp']['data']['value']['eq']);
            }  
            array_push($SelectElm,"ROUND(AVG(fsbg.fsbg_mrp), 2) AS mrp");
            array_push($SelectSumElm,"SUM(fsb.item_count * fsbg.fsbg_mrp) totMRP");
            $comma = ',';
        }

        if($data['epr'] != 'Combined'){
            array_push($SelectElm,"fsbg.fsbg_epr as epr");
            array_push($SelectSumElm,"SUM(fsb.item_count * fsbg.fsbg_epr) totEPR");
            array_push($WhereCondElm,"1 = 1"); 
            array_push($innQryWhereCondElm, " inn.stit_id = fsb.stit_id ");
            array_push($groupByElm,"fsbg.fsbg_epr");
            if (isset($filter['epr']) &&  $filter['epr'] != '') {
                $HAVING = ' HAVING ';
                if($filter['mrp']['data']['comparison']['gt'] == 'gt')
                        array_push($HavingCondElm ,"epr > " . $filter['epr']['data']['value']['gt']);
                if($filter['mrp']['data']['comparison']['lt'] == 'lt')
                        array_push($HavingCondElm ,"epr < " . $filter['epr']['data']['value']['lt']);
                if($filter['mrp']['data']['comparison']['eq'] == 'eq')
                        array_push($HavingCondElm ,"epr = " . $filter['epr']['data']['value']['lt']);
            }  
            $comma = ',';
            $innWHERE = $WHERE=' WHERE ';
        }else{
            if (isset($filter['epr']) &&  $filter['epr'] != '') {
                $HAVING = ' HAVING ';
                if($filter['mrp']['data']['comparison']['gt'] == 'gt')
                        array_push($HavingCondElm ,"epr > " . $filter['epr']['data']['value']['gt']);
                if($filter['mrp']['data']['comparison']['lt'] == 'lt')
                        array_push($HavingCondElm ,"epr < " . $filter['epr']['data']['value']['lt']);
                if($filter['mrp']['data']['comparison']['eq'] == 'eq')
                        array_push($HavingCondElm ,"epr = " . $filter['epr']['data']['value']['eq']);
            }  
            array_push($SelectElm,"ROUND(AVG(fsbg.fsbg_epr), 2) AS epr");
            array_push($SelectSumElm,"SUM(fsb.item_count * fsbg.fsbg_epr) totEPR");
            $comma = ',';
        }

        if($data['partyId'] != 'Combined'){
            array_push($SelectElm,"(SELECT fpe_vendorName FROM finascop_purchase_entry 
            WHERE fpe_fpoId = ( SELECT stiid_fpoid FROM finascop_stock_item_inventorydetails WHERE  1 = fsbg_id LIMIT 0,1) LIMIT 0,1 as sup_Name)");
            array_push($WhereCondElm,"1 = 1");
            array_push($innQryWhereCondElm, " inn.fsbg_id = fsb.fsbg_id");
            array_push($groupByElm,"sup_Name");
            $comma = ',';
            $innWHERE = $WHERE=' WHERE ';
        }
        else{
            $innQryWhereCond = implode(' AND ',$innQryWhereCondElm);
            array_push($SelectElm,"(SELECT COUNT(DISTINCT(fpe.fpe_vendor_id))
            FROM finascop_purchase_entry fpe WHERE fpe.fpe_id IN (SELECT GROUP_CONCAT(DISTINCT(stiid_fpoid)) FROM finascop_stock_item_inventorydetails WHERE 
            fsbg_id IN (SELECT GROUP_CONCAT(DISTINCT(inn.fsbg_id)) FROM finascop_stock_branch_inventory inn {$innWHERE} {$innQryWhereCond}))) AS  sup_Name");
            $comma = ',';
        }

        if (isset($filter['item_count']) &&  $filter['item_count'] != '') {
            $HAVING = ' HAVING ';
            if($filter['item_count']['data']['comparison']['gt'] == 'gt')
                    array_push($HavingCondElm ,"item_count > " . $filter['item_count']['data']['value']['gt']);
            if($filter['item_count']['data']['comparison']['lt'] == 'lt')
                    array_push($HavingCondElm ,"item_count < " . $filter['item_count']['data']['value']['lt']);
             if($filter['item_count']['data']['comparison']['eq'] == 'eq')
                    array_push($HavingCondElm ,"item_count = " . $filter['item_count']['data']['value']['eq']);
        }  

        if($data['excludeZeroStock'] == 'true'){
            array_push($WhereCondElm,"fsb.item_count > 0 "); 
            $WHERE=' WHERE ';
        }

        array_push($SelectSumElm,"SUM(fsb.item_count * fsb.selling_price) as totSellingPrice");
        //array_push($SelectSumElm,"SUM(fsb.item_count * fsbg.fsbg_epr) as totEPR");

        $SelectStmt = implode(',',$SelectElm);
        $SelectSumStmt = implode(',',$SelectSumElm);
        $innQryWhereCond = implode(' AND ',$innQryWhereCondElm);
        $mainQryWhere = implode(' AND ',$WhereCondElm);
        $groupBy = implode(',',$groupByElm);
        $havingQry = implode(' AND ',$HavingCondElm);

        if($fromAggreateDetails){
            $AND = !empty($HAVING)?' AND ': ' ';
            $aggreagteQuery = "SELECT {$SelectSumStmt},
            SUM(fsb.item_count) AS totItemCount
            FROM finascop_stock_branch_inventory fsb 
            INNER JOIN finascop_stock_item_batch_group fsbg ON fsb.fsbg_id IN (fsbg.fsbg_id) AND fsb.stit_id = fsbg.stit_ID
            {$WHERE} {$mainQryWhere} {$AND} {$havingQry} ";

            $aggregateResult = $db->getFromDB($aggreagteQuery,true);

            if(!empty($aggregateResult)) {  
                echo "{success: true,aggregateValues:" . json_encode($aggregateResult)  . ", qry:" . json_encode($aggreagteQuery) . "}";
            } else {
                $result['msg'] = "Failed to load aggregate Values";
                echo "{success: false, record:" . json_encode($result) . ", qry:" . json_encode($aggreagteQuery) . "}";
            }
            exit(0);
        }


        $dQry = "SELECT {$SelectStmt}{$comma}
                (SELECT ROUND(AVG(inn.selling_price),2) FROM finascop_stock_branch_inventory inn {$innWHERE} {$innQryWhereCond}) AS selling_price,
                (SELECT SUM(inn.item_count) FROM finascop_stock_branch_inventory inn {$innWHERE} {$innQryWhereCond}) AS item_count,
                (SELECT ROUND((SUM(inn.item_count)*AVG(inn.selling_price)),2) FROM finascop_stock_branch_inventory inn {$innWHERE} {$innQryWhereCond}) AS stock_value
                FROM finascop_stock_branch_inventory fsb
                INNER JOIN finascop_stock_item_batch_group fsbg ON fsb.fsbg_id IN (fsbg.fsbg_id) AND fsb.stit_id = fsbg.stit_ID
                {$WHERE} {$mainQryWhere} GROUP BY {$groupBy}  {$HAVING} {$havingQry} ORDER BY $rec_sort $rec_sort_dir";    
//echo($dQry);
//exit(1);
        
        $result = $db->getMultipleData($dQry, true);

  
        // $count = count($db->getMultipleData("SELECT {$SelectStmt}{$comma} 
        // (SELECT SUM(inn.item_count) FROM finascop_stock_branch_inventory inn {$innWHERE} {$innQryWhereCond}) AS item_count
        // FROM finascop_stock_branch_inventory fsb 
        // INNER JOIN finascop_stock_item_batch_group fsbg ON fsb.fsbg_id IN (fsbg.fsbg_id) AND fsb.stit_id = fsbg.stii_id
        // {$WHERE} {$mainQryWhere} GROUP BY {$groupBy} {$HAVING} {$havingQry}",true));
        
        $i=0; $itemNameQry='';
        foreach($result as $key => $item){
            if($data['stit_IDs'] == 'All'){
                $itemNameQry = " AND stit_SKU like '%" . $filter['stit_SKU']['data']['value'] . "%'";
            }    
            $SKU = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$item['stit_id']} {$itemNameQry}");
            if(!empty($SKU)){
                $item['stit_SKU'] = $SKU;
             }
            else{
                continue;
            }
            if($data['br_IDs'] != 'Combined'){
                $brName = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$item['branch_id']}");
                $item['br_Name'] = $brName;
            }
            $resfilteredResultult[$i] =$item;
            $i++;
        }


        if (!empty($data)) {
            echo '{"totalCount":' . count($resfilteredResultult) . ',"data":' . json_encode($resfilteredResultult) .'}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'listCurrentStock':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1';
		$filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                    $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                        }
                
            }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;        
        $where = " AND branch_id =" . $br_ID;

        $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$br_ID}");
        if($br_PyramidLevel == 2){
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " AND stiid_status = -1";
            $deliverStatus = " AND stiid_status = -1";
        }else if($br_PyramidLevel == 3){
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = -1";
        } else if($br_PyramidLevel == 4){
            $rackStatus = " AND stiid_status = 4";
            $dispatchStatus = " AND stiid_status = -1";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = 5";
        }
        
        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_stock_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,purchasing_unit,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stitl1_optimumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stitl2_optimumqty "
                . "ELSE stitl3_optimumqty END AS optimumqty,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_minimumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stit12_minimumqty "
                . "ELSE stit13_minimumqty END AS minimumqty,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_maximumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stit13_maximumqty "
                . "ELSE stit13_maximumqty END AS maximumqty,csb_package_type_name,cs_nos,cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,br_PyramidLevel,least_package_type_id "
                . "FROM finascop_stock_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                //foreach ($datas as $data) {
                $datas[$i]['rack_count'] = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} AND fsbg_id = {$datas[$i]['fsbg_id']} {$rackStatus}");
                //$datas[$i]['dispatch_count'] = ($selBranchCpd == 1) ? ($db->getItemFromDB("SELECT COUNT(*) FROM  finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} {$dispatchStatus}")) : '-';
                $datas[$i]['dispatch_count'] = ($selBranchCpd == 1) ? dispatchCount($datas[$i]['branchId'], $datas[$i]['stit_id']) : '-';
                $datas[$i]['receive_count'] = ($selBranchCpd == 1) ? '-' : ($db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} AND fsbg_id = {$datas[$i]['fsbg_id']} {$receiveStatus}"));
                $datas[$i]['deliver_count'] = ($selBranchCpd == 1) ? '-' : ($db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} AND fsbg_id = {$datas[$i]['fsbg_id']} {$deliverStatus}"));
                $datas[$i]['fsbg_name'] = $db->getItemFromDB("SELECT fsbg_name FROM finascop_stock_item_batch_group WHERE fsbg_id = {$datas[$i]['fsbg_id']}");
                $datas[$i]['cart_count'] = $db->getItemFromDB("SELECT sum(count) FROM finascop_stock_blocked  WHERE markedfordelivery =1 and item_id ={$datas[$i]['stit_id']} and branch_id = {$datas[$i]['branchId']}");
                $datas[$i]['blocked_count'] = $db->getItemFromDB("SELECT sum(count) FROM finascop_stock_blocked  WHERE markedfordelivery = 0 and item_id ={$datas[$i]['stit_id']} and branch_id = {$datas[$i]['branchId']}");
                $datas[$i]['least_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type  WHERE package_type_id = {$datas[$i]['least_package_type_id']}");
                switch($datas[$i]['br_PyramidLevel']){
                    case 2:
                        $datas[$i]['purchasing_unitname'] = $datas[$i]['cs_package_type_name'];
                        break;
                    case 3:
                        $datas[$i]['purchasing_unitname'] = $datas[$i]['ds_package_type_name'];
                        break;
                    case 4:
                        $datas[$i]['purchasing_unitname'] = $datas[$i]['ds_package_type_name'];
                        break;
                } 
                
        }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        
// $db->printGridJson($countQuery, $listQuery);
		break;

    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_cpd={$branch_id} OR br_ID={$branch_id}) order by br_Name asc", true);
		if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listbarcodesinCurrentStock':
//pass branchid and item list details from stock inventory details where sttus id in 1,4
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stiid_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($_POST['filter'])) {

            foreach ($_POST['filter'] as $key => $val) {
                if($val['field'] == 'stiid_barcode'){
                    $filter_part .= " and " . $val['field'] . " = " . $val['data']['value'] . " ";
                }else{
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
                
}
        }

        $countQuery = "SELECT COUNT(*) from finascop_stock_item_inventorydetails WHERE cpd_branch_id = " . intval($_POST['branchId']) . " AND stiid_itemmasterid = {$_POST['stit_id']} AND fsbg_id = {$_POST['fsbg_id']}  {$filter_part}";
        $listQuery = "SELECT stiid_barcode,DATE_FORMAT(stiid_createdon, '%d-%m-%Y %H:%i:%s') AS stiid_createdon,DATE_FORMAT(stiid_updatedon, '%d-%m-%Y %H:%i:%s') AS stiid_updatedon,stiid_leastSKUmrp,"
                . "(SELECT stiid_description from finascop_stock_item_inventorydetails_status where finascop_stock_item_inventorydetails_status.stiid_status = finascop_stock_item_inventorydetails.stiid_status) as stiid_statusStat,"
                . "ROUND(stiid_customerRateHmDel, 2) as stiid_customerRateHmDel,ROUND(stiid_customerRateCouDel, 2) as stiid_customerRateCouDel,ROUND(stiid_customerRatePikup, 2) as stiid_customerRatePikup,DATE_FORMAT(stiid_expirydate, '%d-%m-%Y') AS stiid_expirydate,"
                . "stiid_itemleastSKUptr,stiid_itemleastSKUpts,stiid_batchno,IF(stiid_itemleastSKUptr > 0,stiid_itemleastSKUptr , stiid_leastSKUb2bRetailsp) AS retailPrice,"
                . "IF(stiid_itemleastSKUpts > 0,stiid_itemleastSKUpts , stiid_leastSKUb2bCSsp) AS csPrice "
                . "from finascop_stock_item_inventorydetails WHERE cpd_branch_id = {$_POST['branchId']} AND stiid_itemmasterid = {$_POST['stit_id']} AND fsbg_id = {$_POST['fsbg_id']} {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listbarcodesMovement':
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'created_at' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from finascop_stock_item_inventorydetails_movement WHERE stiidm_barcode = '{$_POST['barcode']}' {$filter_part}";
        $listQuery = "SELECT stiidm_id,stiidm_barcode,stiidm_details,DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as created_at from finascop_stock_item_inventorydetails_movement WHERE stiidm_barcode = '{$_POST['barcode']}' {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        //$db->printGridJson($countQuery, $listQuery);
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['type'] = getType($datas[$i]['stiidm_details']);
                $stiidm_details = json_decode($datas[$i]['stiidm_details'], true);
                if ($stiidm_details['text'] != null) {
                    $datas[$i]['stiidm_details'] = $stiidm_details['text'] . ' Order - ' . $stiidm_details['order_id'] . ' in ' . $stiidm_details['type'];
                } else {
                    $datas[$i]['stiidm_details'] = $datas[$i]['stiidm_details'];
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        break;
}