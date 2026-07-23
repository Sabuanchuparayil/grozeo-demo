<?php

namespace Models; {

    class CallLog extends ModelAbstract
    {
		
        public function POST_saveCallLogs($flag, $request)
        {

            file_put_contents('php://stderr', "*****************POST_saveCallLogsmySql************");
            file_put_contents('php://stderr', print_r($request, TRUE));
			file_put_contents('php://stderr', print_r($flag, TRUE));
            try {
                //$supportdb = new \cgoSqlDB();				
                //$supportdb = new \sqlDb(SUPPORTDSN);
                $msg = "";


                $valdate = date("Y-m-d");
                $valdatetime = date("Y-m-d H:i:s");

                switch (CALLCENTER) {
                    case 'OZONTEL':

                        $callLogDetails = $request['data'];
                        $callLogRequest = json_decode($request['data'], true);
                        file_put_contents('php://stderr', "*****************OZONTELOZONTELOZONTEL************");
                        file_put_contents('php://stderr', print_r($callLogRequest, TRUE));

                        $CallerID = $callLogRequest['CallerID'];
                        $StartTime = $callLogRequest['StartTime'];
                        $EndTime = $callLogRequest['EndTime'];
                        $TimeToAnswer = $callLogRequest['TimeToAnswer'];
                        $CallDuration = $callLogRequest['CallDuration'];
                        $Duration = $callLogRequest['Duration'];
                        $AgentID = $callLogRequest['AgentID'];
                        $AgentName = $callLogRequest['AgentName'];
                        $Disposition = $callLogRequest['Disposition'];
                        $HangupBy = $callLogRequest['HangupBy'];
                        $Status = $callLogRequest['Status'];
                        $Comments = $callLogRequest['Comments'];
                        $DialStatus = $callLogRequest['DialStatus'];
                        $AgentStatus = $callLogRequest['AgentStatus'];
                        $CustomerStatus = $callLogRequest['CustomerStatus'];
                        $ConfDuration = $callLogRequest['ConfDuration'];
                        $WrapUpDuration = $callLogRequest['WrapUpDuration'];
                        $HoldDuration = $callLogRequest['HoldDuration'];
                        $AudioFile = $callLogRequest['AudioFile'];
                        $monitorUCID = $callLogRequest['monitorUCID'];
                        $UUI = $callLogRequest['UUI'];

                        break;
                    case 'VOXBAY':
                        $callLogDetails = file_get_contents('php://input');
						//if (is_string($request)) {
    $callLogRequest = json_decode(file_get_contents('php://input'),true); // true converts it to an associative array
//} else {
                            //file_put_contents('php://stderr', "*****************VOXBAYVOXBAYVOXBAY************");
                        //file_put_contents('php://stderr', "*****************The data provided is not a valid JSON string************");

//}
                        
                        file_put_contents('php://stderr', "*****************VOXBAYVOXBAYVOXBAY************");
                        file_put_contents('php://stderr', print_r($callLogRequest, TRUE));

                        if($callLogRequest['Calltype'] == 'Incoming Call')
                            $CallerID = $callLogRequest['SourceNumber'];
                        else
                            $CallerID = $callLogRequest['DestinationNumber'];
                        $StartTime = $callLogRequest['CallStartTime'];
                        $CallDuration = $callLogRequest['AnsweredCallDuration'];
                        $AgentID = $callLogRequest['DID'];
                        $Status = $callLogRequest['CallStatus'];
                        $AudioFile = $callLogRequest['CallRecordingURL'];
                        $AgentName = $callLogRequest['AgentName'];                        
						$AgentStatus = $callLogRequest['AgentStatus'];
						$Disposition = $callLogRequest['Calltype'];

						$UUI = '';
                        $EndTime = $callLogRequest['CallEndTime'];
                        $Duration = $callLogRequest['TotalCallDuration'];                        
                        $HangupBy = '';
                        $TimeToAnswer = '';
                        $Comments = '';
                        $DialStatus = '';                        
                        $CustomerStatus = '';
                        $ConfDuration = '';
                        $WrapUpDuration = '';
                        $HoldDuration = '';
                        $monitorUCID = $callLogRequest['CallUUID'];

                        break;
                }
                $db = new \supportSqlDB();
                $status = $db->query("insert into call_communications(createdOn,createddate,
               CallerID,StartTime,EndTime,TimeToAnswer,Duration,CallDuration,AgentID,AgentName,Disposition,
               HangupBy,Status,Comments,DialStatus,AgentStatus,CustomerStatus,ConfDuration,WrapUpDuration,
               HoldDuration,AudioFile,callLogDetails,monitorUCID,UUI) values('{$valdatetime}','{$valdate}','{$CallerID}',
               '{$StartTime}','{$EndTime}','{$TimeToAnswer}','{$Duration}','{$CallDuration}','{$AgentID}',
               '{$AgentName}','{$Disposition}','{$HangupBy}','{$Status}','{$Comments}','{$DialStatus}',
               '{$AgentStatus}','{$CustomerStatus}','{$ConfDuration}','{$WrapUpDuration}','{$HoldDuration}',
               '{$AudioFile}','{$callLogDetails}','{$monitorUCID}','{$UUI}')");

                $lastId = $db->insert_id();
                $status = $db->query("insert into call_logs(entryFrom,entryAction,entryMode,contactNumber,createdOn,callRemarks,callRecords,referenceId) 
               values ('Call Recording','Outbound','Phone Call','{$CallerID}','{$valdatetime}','{$Comments}','{$AudioFile}','{$lastId}')");


                file_put_contents('php://stderr', print_r($status, TRUE));
                $msg = "Call log updated.";
                return [
                    'status'    => "ok",
                    'data'      => [],
                    'msg'       => $msg
                ];
            } catch (\Exception $e) {
                return [
                    'status'    => "false",
                    'data'      => [],
                    'msg'       => $e->getMessage()
                ];
            }
        }
        public function POST_saveCallLogsnosql($flag, $request)
        {

            file_put_contents('php://stderr', "*****************88POST_saveCallLogs************");
            file_put_contents('php://stderr', print_r($request, TRUE));
            try {
                $nodb = new \cgoDynamiteDB();
                $msg = "";

                $callLogRequest = json_decode($request['data'], true);
                $arrOrder = array();
                $arrOrder['Data'] = array();
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                $uniqueId = getNewFinascopApiKey();
                $callLogDetails = $request['data'];
                file_put_contents('php://stderr', print_r($callLogRequest, TRUE));


                $uuid = $uniqueId;
                $createddate = $valdate;
                array_push($arrOrder['Data'], array('col' => 'uuid', 'val' => $uniqueId));
                array_push($arrOrder['Data'], array('col' => 'createddatetime', 'val' => (int) $valdatetime));
                array_push($arrOrder['Data'], array('col' => 'createddate', 'val' => (int) $valdate));

                array_push($arrOrder['Data'], array('col' => 'AgentID', 'val' => $callLogRequest['AgentID']));
                array_push($arrOrder['Data'], array('col' => 'AgentName', 'val' => $callLogRequest['AgentName']));
                array_push($arrOrder['Data'], array('col' => 'AgentPhoneNumber', 'val' => $callLogRequest['AgentPhoneNumber']));
                array_push($arrOrder['Data'], array('col' => 'AgentStatus', 'val' => $callLogRequest['AgentStatus']));
                array_push($arrOrder['Data'], array('col' => 'AgentUniqueID', 'val' => (int) $callLogRequest['AgentUniqueID']));
                array_push($arrOrder['Data'], array('col' => 'AudioFile', 'val' => $callLogRequest['AudioFile']));
                array_push($arrOrder['Data'], array('col' => 'CallDuration', 'val' => $callLogRequest['CallDuration']));
                array_push($arrOrder['Data'], array('col' => 'CallerID', 'val' => $callLogRequest['CallerID']));
                array_push($arrOrder['Data'], array('col' => 'CustomerStatus', 'val' => $callLogRequest['CustomerStatus']));
                array_push($arrOrder['Data'], array('col' => 'DialStatus', 'val' => $callLogRequest['DialStatus']));
                array_push($arrOrder['Data'], array('col' => 'Duration', 'val' => $callLogRequest['Duration']));
                array_push($arrOrder['Data'], array('col' => 'EndTime', 'val' => $callLogRequest['EndTime']));
                array_push($arrOrder['Data'], array('col' => 'Location', 'val' => $callLogRequest['Location']));
                array_push($arrOrder['Data'], array('col' => 'monitorUCID', 'val' => $callLogRequest['monitorUCID']));
                array_push($arrOrder['Data'], array('col' => 'StartTime', 'val' => $callLogRequest['StartTime']));
                array_push($arrOrder['Data'], array('col' => 'UUI', 'val' => $callLogRequest['UUI']));
                array_push($arrOrder['Data'], array('col' => 'callLogDetails', 'val' => $callLogDetails));

                $NewOrder = $nodb->perform('SupportCallLog', 'insert', $arrOrder, $response);

                //copy audio file to our s3
                /*$audioFilePath = $callLogRequest['AudioFile'];
                $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . "." . pathinfo(basename($audioFilePath), PATHINFO_EXTENSION);
                $destinationPath = $_SERVER["DOCUMENT_ROOT"] . '/tmp/audiofile/' . $fileuploadname;
                if (copy($audioFilePath, $destinationPath)) {

                    $s3upload = new \cgoS3FileHandler();

                    $cloudFrontPath = 'calllog/';

                    $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETNAME, $destinationPath, $fileuploadname, 'mp3', 'audio');
                    //echo 'isFileUploaded'.$isFileUploaded;
                    if ($isFileUploaded == 1)
                        $newAudioPath = AWSBUCKETPATH . '/' . $cloudFrontPath . $fileuploadname;
                }

                $arrSession = array();
                $arrSession['PartitionKey'] = array('col' => 'uuid', 'val' => (string) $uuid);
                $arrSession['SortKey'] = array('col' => 'createddate', 'val' => (int) $createddate);
                $arrSession['Data'] = array();
                array_push($arrSession['Data'], array('col' => 'newAudioPath', 'val' => $newAudioPath));
                $nors = $nodb->perform('SupportCallLog', 'update', $arrSession, $response);*/

                $msg = "Call log updated.";
                return [
                    'status'    => "ok",
                    'data'      => [],
                    'msg'       => $msg
                ];
            } catch (\Exception $e) {
                return [
                    'status'    => "false",
                    'data'      => [],
                    'msg'       => $e->getMessage()
                ];
            }
        }
    }
}
