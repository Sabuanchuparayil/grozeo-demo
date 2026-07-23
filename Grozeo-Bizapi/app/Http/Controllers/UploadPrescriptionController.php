<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Carbon\Carbon;
use App\Models\UploadPrescription;
use Illuminate\Http\Request;
use App\Models\prescriptiomMedicineMap;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\VellnezUserRepository;

class UploadPrescriptionController extends Controller
{


    protected $uploaddocument;
    private $cart;
    private $medicinemap;
    public function __construct(UploadPrescription $uploaddocument,Cart $cart,prescriptiomMedicineMap $medicinemap)
    {


        $this->uploaddocument = $uploaddocument;
        $this->cart=$cart;
        $this->medicinemap=$medicinemap;
    }

    public  function delete_prescription($id)
    {
        $this->uploaddocument->where('cust_id', auth()->user()->cust_id)->where('id',$id)->delete();
        $isexit=$this->medicinemap->where('prescription_id',$id)->get();
        if(count($isexit))
        {
            $this->medicinemap->where('prescription_id',$id)->delete();
        }

        return  new SuccessResponse("Successfully deleted");
    }
    public function getdiscription(Request $request)
    {
        //$domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . "." . config('filesystems.disks.s3.region') . ".amazonaws.com/prescription/";

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/prescription/";

        $prefix_url=DB::table('sys_configuration')->whereIn('cfg_Name',array("IMGTHUMB","IMGPREVIEW"))->get();
        $thumb="";
        $preview="";
        if(count($prefix_url)>1)
        {

            if($prefix_url[0]->cfg_Name=="IMGTHUMB")
            {
                $thumb=$prefix_url[0]->cfg_Value;
                $preview=$prefix_url[1]->cfg_Value;
            }
            else
            {
                $thumb=$prefix_url[1]->cfg_Value;
                $preview=$prefix_url[0]->cfg_Value;
            }
        }


        $datas=$this->uploaddocument->where('cust_id', auth()->user()->cust_id)->get();
        $item=array();
        foreach($datas as $data)
        {
            array_push($item,array(
                'id'=>$data['id'],
                'thumb_image'=>$domain.$thumb.$data['document_url'],
                'preview_image'=>$domain.$preview.$data['document_url'],
                'orignal_image'=>$domain.$data['document_url'],
                'description'=>$data['description'],
                'status'=>$data['status'],
                'expiry_date'=>Carbon::parse($data['expiry_date'])->format('d-m-Y')

            ));
        }
    //    $discription= $this->uploaddocument->where('cust_id', auth()->user()->cust_id)
    //    ->select('id',DB::raw('CONCAT("'.$domain.'",document_url) AS document_url'),'description','status','expiry_date')
    //     ->get();


        return  new SuccessWithData($item);
    }


    public function update_upload_data(Request $request)
    {
        $validatedData = $request->validate([

            'id' => 'required',
            //'upload_id'=>'required',


        ]);

        $this->uploaddocument::where('id',$request['upload_id'])
        ->update(['status' =>1]);

        // $this->cart::where('id',$request['id'])
        // ->update(['upload_id'=>$request['upload_id']]);

        return  new SuccessResponse("Successfully updated");

    }
    public function upload_data(Request $request)
    {

        $validatedData = $request->validate([
            'priority'=>'required',
            'file' => 'required|array',
            'file.*.name'=>'required',
            'file.*.description'=>'nullable',
            'file.*.medinicemasterid'=>'nullable',
            'file.*.item_master_id'=>'nullable',
            'file.*.status'=>'nullable',
            'file.*.expiry_date'=>'nullable',

        ]);

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . "." . config('filesystems.disks.s3.region') . ".amazonaws.com/prescription/";

        $this->uploaddocument->where('cust_id',auth()->user()->cust_id)->update(['ready_to_order_status' => 0]);

        $current_status=1;
        $inputdata=$request->priority;
        if($inputdata==0)
        {
            $prioty=0;
        }
        else{
            $current_status=1;
            $current_prioty=$this->uploaddocument->select('priority')->where('cust_id',auth()->user()->cust_id)->orderBy('priority', 'desc')->first();

            if($current_prioty['priority']!=0)
            {
                $prioty=$current_prioty['priority']+1;

            }

            if($current_prioty['priority']==0)
            {
                $prioty=1;

            }
        }


        $files_datas = $request->file;  // your base64 encoded
        $uploadedPrescriptions=[];
        foreach ($files_datas as $files_data) {

         $prescription=   $this->uploaddocument->create([
                'cust_id' => auth()->user()->cust_id,
                'document_url' =>trim($files_data['name']),
                'description' => $files_data['description'],
                'status'=> 0,
                'priority'=>$prioty,
                'expiry_date'=>date('Y-m-d'),
                'ready_to_order_status'=>$current_status,
                'prescription_folder'=>"prescription/",
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);
                if($prioty!=0)
                {
                    $prioty++;
                }


        }
        $uploadedPrescriptions[]["id"]=$prescription->id;

        return  new SuccessResponse([
            "message"=>"Successfully upload",
            "uploadedPrescriptionsIDs"=>(!empty($uploadedPrescriptions))?array_values($uploadedPrescriptions):[]
        ]);
    }


}
