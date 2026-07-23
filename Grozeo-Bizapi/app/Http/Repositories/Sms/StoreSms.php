<?php

namespace App\Http\Repositories\Sms;

use BackOffice\Models\EmailsmsQueue;
use App\Sms\SmsSender;

class StoreSms {

    public static function orderCod(array $data, $isFullyWallet = false, $walletAmount = 0) {
        $msg = "You have successfully created an order in " . config('siteinfo.app_client_project_name') . " vide Order No.{$data['order_id']}.";
        $currency = config('app.def_currency_symbol');
        $msg .= $isFullyWallet ? "Since you opt Wallet payment, {$currency} {$walletAmount} has been deducted from your wallet balance. Thank you." : "Since you opt Cash on Delivery payment, please keep ready {$currency} {$data['amount']}. Thank you"; //1607100000000004822
//You have successfully created an order in {#var#} vide Order No.{#var#}. Since you opt Cash on Delivery payment, please keep ready Rs.{#var#}. Thank you
        return (new static)->store($data['mobile'], $msg);
    }

    private function store($mobile, $msg) {
        return EmailsmsQueue::create([
                    "is_sent" => 0,
                    "receiver_id" => $mobile,
                    "type" => 1,
                    "is_sms" => 1,
                    "text_message" => $msg
        ]);
    }

    public static function orderOnlineCreate($mobile, $order_id) {
        //You have successfully created an order (No.{#var#}) in {#var#}. You have selected online payment and we will update you once the payment complete.
        //1607100000000004820
        $msg = "You have successfully created an order (No.{$order_id}) in " . config('siteinfo.app_client_project_name') . ". You have selected online payment and we will update you once the payment complete.";
        return (new static)->store($mobile, $msg);
    }

    public static function successOnline(array $data) {

        app(SmsSender::class)->fetchContentSendSms($data, $data['mobile'], 4,$data['storegroup_id']);
        //1607100000000004823
        //We have received Rs.{#var#} against your Order No.{#var#} vide Bank Ref. No.{#var#}. Thank you for selecting {#var#}
        //$msg = "We have received Rs.{$data['amount']} against your Order No.{$data['order_id']} vide Bank Ref. No.{$data['ref_no']}. Thank you for selecting ".config('siteinfo.app_client_project_name');
        // return (new static)->store($data['mobile'], $msg);
    }

    public static function failureOnline($mobile, $order_id) {
        $data['order_id'] = $order_id;
        app(SmsSender::class)->fetchContentSendSms($data, $mobile, 5);
        //Sorry to mention that your attempt to pay for {#var#} Order No.{#var#} is failed. We have saved your order so that you may complete the same.
        //1607100000000004821
        //$msg = "Sorry to mention that your attempt to pay for " . config('siteinfo.app_client_project_name') . " Order No.{$order_id} is failed. We have saved your order so that you may complete the same.";
        //return (new static)->store($mobile, $msg);
    }

}
