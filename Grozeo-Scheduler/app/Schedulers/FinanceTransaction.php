<?php

namespace App\Schedulers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\FinanceTransaction as FTModel;
use App\Models\MerchantSettlements as MSModel;
use App\Models\FinanceTransactionLog as FTLogModel;
use App\Models\ProcessLock;

class FinanceTransaction
{
    public function __invoke()
    {
        try
        {
            $merchantSettlements = MSModel::select('storegroup_id', 'branch_id', 'bank_id', 'account_number', 'ifsc_code', 'bank_account_name', 'bank_account_email', DB::raw('GROUP_CONCAT(id) as ids'), DB::raw('SUM(amount_due) as payout_total'))
            ->where('status', 0)
            // ->where(DB::raw('TIMESTAMPDIFF(HOUR,created_at,NOW())'), '>', 12)
            ->groupBy('storegroup_id', 'branch_id', 'bank_id', 'account_number', 'ifsc_code', 'bank_account_name', 'bank_account_email')->get();

            foreach ($merchantSettlements as $ms)
            {
                $getIFSC = DB::table('sys_configuration')->where('cfg_Name', 'GROZEO_IFSCCODE')->value('cfg_Value');
                $getIFSC = @$getIFSC ?? "IDFB0080693";
                $transactnType = (substr($getIFSC, 0, 4) == substr($ms->ifsc_code, 0, 4)) ? "IFT" : "NEFT";
                $ftID = FTModel::insertGetId([
                    'settlement_id'         => MSModel::merchantSettlementNumbering(),
                    'storegroup_id'         => $ms->storegroup_id,
                    'branch_id'             => $ms->branch_id,
                    'bank_id'               => $ms->bank_id,
                    'account_number'        => $ms->account_number,
                    'ifsc_code'             => $ms->ifsc_code,
                    'bank_account_name'     => $ms->bank_account_name,
                    'bank_account_email'    => $ms->bank_account_email,
                    'transaction_type'      => $transactnType,
                    'payout_amount'         => $ms->payout_total
                ]);
                if($ftID)
                {
                    $ftLogData = array_map(function($i) use ($ftID, $ms)
                    {
                        return [
                            'ft_id'             => $ftID,
                            'ms_id'             => $i,
                            'bank_id'           => $ms->bank_id,
                            'account_number'    => $ms->account_number,
                            'ifsc_code'         => $ms->ifsc_code
                        ];
                    }, explode(',', $ms->ids));
                    FTLogModel::insert($ftLogData);

                    MSModel::whereIn('id', explode(',', $ms->ids))->where('status', 0)->update([
                        'status'    => 3
                    ]);
                }
            }
            ProcessLock::updateColData("BizAPI_FinanceTransaction", 0);
        }
        catch (\Exception $e)
        {
            Log::error("FinanceTransaction ERROR => " . $e->getMessage(), ['exception' => $e]);
            ProcessLock::updateColData("BizAPI_FinanceTransaction", 0);
        }
    }
}
