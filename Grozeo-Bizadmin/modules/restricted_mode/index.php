<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'brandName':
        getJsonKeyArray("SELECT brand_id ,brand_name from mypha_productbrands where status = 1");
        break;
    case 'listProductsToDelete':
        $data = $_POST;
        $prddelbrand_id = $data['prddelbrand_id'];
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 AND `stit_status` = 1 AND isMedicine = 0 ';
        if (isset($data['filter'])) {
        $allowedFields = ['rm_id', 'rm_branch', 'rm_type', 'rm_status', 'rm_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        if( $prddelbrand_id > 0){
            $searchitem .= " and  pdt_brand = {$prddelbrand_id} ";
        }
        
        $query = "SELECT fsi.stit_ID,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category AS parent_category,stit_quantity,
            stit_product_variant,stit_status,fsi.isMedicine 
             FROM finascop_stock_itemmaster fsi
            {$conQuery}
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id ";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS countStock {$search}{$where}{$searchitem}";
        $listQuery = "SELECT * FROM ({$query}) AS listStock {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //echo $listQuery;
        $db->printGridJson($countQuery, $listQuery);
        break;
        case 'deleteProduct':
            break;
}