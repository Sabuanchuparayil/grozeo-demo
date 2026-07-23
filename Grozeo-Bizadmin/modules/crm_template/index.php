<?php

switch ($op) {
    case 'listTemplates':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'template_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $date = date('Y-m-d');
        $countQuery = "SELECT COUNT(*) FROM finascop_crm_templates {$search} AND template_IsActive=1";
        $listQuery = "SELECT template_id,template_name,campaign_type,template_type FROM finascop_crm_templates {$search} AND template_IsActive=1"
                . " ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'get_file_s3_details':
        $data['albumBucketName'] = AWSS3ASSETUPLOADBUCKET;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        if ($data) {
            echo "{success: true,msg:'Saved Successfully','data':" . json_encode($data) . "}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }

        break;
    case 'insertTemplate':
        $db->query('begin');
        $filedata['template_name'] = $_POST['template_name'];
        $filedata['campaign_type'] = $_POST['campaign_type'];
        $filedata['template_type'] = $_POST['template_type'];
        $filedata['template_sms'] = $_POST['sms'];
        $filedata['template_voice_file'] = $_POST['voice'];
        $filedata['template_email'] = $_POST['email'];
        $filedata['template_createdOn'] = date('Y-m-d H:i:s');
        $filedata['template_createdBy'] = $_SESSION['admin']->Finascop_UserId;
//        $variables_id = $_POST['variabless_id'];
//        $filedata['template_variables'] = $variables_id;
//        $variables = explode(",",$variables_id);
        
        $status = $db->perform(FINASCOP_DB . 'finascop_crm_templates', $filedata);
        $lastId = $db->insert_id();
//        for ($i = 0; $i < count($variables); $i++) {
//            $data['template_variable_templateId'] = $lastId;
//            $data['template_variable_variableId'] = $variables[$i];
//            $qry = $db->perform(FINASCOP_DB . 'template_variables', $data);
//        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Added Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'insertVariable':
        $db->query('begin');
        $templateId = $_POST['templateId'];
        $unique=$db->getItemFromDB("SELECT COUNT(template_variable_id) FROM template_variables WHERE template_variable_templateId='{$templateId}'");
        if($unique>0){
            $delQuery = $db->query("delete FROM template_variables WHERE template_variable_templateId={$templateId}");
        }
        $variables_id = $_POST['variables_id'];
        $variables = explode(",",$variables_id);
        for ($i = 0; $i < count($variables); $i++) {
            $data['template_variable_templateId'] = $templateId;
            $data['template_variable_variableId'] = $variables[$i];
            $qry = $db->perform(FINASCOP_DB . 'template_variables', $data);
        }
        $filedata['template_variables'] = $variables_id;
        $filedata['template_updatedOn'] = date('Y-m-d H:i:s');
        $filedata['template_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $qry = $db->perform(FINASCOP_DB . 'finascop_crm_templates', $filedata, 'update', 'template_id=' . $templateId);
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Added Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'templateDetailsView':
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        if ($template_id) {
            $data = $db->getFromDB("SELECT template_name,campaign_type,template_type,if(template_sms<>'',template_sms,template_email) as template_sms FROM finascop_crm_templates WHERE template_id= " . $template_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'removeTemplate':
        $id = $_POST['template_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'template_IsActive' => 0
        );
        $qry = $db->perform(FINASCOP_DB . 'finascop_crm_templates', $data, 'update', 'template_id=' . $id);
        if ($qry) {
            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'MapVariables':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'variable_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $date = date('Y-m-d');
        $countQuery = "SELECT COUNT(*) FROM finascop_crm_variables {$search} ORDER BY {$sort} {$dir} limit $start,$limit";
        $listQuery = "SELECT variable_id,variable_name FROM finascop_crm_variables {$search} "
                . " ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
//        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getVariables':
        $qry = "SELECT variable_id,variable_name FROM finascop_crm_variables";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
}
