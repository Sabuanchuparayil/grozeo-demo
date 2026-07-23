<?php


$userid = $_SESSION['admin']->Finascop_UserId;


switch ($op) {
    case 'getProfitabilityStatement':
        $data = $_POST;
 /*
     fields: ['SODate','SONumber','br_ID','br_Name','customer_ID', 'customer_Name', 'customer_Mobile', 
    'invValAtax','GST', ' purchaseEPR','grossProfit','grossProfitPercentage', 'companyProfit', 'companyProfitPercentage',
    'branchProfit','branchProfitPercentage','incentitve','technology' ],
 */       

if($data['br_IDs'] != 'Combined'){
    $WHERE = " WHERE br_ID IN({$data['br_IDs']})";
}
$query = "SELECT bbso_SODate AS SODate, bbso_SONumber AS SONumber, fb.br_ID,fb.br_Name,rbso.b2b_Customer_ID AS customer_ID,rbso.b2b_Customer_Name AS customer_Name,
            rbc.b2b_Customer_Mobile AS customer_Mobile,bbso_InvValAtax AS invValAtax, (bbso_CGSTVal + bbso_SGSTVal) AS GST
            FROM retaline_B2B_SalesOrder rbso
            INNER JOIN finascop_branch fb ON rbso.br_ID = fb.br_ID 
            INNER JOIN retaline_B2Bcustomer rbc ON rbso.b2b_Customer_ID = rbc.b2b_Customer_ID";
$prftStmt = $db->getMulipleData($query, true);

    if (!empty($prftStmt)){
        echo '{"totalCount":' . count($prftStmt) . ',"data":' . json_encode($prftStmt) . '}';
    } else{
        echo '{"totalCount":"0","data":[]}';
    }
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
}