<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    private const DEVELOPER_LOTS = [
        'CRESCENT',
        'PRIMEWATER INFRASTRUCTURE CORP. (P1)',
        'SYMMETRICAL VENTURES, INC.',
        'CROWN ASIA PROPERTIES, INC.',
        'PS BANK (CROWN ASIA PROPERTIES, INC)',
        'FLORA P. CAVILES',
        'LAND BANK OF THE PHILS.',
    ];

    public function index()
    {
        return view('analytics.dashboard', [
            'developerLots' => self::DEVELOPER_LOTS,
        ]);
    }

    public function getKpis(Request $request)
    {
        try {
            $year  = (int) $request->get('year',  now()->year);
            $month = (int) $request->get('month', now()->month);

            $totalCollection = DB::table('vw_acct_receivable')
                ->whereYear('ar_date', $year)
                ->whereMonth('ar_date', $month)
                ->where('ar_amount', '>', 0)
                ->sum('ar_amount');

            $expenseSub = DB::table('vw_acct_payable')
                ->whereYear('ap_date', $year)
                ->whereMonth('ap_date', $month)
                ->select('ap_voucherno', DB::raw('MAX(ap_total) as voucher_total'))
                ->groupBy('ap_voucherno');

            $totalExpenses = DB::query()->fromSub($expenseSub, 't')->sum('voucher_total');

            $activeMembers = DB::table('vw_member_data')
                ->where('hoa_status', 'ACTIVE')
                ->whereNotIn('mem_name', self::DEVELOPER_LOTS)
                ->count();

            $totalLots = DB::table('vw_member_data')
                ->whereNotIn('mem_name', self::DEVELOPER_LOTS)
                ->count();

            Log::info('[Analytics] Total Lots', ['total_lots' => $totalLots]);

            $paidLots = DB::table('acct_receivable as ar')
                ->join('charts_of_account as coa', 'ar.acct_type_id', '=', 'coa.acct_type_id')
                ->join('vw_member_data as vmd', 'ar.mem_id', '=', 'vmd.mem_id')
                ->where('coa.acct_description', 'Association Dues')
                ->whereYear('ar.ar_date', $year)
                ->whereMonth('ar.ar_date', $month)
                ->where('ar.ar_amount', '>', 0)
                ->whereNotIn('vmd.mem_name', self::DEVELOPER_LOTS)
                ->distinct()
                ->count('ar.payor_address');

            Log::info('[Analytics] Paid Lots Count', ['paid_lots' => $paidLots, 'month' => $month, 'year' => $year]);

            $collectionRate = $totalLots > 0
                ? round(($paidLots / $totalLots) * 100, 1)
                : 0;

            $payload = [
                'total_collection' => (float) $totalCollection,
                'total_expenses'   => (float) $totalExpenses,
                'active_members'   => (int) $activeMembers,
                'collection_rate'  => $collectionRate,
            ];

            Log::info('[Analytics] KPIs', array_merge($payload, [
                'filter_month'               => $month,
                'filter_year'                => $year,
                'collection_rate_paid_lots'  => $paidLots,
                'collection_rate_total_lots' => $totalLots,
            ]));

            return response()->json($payload);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load KPI data'], 500);
        }
    }

    public function getMonthlyTrend(Request $request)
    {
        try {
            $mode = $request->get('mode', 'rolling');
            $year = (int) $request->get('year', now()->year);

            $start = $mode === 'year'
                ? Carbon::createFromDate($year, 1, 1)->startOfMonth()
                : Carbon::now()->startOfMonth()->subMonths(11);

            $incomeMap = DB::table('vw_acct_receivable')
                ->where('ar_amount', '>', 0)
                ->where('ar_date', '>=', $start->toDateString())
                ->select(DB::raw("DATE_FORMAT(ar_date, '%Y-%m') as ym"), DB::raw('SUM(ar_amount) as total'))
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $expenseSub = DB::table('vw_acct_payable')
                ->where('ap_date', '>=', $start->toDateString())
                ->select('ap_voucherno', DB::raw("DATE_FORMAT(ap_date, '%Y-%m') as ym"), DB::raw('MAX(ap_total) as voucher_total'))
                ->groupBy('ap_voucherno', 'ym');

            $expenseMap = DB::query()->fromSub($expenseSub, 't')
                ->select('ym', DB::raw('SUM(voucher_total) as total'))
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $labels   = [];
            $income   = [];
            $expenses = [];

            for ($i = 0; $i < 12; $i++) {
                $month  = $start->copy()->addMonths($i);
                $ym     = $month->format('Y-m');
                $labels[]   = $month->format('M Y');
                $income[]   = (float) ($incomeMap[$ym] ?? 0);
                $expenses[] = (float) ($expenseMap[$ym] ?? 0);
            }

            Log::info('[Analytics] Monthly Trend', [
                'range'    => $labels[0] . ' → ' . $labels[11],
                'income'   => $income,
                'expenses' => $expenses,
            ]);

            return response()->json(compact('labels', 'income', 'expenses'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load monthly trend data'], 500);
        }
    }

    public function getArrearAging()
    {
        try {
            $rows = DB::table('vw_member_data')
                ->where('hoa_status', 'DELINQUENT')
                ->whereNotIn('mem_name', self::DEVELOPER_LOTS)
                ->select(DB::raw('
                    SUM(CASE WHEN arrear_count BETWEEN 3 AND 6  THEN 1 ELSE 0 END) as b1,
                    SUM(CASE WHEN arrear_count BETWEEN 7 AND 12 THEN 1 ELSE 0 END) as b2,
                    SUM(CASE WHEN arrear_count BETWEEN 13 AND 24 THEN 1 ELSE 0 END) as b3,
                    SUM(CASE WHEN arrear_count > 24             THEN 1 ELSE 0 END) as b4
                '))
                ->first();

            $counts = [
                (int) $rows->b1,
                (int) $rows->b2,
                (int) $rows->b3,
                (int) $rows->b4,
            ];

            $total = array_sum($counts);

            $labels = ['3–6 months', '7–12 months', '13–24 months', '24+ months'];

            $buckets = [];
            foreach ($counts as $i => $count) {
                $buckets[] = [
                    'label'   => $labels[$i],
                    'count'   => $count,
                    'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                ];
            }

            Log::info('[Analytics] Arrear Aging', [
                'total_delinquent' => $total,
                'buckets'          => $buckets,
            ]);

            return response()->json([
                'buckets'          => $buckets,
                'total_delinquent' => $total,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load arrear aging data'], 500);
        }
    }

    public function getPermitStats()
    {
        try {
            $stats = DB::table('vw_construction_permit')
                ->select(DB::raw('`Permit Status` as label'), DB::raw('COUNT(*) as count'))
                ->groupBy('Permit Status')
                ->orderByDesc('count')
                ->get()
                ->map(fn($row) => ['label' => $row->label, 'count' => (int) $row->count])
                ->values();

            $total = $stats->sum('count');

            Log::info('[Analytics] Permit Stats', [
                'total' => (int) $total,
                'stats' => $stats->toArray(),
            ]);

            return response()->json([
                'stats' => $stats,
                'total' => (int) $total,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load permit stats'], 500);
        }
    }
}
