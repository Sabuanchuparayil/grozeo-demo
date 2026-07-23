<?php

namespace App\Console;

use BackOffice\Tasks\AssignOrder;
use BackOffice\Tasks\RemoveBlockedItems;
use BackOffice\Tasks\ClearCpdOrder;
use BackOffice\Tasks\ReAssignOrder;
use BackOffice\Tasks\BoyStatusChecker;
use BackOffice\Tasks\GenerateCpdOrders;
use BackOffice\Tasks\OrderStatusUpdate;
use BackOffice\Tasks\BranchStatusUpdate;
use BackOffice\Tasks\MerchantSettlements;
use BackOffice\Tasks\FinanceTransaction;
use BackOffice\Tasks\ConsignmentTrackingUpdate;
use BackOffice\Tasks\CreateShippingConsignment;
use BackOffice\Tasks\RelationOfficer\ContactToLead;
use BackOffice\Tasks\PostingScheduler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('es:export-all-tables')->everyFiveMinutes();

       //$schedule->call(new GenerateCpdOrders)->daily();

       $schedule->call(new AssignOrder)->name('AssignOrder')->withoutOverlapping();

        $schedule->call(new ReAssignOrder)->name('ReAssignOrder')->withoutOverlapping();

        //$schedule->call(new ClearCpdOrder)->everyMinute();

        $schedule->call(new BoyStatusChecker)->name('BoyStatusChecker')->withoutOverlapping();

        $schedule->call(new RemoveBlockedItems)->name('RemoveBlockedItems')->withoutOverlapping();
        
        $schedule->call(new OrderStatusUpdate)->name('OrderStatusUpdate')->withoutOverlapping();
        
        $schedule->call(new BranchStatusUpdate)->name('BranchStatusUpdate')->withoutOverlapping();

        //merchant settlements
        $schedule->call(new MerchantSettlements)->name('MerchantSettlements')->dailyAt('00:02');
        //merchant settlements ro finance transaction
        $schedule->call(new FinanceTransaction)->name('FinanceTransaction')->dailyAt('13:02');
        //consignment tracking update
        $schedule->call(new ConsignmentTrackingUpdate)->name('ConsignmentTrackingUpdate')->withoutOverlapping();
        //create new shipping consignment
        $schedule->call(new CreateShippingConsignment)->name('CreateShippingConsignment')->withoutOverlapping();
        //convert crm contact to lead by area
        $schedule->call(new ContactToLead)->name('ContactToLead')->withoutOverlapping();
        
        //scheduler for autoposting and costdistribution
        $schedule->call(new PostingScheduler)->name('PostingScheduler')->withoutOverlapping();


        //$schedule->command('command:expire')->everyMinute();
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
