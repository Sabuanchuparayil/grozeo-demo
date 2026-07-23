<?php

namespace BackOffice\Http\Repositories;

use Illuminate\Support\Facades\DB;
use App\Sms\SmsSender;
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use App\Models\{
    Customer,
    WalletTransaction
};

class WalletTransactionRepository
{
    public function createWalletEntry($request)
    {
        try
        {
            $openBalQuery = '(SELECT brcw_closingBalance FROM retaline_customer_wallet_transaction tx1 WHERE tx1.cust_id = '.$request['customer_id'].' AND tx1.brcw_id = (SELECT MAX(tx2.brcw_id) FROM retaline_customer_wallet_transaction tx2 WHERE tx2.cust_id = '.$request['customer_id'].'))';

            $openBalQueryData = DB::select($openBalQuery);

            $checkWalletTransaction = WalletTransaction::where(
            [
                'cust_id'           => $request['customer_id'],
                'refentry_id'       => $request['order_id'],
                'brcw_SourceType'   => $request['source_type']
            ])->first();
            if($checkWalletTransaction == null)
            {
                $walletTransaction = WalletTransaction::create([
                    'cust_id'               => $request['customer_id'],
                    'refentry_id'           => $request['order_id'],
                    'brcw_SourceType'       => $request['source_type'],
                    'brcw_Amount'           => $request['amount'],
                    'brcw_AddInfo'          => $request['information'],
                    'stiid_barcode'         => $request['barcode'],
                    'brcw_OpeningBalance'   => (@$openBalQueryData[0]->brcw_closingBalance) ? $openBalQueryData[0]->brcw_closingBalance : 0
                ]);
                if($walletTransaction)
                {
                    $customer = Customer::where('cust_id', $request['customer_id'])->first();
                    if($customer)
                    {
                        $customer->cust_walletbalance += $request['amount'];
                        $customer->save();
                    }
                    $templatedata = [
                        "amount"    => $request['amount']
                    ];
                    app(SmsSender::class)->fetchContentSendSms($templatedata, $customer->cust_mobile, 29);
                    return new SuccessWithData($walletTransaction);
                }
                return new ErrorResponse('Unable to create transaction');
            }
            return new ErrorResponse('This transaction already exists in the wallet'); 
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}
