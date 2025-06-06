<?php
namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Dra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DirectionDashboardController extends Controller
{
    public function index(Request $request)
    {
        $id_centre = $request->input('id_centre');
        $month = $request->input('month'); // new month filter

        $query = Dra::query();

        if ($id_centre) {
            $query->where('id_centre', $id_centre);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        // DRAs per month (filtered by centre and/or month ignored here because this is the monthly summary)
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

        // DRAs per centre filtered by selected month if any
        $centreQuery = Dra::query();

        if ($month) {
            $centreQuery->whereMonth('created_at', $month);
        }

        if ($id_centre) {
            $centreQuery->where('id_centre', $id_centre);
        }

        // 1. DRAs count per centre
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

// 2. Total DRA amount per centre
        $draAmountByCentre = $centreQuery->select('id_centre', DB::raw('SUM(total_dra) as total'))
            ->groupBy('id_centre')
            ->with('centre:id_centre,adresse_centre')
            ->get()
            ->map(function ($dra) {
                return [
                    'name' => $dra->centre->adresse_centre ?? 'Unknown',
                    'total' => (float) $dra->total,
                ];
            });



        // Pass months list to Vue for filter dropdown (1 to 12)
        $months = collect(range(1, 12))
            ->map(fn($m) => [
                'value' => $m,
                'label' => \Carbon\Carbon::create()->month($m)->format('F'),
            ])->toArray();

        return Inertia::render('DirectionDashboard', [
            'draChart' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'draCountByCentre' => $draCountByCentre,
            'draAmountByCentre' => $draAmountByCentre,
            'centres' => Centre::select('id_centre', 'adresse_centre')->get(),
            'selectedCentre' => $id_centre ? (string) $id_centre : null,
            'months' => $months,
            'selectedMonth' => $month ? (int) $month : null,
        ]);
    }

}
