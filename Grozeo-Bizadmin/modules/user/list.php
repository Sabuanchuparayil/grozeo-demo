<?php

/*
 * Created on 30-Jul-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * Retrieve the List of User Records and send it as JSON
 *
 */

//Retrieve Parameters
$rec_limit = $_POST['limit'];
$rec_start = $_POST['start'];
$rec_sort = $_POST['sort'];
$rec_sort_dir = $_POST['dir'];
//--
//Query to Get Data
//$totalCount = $db->getItemFromDB("select count(u.UserId) from ".TABLE_USERS." u, ".TABLE_ROLES." r where u.RoleId=r.RoleId and IsSuperUser='No'");

if (isset($_POST['filter'])) {
    $arr = array('admin_username' => "u.UserName", 'admin_fullname' => 'p.FirstName', 'admin_role_name' => 'r.RoleName','admin_active' => 'u.IsActive', 'admin_email' => 'u.UserEmail');
    foreach ($_POST['filter'] as $key => $val) {
        $filter_part .= " and " . $arr[$val['field']] . " LIKE '%" . $val['data']['value'] . "%' ";
    }
}
$o = new stdClass();
$o->totalCount = $db->getItemFromDB("select count(1) from " . FINASCOP_DB . "finascop_usr_master u, sys_role r, finascop_usr_profile p where u.RoleId=r.RoleId AND IsSuperUser='No' AND u.UserId = p.UserId $filter_part");
$CurrentUser = $_SESSION['admin']->Finascop_UserId;
$db->query('set @cnt=0');
$qry = "select * from (select @cnt:=@cnt+1 as rownum,sel.* from (select u.UserId as uidnr_admin, u.UserName as admin_username,u.UserEmail as admin_email, u.IsActive as admin_active,r.RoleName as admin_role_name,"
        . "if(u.IsActive<>'Yes', 'user_disabled', 'user_enabled') as status_icon,CONCAT(p.FirstName, ' ', p.LastName) AS admin_fullname,p.Telephone as umobile,if((SELECT defaultRole FROM retaline_customer WHERE cust_mobile = p.Telephone) = 'impersonate','Yes','No') as impersonated,
        (SELECT languageId FROM language_mapping WHERE type = 0 AND typeId = u.UserId AND isfeatured = 1) as primaryLanguage,
        (SELECT languageId FROM language_mapping WHERE type = 0 AND typeId = u.UserId AND isfeatured = 0) AS secondaryLanguage,
        (SELECT enableCallcenter FROM finascop_user_details WHERE UserId = u.UserId) as enableCallcenter,
        (SELECT enableSupport FROM finascop_user_details WHERE UserId = u.UserId) as enableSupport,
        (SELECT agentID FROM finascop_user_details WHERE UserId = u.UserId) as agentID,
        (SELECT userDid FROM finascop_user_details WHERE UserId = u.UserId) as userDid from " . FINASCOP_DB . "finascop_usr_master u "
        . "inner join  " . FINASCOP_DB . "finascop_usr_profile p on u.UserId = p.UserId,sys_role r where u.RoleId=r.RoleId and IsSuperUser='No' and u.UserId <> '$CurrentUser' $filter_part "
        . "order by $rec_sort $rec_sort_dir ) as sel) as sel2 limit $rec_start,$rec_limit";
$o->users = $db->getMultipleData($qry, true);

echo json_encode($o);


exit;