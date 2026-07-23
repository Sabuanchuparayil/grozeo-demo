<?php
switch ($op) {
    case 'listAreaManager':
        $baId = $_POST['baId'];
        $rec_limit = empty($_POST['limit']) ? 23 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $searchitem = " 1=1 AND type = 2 ";
        $allowedFields = ['am_name', 'am_phone', 'am_email', 'am_status', 'am_area'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        }
        $qry = "SELECT relationship_officer.id,roName,roMobile,roAddress,roPincode,rost_id,rodst_Id,roQualification,roExperience,
        roContactPerson,roContactMobile,roUsername,roPassword,roBloodGroup,roLicenceNo,roPanNo,roAadhaar,roBankAccount,roUPI,roBusAssociate,
        roArea,areaName,dst_Name,st_name,baName,roStatus,name as roStatusName FROM   relationship_officer 
        INNER JOIN area_entries ON area_entries.id = roArea 
        INNER JOIN finascop_state ON st_ID = rost_id 
        INNER JOIN finascop_district ON dst_Id = rodst_Id 
        INNER JOIN relationship_officer_status ON relationship_officer_status.id = roStatus
        INNER JOIN business_associate ON business_associate.id = roBusAssociate where  {$searchitem} order by id asc LIMIT {$rec_start},{$rec_limit} ";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM  relationship_officer INNER JOIN area_entries ON area_entries.id = roArea 
        INNER JOIN finascop_state ON st_ID = rost_id 
        INNER JOIN finascop_district ON dst_Id = rodst_Id 
        INNER JOIN relationship_officer_status ON relationship_officer_status.id = roStatus
        INNER JOIN business_associate ON business_associate.id = roBusAssociate where  {$searchitem} ";
        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'amDetailsView':
        $deli_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($deli_id) {

            $data = $db->getFromDB("SELECT relationship_officer.id,roName,roMobile,roAddress,roPincode,rost_id,rodst_Id,roQualification,roExperience,
            roContactPerson,roContactMobile,roUsername,roPassword,roBloodGroup,roLicenceNo,roPanNo,roAadhaar,roBankAccount,roUPI,roBusAssociate,
            roArea,areaName,dst_Name,st_name,baName,roStatus FROM   relationship_officer 
            INNER JOIN area_entries ON area_entries.id = roArea 
            INNER JOIN finascop_state ON st_ID = rost_id 
            INNER JOIN finascop_district ON dst_Id = rodst_Id 
            INNER JOIN business_associate ON business_associate.id = roBusAssociate WHERE relationship_officer.id = {$deli_id}", true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listAMLogs':
        $roId = intval($_POST['roId']);
        $allowedFields = ['am_name', 'am_phone', 'am_email', 'am_status', 'am_area'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        }
        $qry = "SELECT relational_officer_log.id,roId,roRemarks,roInterviewLink,roInterviewDate,roInterviewTime,roAppointmentOrder,roCreatedOn,roCreatedBy,
            CONCAT(FirstName,' ',LastName) AS createdByName,roStatus,relationship_officer_status.name as roStatusName
            FROM relational_officer_log 
            INNER JOIN relationship_officer_status ON relationship_officer_status.id = roStatus 
            INNER JOIN finascop_usr_profile ON UserId = roCreatedBy   where roId = {$roId} {$searchitem} ORDER BY relational_officer_log.id DESC ";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'roActions':
        $id = $_POST['id'];
        $action = $_POST['action'];
        switch ($action) {
            case 'accept':
                $data['roStatus'] = 2;
                $message = "AM Accepted";
                break;
        }
        $db->query('begin');
        $data['roUpdatedOn'] = date('Y-m-d H:i:s');
        $data['roUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('relationship_officer', $data, 'update', " id = {$id} ");

        $logData['roId'] =  $id;
        $logData['roStatus'] = $data['roStatus'];
        $logData['roRemarks'] = $message;
        $logData['roCreatedOn'] = date('Y-m-d H:i:s');
        $logData['roCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('relational_officer_log', $logData);

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'" . $message . "'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'saveTrainings':

        $supportdb->query('begin');
        $sqaData['roId'] = $_POST['aaId'];
        $sqaData['trainingDate'] = date('Y-m-d', strtotime($_POST['trainingDate']));
        $sqaData['trainingId'] = $_POST['trainingTopics'];
        $sqaData['trainingComments'] = $_POST['trainingComments'];
        $sqaData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $supportdb->perform('relationship_officer_training', $sqaData);


        $success = $supportdb->query('commit');
        if ($success) {
            echo "{success:true,valid:true,message:'Added training.'}";
        } else {
            echo "{success:false,valid:true,message:'Error occred while updating.'}";
        }
        break;
    case 'confirmTrainings':
        $id = $_POST['aaId'];
        $supportdb->query('begin');
        $confirmTrainings = $supportdb->getItemFromDB("SELECT COUNT(*) FROM training_module_topics");
        $trainedTopicCount = $supportdb->getItemFromDB("SELECT COUNT(DISTINCT(trainingId)) FROM relationship_officer_training WHERE roId = {$_POST['aaId']}");
        if ($confirmTrainings == $trainedTopicCount) {
            $db->query('begin');
            $msg = "Training completed.";
            $data['roStatus'] = 13;
            $data['roUpdatedOn'] = date('Y-m-d H:i:s');
            $data['roUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('relationship_officer', $data, 'update', " id = {$id} ");
        } else {
            $msg = "Training updated.";
            $data['roStatus'] = 12;
            $data['roUpdatedOn'] = date('Y-m-d H:i:s');
            $data['roUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('relationship_officer', $data, 'update', " id = {$id} ");
        }
        $logData['roId'] =  $id;
        $logData['roStatus'] = $data['roStatus'];
        $logData['roRemarks'] = $msg;
        $logData['roCreatedOn'] = date('Y-m-d H:i:s');
        $logData['roCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('relational_officer_log', $logData);
        $success = $db->query('commit');
        if ($success) {
            echo "{success:true,valid:true,message:'{$msg}'}";
        } else {
            echo "{success:false,valid:true,message:'Error occred while updating.'}";
        }
        break;
    case 'getTopics':
        $ModuleId = $_POST['ModuleId'];
        $qry = "SELECT id,topicName FROM training_module_topics WHERE ModuleId = '{$ModuleId}' ";
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getModules':
        $qry = "SELECT id,name FROM training_module";
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'listTrainingModules':
        $aaId = intval($_POST['aaId']);
        $edit_status = $_POST['edit_status'];
        $allowedFields = ['am_name', 'am_phone', 'am_email', 'am_status', 'am_area'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        }
        $qry = "SELECT tmt.id as tmtId,ModuleId,topicName,tm.name as moduleName,trainingId,
                if(aat.trainingId is null,0,1) as checked,trainingDate,trainingComments
                    FROM training_module_topics tmt 
                    INNER JOIN training_module tm on tm.id = ModuleId 
                    INNER JOIN relationship_officer_training aat on aat.trainingId = tmt.id and roId = {$aaId} where 1 = 1 {$searchitem}";

        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
}
