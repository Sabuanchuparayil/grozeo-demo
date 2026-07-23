<?php

namespace BackOffice\Tasks;

use Illuminate\Support\Facades\DB;
use BackOffice\Tasks\OrderAssigner;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\TransferOrderStatus;

class AssignOrder extends OrderAssigner
{     
    public function __invoke()
    {
        try{

        
        DB::transaction(function ()  {
        $this->clearGodownBoys();
        $this->clearScheduledOrders();
        $this->openOrders = $this->getOpenOrders();
        $this->godownBoys = $this->getGodownBoys();
        $this->tempAssignedOrders = $this->getTempAssignedOrders($this->openOrders->pluck('fsto_id'));

        $this->processOrders();
    });
}catch (\Exception $e)
{
    info("AssignOrder ERROR => ".$e->getMessage());
}
    }

    /**
     * {@inheritDoc}
     */
    protected function getOpenOrders()
    {  
        //DB::enableQueryLog(); 
        $data = TransferOrder::select('fsto_id', 'fsto_uid', 'fsto_source', 'fsto_sourcetype', 'fsto_openingtime')
            ->where('fsto_status', TransferOrderStatus::CREATED)
            ->orWhere('fsto_status', TransferOrderStatus::SCHEDULED_ORDER)
            ->orWhere('fsto_status', TransferOrderStatus::TO_MANUALLY_ASSIGN)
            ->whereIn('fsto_ordertype', [0,1,2])
            ->where('fsto_openingtime', '<=', now()->format('Y-m-d H:i:s'))           
            ->get();
        return $data;
    }

}
