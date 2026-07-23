<?php

namespace Models; {

    class ContactEntry extends ModelAbstract
    {

        public function POST_saveContactForms($flag, $request)
        {
            try {
                $supportdb = new \sqlDb(SUPPORTDSN);
                $enquiryId = 0;
                $msg = "";

                $toInsert = [
                    'organisationName'           => @$request['business_name'],
                    'name'                 => (preg_match('/^[a-zA-Z\s]+$/', @$request['name']) == 1?$request['name']:''),
                    'mobile'               => (preg_match('/^\d+$/', @$request['phone']) == 1?$request['phone']:''),
                    'email'                => filter_var(@$request['email'], FILTER_VALIDATE_EMAIL),
                    'businessLocation'      => @$request['location'],
                    'sourceId'              => @$request['source'],
                    'message'               => @$request['message'],
                    'country_code'          => @$request['country_code'],
                    'entryType'             => 0
                ];
                switch ($request['type']) {
                    case 1:
                        $toInsert = array_filter($toInsert);
						unset($toInsert['sourceId']);
						unset($toInsert['entryType']);
                        $insertProduct = $supportdb->perform('general_enquiry', $toInsert);
                        $enquiryId = $supportdb->getLastInsertId();
                        $msg = "Inserted";
                        break;
                    case 4:
                        $toInsert = array_filter($toInsert);
                        $insertProduct = $supportdb->perform('crm_area_associate', $toInsert);
                        $enquiryId = $supportdb->getLastInsertId();
                        $msg = "Inserted";
                        break;
                    case 6:
                        $toInsert = array_filter($toInsert);
                        $insertProduct = $supportdb->perform('crm_consulting_partner', $toInsert);
                        $enquiryId = $supportdb->getLastInsertId();
                        $msg = "Inserted";
                        break;
                }
                /*if (@$request['type'] == 4) {
                    $insertProduct = $supportdb->perform('crm_area_associate', $toInsert);
                    $enquiryId = $supportdb->getLastInsertId();
                    $msg = "Inserted";
                } else {
                    $insertProduct = $supportdb->perform('crm_consulting_partner', $toInsert);
                    $enquiryId = $supportdb->getLastInsertId();
                    $msg = "Inserted";
                }*/


                if (@$request['redirect_to'] != "") {
                    header('Location: ' . $request['redirect_to'], TRUE, 302);
                    exit();
                } else {
                    return [
                        'status'    => "ok",
                        'data'      => [],
                        'msg'       => $msg
                    ];
                }
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
