<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\ProductSize;
use Carbon\Carbon;

class ProfitPredictionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Target Date Configuration (Default: 30 days from today)
        $targetDateInput = $request->input('target_date', Carbon::today()->addDays(30)->format('Y-m-d'));
        $targetDate = Carbon::parse($targetDateInput);
        if ($targetDate->isPast() && !$targetDate->isToday()) {
            $targetDate = Carbon::today()->addDays(30);
        }

        $daysToTarget = max(1, Carbon::today()->diffInDays($targetDate, false));
        if ($daysToTarget < 1) {
            $daysToTarget = 1;
        }

        // 2. Restock Bundle Parameters (Defaults based on user business model)
        $bundleWeightKg = (float) $request->input('bundle_weight_kg', 80);        // 80 kg
        $bundleRatePerKg = (float) $request->input('bundle_rate_per_kg', 340);    // ₹340 per kg
        $bundleYieldPcs = (int) $request->input('bundle_yield_pcs', 380);          // ~380 pcs per bundle

        $bundleCost = $bundleWeightKg * $bundleRatePerKg;                          // 80 * 340 = ₹27,200

        // 3. Historical Data Analysis (Last 30 days window for sales velocity)
        $historyDays = 30;
        $historyStartDate = Carbon::today()->subDays($historyDays);

        $historicalOrders = Order::whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->where('created_at', '>=', $historyStartDate)
            ->get();

        $historicalOrderIds = $historicalOrders->pluck('id');
        $historicalSoldPcs = OrderItem::whereIn('order_id', $historicalOrderIds)->sum('quantity');
        $historicalGrossSales = $historicalOrders->sum('grand_total');

        $historicalExpensesTotal = Expense::where('created_at', '>=', $historyStartDate)->sum('amount');

        // Calculate Daily Velocities
        $historicalDaysCount = max(1, $historyStartDate->diffInDays(Carbon::today()));
        
        $avgDailyOrders = count($historicalOrders) > 0 ? (count($historicalOrders) / $historyDays) : 3;
        $avgDailyPieces = $historicalSoldPcs > 0 ? ($historicalSoldPcs / $historyDays) : 10;
        $avgDailySalesRevenue = $historicalGrossSales > 0 ? ($historicalGrossSales / $historyDays) : 3500;
        $avgDailyExpense = $historicalExpensesTotal > 0 ? ($historicalExpensesTotal / $historyDays) : 250;
        $avgPricePerPiece = $avgDailyPieces > 0 ? ($avgDailySalesRevenue / $avgDailyPieces) : 350;

        // User Overrides (if manual custom velocity is provided)
        $customDailyPieces = $request->filled('manual_daily_pieces') ? (float) $request->input('manual_daily_pieces') : null;
        $customDailyExpense = $request->filled('manual_daily_expense') ? (float) $request->input('manual_daily_expense') : null;
        $customPricePerPiece = $request->filled('manual_price_per_piece') ? (float) $request->input('manual_price_per_piece') : null;

        $projectedDailyPieces = $customDailyPieces ?? $avgDailyPieces;
        $projectedDailyExpense = $customDailyExpense ?? $avgDailyExpense;
        $projectedPricePerPiece = $customPricePerPiece ?? $avgPricePerPiece;

        // 4. Current Available Stock & Reserved Stock in Shop
        $totalCurrentAvailableStock = ProductSize::sum('stock');
        
        // 5. Future Projection Calculations
        $projectedTotalPiecesSold = ceil($projectedDailyPieces * $daysToTarget);
        $projectedTotalOrders = ceil(($avgDailyOrders / max(1, $avgDailyPieces)) * $projectedTotalPiecesSold);
        $projectedGrossRevenue = $projectedTotalPiecesSold * $projectedPricePerPiece;

        // Bundle Restock Trigger Calculation
        // How many pieces need to be covered beyond current stock?
        $netPiecesNeeded = max(0, $projectedTotalPiecesSold - $totalCurrentAvailableStock);
        $bundlesRequired = ceil($netPiecesNeeded / max(1, $bundleYieldPcs));
        $totalRestockExpense = $bundlesRequired * $bundleCost;

        // Daily Operational Expenses up to target date
        $totalProjectedDailyExpenses = $projectedDailyExpense * $daysToTarget;

        // Total Expenses & Net Profit
        $totalProjectedExpenses = $totalRestockExpense + $totalProjectedDailyExpenses;
        $projectedNetProfit = $projectedGrossRevenue - $totalProjectedExpenses;
        $profitMarginPercent = $projectedGrossRevenue > 0 ? ($projectedNetProfit / $projectedGrossRevenue) * 100 : 0;

        // 6. Restock Milestones Projection Timeline
        $milestones = [];
        $accumulatedPieces = $totalCurrentAvailableStock;
        $currentSimDate = Carbon::today();
        
        if ($projectedDailyPieces > 0) {
            $daysUntilFirstRestock = max(0, floor($totalCurrentAvailableStock / $projectedDailyPieces));
            $firstRestockDate = Carbon::today()->addDays($daysUntilFirstRestock);

            for ($i = 1; $i <= max(1, $bundlesRequired); $i++) {
                $daysForThisBundle = ceil(($i * $bundleYieldPcs + $totalCurrentAvailableStock) / $projectedDailyPieces);
                if ($daysForThisBundle <= $daysToTarget) {
                    $milestones[] = [
                        'bundle_num' => $i,
                        'estimated_date' => Carbon::today()->addDays($daysForThisBundle)->format('d M Y (D)'),
                        'days_from_now' => $daysForThisBundle,
                        'pieces_sold_at_trigger' => $i * $bundleYieldPcs + $totalCurrentAvailableStock,
                        'cost' => $bundleCost,
                    ];
                }
            }
        }

        return view('admin.profit_prediction.index', compact(
            'targetDateInput',
            'targetDate',
            'daysToTarget',
            'bundleWeightKg',
            'bundleRatePerKg',
            'bundleYieldPcs',
            'bundleCost',
            'historyDays',
            'avgDailyOrders',
            'avgDailyPieces',
            'avgDailySalesRevenue',
            'avgDailyExpense',
            'avgPricePerPiece',
            'projectedDailyPieces',
            'projectedDailyExpense',
            'projectedPricePerPiece',
            'totalCurrentAvailableStock',
            'projectedTotalPiecesSold',
            'projectedTotalOrders',
            'projectedGrossRevenue',
            'bundlesRequired',
            'totalRestockExpense',
            'totalProjectedDailyExpenses',
            'totalProjectedExpenses',
            'projectedNetProfit',
            'profitMarginPercent',
            'milestones'
        ));
    }
}
