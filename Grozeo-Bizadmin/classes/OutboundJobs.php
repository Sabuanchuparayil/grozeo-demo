<?php


class OutboundJobs
{

    public static function createEventBasedJobs()
    {
        $db = new sqlDb(DSN);
        $supportdb = new sqlDb(SUPPORTDSN);

        $qry = "SELECT * FROM support_user_events ue  ORDER BY ue.rank ASC ";
        $outboundEvents = $supportdb->getMultipleData($qry, true);
        foreach ($outboundEvents as $outboundEvent) {
            switch ($outboundEvent['id']) {
                case 1:
                    break;
                case 2:
                    $enteredEntries = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(calleeId) FROM outbound_jobs WHERE eventId = {$outboundEvent['id']}");
                    if (empty($enteredEntries))
                        $enteredEntries = 0;
                    $expectedEntries = $db->getMultipleData("WITH RankedBranches AS (SELECT store_group_id,store_group_name,br_id,br_name,br_Email,br_Phone,ROW_NUMBER() OVER (PARTITION BY store_group_id ORDER BY br_id) AS branch_rank  FROM  finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id)
                    SELECT store_group_id,store_group_name,br_id,br_name,br_Email,br_Phone FROM RankedBranches  WHERE branch_rank = 1 AND store_group_id NOT IN ({$enteredEntries}) ORDER BY    store_group_id DESC", true);
                    foreach ($expectedEntries as $expectedEntry) {
                        $jobData['eventId'] = $outboundEvent['id'];
                        $jobData['jobTitle'] = $outboundEvent['eventName'] . '-' . $expectedEntry['store_group_name'];
                        $jobData['calleeId'] = $expectedEntry['store_group_id'];
                        $jobData['calleeName'] = $expectedEntry['store_group_name'];
                        $jobData['calleeMobile'] = $expectedEntry['br_Phone'];
                        $jobData['calleeType'] = 2;
                        $jobData['eventRank'] = $outboundEvent['rank'];
                        $jobData['status'] = 1;
                        $jobData['createdBy'] = $_SESSION['admin']->UserId;

                        $supportdb->query('begin');
                        $supportdb->perform('outbound_jobs', $jobData);
                        $status = $supportdb->query('commit');
                    }
                    break;
                case 3:
                    $finishedFirstEvents = $supportdb->getMultipleData("SELECT * FROM outbound_jobs WHERE eventId = 1 AND status = 3");
                    foreach ($finishedFirstEvents as $finishedFirstEvent) {
                        $secEveData['eventId'] = $outboundEvent['id'];
                        $secEveData['jobTitle'] = $finishedFirstEvent['jobTitle'];
                        $secEveData['calleeId'] = $finishedFirstEvent['calleeId'];
                        $secEveData['calleeName'] = $finishedFirstEvent['calleeName'];
                        $secEveData['calleeMobile'] = $finishedFirstEvent['calleeMobile'];
                        $secEveData['calleeType'] = 2;
                        $secEveData['eventRank'] = $outboundEvent['rank'];
                        $secEveData['status'] = 1;
                        $secEveData['createdBy'] = $_SESSION['admin']->UserId;
                        $isAvailable = $db->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE calleeId = {$finishedFirstEvent['calleeId']} AND calleeType = 2");
                        $supportdb->query('begin');
                        if ($isAvailable == 0)
                            $supportdb->perform('outbound_jobs', $secEveData);
                        $status = $supportdb->query('commit');
                    }
                    break;
                case 4:
                    $currentTime = date('Y-m-d H:i:s');
                    $getPendingPackedJobs = array();
                    //$getPendingPackedJobs = $db->getMulipleData("SELECT fsto_source,COUNT(fsto_id),fsto_createdOn,fsto_openingtime,store_group_id,store_group_name,br_Email,br_Phone FROM finascop_stock_transfer_order WHERE fsto_status = 6 AND  fsto_openingtime <= '{$currentTime}' GROUP BY fsto_source", true);
                    foreach ($getPendingPackedJobs as $getPendingPackedJob) {
                        $secEveData['eventId'] = $outboundEvent['id'];
                        $secEveData['calleeId'] = $getPendingPackedJob['store_group_id'];
                        $secEveData['calleeName'] = $getPendingPackedJob['store_group_name'];
                        $secEveData['calleeMobile'] = $getPendingPackedJob['br_Phone'];
                        $secEveData['calleeType'] = 2;
                        $secEveData['eventRank'] = $outboundEvent['rank'];
                        $secEveData['status'] = 1;
                        $secEveData['createdBy'] = $_SESSION['admin']->UserId;
                    }
                    break;
            }
        }

        if ($status) {
            return true;
        } else {
            return false;
        }
    }
    public static function jobsForEvent($eventId, $releventId)
    {
        $db = new sqlDb(DSN);
        $supportdb = new sqlDb(SUPPORTDSN);
        $eventDetails = $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = {$eventId}", true);
        switch ($eventId) {

            case 16:
                $roDetails = $db->getFromDB("SELECT roName,roMobile FROM relationship_officer WHERE id = {$releventId}", true);
                $jobData['eventId'] = $eventId;
                $jobData['jobTitle'] = $eventDetails['eventName'] . '-' . $roDetails['roName'];
                $jobData['calleeId'] = $releventId;
                $jobData['calleeName'] = $roDetails['roName'];
                $jobData['calleeMobile'] = $roDetails['roMobile'];
                $jobData['calleeType'] = 4;
                $jobData['eventRank'] = $eventDetails['rank'];
                $jobData['status'] = 1;
                $jobData['createdBy'] = $_SESSION['admin']->UserId;

                $supportdb->query('begin');
                $supportdb->perform('outbound_jobs', $jobData);
                $status = $supportdb->query('commit');
                break;
        }
        if ($status) {
            return true;
        } else {
            return false;
        }
    }
}
