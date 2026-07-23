<?php

namespace BackOffice\Tasks;


use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\LaravelJobs;
use Carbon\carbon;


class SendNotificationCheck
{

    public function __invoke()
    {
        $orders = $this->getpendingNotificationqueue();
        // DB::transaction(function () use ($orders) {
        //     $this->updateRequests($orders->pluck('id'));
        //     $this->revertOrderStatus($orders->pluck('order_id'));
        // });
    }

    public function getpendingEmailqueue()
    {
        $count = 0;
        while ($count < 59) {
            $startTime =  Carbon::now();

            $datas=LaravelJobs::where("attempts",0)->where("available_at","<=",$startTime)->get();
            $id=array();
            foreach($datas as $data)
            {
                array_push($id,$data['id']);
            }
            if(count($id)>0)
            {
                LaravelJobs::whereIn('id',$id)
                ->update(['attempts' => 1]);
            }

            foreach($datas as $data)
            {
                dispatch(new SendNotificationJob($data->payload));

            }
            $endTime = Carbon::now();
             $totalDuration = $endTime->diffInSeconds($startTime);
            if($totalDuration > 0) {
                $count +=  $totalDuration;
            }
            else {
                $count++;
            }
            sleep(1);

        }

    }


}
