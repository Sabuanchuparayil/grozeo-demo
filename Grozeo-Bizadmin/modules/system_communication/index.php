<?php


switch ($op) {
    case 'listCommunications':
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1  ";

        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['sc_type', 'sc_template', 'sc_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }
        if ($_POST['current_type'] > 0) {
            $filterCon .= "  and type = {$_POST['current_type']} ";
        }
        $condition = " ";
        $qry = "select count(*) from  communication_entry {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);

        $db->query('set @cnt=0');
        $query = "SELECT id,TYPE,CASE WHEN TYPE = 1 THEN 'SMS' WHEN TYPE = 2 THEN 'Email' WHEN TYPE = 3 THEN 'WatsApp' END AS typeName,
        typeId,isRequired,IF(isRequired = 1,'Yes','No') AS isRequiredStatus,isActive,IF(isActive = 1,'Yes','No') AS isActiveStatus,
        CASE WHEN TYPE = 1 THEN (SELECT templateName FROM sms_templates WHERE sms_templates.id = typeId)  
        WHEN TYPE = 2 THEN '-'   WHEN TYPE = 3 THEN '-' END AS title,CASE WHEN TYPE = 1 THEN (SELECT templateContent FROM sms_templates WHERE sms_templates.id = typeId)   
        WHEN TYPE = 2 THEN '-'  WHEN TYPE = 3 THEN '-' END AS message  FROM  communication_entry  {$filterCon} {$condition} order by id asc ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'setAsRequired':
        $id = $_POST['id'];
        $isRequired = $_POST['isRequired'];
        if ($isRequired == 1) {
            $setNewVal = 0;
        } else {
            $setNewVal = 1;
        }
        $ceEntry['isRequired'] = $setNewVal;
        $ceEntry['UpdatedOn'] = date('Y-m-d H:i:s');
        $ceEntry['UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;

        if ($id > 0) {
            $db->query('begin');
            $status = $db->perform("communication_entry", $ceEntry, 'update', 'id =' . $id);
            $status = $db->query('commit');
        }
        if ($status) {
            echo "{success: true,message:'Updated Successfully.'}";
        } else {
            echo "{success: false,errors: { message: 'Error occured while saving data' }}";
        }
        break;
    case 'setStatus':
            $id = $_POST['id'];
            $isActive = $_POST['isActive'];
            if ($isActive == 1) {
                $setNewVal = 0;
            } else {
                $setNewVal = 1;
            }
            $ceEntry['isActive'] = $setNewVal;
            $ceEntry['UpdatedOn'] = date('Y-m-d H:i:s');
            $ceEntry['UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
    
            if ($id > 0) {
                $db->query('begin');
                $status = $db->perform("communication_entry", $ceEntry, 'update', 'id =' . $id);
                $status = $db->query('commit');
            }
            if ($status) {
                echo "{success: true,message:'Updated Successfully.'}";
            } else {
                echo "{success: false,errors: { message: 'Error occured while saving data' }}";
            }
            break;
}
