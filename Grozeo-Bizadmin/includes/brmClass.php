<?php

namespace finascop\accounts\Master {
    require_once(ROOT . '/finascop_config/lib.php');
    require_once(ROOT . '/includes/finascop_accounts_Master.php');

    class brmBranch extends \finascop\accounts\Master\Branch {
        
        public function listBranch($data) {
            global $db;
            $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
            $rec_start = empty($data['start']) ? 0 : $data['start'];
            $rec_sort = empty($data['sort']) ? 'br_Name' : $data['sort'];
            $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
            $filter_part = ' 1=1';

            if (isset($data['filter'])) {

                foreach ($data['filter'] as $key => $val) {
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }

            $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_branch";
            $listQuery = "SELECT br_ID,br_Name,branch_shortname,"
                    . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = br_District) as br_District,"
                    . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = br_State) as br_State,"
                    . "(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = "
                    . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
                    . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,"
                    . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng,if(br_PyramidLevel <> 0,(SELECT br_Name FROM finascop_branch WHERE br_ID = a.br_cpd),' ') AS  branchCpd "
                    . "from " . FINASCOP_DB . "finascop_branch a WHERE {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
            $db->printGridJson($countQuery, $listQuery);
        }

        public function saveBranch($data, $IsAddNew, $IsFinascopIntegration = true) {
//            $data['br_IsCPD'] = $data['br_IsCPD'];
//            if ($data['br_IsCPD'] == 1) {
//                $data['br_cpd'] = 0;
//            }
global $db;
            unset($data['br_sales']);
            unset($data['branchIsCPD']);
            unset($data['br_IsCPD']);
            unset($data['br_RetailType']);
            unset($data['br_isFullfillmentbranch']);
            
            if(empty($data['br_cpd'])){
                unset($data['br_cpd']);
            }
             
                if(empty($data['br_rdrIdExpress']))
                unset($data['br_rdrIdExpress']);
			if(empty($data['br_rdrIdSlotted']))
                unset($data['br_rdrIdSlotted']);
			if(empty($data['br_rdrIdCourier']))
                unset($data['br_rdrIdCourier']);
                            
                
                
                
            if(($data['br_PyramidLevel'] == 4) && ($data['br_StoreType'] == 'Dealer')){
                unset($data['br_stockLevel']);
                
               
            }
            if($data['br_PyramidLevel'] == 1){
                unset($data['br_stockLevel']);
            }
            if(($data['br_PyramidLevel'] == 4) && ($data['br_StoreType'] != 'Dealer')){
                $data['br_storeGroup'] = $db->getItemFromDB("SELECT store_group_id FROM finascop_branch_group WHERE sg_isDefault = 1");
            }
            if($data['br_PyramidLevel'] != 4){
                unset($data['br_SalesOffline']);
                unset($data['br_SalesOnline']);
                unset($data['br_courierDelivery']);
                unset($data['br_directDelivery']);
                unset($data['br_scheduledDelivery']);
                unset($data['br_type']);
                unset($data['br_typeParent']);
                unset($data['br_parentPacking']);
            }
            return parent::saveBranch($data, $IsAddNew, $IsFinascopIntegration, $br_Id);
        }


        public function getDetails($id) {
            global $db;
            if (!empty($id)) {
                $db->_loadRecordJson("SELECT br_ID,br_Name, br_District, br_State,br_stockLevel,branch_shortname,br_cpd,br_storeGroup,br_ranking,br_directDelivery,br_courierDelivery,br_scheduledDelivery,            
					(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) AS br_Company,br_type,br_typeParent,br_parentPacking,
					(SELECT if(cmp_DefaultBranch={$id},1,0) from " . FINASCOP_DB . "finascop_company inner join 
					" . FINASCOP_DB . "finascop_branch_company using(comp_id) WHERE br_Id = a.br_ID) AS br_defaultapi, br_StoreType,isFullfillmentbranch,isFullfillmentbranch as br_isFullfillmentbranch,
					br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_pincode,br_Lat,br_Lng, br_RetailType,br_rdrIdExpress,br_rdrIdSlotted,br_rdrIdCourier,br_SalesOffline,br_SalesOnline,
                                        if(br_open_time = '00:00:00','',DATE_FORMAT(br_open_time,'%h:%i %p')) AS br_open_time,if(br_close_time = '00:00:00','',DATE_FORMAT(br_close_time,'%h:%i %p')) AS br_close_time  
					from " . FINASCOP_DB . "finascop_branch a WHERE br_ID = " . $id, true);
            }
        }
        public function getComboStore($ind, $state) {
            //echo $ind;
            switch ($ind) {
                case 4:
                    $qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch where br_status= 'Active' AND br_IsCPD = 1";
                    $qry .= " ORDER BY `name` ASC";
                    finascop_getjsonkeyarray($qry);
                    break;
                case 6:
                    $qry = "SELECT business_type_id AS id, business_type_name AS `name` from " . FINASCOP_DB . "finascop_business_type where status= 1 ";
                    $qry .= " ORDER BY `name` ASC";
                    finascop_getjsonkeyarray($qry);
                    break;
                case 5:
                    $qry = "SELECT store_group_id AS id, store_group_name AS `name` from " . FINASCOP_DB . "finascop_branch_group where status= 1 ";
                    $qry .= " ORDER BY `name` ASC";
                    finascop_getjsonkeyarray($qry);
                    break;
                default:
                     parent::getComboStore($ind, $state);
                    break;
                
            }
               
        }
    }

}
    


