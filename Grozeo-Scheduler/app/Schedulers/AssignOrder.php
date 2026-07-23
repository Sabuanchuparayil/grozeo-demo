<?php

namespace App\Schedulers;

use Illuminate\Support\Facades\DB;
use App\Schedulers\OrderAssigner;
use Illuminate\Support\Facades\Log;
use App\Models\TransferOrder;
use App\Status\TransferOrderStatus;
use App\Models\ProcessLock;


class AssignOrder extends OrderAssigner
{
    public function __invoke()
    {
        try
        {
            DB::transaction(function ()  {
                $this->clearGodownBoys();
                $this->clearScheduledOrders();
                $this->openOrders = $this->getOpenOrders();
                $this->godownBoys = $this->getGodownBoys();
                $this->tempAssignedOrders = $this->getTempAssignedOrders($this->openOrders->pluck('fsto_id'));

                $this->processOrders();
            });
            ProcessLock::updateColData("BizAPI_AssignOrder", 0);
        }
        catch (\Exception $e)
        {
            info("AssignOrder ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_AssignOrder", 1);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getOpenOrders()
    {
        $data = TransferOrder::select('fsto_id', 'fstr_id', 'fsto_uid', 'fsto_source', 'fsto_sourcetype', 'fsto_openingtime')
            ->where(function ($query) {
                $query->where('fsto_status', TransferOrderStatus::CREATED)
                    ->orWhere('fsto_status', TransferOrderStatus::SCHEDULED_ORDER)
                    ->orWhere('fsto_status', TransferOrderStatus::TO_MANUALLY_ASSIGN);
            })
            ->whereIn('fsto_ordertype', [0,1,2])
            ->where('fsto_openingtime', '<=', now()->format('Y-m-d H:i:s'))
            ->get();
        return $data;
    }

}
