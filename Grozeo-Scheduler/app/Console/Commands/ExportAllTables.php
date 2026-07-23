<?php

namespace App\Console\Commands;

use App\Models\ESInventory;
use Illuminate\Console\Command;
use Elasticsearch;

class ExportAllTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:export-all-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all tables to Elastic Search';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $ESInventory = \App\Models\ESInventory::export();
        $ESBlockedItems = \App\Models\ESBlockedItems::export();
        $ESItemmaster = \App\Models\ESItemmaster::export();
        
    }
}
