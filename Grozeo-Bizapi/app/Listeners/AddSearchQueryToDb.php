<?php

namespace App\Listeners;

use App\Events\Searched;
use App\Models\SearchHistory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddSearchQueryToDb
{
    protected $searchHistory;
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(SearchHistory $searchHistory)
    {
        $this->searchHistory = $searchHistory;
    }

    /**
     * Handle the event.
     *
     * @param  Searched  $event
     * @return void
     */
    public function handle(Searched $event)
    {
        if (strlen($event->keyword <= 255)) {
            $this->saveSearch($event->keyword);
        }
    }

    protected function saveSearch($keyword)
    {
        $search = $this->searchHistory
                       ->firstOrNew([
                            'search_term' => $keyword,
                            'customer_id' => auth_user()->cust_id
                       ]);

        $search->count = $search->count ? $search->count + 1 : 1;
        $search->save(); 
    }
}
