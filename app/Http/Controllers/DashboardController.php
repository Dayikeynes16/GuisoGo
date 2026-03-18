<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly StatisticsService $statistics) {}

    public function index(Request $request): Response
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $restaurant = $user->load('restaurant')->restaurant;
        $allowedBranches = $user->allowedBranchIds();

        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : today()->startOfDay();
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : today()->endOfDay();

        // Parse status filter (comma-separated string → array or null).
        $statuses = null;
        if ($request->filled('status')) {
            $valid = ['received', 'preparing', 'on_the_way', 'delivered', 'cancelled'];
            $statuses = array_values(array_intersect(explode(',', $request->input('status')), $valid));
            if (empty($statuses)) {
                $statuses = null;
            }
        }

        $minAmount = $request->filled('min_amount') ? (float) $request->input('min_amount') : null;
        $maxAmount = $request->filled('max_amount') ? (float) $request->input('max_amount') : null;

        $data = $this->statistics->getDashboardData($restaurant, $from, $to, $allowedBranches, $statuses, $minAmount, $maxAmount);

        if ($user->isOperator()) {
            unset($data['net_profit'], $data['revenue']);
        }

        $data['filters'] = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'status' => $request->input('status', ''),
            'min_amount' => $request->input('min_amount', ''),
            'max_amount' => $request->input('max_amount', ''),
        ];
        $data['orders_limit_start'] = $restaurant->orders_limit_start?->toDateString();
        $data['orders_limit_end'] = $restaurant->orders_limit_end?->toDateString();

        return Inertia::render('Dashboard/Index', $data);
    }
}
