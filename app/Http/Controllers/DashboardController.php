<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(Request $request): Response
    {
        [$period, $dateFrom, $dateTo] = $this->resolveRange($request);

        return Inertia::render('Dashboard', [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $this->dashboard->summary($dateFrom, $dateTo),
            'salesTrend' => $this->dashboard->salesTrend($dateFrom, $dateTo),
            'profitTrend' => $this->dashboard->profitTrend($dateFrom, $dateTo),
        ]);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveRange(Request $request): array
    {
        $period = $request->string('period')->toString() ?: 'month';
        $now = now();

        return match ($period) {
            'today' => ['today', $now->toDateString(), $now->toDateString()],
            'week' => ['week', $now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'year' => ['year', $now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'custom' => [
                'custom',
                $request->string('date_from')->toString() ?: $now->copy()->startOfMonth()->toDateString(),
                $request->string('date_to')->toString() ?: $now->toDateString(),
            ],
            default => ['month', $now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        };
    }
}
