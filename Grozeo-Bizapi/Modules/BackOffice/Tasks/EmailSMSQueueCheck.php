<?php

namespace BackOffice\Tasks;


use App\Jobs\SendEmailSmsJob;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\EmailsmsQueue;



class EmailSMSQueueCheck
{

    public function __invoke()
    {
        $orders = $this->getpendingEmailqueue();
        // DB::transaction(function () use ($orders) {
        //     $this->updateRequests($orders->pluck('id'));
        //     $this->revertOrderStatus($orders->pluck('order_id'));
        // });
    }

    public function getpendingEmailqueue()
    {
        $datas=DB::table('retaline_emailsms_queue')
        ->join('retaline_customer','retaline_emailsms_queue.receiver_id', '=', 'retaline_customer.cust_id')
        ->select('retaline_emailsms_queue.text_message as message','retaline_emailsms_queue.type as type','retaline_emailsms_queue.is_sent','retaline_emailsms_queue.id','retaline_customer.cust_email as email')
        ->where('retaline_emailsms_queue.is_sent',0)->where('retaline_emailsms_queue.is_sms',0)->get();
        $id=array();
        foreach($datas as $data)
        {
            //dispatch(new \App\Jobs\SendEmailSmsJob($data));
            array_push($id,$data['id']);
        }
        if(count($id)>0)
        {
            DB::table('retaline_emailsms_queue')
            ->whereIn('id',$id)
            ->update(['is_sent' => 1]);
        }

        foreach($datas as $data)
        {
            dispatch(new \App\Jobs\SendEmailSmsJob($data));

        }

    }


}
