<?php
switch ($op) {
    case 'listRelationshipOfficer':
        $baId = $_POST['baId'];
        $rec_limit = empty($_POST['limit']) ? 23 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $searchitem = " 1=1 AND relationship_officer.type = 1 ";
        $allowedFields = ['ro_name', 'ro_phone', 'ro_email', 'ro_status', 'ro_area'];
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

        $countQuery = "SELECT COUNT(*) FROM  relationship_officer  INNER JOIN area_entries ON area_entries.id = roArea 
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
    case 'roDetailsView':
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
    case 'roActions':
        $id = $_POST['id'];
        $action = $_POST['action'];
        switch ($action) {
            case 'accept':
                $data['roStatus'] = 2;
                $message = "RO Accepted";
                break;
            case 'reject':
                $data['roStatus'] = 4;
                $message = "RO Rejected";
                break;
            case 'schedule-interview':
                $data['roStatus'] = 5;
                $logData['roInterviewLink'] = $_POST['roInterviewLink'];
                $logData['roInterviewDate'] = date('Y-m-d', strtotime($_POST['roInterviewDate']));
                $logData['roInterviewTime'] = date('H:i A', strtotime($_POST['roInterviewTime']));
                $message = "RO Interview Scheduled";
                break;
            case 'interview-passed':
                $data['roStatus'] = 6;
                $message = "RO Interview Passed";
                break;
            case 'interview-failed':
                $data['roStatus'] = 7;
                $message = "RO Interview Failed";
                break;
            case 'interview-hold':
                $data['roStatus'] = 8;
                $message = "RO Interview On Hold";
                break;
            case 'rebut-appointment':
                $data['roStatus'] = 11;
                $message = $_POST['roActionReason'] ." - Rebut Appointment";
                break;
            case 'verify-appointment':
                $data['roStatus'] = 12;
                $message = "RO Appointment Verifed.";
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
    case 'get_file_s3_details':
        $rid = $_POST['rid'];
        $data['grzBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'ro/';
        if ($data) {
            echo "{success: true,msg:'Saved Successfully','data':" . json_encode($data) . "}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'uploadAppointment':

        $id = $_POST['id'];
        $action = $_POST['action'];
        switch ($action) {
            case 'upload':
                $data['roStatus'] = 9;
                $logData['roAppointmentOrder'] = $_POST['appointmentFile'];
                $message = "RO Waiting For Appointment";
                break;
            case 'accept':
                $data['roStatus'] = 10;
                $logData['roAppointmentOrder'] = $_POST['appointmentFile'];
                $message = "RO Appointed by Associate";
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
        if ($data['roStatus'] == 9) {
            $eventId = 16;
            $status = OutboundJobs::jobsForEvent($eventId, $id);
        }
        if ($status) {
            echo "{success: true,msg:'" . $message . "'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'loadAppointmentLink':
        $id = $_POST['id'];
        $path = $db->getItemFromDB("SELECT roAppointmentOrder FROM relational_officer_log WHERE roId = {$id} AND roStatus = 10");
        if (!empty($path)) {
            echo "{success: true,path:'" . $path . "'}";
        } else {
            echo "{success: false, msg: 'File not found' }";
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
        $allowedFields = ['ro_name', 'ro_phone', 'ro_email', 'ro_status', 'ro_area'];
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
        if ($data['roStatus'] == 13) {
            //support ticket creation
            $roDetails = $db->getFromDB("SELECT roName,roMobile,roEmailId FROM relationship_officer WHERE id = {$id}", true);
            $reqData['ticketTitle'] = "Generate Support tickets for RO";

            $reqData['ticketSuId'] = 7;
            $reqData['ticketStatus'] = 1;
            $stLog['ticketStage'] = 2;

            $reqData['ticketContactName'] = $roDetails['roName'];
            $reqData['ticketContactNo'] = $roDetails['roMobile'];
            $reqData['ticketContactEmail'] = $roDetails['roEmailId'];

            $reqData['ticketSupTypeId'] = 9;
            $reqData['ticketDescription'] = "Generate Support tickets to Create the Resource ID, Email ID, ID Card, Visiting Cards, Mobile No., Assign Tablet";
            $reqData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $reqData['createdOn'] = date('Y-m-d H:i:s');

            $sddata['createdDate'] = date('Y-m-d H:i:s');
            $status = $db->perform('support_ticketnumbering', $sddata);
            $ticketNo = $db->insert_id();

            $reqData['ticketNumber'] = 'GRST' . date('ymd') . str_pad($ticketNo, 4, '0', STR_PAD_LEFT);


            $status = $db->perform('support_ticket', $reqData);
            $ticketId = $db->insert_id();

            $stLog['ticketId'] = $ticketId;
            $stLog['ticketSupportUnit'] = $reqData['ticketSuId'];
            $stLog['ticketStatus'] = $reqData['ticketStatus'];
            $stLog['ticketRemarks'] = "Ticket Created";

            $stLog['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $stLog['createdOn'] = date('Y-m-d H:i:s');
            $status = $db->perform('support_ticket_log', $stLog);

            sendEmail::SupportCommonEmail($ticketId, 1);
        }
        $success = $db->query('commit');
        if ($success) {
            echo "{success:true,valid:true,message:'{$msg}'}";
        } else {
            echo "{success:false,valid:true,message:'Error occred while updating.'}";
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
    case 'saveRODetails':
        $id = $_POST['id'];
        $data['roResourceId'] = $_POST['roResourceId'];
        $data['roEmailId'] = $_POST['roEmailId'];
        $data['roMobile'] = $_POST['roMobile'];
        $data['roImeiNo'] = $_POST['roImeiNo'];
        $data['roCourierWaybill'] = $_POST['roCourierWaybill'];
        $data['roCourierDate'] = date('Y-m-d', strtotime($_POST['roCourierDate']));
        $data['roLicenceNo'] = $_POST['roLicenceNo'];
        $data['roAadhaar'] = $_POST['roAadhaar'];

        $data['roIdcard'] = $_POST['roIdcard'];
        $data['roVisitingcard'] = $_POST['roVisitingcard'];
        $db->query('begin');
        if (!empty($data['roResourceId']) && !empty($data['roEmailId']) && !empty($data['roMobile']) && !empty($data['roImeiNo']) && !empty($data['roCourierWaybill']) && !empty($data['roCourierDate'])) {
            $data['roStatus'] = 15;
            $msg = "Onboarding Awaited.";
        } else {
            $data['roStatus'] = 14;
            $msg = "RO Details updated.";
        }
        $data = array_filter($data);
        $data['roUpdatedOn'] = date('Y-m-d H:i:s');
        $data['roUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('relationship_officer', $data, 'update', " id = {$id} ");

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
    case 'completeROProcess':
        $roId = $_POST['roId'];
        $getRODetails = $db->getFromDB("SELECT roResourceId,roEmailId,roMobile,roImeiNo,roCourierWaybill,
        roCourierDate,roLicenceNo,roAadhaar FROM relationship_officer WHERE id = {$roId}", true);
        $hasEmptyValue = false;
        foreach ($getRODetails as $key => $value) {
            if (empty($value)) {
                $hasEmptyValue = true;
                break;
            }
        }

        if ($hasEmptyValue) {
            $msg = "Some fields are still missing.Please update the details to complete onboarding";
            echo "{success:true,valid:false,message:'{$msg}'}";
            exit();
        } else {
            $msg = "Onboarding Completed.";
            $db->query('begin');
            $data['roStatus'] = 16;
            $data['roUpdatedOn'] = date('Y-m-d H:i:s');
            $data['roUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('relationship_officer', $data, 'update', " id = {$roId} ");

            $logData['roId'] =  $roId;
            $logData['roStatus'] = $data['roStatus'];
            $logData['roRemarks'] = $msg;
            $logData['roCreatedOn'] = date('Y-m-d H:i:s');
            $logData['roCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('relational_officer_log', $logData);
            $success = $db->query('commit');
            if ($success) {
                echo "{success:true,valid:true,message:'{$msg}'}";
            } else {
                echo "{success:false,valid:false,message:'Error occred while updating.'}";
            }
        }
        break;
    case 'get_img_s3_details':
        $rid = $_POST['rid'];
        $data['grzBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'ro/';
        if ($rid) {
            $roImages = $db->getFromDB("select roIdcard,roVisitingcard from relationship_officer where `id`= {$rid}", true);
            $data['roIdcard'] = $roImages['roIdcard'];
            $data['roVisitingcard'] = $roImages['roVisitingcard'];
        } else {
            $data['roIdcard'] = '';
            $data['roVisitingcard'] = '';
        }
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'loadRODetails':
        $roId = $_POST['roId'];
        $qry = "select roResourceId,roEmailId,roImeiNo,roCourierWaybill,roCourierDate,roMobile,roIdcard,roVisitingcard,roLicenceNo,roAadhaar from relationship_officer where `id`= {$roId}";
        $data = $db->getFromDB($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }
        break;
    case 'listROLogs':
        $roId = intval($_POST['roId']);
        $allowedFields = ['ro_name', 'ro_phone', 'ro_email', 'ro_status', 'ro_area'];
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
}
