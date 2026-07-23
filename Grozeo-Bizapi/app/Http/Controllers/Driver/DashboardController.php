<?php
namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Driver\DashboardRepository;

class DashboardController extends Controller
{
    protected $dashRepo;
    public function __construct(DashboardRepository $dashRepo)
    {
        $this->dashRepo = $dashRepo;
    }

    /**
     * Dashboard API
     * @return array
    */
    public function dashboardDetails()
    {
        return $this->dashRepo->getDashboardData();
    }
}
