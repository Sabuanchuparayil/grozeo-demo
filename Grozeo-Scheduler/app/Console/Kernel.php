<?php

namespace App\Console;
use App\Schedulers\{
    AssignOrder,
    RemoveBlockedItems,
    ReAssignOrder,
    BoyStatusChecker,
    OrderStatusUpdate,
    BranchStatusUpdate,
    MerchantSettlements,
    FinanceTransaction,
    ConsignmentTrackingUpdate,
    CreateShippingConsignment,
    CheckOrderTimeout,
    CheckOrderFailed,
    CreateExpressConsignment,
    ExpressTrackingUpdate,
    CreatePacking,
    CustomerOrderRefunds,
    PartnerDeliveryStartedCheck,
    PartnerDeliveryCompletedCheck,
    InventoryUpdate
};
use App\Schedulers\Drivers\{
    ResponsePolls,
    RescheduleDelivery,
    RescheduleBookings,
    ValidateLiveDrivers,
    ScheduleNewBookings
};
use App\Schedulers\Supports\{
    PackingDelayCalls,
    PackingDelayManualCalls
};
use App\Schedulers\ScheduledDelays\{
    /* PackingNotStartedDelay,
    PackingNotCompletedDelay,
    DeliveryNotStartedDelay,
    DeliveryNotCompletedDelay, */
    DelayedActions\DelayedAPICancellations,
    DelayedActions\DelayedMerchantCancellations
};
use Illuminate\Console\Scheduling\Schedule;
use App\Schedulers\RelationOfficer\ContactToLead;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Schedulers\PostingScheduler\Postings\AutoPostingNew;

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
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(new AssignOrder)->name('AssignOrder')->withoutOverlapping();
        // $schedule->call(new ReAssignOrder)->name('ReAssignOrder')->withoutOverlapping();
        //$schedule->call(new BoyStatusChecker)->name('BoyStatusChecker')->withoutOverlapping();
        $schedule->call(new RemoveBlockedItems)->name('RemoveBlockedItems')->withoutOverlapping();
        $schedule->call(new OrderStatusUpdate)->name('OrderStatusUpdate')->withoutOverlapping();
        $schedule->call(new BranchStatusUpdate)->name('BranchStatusUpdate')->cron('*/3 * * * *')->withoutOverlapping();
        //$schedule->call(new InventoryUpdate)->name('InventoryUpdate')->withoutOverlapping();
       
        
        //merchant settlements
        $schedule->call(new MerchantSettlements)->name('MerchantSettlements')->dailyAt('00:02')->withoutOverlapping();

        //merchant settlements ro finance transaction
        $schedule->call(new FinanceTransaction)->name('FinanceTransaction')->dailyAt('13:02')->withoutOverlapping();

        //consignment tracking update
        $schedule->call(new ConsignmentTrackingUpdate)->name('ConsignmentTrackingUpdate')->cron('0 */2 * * *');

        //create new shipping consignment
        $schedule->call(new CreateShippingConsignment)->name('CreateShippingConsignment')->withoutOverlapping();

        //create express consignment
        $schedule->call(new CreateExpressConsignment)->name('CreateExpressConsignment')->withoutOverlapping();
        //express delivery status update
        // $schedule->call(new ExpressTrackingUpdate)->name('ExpressTrackingUpdate')->cron('0 */1 * * *');
        // express tracking by delay
        $schedule->call(new PartnerDeliveryStartedCheck)->name('PartnerDeliveryStartedCheck')->cron('*/30 * * * *')->withoutOverlapping();
        $schedule->call(new PartnerDeliveryCompletedCheck)->name('PartnerDeliveryCompletedCheck')->cron('*/30 * * * *')->withoutOverlapping();
        
        //create packing
        $schedule->call(new CreatePacking)->name('CreatePacking')->withoutOverlapping();
        
        //customer order refunds
        $schedule->call(new CustomerOrderRefunds)->name('CustomerOrderRefunds')->withoutOverlapping();



        //convert crm contact to lead by area
        $schedule->call(new ContactToLead)->name('ContactToLead')->withoutOverlapping();

        //scheduler for autoposting and costdistribution
        $schedule->call(new AutoPostingNew)->name('AutoPostingNew')->withoutOverlapping();

        // scheduled delay orders
        // $schedule->call(new PackingNotStartedDelay)->name('PackingNotStartedDelay')->cron('*/5 * * * *')->withoutOverlapping();
        // $schedule->call(new PackingNotCompletedDelay)->name('PackingNotCompletedDelay')->cron('*/5 * * * *')->withoutOverlapping();
        // $schedule->call(new DeliveryNotStartedDelay)->name('DeliveryNotStartedDelay')->cron('*/5 * * * *')->withoutOverlapping();
        // $schedule->call(new DeliveryNotCompletedDelay)->name('DeliveryNotCompletedDelay')->cron('*/5 * * * *')->withoutOverlapping();
        // Merchant Cancellation Delay
        $schedule->call(new DelayedMerchantCancellations)->name('DelayedMerchantCancellations')->withoutOverlapping();
        // Thirdparty Delivery Cancellation Delay
        $schedule->call(new DelayedAPICancellations)->name('DelayedAPICancellations')->withoutOverlapping();

        //packing delay ivr calls
        $schedule->call(new PackingDelayCalls)->name('PackingDelayCalls')->cron('*/20 * * * *')->withoutOverlapping();

        //packing delay outbound calls
        $schedule->call(new PackingDelayManualCalls)->name('PackingDelayManualCalls')->cron('*/10 * * * *')->withoutOverlapping();

        // check and set timout to orders
        $schedule->call(new CheckOrderTimeout)->name('CheckOrderTimeout')->withoutOverlapping();

        // check and set failed status to timedout orders
        $schedule->call(new CheckOrderFailed)->name('CheckOrderFailed')->withoutOverlapping();
        
        // drive schedulers
        $schedule->call(new ValidateLiveDrivers)->name('ValidateLiveDrivers')->withoutOverlapping();
        $schedule->call(new ScheduleNewBookings)->name('ScheduleNewBookings')->withoutOverlapping();
        $schedule->call(new ResponsePolls)->name('ResponsePolls')->withoutOverlapping();
        $schedule->call(new RescheduleBookings)->name('RescheduleBookings')->withoutOverlapping();
        $schedule->call(new RescheduleDelivery)->name('RescheduleDelivery')->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
