<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractualCourier;
use App\Models\Expense;
use App\Models\WalletRecharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractualPostController extends Controller
{
    public function index(Request $request)
    {
        $startDateRaw = $request->get('start_date');
        $endDateRaw = $request->get('end_date');
        $activeTab = $request->get('tab', 'wallet');

        $startDate = $startDateRaw ? $this->parseInputDate($startDateRaw) : null;
        $endDate = $endDateRaw ? $this->parseInputDate($endDateRaw) : null;

        // All-Time Summary Totals
        $totalRecharged = (float) WalletRecharge::sum('amount');
        $totalUsage = (float) ContractualCourier::sum('total_amount');
        $currentWalletBalance = max(0, $totalRecharged - $totalUsage);
        $totalCouriers = (int) ContractualCourier::sum('courier_count');

        // Wallet Recharges Query
        $rechargeQuery = WalletRecharge::with(['expense', 'creator'])->orderBy('date', 'desc')->orderBy('id', 'desc');
        if ($startDate) {
            $rechargeQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $rechargeQuery->whereDate('date', '<=', $endDate);
        }
        $filteredRecharged = (clone $rechargeQuery)->sum('amount');
        $recharges = $rechargeQuery->paginate(15, ['*'], 'recharge_page')->withQueryString();

        // Contractual Couriers Query
        $courierQuery = ContractualCourier::with('creator')->orderBy('date', 'desc')->orderBy('id', 'desc');
        if ($startDate) {
            $courierQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $courierQuery->whereDate('date', '<=', $endDate);
        }
        $filteredUsage = (clone $courierQuery)->sum('total_amount');
        $filteredCouriers = (clone $courierQuery)->sum('courier_count');
        $couriers = $courierQuery->paginate(15, ['*'], 'courier_page')->withQueryString();

        $startDate = $startDateRaw;
        $endDate = $endDateRaw;

        return view('admin.contractual_posts.index', compact(
            'startDate',
            'endDate',
            'activeTab',
            'totalRecharged',
            'totalUsage',
            'currentWalletBalance',
            'totalCouriers',
            'filteredRecharged',
            'filteredUsage',
            'filteredCouriers',
            'recharges',
            'couriers'
        ));
    }

    private function parseInputDate($dateString)
    {
        if (!$dateString) return date('Y-m-d');
        try {
            if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', trim($dateString), $matches)) {
                $day = sprintf('%02d', $matches[1]);
                $month = sprintf('%02d', $matches[2]);
                $year = $matches[3];
                return "{$year}-{$month}-{$day}";
            }
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return date('Y-m-d');
        }
    }

    public function storeRecharge(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required',
            'amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dbDate = $this->parseInputDate($validated['date']);

        DB::transaction(function () use ($validated, $dbDate) {
            // 1. Create corresponding Expense entry
            $expense = Expense::create([
                'title' => 'Contractual Post Wallet Recharge',
                'amount' => $validated['amount'],
                'expense_date' => $dbDate,
                'category' => 'Contractual Post',
                'notes' => $validated['notes'] ?? 'India Post wallet prepaid recharge',
            ]);

            // 2. Create WalletRecharge entry linked to Expense
            WalletRecharge::create([
                'date' => $dbDate,
                'amount' => $validated['amount'],
                'expense_id' => $expense->id,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('admin.contractual-posts.index', ['tab' => 'wallet'])
            ->with('success', 'Wallet recharged by ₹' . number_format($validated['amount'], 2) . ' and recorded in Expenses & P&L successfully!');
    }

    public function updateRecharge(Request $request, WalletRecharge $recharge)
    {
        $validated = $request->validate([
            'date' => 'required',
            'amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dbDate = $this->parseInputDate($validated['date']);
        $newAmount = (float) $validated['amount'];
        $totalRechargedOthers = (float) WalletRecharge::where('id', '!=', $recharge->id)->sum('amount');
        $totalUsageAll = (float) ContractualCourier::sum('total_amount');
        $availableBalanceNew = ($totalRechargedOthers + $newAmount) - $totalUsageAll;

        if ($availableBalanceNew < 0) {
            $requiredMin = $totalUsageAll - $totalRechargedOthers;
            return redirect()->back()
                ->with('error', 'Cannot reduce wallet recharge amount. Existing contractual courier usage requires at least ₹' . number_format($requiredMin, 2) . ' balance.');
        }

        DB::transaction(function () use ($recharge, $validated, $dbDate, $newAmount) {
            // 1. Update WalletRecharge
            $recharge->update([
                'date' => $dbDate,
                'amount' => $newAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            // 2. Sync linked Expense
            if ($recharge->expense) {
                $recharge->expense->update([
                    'amount' => $newAmount,
                    'expense_date' => $dbDate,
                    'notes' => $validated['notes'] ?? 'India Post wallet prepaid recharge',
                ]);
            } else {
                $expense = Expense::create([
                    'title' => 'Contractual Post Wallet Recharge',
                    'amount' => $newAmount,
                    'expense_date' => $dbDate,
                    'category' => 'Contractual Post',
                    'notes' => $validated['notes'] ?? 'India Post wallet prepaid recharge',
                ]);
                $recharge->update(['expense_id' => $expense->id]);
            }
        });

        return redirect()->route('admin.contractual-posts.index', ['tab' => 'wallet'])
            ->with('success', 'Wallet recharge updated successfully and synced with Expenses & P&L.');
    }

    public function destroyRecharge(WalletRecharge $recharge)
    {
        $totalRechargedOthers = (float) WalletRecharge::where('id', '!=', $recharge->id)->sum('amount');
        $totalUsageAll = (float) ContractualCourier::sum('total_amount');

        if ($totalRechargedOthers < $totalUsageAll) {
            return redirect()->back()
                ->with('error', 'Cannot delete wallet recharge. Total contractual courier usage (₹' . number_format($totalUsageAll, 2) . ') exceeds remaining wallet balance.');
        }

        DB::transaction(function () use ($recharge) {
            if ($recharge->expense) {
                $recharge->expense->delete();
            }
            $recharge->delete();
        });

        return redirect()->route('admin.contractual-posts.index', ['tab' => 'wallet'])
            ->with('success', 'Wallet recharge record deleted and removed from Expenses & P&L successfully.');
    }

    public function storeCourier(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required',
            'courier_count' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dbDate = $this->parseInputDate($validated['date']);
        $totalAmount = (float) $validated['total_amount'];
        $totalRecharged = (float) WalletRecharge::sum('amount');
        $totalUsage = (float) ContractualCourier::sum('total_amount');
        $availableWallet = max(0, $totalRecharged - $totalUsage);

        if ($totalAmount > $availableWallet) {
            return redirect()->back()
                ->with('error', 'Insufficient wallet balance. Available balance: ₹' . number_format($availableWallet, 2) . '. Please recharge wallet first.');
        }

        ContractualCourier::create([
            'date' => $dbDate,
            'courier_count' => $validated['courier_count'],
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.contractual-posts.index', ['tab' => 'courier'])
            ->with('success', 'Contractual courier record added successfully! Wallet balance reduced by ₹' . number_format($totalAmount, 2) . '.');
    }

    public function updateCourier(Request $request, ContractualCourier $courier)
    {
        $validated = $request->validate([
            'date' => 'required',
            'courier_count' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dbDate = $this->parseInputDate($validated['date']);
        $newTotalAmount = (float) $validated['total_amount'];
        $totalRecharged = (float) WalletRecharge::sum('amount');
        $totalUsageOthers = (float) ContractualCourier::where('id', '!=', $courier->id)->sum('total_amount');
        $availableWithoutThis = max(0, $totalRecharged - $totalUsageOthers);

        if ($newTotalAmount > $availableWithoutThis) {
            return redirect()->back()
                ->with('error', 'Insufficient wallet balance. Available balance: ₹' . number_format($availableWithoutThis, 2) . '.');
        }

        $courier->update([
            'date' => $dbDate,
            'courier_count' => $validated['courier_count'],
            'total_amount' => $newTotalAmount,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.contractual-posts.index', ['tab' => 'courier'])
            ->with('success', 'Contractual courier record updated successfully.');
    }

    public function destroyCourier(ContractualCourier $courier)
    {
        $restoredAmount = $courier->total_amount;
        $courier->delete();

        return redirect()->route('admin.contractual-posts.index', ['tab' => 'courier'])
            ->with('success', 'Contractual courier record deleted. ₹' . number_format($restoredAmount, 2) . ' returned to wallet balance.');
    }
}
