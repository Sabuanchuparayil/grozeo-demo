<?php

namespace App\Http\Repositories\SearchHistory;

use App\Models\SearchHistory;

class SearchHistoryRepository
{
    protected $searchHistory;

    public function __construct(SearchHistory $searchHistory)
    {
        $this->searchHistory = $searchHistory;
    }

    public function get()
    {
        return $this->searchHistory
                    ->select('search_term')
                    ->distinct()
                    ->orderBy('count', 'desc')
                    ->limit(20)
                    ->get()
                    ->pluck('search_term');
    }

    public function getRecent()
    {
        return $this->searchHistory
                    ->select('search_term')
                    ->where('customer_id', auth_user()->cust_id)
                    ->latest()
                    ->limit(20)
                    ->get()
                    ->pluck('search_term');
    }
}
