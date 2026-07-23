<?php

namespace App\Http\Repositories\Advertisement;

use App\Models\Advertisement;

class AdvertisementRepository
{
    protected $advertisement;

    public function __construct(Advertisement $advertisement)
    {
        $this->advertisement = $advertisement;
    }

    /**
     * Fetch all active advertisement and offers
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->advertisement
                    ->where('adv_status', 'active')
                    ->get()
                    ->groupBy('adv_type');
    }
}
