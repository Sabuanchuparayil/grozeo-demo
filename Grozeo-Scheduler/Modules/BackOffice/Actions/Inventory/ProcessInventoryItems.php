<?php

namespace BackOffice\Actions\Inventory;

use BackOffice\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\InventoryDetails;

class ProcessInventoryItems
{
    protected $items;

    protected $scanned;

    /**
     * Process the inventory items.
     *
     * @param array $items
     * @param \Illuminate\Support\Collection $inventoryItems
     * @return array
     */
    public function process($items, $inventoryItems)
    {
        $mismatched = [];
        $this->scanned = collect([]);
        $this->fetchAllItemName(Arr::pluck($items, 'item_id'));
        
        foreach ($items as $item) {
          
            $pickedItem = InventoryDetails::where('stiid_itemmasterid', $item['item_id'])
                          ->select('stiid_barcode')
                          ->get()  ;
            $incorrectItems = $this->findIncorrectBarcodes($pickedItem, $item['barcodes']);

            if (!empty($incorrectItems)) {
                $mismatched[] = [
                    'item_id' => $item['item_id'],
                    'item_name' => $this->getItemName($item['item_id']),
                    'barcodes' => $incorrectItems,
                ];
            }
        }
        return $mismatched;
    }

    /**
     * Find the items with incorrect barcodes.
     *
     * @param \Illuminate\Support\Collection $itemList
     * @param array $barcodes
     * @return array
     */
    protected function findIncorrectBarcodes($itemList, $barcodes)
    {
        $incorrectItems = [];

        foreach ($barcodes as $barcode) {
            if (
                $itemList->contains('stiid_barcode', $barcode)
                && !$this->scanned->contains($barcode)
            ) {
                $this->scanned->push($barcode);
                continue;
            }
            $incorrectItems[] = $barcode;
        }

        return $incorrectItems;
    }

    /**
     * Fetch the item names of all available items.
     *
     * @param array $itemIds
     * @return void
     */
    protected function fetchAllItemName($itemIds)
    {
        $this->items = Item::select('stit_id', 'stit_sku')
            ->whereIn('stit_id', $itemIds)
            ->get();
    }

    /**
     * Get item name for a single item
     *
     * @param string $itemId
     * @return string
     */
    protected function getItemName($itemId)
    {
        return $this->items
            ->firstWhere('stit_id', $itemId)
            ->stit_sku ?? '';
    }
}
