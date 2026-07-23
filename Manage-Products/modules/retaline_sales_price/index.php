<?php

switch ($op) {
    case 'listStockSalesPrice':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1';
        if (isset($data['filter'])) {
        $allowedFields = ['sp_id', 'item_id', 'item_name', 'sp_price', 'sp_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        switch ($_POST['type']) {
            case 'Branch':
                $where = " AND br_Name LIKE '{$_POST['branchName']}%' ";
                break;
            case 'Vendor':
                $where = " AND br_Name LIKE '{$_POST['branchName']}%' ";
                break;
            default :
                $where = " ";
                break;
        }

        $query = "SELECT 'Branch' as type,id as spId,br_Name,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category as parent_category,
            stit_quantity,item_count,fpod_leastSKUmrp,fpod_customerRateHmDel,fpod_customerRateCouDel,fpod_customerRatePikup,if(fpod_itemleastSKUptr>0,fpod_itemleastSKUptr,fpod_leastSKUb2bRetailsp) as retailPrice,
            if(fpod_itemleastSKUpts > 0,fpod_itemleastSKUpts,fpod_leastSKUb2bCSsp) as csPrice FROM finascop_stock_itemmaster fsi 
LEFT JOIN finascop_stock_branch_inventory fsb  ON fsb.stit_id=fsi.stit_ID 
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id 
INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id WHERE fsi.isMedicine = 0
UNION
SELECT 'Vendor' as type,fcpod_id as spId,stpa_Fname AS br_Name,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category as parent_category,
stit_quantity,1000 AS item_count,fcpod_leastSKUmrp AS fpod_leastSKUmrp,fcpod_customerRateHmDel AS fpod_customerRateHmDel,fcpod_customerRateCouDel AS fpod_customerRateCouDel,0 AS fpod_customerRatePikup,
0 as retailPrice,0 as csPrice FROM finascop_stock_itemmaster fsi 
LEFT JOIN finascop_contractpo_products fcp  ON fcp.fcpod_itemid=fsi.stit_ID 
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id 
INNER JOIN finascop_stock_party br ON br.stpa_id=fcp.fcpod_vendorid WHERE fsi.isMedicine = 0";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS countStock {$search}{$where}{$searchitem}";
        $listQuery = "SELECT * FROM ({$query}) AS listStock {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
//echo $listQuery;
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getBranchName':
        $type = $_POST['type'];
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        if ($type == 'Branch') {
            $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' order by br_Name asc", true);
        } else {
            $qry = $db->getMulipleData("SELECT stpa_id as br_ID,stpa_Fname as br_Name FROM finascop_stock_party WHERE stpa_IsVendor = 1 AND deliverMode_cpr <> 3 order by stpa_Fname asc", true);
        }

        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
}
