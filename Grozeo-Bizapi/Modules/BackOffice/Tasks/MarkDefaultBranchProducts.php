<?php

namespace BackOffice\Tasks;

use Carbon\carbon;
use App\SystemConfig;
use App\Models\StockItemMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\BranchInventory;

class MarkDefaultBranchProducts 
{     
    public function __invoke()
    {
        $startnow = Carbon::now()->toDateTimeString();
        $lastrundets = SystemConfig::where('cfg_Name', "DefaultBranchProductsLastRun")->first(['cfg_Name', 'cfg_Value']);
        if($lastrundets){
            $lastrun = $lastrundets->cfg_Value;
        }else{
            $lastrun = "2000-01-01 00:00:00";
        }
       // DB::enableQueryLog();
        $itemstoupdate  = BranchInventory::where('updated_on','>', $lastrun)
                ->select('stit_id','branch_id','item_count','mrp','selling_price','fsbg_id','is_default')
                ->get();
                $updateditems =[];
                foreach($itemstoupdate as $items){
                    if(($items['item_count']>0) || $items['is_default'] ==1){
                        //select the items in the same uniqueitem
                        //(StockItemMaster $itemMaster = null, StockUniqueItem $uniqueItem = null)
                    $unqid = StockItemMaster::where('stit_ID',$items['stit_id'])
                    ->first(['stit_fsiuid']);
                    $allitems = StockItemMaster::select('finascop_stock_itemmaster.stit_ID','fb.mrp','fb.item_count','fb.selling_price','fb.is_default','fb.id')
                    ->join('finascop_stock_branch_inventory as fb', 'fb.stit_id', 'finascop_stock_itemmaster.stit_ID')                    
                    ->where('fb.branch_id',$items['branch_id'])
                    ->where('fb.fsbg_id',$items['fsbg_id'])
                    ->where('finascop_stock_itemmaster.stit_fsiuid',$unqid['stit_fsiuid']);      
                    $allitems = $allitems->get();                  
                    DB::transaction(function () use($allitems)  {
                        $allitemids = $allitems->pluck('id')->toArray();
                        DB::table('finascop_stock_branch_inventory')
                        ->whereIn('id',$allitemids)
                        ->update(['is_default' => 0]);
                        $leastpriceid=0;
                        $leastprice=0;
                        foreach($allitems as $allitem){
                            if($allitem['mrp']>0 && $allitem['item_count']>0 && $allitem['selling_price']>0){
                                if($allitem['selling_price']<$leastprice || $leastprice==0){
                                    $leastpriceid=$allitem['id'];
                                    $leastprice=$allitem['selling_price'];
                                }
                            }
                        }
                        if($leastpriceid>0){
                            DB::table('finascop_stock_branch_inventory')
                            ->where('id',$leastpriceid)
                            ->update(['is_default' => 1]);
                        }
                    });
                }
                }
                DB::statement("replace into sys_configuration(cfg_Name,cfg_Value,cfg_Enabled) values ('DefaultBranchProductsLastRun','".  $startnow ."','Yes')");
    }
}
