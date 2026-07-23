<?php

namespace App\Http\Repositories\About;

use App\About\Faq;
use App\About\About;
use App\About\Feedback;
use App\About\CallCenter;
use Illuminate\Support\Facades\DB;

class AboutRepository
{

    public function store($request)
    {
        $storegroupid = getHeaderStoreGroup();
        $request['storegroup_id'] = $storegroupid;
        $request['fb_createdOn'] = now();
        return Feedback::create($request);
    }

    public function get()
    {
        $faq = Faq::where('faq_status', 1)
                    ->select('faq_id', 'faq_title', 'faq_description', 'faq_status')
                    ->get();
        $toll_number = CallCenter::first();
        return [
            "faq" => $faq,
            "toll_free_number" => $toll_number->tnumber ?? '',
        ];
    }

    public function getPages($pagetype=-1)
    {
        $storegroupid = getHeaderStoreGroup();
	return DB::select('SELECT t.`page_id`, t.`page_name`, t.`page_slug`, t.`page_content`, t.`page_status`, t.`page_type` 
FROM app_pages t JOIN (
    SELECT page_type, MAX(storegroup_id) Maxdatetime
    FROM app_pages
    WHERE `page_status` = 1 AND (`storegroup_id` = 0 OR `storegroup_id` = '.$storegroupid.')
    GROUP BY page_type
) r ON t.page_type = r.page_type AND t.storegroup_id = r.Maxdatetime
WHERE t.`page_status` = 1 AND (`storegroup_id` = 0 OR `storegroup_id` = '.$storegroupid.')
ORDER BY t.storegroup_id DESC');

/*
        return About::where('page_status', 1)
                    ->select('page_id', 'page_name', 'page_slug', 'page_content', 'page_status', 'page_type')
                    ->where(function($query) use ($storegroupid){
                        $query->where('storegroup_id', 0);
                        if($storegroupid > 0)
                        	$query->orWhere('storegroup_id', $storegroupid);
                })->groupByRaw('page_type desc')
                ->get();
*/

    }

}
