<?php

namespace Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use Modules\Sistema\Services\DashboardStatsService;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatsService $stats) {}

    public function index()
    {
        return view('sistema::dashboard', [
            'stats' => $this->stats->para(auth()->user()),
        ]);
    }
}
