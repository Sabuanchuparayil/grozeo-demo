<?php

namespace App\Schedulers;

use App\Models\{
    ProcessLock,
    BranchInventory,
    BranchInventoryUpload,
    Branch,
    StockItemMaster
};
use App\Models\Supports\InventoryUploadLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryUpdate
{
    protected $itemMaster;
    protected $branchUpload;
    protected $branchInventory;
    protected $openEntries;

    public function __invoke(): void
    {
        try {
            $this->itemMaster = new StockItemMaster;
            $this->branchUpload = new BranchInventoryUpload;
            $this->branchInventory = new BranchInventory;

            $this->openEntries = $this->getPendingData();
            $this->processInventoryUpdates();
            ProcessLock::updateColData("BizAPI_InventoryUpdate", 0);
        } catch (\Exception $e) {
            Log::error("InventoryUpdate Scheduler Error: {$e->getMessage()}");
            ProcessLock::updateColData("BizAPI_InventoryUpdate", 0);
        }
    }

    protected function getPendingData()
    {
        return InventoryUploadLog::select('id', 'branchId', 'status', 'uploadedapikey', 'request')
            ->where('status', 1)
            ->whereDate('createdOn', today()) // Ensures the date is today
            ->where('createdOn', '>=', now()->subMinutes(10))
            ->get();
    }

    protected function processInventoryUpdates(): void
    {
        foreach ($this->openEntries as $entry) {
            $branchId = $entry->branchId;
            $branchReference = $entry->uploadedapikey;
            $itemData = json_decode($entry->request);
            $stockItems = $this->mapStockItems($itemData);

            $errors = $this->validateItems($stockItems, $branchId);

            DB::transaction(function () use ($stockItems, $errors, $branchId,$branchReference,$entry) {
                $this->saveItems($stockItems, $errors, $branchId);
                $this->logInventoryUpdates($stockItems, $errors, $branchId,$branchReference);
                InventoryUploadLog::where('id', $entry->id)
                ->update([
                    'status' => 2,
                    'updatedOn' => now(),
                ]);
            });
        }
    }

    protected function mapStockItems(array $data): array
    {
        $data = json_decode(json_encode($data), true);
        return array_map(function ($item) {
            return [
                'item_count' => $item['item_count'],
                'mrp' => $item['mrp'],
                'selling_price' => $item['selling_price'],
                'discount_selling_price' => $item['discount_selling_price'] ?? 0,
                'stit_itemERPId' => $item['stit_itemERPId'],
            ];
        }, $data);
    }

    protected function validateItems(array &$stockItems, int $branchId): array
    {
        $errors = [];
        $childItems = [];

        foreach ($stockItems as $key => $item) {
            $stitId = $this->getStockItemId($item['stit_itemERPId'], $branchId);

            if ($stitId > 0) {
                $parentItem = $this->itemMaster->find($stitId, ['stit_ID', 'stit_HasChildItem', 'stit_GST', 'least_package_type_id']);
                $stockItems[$key] = array_merge($item, [
                    'itemId' => $parentItem->stit_ID,
                    'stit_GST' => $parentItem->stit_GST,
                    'least_package_type_id' => $parentItem->least_package_type_id,
                ]);

                if ($parentItem->stit_HasChildItem) {
                    $childItems = array_merge($childItems, $this->getChildItems($parentItem, $item));
                }
            } else {
                $errors[] = $item['stit_itemERPId'];
                unset($stockItems[$key]);
            }
        }

        $stockItems = array_merge($stockItems, $childItems);
        return $errors;
    }

    protected function getStockItemId(string $erpId, int $branchId): int
    {
        $storeCode = DB::table('finascop_stock_itemmaster_product_code_stores')
            ->where('fsipcs_store', $branchId)
            ->where('fsipcs_Code', $erpId)
            ->value('fsipc_stit_id');

        if (!$storeCode) {
            $branch = Branch::find($branchId, ['br_storeGroup']);
            $storeGroup = $branch->br_storeGroup;

            $storeCode = DB::table('finascop_stock_itemmaster_product_codes')
                ->where('fsipc_code', $erpId)
                ->where(function ($query) use ($storeGroup) {
                    $query->where([
                        ['fsipc_isIndividual', 0],
                        ['fsipc_isCompany', 0],
                        ['fsipc_storeGroup', $storeGroup],
                    ])->orWhere('fsipc_isCompany', 1);
                })
                ->value('fsipc_stit_id');
        }

        return $storeCode ?? 0;
    }

    protected function getChildItems($parentItem, $parentData): array
    {
        $childItems = [];
        $subItems = $this->itemMaster->where('stit_ParentItemId', $parentItem->stit_ID)->get(['stit_ID', 'stit_ConvertCalcMode', 'stit_ConvertCalcRate']);

        foreach ($subItems as $subItem) {
            $conversionRate = $subItem->stit_ConvertCalcRate;
            $mrp = $parentData['mrp'] / $conversionRate;
            $sellingPrice = $parentData['selling_price'] / $conversionRate;

            if ($subItem->stit_ConvertCalcMode == 1) {
                $mrp = $parentData['mrp'] * $conversionRate;
                $sellingPrice = $parentData['selling_price'] * $conversionRate;
            }

            $childItems[] = [
                'stit_itemERPId' => $parentData['stit_itemERPId'],
                'item_count' => $parentData['item_count'],
                'mrp' => round($mrp, 2),
                'selling_price' => round($sellingPrice, 2),
                'itemId' => $subItem->stit_ID,
            ];
        }

        return $childItems;
    }

    protected function saveItems(array $stockItems, array $errors, int $branchId): void
    {
        foreach ($stockItems as $item) {
            $this->branchInventory->updateOrCreate(
                ['stit_id' => $item['itemId'], 'branch_id' => $branchId],
                [
                    'item_count' => $item['item_count'],
                    'mrp' => $item['mrp'],
                    'selling_price' => $item['selling_price'],
                    'updated_on' => now(),
                ]
            );
        }
    }

    protected function logInventoryUpdates(array $stockItems, array $errors, int $branchId, int $branchReference): void
    {
        $log = $this->branchUpload->create([
            'fbiu_branch' => $branchId,
            'fbiu_uploadedbyapi' => 1,
            'fbiu_status' => 1,
            'fbiu_uploadedapikey' => $branchReference,
            'fbiu_missingerpids' => implode(',', $errors),
        ]);

        $details = array_map(function ($item) use ($branchId) {
            return [
                'stit_id' => $item['itemId'],
                'branch_id' => $branchId,
                'item_count' => $item['item_count'],
                'mrp' => $item['mrp'],
                'selling_price' => $item['selling_price'],
            ];
        }, $stockItems);

        $log->details()->createMany($details);
    }
}
