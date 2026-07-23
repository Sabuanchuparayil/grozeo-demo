<?php

namespace App\Schedulers;

use App\Models\{
    BlockedItems,
    ProcessLock
};
use Illuminate\Console\Command;

class RemoveBlockedItems extends Command
{
    public function __invoke()
    {
        try
        {
            BlockedItems::where('expiry','<', now())
            ->where('markedfordelivery', 0)
            ->delete();
            ProcessLock::updateColData("BizAPI_RemoveBlockedItems", 0);
        }
        catch (\Exception $e)
        {
            info("ReAssignOrder ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_RemoveBlockedItems", 0);
        }
    }
}
