<?php

namespace BackOffice\Tasks;

use Carbon\carbon;
use BackOffice\Models\Branch;
use BackOffice\Models\GodownBoy;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Models\GodownBoyHistory;
use BackOffice\Models\GodownBoyActivity;
use BackOffice\Status\TransferOrderStatus;

class BoyActivitySummary
{
    public function __invoke()
    { 

        DB::transaction(function ()  {
            $prevdate = date('Y-m-d',strtotime("-1 days"));
            $branches = Branch::where('br_status', 'Active')
                        ->select('br_id','br_name')
                       ->where('br_IsCPD', '0')
                       ->orderby('br_name')
                        ->get();
            $mailerarray = [];
            $from = $prevdate . " 00:00:00" ;
            $to = $prevdate . " 23:59:59";
            foreach($branches as $branch){

            //Boy Count
            $boydets = GodownBoy::whereBetween('retaline_godown_boy_orders_request.created_at', [$from, $to])
                ->selectRaw(" MIN(retaline_godown_boy_orders_request.created_at) AS StartFrom, MAX(retaline_godown_boy_orders_request.updated_at) AS EndTo")
                ->where('retaline_godown_boy_orders_request.branch_id',$branch->br_id)      
                ->wherein('retaline_godown_boy_orders_request.status',[2,3])            
                ->join('retaline_godown_boy_orders_request','retaline_godown_boy_orders_request.boy_id', '=', 'retaline_godown_boy.id')
                ->groupby('retaline_godown_boy_orders_request.branch_id')
                ->first();
            
            $boychecks = DB::table('retaline_godown_boy_pendingcheck') 
                ->whereDate('rgbp_date', $prevdate )
                ->selectRaw(" MIN(rgbp_time) AS StartFrom, MAX(rgbp_time) AS EndTo, count(*) as cheks")
                ->where('retaline_godown_boy.branch_id',$branch->br_id)                               
                ->join('retaline_godown_boy','retaline_godown_boy_pendingcheck.boy_id', '=', 'retaline_godown_boy.id')
                ->groupby('retaline_godown_boy.branch_id')
                ->first();

            $totaljobstoday  = DB::table('finascop_stock_transfer_order')
            ->where('fsto_source',  $branch->br_id)  
            ->wherenotin('fsto_status',  [TransferOrderStatus::CANCELLED])  
            ->whereBetween('fsto_createdOn',  [$from, $to])                  
            ->count(); 
             
            $jobscompletedtoday = DB::table('retaline_godown_boy_orders')           
            ->whereBetween('retaline_godown_boy_orders_request.created_at', [$from, $to])           
            ->where("retaline_godown_boy_orders.status","=",4)
            ->where('retaline_godown_boy_orders_request.branch_id',$branch->br_id)   
            ->join('retaline_godown_boy_orders_request','retaline_godown_boy_orders.bgor_id', '=', 'retaline_godown_boy_orders_request.id')
            ->selectraw('count(distinct retaline_godown_boy_orders.order_pk_id) as jobsdone, max(retaline_godown_boy_orders.completed_time) as completetime')
            ->first();    

            $jobspendingtoday = DB::table('finascop_stock_transfer_order')
            ->where('fsto_source',  $branch->br_id)  
            ->wherein('fsto_status', [TransferOrderStatus::ASSIGNED_GODOWN_BOY, TransferOrderStatus::TO_MANUALLY_ASSIGN])      
            ->count();  
            
            $prevprevdate =  date('Y-m-d', strtotime('-1 day', strtotime($prevdate))); 
            $prevjobspending = DB::table('retaline_godown_boy_activity')
            ->where('rgba_Branchid',  $branch->br_id)  
            ->where('rgba_date',  $prevprevdate)  
            ->select('rgba_JobsPending')      
            ->latest('rgba_id')         
            ->first();  


            $boyhistory = GodownBoyHistory::whereDate('rgbh_date', $prevdate )
            ->where('retaline_godown_boy.loggedout_by' , '!=', 5)
            ->selectRaw(" sum(TIME_TO_SEC(TIMEDIFF(retaline_godown_boy_history.logout_at,retaline_godown_boy_history.login_at))) AS TimeDiffs, count(*) as countsessions   ")    
            ->whereDate('rgbh_date', $prevdate )
            ->where('retaline_godown_boy.branch_id',$branch->br_id)   
            ->join('retaline_godown_boy','retaline_godown_boy.id', '=', 'retaline_godown_boy_history.id')           
            ->groupby('retaline_godown_boy.branch_id')
            ->first();

            $userrequestcount =DB::table('retaline_godown_boy')            
            ->selectraw('group_concat(distinct retaline_godown_boy.id) as usercount')            
            ->whereBetween('retaline_godown_boy_orders_request.created_at', [$from, $to])    
            ->wherein('retaline_godown_boy_orders_request.status',[2,3])         
            ->where('retaline_godown_boy.branch_id',$branch->br_id)   
            ->join('retaline_godown_boy_orders_request','retaline_godown_boy.id', '=', 'retaline_godown_boy_orders_request.boy_id')
            ->first();

            $usercheckcount =DB::table('retaline_godown_boy')     
            ->selectraw('group_concat(distinct retaline_godown_boy.id) as usercount')   
            ->whereDate('rgbp_date', $prevdate )      
            ->where('retaline_godown_boy.branch_id',$branch->br_id)  
            ->join('retaline_godown_boy_pendingcheck','retaline_godown_boy.id', '=', 'retaline_godown_boy_pendingcheck.boy_id')   
            ->first();
            
            $firstlogin = "00:00";
            $lastlogout = "00:00";
            $firstloginDB = "00:00:00";
            $lastlogoutDB = "00:00:00";

            if(isset($boydets->StartFrom)){
                $firstlogin = date("h:i A",strtotime($boydets->StartFrom)) ;
                $firstloginDB = date("H:i",strtotime($boydets->StartFrom));
            }
            if(isset($boychecks->StartFrom)){
                if(strtotime(date("H:i",strtotime($boychecks->StartFrom))) <  strtotime($firstloginDB) || $firstloginDB == "00:00:00" ){
                    $firstlogin = date("h:i A",strtotime($boychecks->StartFrom)) ;
                    $firstloginDB = date("H:i",strtotime($boychecks->StartFrom));
                }
            }
            
            if(isset($boydets->EndTo)){
                $lastlogout = date("h:i A",strtotime($boydets->EndTo))  ;
                $lastlogoutDB = date("H:i",strtotime($boydets->EndTo))  ;            
            }

            if(isset($boychecks->EndTo)){
                if(strtotime(date("H:i",strtotime($boychecks->EndTo))) >  strtotime($lastlogout)){
                    $lastlogout = date("h:i A",strtotime($boychecks->EndTo)) ;
                    $lastlogoutDB = date("H:i",strtotime($boychecks->EndTo));
                }
            }

            
            if(strtotime($firstlogin) > strtotime($lastlogout)){
                $lastlogout =  $firstlogin;
                $lastlogoutDB = $firstloginDB;
            }

            if(isset($jobscompletedtoday->completetime)){
                if(strtotime(date("H:i",strtotime($jobscompletedtoday->completetime))) >  strtotime($lastlogout)){
                    $lastlogout = date("h:i A",strtotime($jobscompletedtoday->completetime)) ;
                    $lastlogoutDB = date("H:i",strtotime($jobscompletedtoday->completetime));
                }
            }
            

            $totaluser ="";
            if(isset($userrequestcount->usercount)) {
                $totaluser = $userrequestcount->usercount;
            }

          
            if(isset($usercheckcount->usercount)) {
                 $totaluser = $totaluser . "," . $usercheckcount->usercount;
            }


            $totaluser = array_unique(explode(",",$totaluser));

            $totaluser = array_filter($totaluser);
            $countuser =count($totaluser);
           // if(isset($usercount->usercount))
            //    $countuser  =  $usercount->usercount;
            $countsessions =0;
                if(isset($boyhistory->countsessions))
                    $countsessions  =  $boyhistory->countsessions;

            $TimeDiffs ="00:00";
            if( $firstlogin != "00:00" && $lastlogout != "00:00" ){
                if($firstlogin == $lastlogout){
                    $TimeDiffs  = gmdate("H:i", 60);
                }else{
                    $TimeDiffs  = gmdate("H:i", strtotime($lastlogout) - strtotime($firstlogin));
                }
            }
            
            $checks = isset($boychecks->cheks)?$boychecks->cheks:0;

            $jobscompletedtoday = (isset($jobscompletedtoday->jobsdone)?$jobscompletedtoday->jobsdone:0);

            $prevjobspendingcount = (isset($prevjobspending->rgba_JobsPending)?$prevjobspending->rgba_JobsPending:0);
            //$jobspendingtoday = abs(($totaljobstoday+$prevjobspendingcount)-$jobscompletedtoday);
             
            $activity = new GodownBoyActivity();
            $activity->rgba_Branchid = $branch->br_id;
            $activity->rgba_Users =  $countuser;
            $activity->rgba_date = $prevdate;
            $activity->rgba_FirstAccept = $firstloginDB;
            $activity->rgba_LastAccept = $lastlogoutDB;
            $activity->rgba_Sessions =  $countsessions;
            $activity->rgba_JobsToday = $totaljobstoday;
            $activity->rgba_JobsCompleted = $jobscompletedtoday;
            $activity->rgba_JobsPreviousPending = $prevjobspendingcount;
            $activity->rgba_JobsPending = $jobspendingtoday;
            $activity->rgba_Duration =  $TimeDiffs;
            $activity->rgba_JobChecks = $checks;
            $activity->save();

            array_push($mailerarray,array("branch" => $branch->br_name,"users" => $countuser, "first" => $firstlogin, "last" => $lastlogout, "session" => $countsessions, "jobstoday" => $totaljobstoday, "jobscompleted" => $jobscompletedtoday, "jobspreviouspending" => $prevjobspendingcount, "jobspending" => $jobspendingtoday , "jobchecks" => $checks, "duration" =>   $TimeDiffs));           

        }
            
            //$columns = array_column($mailerarray, 'branch');
            //array_multisort($columns, SORT_ASC, $mailerarray);
        
        
            $message = '<html><body>';
            $message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
            $message .= "<tr style='background: #eee;'><td><strong>Branch</strong> </td>";
            $message .= "<td><strong>User Count</strong> </td>";
            $message .= "<td><strong>First Seen</strong> </td>";
            $message .= "<td><strong>Last Seen</strong> </td>";            
            $message .= "<td><strong>Jobs Today</strong> </td>";
            $message .= "<td><strong>Previous Pending</strong> </td>";
            $message .= "<td><strong>Jobs Packed</strong> </td>";
            $message .= "<td><strong>Pending Jobs</strong> </td>";           
            $message .= "<td><strong>Availability</strong> </td>";
            $message .= "<td><strong>Job Checks</strong> </td>";
            $message .= "<td><strong>Sessions</strong> </td></tr>";
           
            

            foreach($mailerarray as $mailerarr){
                $message .= "<tr><td>" . $mailerarr['branch'] . "</td>";
                $message .= "<td align='center'>" .  (($mailerarr['users']==0)?"-":$mailerarr['users']) . "</td>";
                $message .= "<td align='center' >" . (($mailerarr['first'] == "00:00:00" || $mailerarr['first'] == "00:00")?"-": $mailerarr['first'] ) . "</td>";
                $message .= "<td align='center' >" . (($mailerarr['last'] == "00:00:00" || $mailerarr['last'] == "00:00")?"-": $mailerarr['last'] ) . "</td>";                
                $message .= "<td align='center' >" . (($mailerarr['jobstoday']==0)?"-":$mailerarr['jobstoday']) . "</td>";   
                $message .= "<td align='center' >" . (($mailerarr['jobspreviouspending']==0)?"-":$mailerarr['jobspreviouspending']) . "</td>";   
                $message .= "<td align='center' >" . (($mailerarr['jobscompleted']==0)?"-":$mailerarr['jobscompleted']) . "</td>";   
                $message .= "<td align='center' >" . (($mailerarr['jobspending']==0)?"-":$mailerarr['jobspending'])   . "</td>";   
                
                                
                $message .= "<td align='center' >" . (($mailerarr['duration'] == "00:00:00" || $mailerarr['duration'] == "00:00")?"-": $mailerarr['duration'])    . "</td>";
                $message .= "<td align='center' >" . (($mailerarr['jobchecks']==0)?"-":$mailerarr['jobchecks'])   . "</td>";  
                $message .= "<td align='center' >" . (($mailerarr['session']==0)?"-":$mailerarr['session'])   . "</td></tr>";  
            }
            $message .= "</table>";
            $message .= "</body></html>";
            $maillist = config('emailschedule.packsure_activity_mail');
            if($maillist !=""){
                $mailids = explode(",",$maillist);
                foreach($mailids as $mailid){
                    if($mailid !=""){
                    DB::table('retaline_emailsms_queue')->insert(
                        [
                        'sender_id' => config('emailschedule.welcome_email'),
                        'sender_name' => config('emailschedule.welcome_sender'),
                        'receiver_id' =>$mailid, 
                        'type' => 2,
                        'extra_info'=>"Order Picker Activity  - " .  Date("d-M-y", strtotime($prevdate)) ,
                        'text_message'=>$message,
                        'created_on'=>date('Y-m-d H:i:s'),
                        'updated_on'=>date('Y-m-d H:i:s')
                        ]
                );
            }
                }
            }
        
        });
       



        
    }
}
