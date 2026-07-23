<?php

namespace BackOffice\Tasks;

use Illuminate\Support\Facades\DB;
use BackOffice\Models\FinanceTransaction as FTModel;
use BackOffice\Models\MerchantSettlements as MSModel;
use BackOffice\Models\FinanceTransactionLog as FTLogModel;

class FinanceTransaction
{
    public function __invoke()
    {
        try
        {
            $merchantSettlements = MSModel::select('storegroup_id', 'branch_id', 'bank_id', 'account_number', 'ifsc_code', DB::raw('GROUP_CONCAT(id) as ids'), DB::raw('SUM(amount_due) as payout_total'))
            ->where('status', 0)
            ->where(DB::raw('TIMESTAMPDIFF(HOUR,created_at,NOW())'), '>', 12)
            ->groupBy('bank_id')->get();

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
            return response()->json($outs);
        }
        catch (\Exception $e)
        {
            info("FinanceTransaction ERROR => ".$e->getMessage());
        }
    }
}