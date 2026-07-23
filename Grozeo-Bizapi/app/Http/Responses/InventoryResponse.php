<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventoryResponse implements Responsable
{
    protected $data;

    protected $error;

    protected $refid;


    public function __construct($data, $error, $refid)
    {
        $this->data = $data;
        $this->error = $error;
        $this->refid = $refid;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
       $totalcounts=count($this->data);
        $errorCount=count($this->error);
        if($errorCount){
            foreach ($this->error as  $value) {
                $errorMessage[]=["msg"=>"Error - erpID ".$value];
            }
            DB::table('retaline_emailsms_queue')->insert(
                [
                'sender_id' => config('emailschedule.welcome_email'),
                'sender_name' => config('emailschedule.welcome_sender'),
                'receiver_id' => config('emailschedule.inventory_upload_email'),  
                'type' => 2,
                'extra_info'=> config('siteinfo.app_client_project_name') . " - Error Stock upload API"  ,
                'text_message'=>"<pre>".json_encode(["Reference Id" => $this->refid,"Total Records" =>$totalcounts,"Total Updated" =>($totalcounts - $errorCount), "Error Message" => $errorMessage],JSON_PRETTY_PRINT)."<pre>",
                'created_on'=>date('Y-m-d H:i:s'),
                'updated_on'=>date('Y-m-d H:i:s')
                ]
        );
          return response()->json([
            'status' => 'ok',
            'data' => ["Total Records" =>$totalcounts,"Total Updated" =>$totalcounts - $errorCount],
            'error'=>$errorMessage,
             ], 406);

        }else{
            return response()->json([
            'status' => 'ok',
            'data' => ["Total Records" =>$totalcounts,"Total Updated" =>$totalcounts]
             ], 200);
        }
    
        
    }
    
}
