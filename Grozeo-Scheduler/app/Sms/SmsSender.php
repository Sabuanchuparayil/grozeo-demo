<?php

namespace App\Sms;

use Exception;
use App\Sms\TextLocalSms;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\SmsEmailRepository;
use App\Models\BranchGroup;

class SmsSender {

    protected $smsEmailLogs;

    public function __construct(SmsEmailRepository $smsEmailLogs, TextLocalSms $curlurl) {
        $this->smsEmailLogs = $smsEmailLogs;
        $this->curlurl = $curlurl;
    }

    public function fetchContentSendSms($templatedata, $mobile, $templateType, $strGrpId = -1, $strGrpType = 1)
    {
        try
        {
            $storegroupid = $strGrpId;
            if($storegroupid == -1)
            {
                $storegroupid = getHeaderStoreGroup();
            }

            $getTemplateContent = DB::select('SELECT templateContent,templateId,templateHeader, (SELECT COUNT(*) AS ismobile FROM test_mobile WHERE mobile="'.$mobile.'") AS ismobile FROM sms_templates WHERE stn_templateNameId= '.$templateType.' AND (store_group_id = '.$strGrpId.' OR store_group_id = 0) AND is_used = '.$strGrpType.' ORDER BY store_group_id DESC LIMIT 1');

            if($storegroupid > 0){
                $data = BranchGroup::where('store_group_id', $storegroupid)                               
                    ->select('store_group_name')
                    ->first();
                $siteName = $data['store_group_name'];
            }else{
                $siteName = config('siteinfo.app_client_project_name');
            }
            switch ($templateType) {// from table sms_template_name
                case 3:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['amount'],
                        '{#var2}'   => $templatedata['order_order_id'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 4:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['amount'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 5:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        // '{#var1}' => $templatedata['order_id'],
                        '{#var1}'   => $siteName,
                        '{#var2}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 6:
                    $type = 'OTP';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $siteName,
                        '{#var2}'   => $templatedata['otp'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 7:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}' => $templatedata['order_order_id'],
                        '{#var2}' => $siteName,
                        '{#var3}' => $templatedata['otp'],
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 9:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $siteName,
                        '{#var2}'   => $templatedata['orderAmt'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 10:
                    $type = 'OTP';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                $vars = array(
                    '{#var1}'   => $templatedata['otp'],
                    '{#var2}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 11:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['order_order_id'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 12:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['order_order_id'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 15:
                    $type = 'OTP';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $siteName,
                        '{#var2}'   => $templatedata['otp'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 20:
                    $type = 'OTP';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['gst'],
                        '{#var2}'   => $templatedata['otp'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 21:
                    $type = 'OTP';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['otp'],
                        '{#var2}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 22:
                    $type = 'MKT';
    
                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;
    
                    $vars = array(
                        '{#var1}'   => $templatedata['amount'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );
    
                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 23:
                    $type = 'OTP';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['otp'],
                        '{#var2}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 24:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['code'],
                        '{#var2}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 25:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['otp'],
                        '{#var2}'   => $templatedata['amount'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 26:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['otp'],
                        '{#var2}'   => $templatedata['feature'],
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 27:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['order_order_id'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 28:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['amount'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
                case 29:
                    $type = 'MKT';

                    $data_base[0]['msg'] = $getTemplateContent[0]->templateContent;

                    $vars = array(
                        '{#var1}'   => $templatedata['amount'],
                        '{#var2}'   => $siteName,
                        '{#var3}'   => strtoupper(config('siteinfo.app_env_type')).strtoupper(substr(md5(now().$mobile), 0, 5))
                    );

                    $msg = strtr($data_base[0]['msg'], $vars);
                    $tempid = $getTemplateContent[0]->templateId;
                    break;
            }

            $testMobile = @$getTemplateContent[0]->ismobile;
            $response = '';
            if(substr($mobile, 0, 1) != "+")
            {
                $phonecode = config('app.phonecode');
                $pos = strpos($mobile, $phonecode);
                if ($pos == false)
                {
                    $mobile = $phonecode.$mobile;
                }
            }
            $data = compact("mobile", "msg", "response", "storegroupid");
            $emailLog = $this->smsEmailLogs->store($data);
            if($testMobile == 0)
            {
                $defaultProvider = config('sms.default');
                $smsClass = config("sms.{$defaultProvider}.class");
                $smsObj = new $smsClass();
                $response = $smsObj->sendSMS($mobile, $type, $msg, $tempid);
            }
            return $response;
        }
        catch (\Exception $e)
        {
            info($e);
            return $e->getMessage(); 
        }
    }
}
