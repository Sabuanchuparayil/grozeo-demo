<?php

namespace App\Schedulers;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use App\Models\ProcessLock;

class BranchStatusUpdate {

    public function __invoke() {
        try
        {
            //$this->timedBranches = $this->getRelatedBrances();
            //$this->updateOnBranchTimings();
            $query = "CALL setStoreOnOffBySchedule();";
            $BranchStatusUpdate = DB::select($query);
            ProcessLock::updateColData("BizAPI_BranchStatusUpdate", 0);
        }
        catch (\Exception $e)
        {
            info("BranchStatusUpdate ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_BranchStatusUpdate", 0);
        }
    }

    protected function getRelatedBrances() {
        $data = DB::select("SELECT br_SalesOnline,br_SalesOffline,br_ID FROM finascop_branch WHERE br_ID IN (SELECT DISTINCT(branch_id) FROM branch_timings WHERE COALESCE(DATE_FORMAT(updatedOn,'%Y-%m-%d'),'')  <> '" . now()->format('Y-m-d') . "')");
        return $data;
    }

    protected function updateOnBranchTimings() {
        foreach ($this->timedBranches as $branch) {

            //if ($branch->br_SalesOnline == 0 && $branch->br_SalesOffline == 1) {
            $brTimingss = DB::table('branch_timings')
                    ->select('br_open_time', 'br_close_time')
                    ->orderBy('br_open_time', 'asc')
                    ->where('branch_id', '=', $branch->br_ID)
                    ->get();
            $onFlag = 0;
            foreach ($brTimingss as $brTiming) {
                if ((now()->format('H:i:s') > $brTiming->br_open_time) && (now()->format('H:i:s') < $brTiming->br_close_time)) {
                    $onFlag = $onFlag + 1;
                }
            }
            if ($onFlag > 0) {
                DB::table('finascop_branch')->where('br_ID', $branch->br_ID)->update(array('br_SalesOnline' => 1, 'br_SalesOffline' => 0));
            } else {
                DB::table('finascop_branch')->where('br_ID', $branch->br_ID)->update(array('br_SalesOnline' => 0, 'br_SalesOffline' => 1));
            }
        }
    }

    protected function updateOffBranchTimings() {
        foreach ($this->timedBranches as $branch) {

            if ($branch->br_SalesOnline == 1 && $branch->br_SalesOffline == 0) {
                $brTimingss = DB::table('branch_timings')
                        ->select('br_open_time', 'br_close_time')
                        ->orderBy('br_open_time', 'asc')
                        ->where('branch_id', '=', $branch->br_ID)
                        ->get();
                foreach ($brTimingss as $brTiming) {
                    if (($brTiming->br_open_time < now()->format('H:i:s')) && ($brTiming->br_close_time > now()->format('H:i:s'))) {
                        DB::table('finascop_branch')->where('br_ID', $branch->br_ID)->update(array('br_SalesOnline' => 0, 'br_SalesOffline' => 1));
                        return;
                    }
                }
            }
        }
        return;
    }

}
