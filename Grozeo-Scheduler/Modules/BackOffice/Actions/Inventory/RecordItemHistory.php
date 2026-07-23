<?php

namespace BackOffice\Actions\Inventory;

use Illuminate\Support\Collection;

use BackOffice\Models\InventoryHistory;

class RecordItemHistory
{
    protected $inventoryHistory;

    public function __construct(InventoryHistory $inventoryHistory = null)
    {
        $this->inventoryHistory = $inventoryHistory ?? new InventoryHistory;
    }

    /**
     * Record the item status in history.
     *
     * @param Collection $barcodes
     * @param integer $type
     * @param integer $orderId
     * @return void
     */
    public function record(Collection $barcodes, int $type, int $orderId)
    {
        $details = $this->getItemDetails($type, $orderId);
        
        $barcodes->each(function($barcode) use ($details) {
            $this->inventoryHistory->create([
                'stiid_id' => $barcode->stiid_id,
                'stiidm_itemmasterid' => $barcode->stiid_itemmasterid,
                'stiidm_barcode' => $barcode->stiid_barcode,
                'stiidm_details' => $details,
            ]);
        });
    }

    /**
     * Get the item details to store in history.
     *
     * @param integer $type
     * @param integer $orderId
     * @return string
     */
    protected function getItemDetails(int $type, int $orderId)
    {
        return json_encode([
            'type' => $type == 0 ? 'CPD_OUTWARD_SCANNED' : 'BRANCH_OUTWARD_SCANNED',
            'order_id' => $orderId,
            'text' => 'SCANNED',
        ]);
    }
}
