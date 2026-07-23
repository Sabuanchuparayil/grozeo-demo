<?php

namespace BackOffice\Tasks;



use App\Models\BlockedItems;
use Illuminate\Console\Command;

class RemoveBlockedItems extends Command
{
    public function __invoke()
    {
        BlockedItems::where('expiry','<', now())
                      ->where('markedfordelivery', 0)
                      ->delete();
    }
}
