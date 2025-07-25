<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Dra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;


class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        $userCentreId = $user->id_centre; // assuming user has `id_centre` column
        $month = $request->input('month'); // keep month filter

        // Only get DRAs for the user's centre
        $query = Dra::where('id_centre', $userCentreId);

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        // DRAs per month
        $draStats = $query->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        foreach ($draStats as $stat) {
            $labels[] = \Carbon\Carbon::create()->month($stat->month)->format('M');
            $data[] = $stat->total;
        }

        // DRAs count and amount for the user's centre (optional: can be combined into one query)
        $centreQuery = Dra::where('id_centre', $userCentreId);

        if ($month) {
            $centreQuery->whereMonth('created_at', $month);
        }

        $draCountByCentre = $centreQuery->select('id_centre', DB::raw('COUNT(*) as total'))
            ->groupBy('id_centre')
            ->with('centre:id_centre,adresse_centre')
            ->get()
            ->map(function ($dra) {
                return [
                    'name' => $dra->centre->adresse_centre ?? 'Unknown',
                    'total' => $dra->total,
                ];
            });

        $draAmountByCentre = $centreQuery->select('id_centre', DB::raw('SUM(total_dra) as total'))
            ->groupBy('id_centre')
            ->with('centre:id_centre,adresse_centre')
            ->get()
            ->map(function ($dra) {
                return [
                    'name' => $dra->centre->adresse_centre ?? 'Unknown',
                    'total' => (float)$dra->total,
                ];
            });

        $draAmountByMonthStats = $query->selectRaw('MONTH(created_at) as month, SUM(total_dra) as total')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        $draAmountByMonth = $draAmountByMonthStats->map(function ($item) {
            return [
                'month' => \Carbon\Carbon::create()->month($item->month)->format('M'),
                'total' => (float) $item->total,
            ];
        });


        $months = collect(range(1, 12))
            ->map(fn($m) => [
                'value' => $m,
                'label' => \Carbon\Carbon::create()->month($m)->format('F'),
            ])->toArray();

        return Inertia::render('Dashboard', [
            'draChart' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'draCountByCentre' => $draCountByCentre,
            'draAmountByCentre' => $draAmountByCentre,
            'draAmountByMonth' => $draAmountByMonth,
            'months' => $months,
            'selectedMonth' => $month ? (int)$month : null,
        ]);
    }




}
