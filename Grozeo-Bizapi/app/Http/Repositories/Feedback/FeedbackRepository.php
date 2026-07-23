<?php

namespace App\Http\Repositories\Feedback;

use App\Models\Feedback;

class FeedbackRepository
{
    protected $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function create(array $data)
    {
        $storegroupid = getHeaderStoreGroup();

        $data['customer_id'] = auth()->user()->cust_id;
        $data['storegroup_id'] = $storegroupid;

        $this->feedback->create($data);
    }
}
