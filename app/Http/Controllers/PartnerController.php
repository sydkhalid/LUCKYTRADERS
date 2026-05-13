<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partners\StorePartnerRequest;
use App\Http\Requests\Partners\StorePartnerTransactionRequest;
use App\Models\Ledger;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Services\PartnerPostingService;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::orderBy('name')->paginate(20);
        $activeCount = Partner::active()->count();
        $totalShare = Partner::active()->sum('share_percentage');
        $totalInvestment = Partner::active()->sum('current_investment');

        return view('partners.index', compact('partners', 'activeCount', 'totalShare', 'totalInvestment'));
    }

    public function create()
    {
        return view('partners.create');
    }

    public function store(StorePartnerRequest $request, PartnerPostingService $postingService)
    {
        $partner = $postingService->createPartner($request->validated());

        return redirect()
            ->route('partners.show', $partner)
            ->with('success', 'Partner '.$partner->name.' created successfully.');
    }

    public function show(Partner $partner)
    {
        $ledgers = Ledger::where('party_type', 'partner')
            ->where('party_id', $partner->id)
            ->orderBy('ledger_date')
            ->orderBy('id')
            ->paginate(20);

        $transactions = $partner->transactions()
            ->latest('transaction_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('partners.show', compact('partner', 'ledgers', 'transactions'));
    }

    public function createInvestment(Partner $partner)
    {
        return view('partners.transaction-create', [
            'partner' => $partner,
            'transactionType' => 'investment',
            'title' => 'Add Investment',
        ]);
    }

    public function createWithdrawal(Partner $partner)
    {
        return view('partners.transaction-create', [
            'partner' => $partner,
            'transactionType' => 'withdrawal',
            'title' => 'Add Withdrawal',
        ]);
    }

    public function createTransaction(Request $request, Partner $partner)
    {
        $transactionType = $request->query('transaction_type', 'investment');

        if (! array_key_exists($transactionType, PartnerTransaction::TYPES)) {
            abort(404);
        }

        return view('partners.transaction-create', [
            'partner' => $partner,
            'transactionType' => $transactionType,
            'title' => PartnerTransaction::TYPES[$transactionType],
        ]);
    }

    public function storeTransaction(StorePartnerTransactionRequest $request, Partner $partner, PartnerPostingService $postingService)
    {
        $postingService->recordTransaction($partner, $request->validated());

        return redirect()
            ->route('partners.show', $partner)
            ->with('success', 'Partner transaction saved successfully.');
    }

    public function profitShareReport(Request $request, PartnerPostingService $postingService)
    {
        $data = $request->validate([
            'profit_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $profitAmount = round((float) ($data['profit_amount'] ?? 0), 2);
        $rows = $postingService->profitShareRows($profitAmount);
        $totalShareAmount = $rows->sum('share_amount');
        $totalSharePercentage = $rows->sum(fn (array $row) => (float) $row['partner']->share_percentage);

        return view('partners.profit-share', compact('rows', 'profitAmount', 'totalShareAmount', 'totalSharePercentage'));
    }
}
